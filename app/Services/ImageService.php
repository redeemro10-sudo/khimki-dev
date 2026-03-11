<?php

declare(strict_types=1);

namespace App\Services;

class ImageService
{
    public function processAndAttach(string $url, int $postId = 0, ?string $uuid = null): int
    {
        $url = trim($url);
        if ($url === '') {
            return 0;
        }

        $this->ensureMediaFunctionsLoaded();

        $tmpFile = download_url($url, 30);
        if (is_wp_error($tmpFile)) {
            return 0;
        }

        $file = [
            'name' => $this->buildFilename($url, $uuid),
            'tmp_name' => $tmpFile,
        ];

        $attachmentId = media_handle_sideload($file, $postId, null, [
            'post_status' => 'inherit',
        ]);

        if (is_wp_error($attachmentId)) {
            @unlink($tmpFile);
            return 0;
        }

        if ($uuid !== null && $uuid !== '') {
            update_post_meta($attachmentId, 'import_image_uuid', $uuid);
        }

        return (int) $attachmentId;
    }

    private function ensureMediaFunctionsLoaded(): void
    {
        if (!function_exists('download_url')) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
        }

        if (!function_exists('media_handle_sideload')) {
            require_once ABSPATH . 'wp-admin/includes/media.php';
        }

        if (!function_exists('wp_generate_attachment_metadata')) {
            require_once ABSPATH . 'wp-admin/includes/image.php';
        }
    }

    private function buildFilename(string $url, ?string $uuid): string
    {
        $path = (string) parse_url($url, PHP_URL_PATH);
        $filename = sanitize_file_name(basename($path));

        if ($filename === '' || $filename === '.' || $filename === '..') {
            $filename = ($uuid ?: md5($url)) . '.jpg';
        }

        if (!str_contains($filename, '.')) {
            $filename .= '.jpg';
        }

        return $filename;
    }
}
