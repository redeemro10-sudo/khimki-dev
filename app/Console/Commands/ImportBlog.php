<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\ImageService;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class ImportBlog extends Command
{
    protected $signature = 'blog:import
        {file : Path to the JSON file}
        {--post-type=blog : Target post type for imported entries}
        {--category-taxonomy=category : Taxonomy used for category import}
        {--tag-taxonomy=post_tag : Taxonomy used for keyword import}';

    protected $description = 'Import blog posts from JSON into WordPress';

    private ImageService $imageService;

    public function __construct(?ImageService $imageService = null)
    {
        parent::__construct();
        $this->imageService = $imageService ?? new ImageService();
    }

    public function handle(): int
    {
        $file = (string) $this->argument('file');

        if (!is_file($file) || !is_readable($file)) {
            $this->error("JSON file not found or not readable: {$file}");
            return self::FAILURE;
        }

        $payload = json_decode((string) file_get_contents($file), true);

        if (!is_array($payload)) {
            $this->error('Invalid JSON payload.');
            return self::FAILURE;
        }

        $items = $payload['result'] ?? $payload;
        if (!is_array($items) || $items === []) {
            $this->warn('No blog items found in payload.');
            return self::SUCCESS;
        }

        $this->info('Importing blog posts...');

        $created = 0;
        $updated = 0;
        $skipped = 0;

        $bar = $this->output->createProgressBar(count($items));
        $bar->start();

        foreach ($items as $externalId => $item) {
            if (!is_array($item)) {
                $skipped++;
                $bar->advance();
                continue;
            }

            $result = $this->importPost((string) $externalId, $item);

            if ($result === 'created') {
                $created++;
            } elseif ($result === 'updated') {
                $updated++;
            } else {
                $skipped++;
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info("Finished. Created: {$created}, updated: {$updated}, skipped: {$skipped}");

        return self::SUCCESS;
    }

    private function importPost(string $externalId, array $data): string
    {
        $title = trim((string) ($data['title'] ?? ''));
        if ($title === '') {
            $this->warn('Skipped item without title.');
            return 'skipped';
        }

        $postType = (string) $this->option('post-type');
        $slug = $this->buildSlug($title, $externalId);
        $existingId = $this->findExistingPostId($externalId, $slug, $postType);
        $content = (string) ($data['content'] ?? '');
        $contentImageIds = [];

        if ($content !== '') {
            $content = $this->replaceContentImages($content, $contentImageIds);
        }

        $postData = [
            'ID' => $existingId,
            'post_title' => $title,
            'post_name' => $existingId > 0
                ? $slug
                : wp_unique_post_slug($slug, 0, 'publish', $postType, 0),
            'post_content' => $content,
            'post_excerpt' => $this->buildExcerpt($data, $content),
            'post_status' => 'publish',
            'post_type' => $postType,
        ];

        if ($createdAt = $this->resolveDate($data['created'] ?? $data['created_at'] ?? null)) {
            $postData['post_date'] = $createdAt;
            $postData['post_date_gmt'] = get_gmt_from_date($createdAt);
        }

        if ($modifiedAt = $this->resolveDate($data['modified'] ?? $data['updated_at'] ?? null)) {
            $postData['post_modified'] = $modifiedAt;
            $postData['post_modified_gmt'] = get_gmt_from_date($modifiedAt);
        }

        $postId = $existingId > 0 ? wp_update_post($postData, true) : wp_insert_post($postData, true);

        if (is_wp_error($postId)) {
            $this->warn("Failed to import '{$title}': " . $postId->get_error_message());
            return 'skipped';
        }

        update_post_meta($postId, '_import_source_id', $externalId);
        update_post_meta($postId, '_seo_title', (string) ($data['seo_title'] ?? $title));

        $seoDescription = trim((string) ($data['description'] ?? $data['excerpt'] ?? ''));
        if ($seoDescription !== '') {
            update_post_meta($postId, '_seo_description', $seoDescription);
        }

        $seoText = trim((string) ($data['post_text'] ?? $data['seo_text'] ?? ''));
        if ($seoText !== '') {
            update_post_meta($postId, '_post_text', $seoText);
        }

        foreach ($contentImageIds as $attachmentId) {
            wp_update_post([
                'ID' => $attachmentId,
                'post_parent' => $postId,
            ]);
        }

        $this->syncCategory($postId, $data);
        $this->syncTags($postId, $data);
        $this->syncFeaturedImage($postId, $data);

        return $existingId > 0 ? 'updated' : 'created';
    }

    private function replaceContentImages(string $content, array &$attachmentIds): string
    {
        if (!preg_match_all('/<img[^>]+src=["\']([^"\']+)["\']/i', $content, $matches)) {
            return $content;
        }

        foreach (array_unique($matches[1]) as $url) {
            $uuid = md5($url);
            $attachmentId = $this->getAttachmentByUuid($uuid);

            if (!$attachmentId) {
                $attachmentId = $this->imageService->processAndAttach($url, 0, $uuid);
            }

            if (!$attachmentId) {
                continue;
            }

            $localUrl = wp_get_attachment_url($attachmentId);
            if (!$localUrl) {
                continue;
            }

            $content = str_replace($url, $localUrl, $content);
            $attachmentIds[] = $attachmentId;
        }

        return $content;
    }

    private function syncCategory(int $postId, array $data): void
    {
        $taxonomy = (string) $this->option('category-taxonomy');
        if ($taxonomy === '' || !taxonomy_exists($taxonomy)) {
            return;
        }

        $categoryName = trim((string) ($data['category'] ?? ''));
        if ($categoryName === '') {
            wp_set_object_terms($postId, [], $taxonomy, false);
            return;
        }

        $termId = $this->firstOrCreateTerm($categoryName, $taxonomy);
        if ($termId > 0) {
            wp_set_object_terms($postId, [$termId], $taxonomy, false);
        }
    }

    private function syncTags(int $postId, array $data): void
    {
        $taxonomy = (string) $this->option('tag-taxonomy');
        if ($taxonomy === '' || !taxonomy_exists($taxonomy)) {
            return;
        }

        $rawKeywords = (string) ($data['keywords'] ?? '');
        if ($rawKeywords === '') {
            wp_set_object_terms($postId, [], $taxonomy, false);
            return;
        }

        $tagIds = [];
        foreach (explode(',', $rawKeywords) as $rawTag) {
            $tagName = trim(preg_replace('/[\x00-\x1F\x7F\xA0]/u', '', $rawTag) ?? '');
            if ($tagName === '' || !preg_match('/[\p{L}]/u', $tagName)) {
                continue;
            }

            $termId = $this->firstOrCreateTerm($tagName, $taxonomy);
            if ($termId > 0) {
                $tagIds[] = $termId;
            }
        }

        wp_set_object_terms($postId, array_values(array_unique($tagIds)), $taxonomy, false);
    }

    private function syncFeaturedImage(int $postId, array $data): void
    {
        $url = trim((string) ($data['image'] ?? ''));
        if ($url === '') {
            return;
        }

        $uuid = md5($url);
        $attachmentId = $this->getAttachmentByUuid($uuid);

        if (!$attachmentId) {
            $attachmentId = $this->imageService->processAndAttach($url, $postId, $uuid);
        }

        if ($attachmentId) {
            set_post_thumbnail($postId, $attachmentId);
        }
    }

    private function firstOrCreateTerm(string $name, string $taxonomy): int
    {
        $existing = term_exists($name, $taxonomy);
        if ($existing) {
            return (int) (is_array($existing) ? $existing['term_id'] : $existing);
        }

        $slug = Str::slug($this->transliterate($name), '-');
        $inserted = wp_insert_term($name, $taxonomy, [
            'slug' => $slug !== '' ? $slug : sanitize_title($name),
        ]);

        if (is_wp_error($inserted)) {
            return 0;
        }

        return (int) $inserted['term_id'];
    }

    private function findExistingPostId(string $externalId, string $slug, string $postType): int
    {
        if ($externalId !== '') {
            $existing = get_posts([
                'post_type' => $postType,
                'post_status' => 'any',
                'numberposts' => 1,
                'fields' => 'ids',
                'meta_key' => '_import_source_id',
                'meta_value' => $externalId,
            ]);

            if (!empty($existing[0])) {
                return (int) $existing[0];
            }
        }

        $existing = get_page_by_path($slug, OBJECT, $postType);

        return $existing ? (int) $existing->ID : 0;
    }

    private function buildExcerpt(array $data, string $content): string
    {
        $excerpt = trim((string) ($data['description'] ?? $data['excerpt'] ?? ''));
        if ($excerpt !== '') {
            return $excerpt;
        }

        $plain = trim(wp_strip_all_tags(strip_shortcodes($content), true));
        if ($plain === '') {
            return '';
        }

        return function_exists('mb_strimwidth')
            ? mb_strimwidth($plain, 0, 160, '...', 'UTF-8')
            : wp_html_excerpt($plain, 160, '...');
    }

    private function buildSlug(string $title, string $externalId): string
    {
        $slug = Str::slug($this->transliterate($title), '-');
        if ($slug === '') {
            $slug = sanitize_title($title);
        }

        if ($slug === '') {
            $slug = 'blog-' . ($externalId !== '' ? $externalId : Str::random(8));
        }

        return $slug;
    }

    private function resolveDate(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return wp_date('Y-m-d H:i:s', (int) $value);
        }

        $timestamp = strtotime((string) $value);

        return $timestamp !== false ? wp_date('Y-m-d H:i:s', $timestamp) : null;
    }

    private function getAttachmentByUuid(string $uuid): int
    {
        $attachments = get_posts([
            'post_type' => 'attachment',
            'post_status' => 'inherit',
            'numberposts' => 1,
            'fields' => 'ids',
            'meta_key' => 'import_image_uuid',
            'meta_value' => $uuid,
        ]);

        return (int) ($attachments[0] ?? 0);
    }

    private function transliterate(string $value): string
    {
        if (function_exists('transliterator_transliterate')) {
            $converted = transliterator_transliterate('Any-Latin; Latin-ASCII;', $value);
            if (is_string($converted) && $converted !== '') {
                return $converted;
            }
        }

        $converted = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);

        return is_string($converted) && $converted !== '' ? $converted : $value;
    }
}
