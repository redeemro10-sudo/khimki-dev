<?php

namespace App;

use App\Support\GoneUrls;
use WP_Post;
use WP_REST_Request;
use WP_REST_Response;
use WP_Term;

class StructuredData
{
    public static function boot(): void
    {
        add_action('wp_head', [__CLASS__, 'render'], 20);
    }

    public static function render(): void
    {
        if (is_admin()) {
            return;
        }

        $graphs = array_merge(
            [
                self::organizationSchema(),
                self::websiteSchema(),
            ],
            self::pageGraphs(),
        );

        $graphs = array_values(array_filter(array_map([__CLASS__, 'prune'], $graphs)));

        if ($graphs === []) {
            return;
        }

        echo '<script type="application/ld+json">' .
            wp_json_encode(
                [
                    '@context' => 'https://schema.org',
                    '@graph' => $graphs,
                ],
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES,
            ) .
            "</script>\n";
    }

    private static function pageGraphs(): array
    {
        $ctx = self::pageContext();
        $graphs = [];

        $breadcrumb = self::breadcrumbsSchema($ctx);
        if ($breadcrumb) {
            $graphs[] = $breadcrumb;
            $ctx['breadcrumbId'] = $breadcrumb['@id'];
        }

        if (is_page_template('template-faq.blade.php')) {
            return array_merge($graphs, self::faqPageGraphs($ctx));
        }

        if (self::isBlogSingle()) {
            return array_merge($graphs, self::blogPostingGraphs($ctx));
        }

        if (is_singular('model')) {
            return array_merge($graphs, self::modelProfileGraphs($ctx));
        }

        if (self::isLegalPage()) {
            return array_merge($graphs, self::legalDocumentGraphs($ctx));
        }

        if (self::isAboutPage()) {
            $graphs[] = self::basePageNode('AboutPage', $ctx, [
                'about' => ['@id' => $ctx['organizationId']],
                'mainEntity' => ['@id' => $ctx['organizationId']],
            ]);

            return $graphs;
        }

        if (is_post_type_archive('blog') || is_home()) {
            return array_merge($graphs, self::blogArchiveGraphs($ctx));
        }

        if (is_page_template('template-taxanomy-index.blade.php')) {
            return array_merge($graphs, self::taxonomyIndexGraphs($ctx));
        }

        if (is_page_template('template-sitemap.blade.php')) {
            return array_merge($graphs, self::sitemapGraphs($ctx));
        }

        $modelList = self::modelListSchema($ctx);
        if ($modelList) {
            return array_merge($graphs, $modelList);
        }

        $graphs[] = self::basePageNode(
            (is_post_type_archive() || is_search() || is_tax() || is_category() || is_tag()) ? 'CollectionPage' : 'WebPage',
            $ctx,
            ['about' => ['@id' => $ctx['organizationId']]],
        );

        return $graphs;
    }

    private static function organizationSchema(): array
    {
        $siteUrl = home_url('/');
        $sameAs = array_values(array_filter([
            self::contact('telegram_url'),
            self::contact('whatsapp_url'),
            self::contact('viber_url'),
            self::contact('instagram_url'),
            self::contact('vk_url'),
        ]));

        return [
            '@type' => 'Organization',
            '@id' => $siteUrl . '#organization',
            'name' => get_bloginfo('name'),
            'url' => $siteUrl,
            'logo' => self::logoSchema(),
            'email' => self::contact('email') ?: null,
            'telephone' => self::contact('phone') ?: null,
            'contactPoint' => self::contactPointSchema(),
            'areaServed' => self::cityNode(),
            'sameAs' => $sameAs ?: null,
        ];
    }

    private static function websiteSchema(): array
    {
        $siteUrl = home_url('/');
        $description = get_bloginfo('description');

        if ($description === '') {
            $description = self::metaDescription();
        }

        return [
            '@type' => 'WebSite',
            '@id' => $siteUrl . '#website',
            'url' => $siteUrl,
            'name' => get_bloginfo('name'),
            'description' => $description ?: null,
            'inLanguage' => get_bloginfo('language') ?: 'ru-RU',
            'publisher' => ['@id' => $siteUrl . '#organization'],
        ];
    }

    private static function faqPageGraphs(array $ctx): array
    {
        $faqItems = \App\get_faq_items(get_queried_object_id());

        $questions = array_values(array_map(static function (array $item): array {
            return [
                '@type' => 'Question',
                'name' => $item['question'],
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => $item['answer'],
                ],
            ];
        }, $faqItems));

        return [
            self::basePageNode('FAQPage', $ctx, [
                'mainEntity' => $questions,
            ]),
        ];
    }

    private static function modelProfileGraphs(array $ctx): array
    {
        $postId = get_queried_object_id();
        $galleryUrls = self::modelImageUrls($postId);
        $primaryImageUrl = $galleryUrls[0] ?? null;
        $profileText = trim((string) get_post_meta($postId, '_profile_text', true));
        $districtTerms = wp_get_post_terms($postId, 'district', ['fields' => 'all']);
        if (is_wp_error($districtTerms)) {
            $districtTerms = [];
        }
        $districtName = $districtTerms[0]->name ?? null;
        $height = (int) get_post_meta($postId, 'height', true);
        $weight = (int) get_post_meta($postId, 'weight', true);
        $personId = $ctx['url'] . '#person';

        return [
            self::basePageNode('ProfilePage', $ctx, [
                'mainEntity' => ['@id' => $personId],
                'primaryImageOfPage' => $primaryImageUrl ? [
                    '@type' => 'ImageObject',
                    'url' => $primaryImageUrl,
                ] : null,
            ]),
            [
                '@type' => 'Person',
                '@id' => $personId,
                'name' => get_the_title($postId),
                'gender' => 'Female',
                'description' => $profileText !== '' ? wp_strip_all_tags($profileText) : null,
                'image' => $galleryUrls ?: null,
                'height' => $height > 0 ? [
                    '@type' => 'QuantitativeValue',
                    'value' => $height,
                    'unitCode' => 'CMT',
                ] : null,
                'weight' => $weight > 0 ? [
                    '@type' => 'QuantitativeValue',
                    'value' => $weight,
                    'unitCode' => 'KGM',
                ] : null,
                'homeLocation' => $districtName ? [
                    '@type' => 'Place',
                    'name' => $districtName,
                    'containedInPlace' => [
                        '@type' => 'City',
                        'name' => self::cityName(),
                    ],
                ] : null,
            ],
        ];
    }

    private static function blogArchiveGraphs(array $ctx): array
    {
        global $wp_query;

        $posts = $wp_query instanceof \WP_Query ? (array) $wp_query->posts : [];
        $paged = max(1, (int) get_query_var('paged'), (int) get_query_var('page'));
        $perPage = $wp_query instanceof \WP_Query ? (int) $wp_query->get('posts_per_page') : count($posts);
        $offset = max(0, ($paged - 1) * max(1, $perPage));
        $listId = untrailingslashit($ctx['url']) . '/#article-list';

        $items = [];
        $position = $offset + 1;
        foreach ($posts as $post) {
            if (!$post instanceof WP_Post) {
                continue;
            }

            $articleUrl = get_permalink($post);
            $items[] = [
                '@type' => 'ListItem',
                'position' => $position++,
                'item' => self::prune([
                    '@type' => 'BlogPosting',
                    '@id' => $articleUrl . '#blogposting',
                    'url' => $articleUrl,
                    'headline' => get_the_title($post),
                    'description' => self::excerptForPost($post),
                    'image' => get_the_post_thumbnail_url($post, 'full') ?: null,
                    'datePublished' => get_post_time('c', true, $post),
                    'author' => ['@id' => $ctx['organizationId']],
                    'publisher' => ['@id' => $ctx['organizationId']],
                ]),
            ];
        }

        return [
            self::basePageNode('CollectionPage', $ctx, [
                'mainEntity' => ['@id' => $listId],
            ]),
            [
                '@type' => 'ItemList',
                '@id' => $listId,
                'name' => self::currentHeading(),
                'description' => $ctx['description'],
                'itemListOrder' => 'https://schema.org/ItemListOrderDescending',
                'numberOfItems' => $wp_query instanceof \WP_Query ? (int) $wp_query->found_posts : count($items),
                'itemListElement' => $items,
            ],
        ];
    }

    private static function blogPostingGraphs(array $ctx): array
    {
        $post = get_post();
        if (!$post instanceof WP_Post) {
            return [];
        }

        $imageId = get_post_thumbnail_id($post);
        $imageObject = self::imageObjectFromAttachment($imageId, $ctx['url'] . '#primaryimage');
        $postTags = wp_get_post_terms($post->ID, 'post_tag', ['fields' => 'names']);
        $categories = wp_get_post_terms($post->ID, 'category', ['fields' => 'names']);
        $keywords = array_values(array_unique(array_filter(array_merge(
            is_wp_error($postTags) ? [] : $postTags,
            is_wp_error($categories) ? [] : $categories,
        ))));
        $articleId = $ctx['url'] . '#blogposting';

        return [
            self::basePageNode('WebPage', $ctx, [
                'primaryImageOfPage' => $imageObject ? ['@id' => $imageObject['@id']] : null,
            ]),
            self::prune([
                '@type' => 'BlogPosting',
                '@id' => $articleId,
                'headline' => get_the_title($post),
                'description' => self::excerptForPost($post),
                'mainEntityOfPage' => ['@id' => $ctx['webpageId']],
                'datePublished' => get_post_time('c', true, $post),
                'dateModified' => get_post_modified_time('c', true, $post),
                'author' => ['@id' => $ctx['organizationId']],
                'publisher' => ['@id' => $ctx['organizationId']],
                'image' => $imageObject ? ['@id' => $imageObject['@id']] : null,
                'inLanguage' => $ctx['language'],
                'isAccessibleForFree' => true,
                'keywords' => $keywords ?: null,
            ]),
            $imageObject,
        ];
    }

    private static function legalDocumentGraphs(array $ctx): array
    {
        $slug = (string) get_post_field('post_name', get_queried_object_id());
        $documentId = $ctx['url'] . '#legal-document';

        return [
            self::basePageNode('WebPage', $ctx, [
                'about' => ['@id' => $ctx['organizationId']],
                'mainEntity' => ['@id' => $documentId],
            ]),
            [
                '@type' => 'DigitalDocument',
                '@id' => $documentId,
                'name' => get_the_title(get_queried_object_id()) . ' ' . get_bloginfo('name'),
                'publisher' => ['@id' => $ctx['organizationId']],
                'datePublished' => $ctx['published'],
                'dateModified' => $ctx['modified'],
                'inLanguage' => $ctx['language'],
                'fileFormat' => 'text/html',
                'url' => $slug ? $ctx['url'] : null,
            ],
        ];
    }

    private static function taxonomyIndexGraphs(array $ctx): array
    {
        $pageId = get_queried_object_id();
        $taxSlug = (string) get_post_meta($pageId, '_tax_index', true);

        if ($taxSlug === '') {
            $taxSlug = sanitize_key((string) get_query_var('tax'));
        }

        if ($taxSlug === '' || !taxonomy_exists($taxSlug)) {
            return [self::basePageNode('CollectionPage', $ctx)];
        }

        $terms = get_terms([
            'taxonomy' => $taxSlug,
            'hide_empty' => true,
            'parent' => 0,
        ]);

        if (is_wp_error($terms)) {
            $terms = [];
        }

        $listId = $ctx['url'] . '#list';
        $items = [];

        $position = 1;
        foreach ($terms as $term) {
            if (!$term instanceof WP_Term) {
                continue;
            }

            $termUrl = get_term_link($term);
            $items[] = [
                '@type' => 'ListItem',
                'position' => $position++,
                'item' => self::taxonomyIndexItemSchema($taxSlug, $term, is_wp_error($termUrl) ? null : $termUrl),
            ];
        }

        return [
            self::basePageNode('CollectionPage', $ctx, [
                'mainEntity' => ['@id' => $listId],
            ]),
            [
                '@type' => 'ItemList',
                '@id' => $listId,
                'name' => self::currentHeading(),
                'description' => $ctx['description'],
                'itemListOrder' => 'https://schema.org/ItemListOrderAscending',
                'numberOfItems' => count($items),
                'itemListElement' => $items,
            ],
        ];
    }

    private static function taxonomyIndexItemSchema(string $taxonomy, WP_Term $term, ?string $url): array
    {
        if (in_array($taxonomy, ['district', 'rail_station'], true)) {
            return self::prune([
                '@type' => 'Place',
                'name' => $term->name,
                'url' => $url,
                'containedInPlace' => self::cityNode(),
            ]);
        }

        return self::prune([
            '@type' => 'Thing',
            'name' => $term->name,
            'url' => $url,
        ]);
    }

    private static function sitemapGraphs(array $ctx): array
    {
        $entries = self::sitemapEntries();
        $listId = $ctx['url'] . '#navigation-list';

        $items = [];
        $position = 1;
        foreach ($entries as $entry) {
            $items[] = [
                '@type' => 'ListItem',
                'position' => $position++,
                'name' => $entry['name'],
                'url' => $entry['url'],
            ];
        }

        return [
            self::basePageNode('CollectionPage', $ctx, [
                'mainEntity' => ['@id' => $listId],
            ]),
            [
                '@type' => 'ItemList',
                '@id' => $listId,
                'name' => self::currentHeading(),
                'description' => $ctx['description'],
                'itemListOrder' => 'https://schema.org/ItemListOrderAscending',
                'numberOfItems' => count($items),
                'itemListElement' => $items,
            ],
        ];
    }

    private static function modelListSchema(array $ctx): array
    {
        $config = self::modelListConfig();
        if ($config === null) {
            return [];
        }

        $data = self::fetchModelsData($config);
        $items = (array) ($data['items'] ?? []);
        $listId = $ctx['url'] . '#list';
        $mappedItems = [];

        $position = 1;
        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }

            $caption = self::modelImageCaption($item);
            $mappedItems[] = [
                '@type' => 'ListItem',
                'position' => $position++,
                'item' => self::prune([
                    '@type' => 'Person',
                    'name' => $item['title'] ?? null,
                    'url' => $item['link'] ?? null,
                    'image' => !empty($item['thumb']) ? [
                        '@type' => 'ImageObject',
                        'url' => $item['thumb'],
                        'caption' => $caption ?: null,
                    ] : null,
                ]),
            ];
        }

        return [
            self::basePageNode('CollectionPage', $ctx, [
                'about' => is_front_page() ? ['@id' => $ctx['organizationId']] : null,
                'mainEntity' => ['@id' => $listId],
            ]),
            [
                '@type' => 'ItemList',
                '@id' => $listId,
                'name' => self::currentHeading(),
                'description' => $ctx['description'],
                'itemListOrder' => 'https://schema.org/ItemListOrderDescending',
                'numberOfItems' => isset($data['total']) ? (int) $data['total'] : count($mappedItems),
                'itemListElement' => $mappedItems,
            ],
        ];
    }

    private static function breadcrumbsSchema(array $ctx): ?array
    {
        if (is_post_type_archive('blog') || is_home()) {
            return [
                '@type' => 'BreadcrumbList',
                '@id' => $ctx['url'] . '#breadcrumb',
                'itemListElement' => [
                    [
                        '@type' => 'ListItem',
                        'position' => 1,
                        'name' => self::homeCrumbLabel(),
                        'item' => home_url('/'),
                    ],
                    [
                        '@type' => 'ListItem',
                        'position' => 2,
                        'name' => self::blogLabel(),
                        'item' => $ctx['url'],
                    ],
                ],
            ];
        }

        if (self::isBlogSingle()) {
            $post = get_post();
            if ($post instanceof WP_Post) {
                return [
                    '@type' => 'BreadcrumbList',
                    '@id' => $ctx['url'] . '#breadcrumb',
                    'itemListElement' => [
                        [
                            '@type' => 'ListItem',
                            'position' => 1,
                            'name' => self::homeCrumbLabel(),
                            'item' => home_url('/'),
                        ],
                        [
                            '@type' => 'ListItem',
                            'position' => 2,
                            'name' => self::blogLabel(),
                            'item' => get_post_type_archive_link('blog'),
                        ],
                        [
                            '@type' => 'ListItem',
                            'position' => 3,
                            'name' => get_the_title($post),
                        ],
                    ],
                ];
            }
        }

        $trail = self::breadcrumbTrail();
        if ($trail === [] || count($trail) < 2) {
            return null;
        }

        $items = [];
        $lastIndex = count($trail) - 1;
        foreach ($trail as $index => $crumb) {
            $itemUrl = $crumb['url'] ?? null;
            if ($itemUrl === null && $index === $lastIndex) {
                $itemUrl = $ctx['url'];
            }

            $label = (string) ($crumb['label'] ?? '');
            if ($index === 0) {
                $label = self::homeCrumbLabel();
            }

            $items[] = self::prune([
                '@type' => 'ListItem',
                'position' => $index + 1,
                'name' => wp_strip_all_tags($label),
                'item' => $itemUrl,
            ]);
        }

        return [
            '@type' => 'BreadcrumbList',
            '@id' => $ctx['url'] . '#breadcrumb',
            'itemListElement' => $items,
        ];
    }

    private static function basePageNode(string $type, array $ctx, array $extra = []): array
    {
        return self::prune(array_merge([
            '@type' => $type,
            '@id' => $ctx['webpageId'],
            'url' => $ctx['url'],
            'name' => $ctx['title'],
            'description' => $ctx['description'],
            'inLanguage' => $ctx['language'],
            'isPartOf' => ['@id' => $ctx['websiteId']],
            'breadcrumb' => !empty($ctx['breadcrumbId']) ? ['@id' => $ctx['breadcrumbId']] : null,
            'datePublished' => $ctx['published'],
            'dateModified' => $ctx['modified'],
        ], $extra));
    }

    private static function pageContext(): array
    {
        $url = esc_url_raw(self::canonical());
        $siteUrl = home_url('/');

        return [
            'url' => $url,
            'webpageId' => $url . '#webpage',
            'websiteId' => $siteUrl . '#website',
            'organizationId' => $siteUrl . '#organization',
            'breadcrumbId' => null,
            'title' => wp_get_document_title() ?: null,
            'description' => self::metaDescription(),
            'language' => get_bloginfo('language') ?: 'ru-RU',
            'published' => self::publishedDate(),
            'modified' => self::modifiedDate(),
        ];
    }

    private static function publishedDate(): ?string
    {
        if (is_singular()) {
            $post = get_post();

            return $post instanceof WP_Post ? get_post_time('c', true, $post) : null;
        }

        return null;
    }

    private static function modifiedDate(): ?string
    {
        if (is_singular()) {
            $post = get_post();

            return $post instanceof WP_Post ? get_post_modified_time('c', true, $post) : null;
        }

        return null;
    }

    private static function logoSchema(): ?array
    {
        $logoId = (int) get_theme_mod('custom_logo');
        if ($logoId > 0) {
            $logoUrl = wp_get_attachment_image_url($logoId, 'full');
            if ($logoUrl) {
                $meta = wp_get_attachment_metadata($logoId);
                $width = isset($meta['width']) ? (int) $meta['width'] : null;
                $height = isset($meta['height']) ? (int) $meta['height'] : null;

                if (($width ?? 0) >= 112) {
                    return self::prune([
                        '@type' => 'ImageObject',
                        'url' => $logoUrl,
                        'width' => $width,
                        'height' => $height,
                    ]);
                }
            }
        }

        return [
            '@type' => 'ImageObject',
            'url' => get_theme_file_uri('resources/images/web-app-manifest-512x512.png'),
            'width' => 512,
            'height' => 512,
        ];
    }

    private static function contactPointSchema(): ?array
    {
        $phone = self::contact('phone');
        $email = self::contact('email');
        $url = self::primaryContactUrl();

        if (!$phone && !$email && !$url) {
            return null;
        }

        return self::prune([
            '@type' => 'ContactPoint',
            'contactType' => 'customer service',
            'telephone' => $phone ?: null,
            'email' => $email ?: null,
            'url' => $url ?: null,
            'areaServed' => self::cityNode(),
            'availableLanguage' => ['Russian'],
        ]);
    }

    private static function imageObjectFromAttachment(int $attachmentId, string $id): ?array
    {
        if ($attachmentId <= 0) {
            return null;
        }

        $url = wp_get_attachment_image_url($attachmentId, 'full');
        if (!$url) {
            return null;
        }

        $meta = wp_get_attachment_metadata($attachmentId);
        $caption = (string) get_post_meta($attachmentId, '_wp_attachment_image_alt', true);

        return self::prune([
            '@type' => 'ImageObject',
            '@id' => $id,
            'url' => $url,
            'width' => isset($meta['width']) ? (int) $meta['width'] : null,
            'height' => isset($meta['height']) ? (int) $meta['height'] : null,
            'caption' => $caption !== '' ? $caption : null,
        ]);
    }

    private static function cityNode(): array
    {
        $sameAs = null;
        if (self::cityName() === 'Химки') {
            $sameAs = 'https://ru.wikipedia.org/wiki/Химки';
        }

        return self::prune([
            '@type' => 'City',
            'name' => self::cityName(),
            'sameAs' => $sameAs,
        ]);
    }

    private static function cityName(): string
    {
        return self::contact('city') ?: 'Химки';
    }

    private static function metaDescription(): ?string
    {
        if (is_post_type_archive('blog') || is_home()) {
            $page = get_page_by_path('blog-seo');
            if ($page instanceof WP_Post) {
                $description = (string) get_post_meta($page->ID, '_seo_description', true);
                if ($description !== '') {
                    return $description;
                }
            }
        }

        if (is_tax() || is_category() || is_tag()) {
            $term = get_queried_object();
            if ($term instanceof WP_Term) {
                foreach (['_seo_description', 'seo_description', 'term_description'] as $metaKey) {
                    $value = $metaKey === 'term_description'
                        ? trim(wp_strip_all_tags(term_description($term)))
                        : trim((string) get_term_meta($term->term_id, $metaKey, true));

                    if ($value !== '') {
                        return $value;
                    }
                }
            }
        }

        if (is_singular()) {
            $postId = get_queried_object_id();
            $custom = (string) get_post_meta($postId, '_seo_description', true);
            if ($custom !== '') {
                return $custom;
            }
        }

        if (is_singular('blog')) {
            $seoText = (string) get_post_meta(get_queried_object_id(), '_post_text', true);
            if ($seoText !== '') {
                return self::trimText($seoText, 160);
            }
        }

        $content = '';
        if (is_singular()) {
            $post = get_post();
            if ($post instanceof WP_Post) {
                $content = get_the_excerpt($post) ?: $post->post_content;
            }
        } elseif (is_tax() || is_category() || is_tag()) {
            $term = get_queried_object();
            if ($term instanceof WP_Term) {
                $content = term_description($term);
            }
        }

        $content = trim(wp_strip_all_tags((string) $content));

        return $content !== '' ? self::trimText($content, 160) : null;
    }

    private static function currentHeading(): string
    {
        if (is_post_type_archive('blog') || is_home()) {
            $page = get_page_by_path('blog-seo');
            if ($page instanceof WP_Post) {
                $h1 = trim((string) get_post_meta($page->ID, '_page_h1', true));
                if ($h1 !== '') {
                    return $h1;
                }
            }

            $postType = get_post_type_object('blog');

            return $postType && !empty($postType->labels->name) ? $postType->labels->name : 'Блог';
        }

        if (is_page()) {
            $pageId = get_queried_object_id();
            $h1 = trim((string) get_post_meta($pageId, '_page_h1', true));
            if ($h1 !== '') {
                return $h1;
            }

            return get_the_title($pageId);
        }

        if (is_tax() || is_category() || is_tag()) {
            $term = get_queried_object();
            if ($term instanceof WP_Term) {
                $h1 = trim((string) get_term_meta($term->term_id, '_term_h1', true));
                if ($h1 !== '') {
                    return $h1;
                }

                $h1 = trim((string) get_term_meta($term->term_id, 'term_h1', true));
                if ($h1 !== '') {
                    return $h1;
                }

                return $term->name;
            }
        }

        if (is_singular()) {
            return get_the_title(get_queried_object_id());
        }

        return trim(wp_strip_all_tags(wp_get_document_title()));
    }

    private static function modelListConfig(): ?array
    {
        if (is_front_page()) {
            return [
                'per_page' => 48,
                'order' => 'date',
            ];
        }

        if (is_page_template('template-models.blade.php')) {
            $pageId = get_queried_object_id();
            $slug = (string) get_post_field('post_name', $pageId);
            $tax = [];
            $price = [];

            switch ($slug) {
                case 'na-vyyezd':
                    $tax['service'] = ['prostitutki-po-vyzovu'];
                    break;

                case 'deshovyye':
                    $price['max'] = 14999;
                    break;

                case 'proverennye':
                    $tax['feature'] = ['proverennyye'];
                    break;

                case 'massazh':
                    $massage = get_terms([
                        'taxonomy' => 'massage',
                        'hide_empty' => true,
                        'fields' => 'slugs',
                    ]);

                    if (!is_wp_error($massage) && $massage) {
                        $tax['massage'] = $massage;
                    }
                    break;

                case 'elitnye':
                    $tax['feature'] = ['vip'];
                    break;
            }

            return [
                'per_page' => 48,
                'order' => 'date',
                'tax' => $tax,
                'price' => $price,
            ];
        }

        if (is_tax()) {
            $term = get_queried_object();
            if ($term instanceof WP_Term) {
                return [
                    'per_page' => 48,
                    'order' => 'date',
                    'tax' => [$term->taxonomy => [$term->slug]],
                ];
            }
        }

        return null;
    }

    private static function fetchModelsData(array $config): array
    {
        $request = new WP_REST_Request('GET', '/site/v1/models');
        $request->set_param('page', 1);
        $request->set_param('per_page', (int) ($config['per_page'] ?? 12));
        $request->set_param('sort', $config['sort'] ?? ($config['order'] ?? 'date'));

        foreach ((array) ($config['tax'] ?? []) as $taxonomy => $values) {
            if ($values !== []) {
                $request->set_param($taxonomy, array_values((array) $values));
            }
        }

        if (isset($config['price']['min'])) {
            $request->set_param('price_min', (int) $config['price']['min']);
        }

        if (isset($config['price']['max'])) {
            $request->set_param('price_max', (int) $config['price']['max']);
        }

        foreach ((array) ($config['meta'] ?? []) as $key => $value) {
            if (!is_array($value) || !isset($value[0], $value[1])) {
                continue;
            }

            if ($key === 'age') {
                $request->set_param('age_min', (int) $value[0]);
                $request->set_param('age_max', (int) $value[1]);
            }

            if ($key === 'height') {
                $request->set_param('height_min', (int) $value[0]);
                $request->set_param('height_max', (int) $value[1]);
            }

            if ($key === 'weight') {
                $request->set_param('weight_min', (int) $value[0]);
                $request->set_param('weight_max', (int) $value[1]);
            }
        }

        $response = rest_do_request($request);

        return $response instanceof WP_REST_Response ? (array) $response->get_data() : (array) $response;
    }

    private static function modelImageUrls(int $postId): array
    {
        $galleryIds = array_filter(array_map('intval', (array) get_post_meta($postId, '_gallery_ids', true)));

        if ($galleryIds === []) {
            $thumbId = get_post_thumbnail_id($postId);
            if ($thumbId) {
                $galleryIds = [$thumbId];
            }
        }

        return array_values(array_filter(array_map(static function (int $attachmentId): ?string {
            $url = wp_get_attachment_image_url($attachmentId, 'full');

            return $url ?: null;
        }, $galleryIds)));
    }

    private static function modelImageCaption(array $item): string
    {
        $parts = array_filter([
            $item['title'] ?? null,
            !empty($item['age']) ? (string) $item['age'] : null,
            $item['district'] ?? null,
        ]);

        return implode(', ', $parts);
    }

    private static function sitemapEntries(): array
    {
        $currentPageId = get_queried_object_id();
        $entries = [];

        $pages = get_pages([
            'sort_column' => 'menu_order,post_title',
            'sort_order' => 'ASC',
            'post_status' => 'publish',
        ]);

        foreach ($pages as $page) {
            if (!$page instanceof WP_Post) {
                continue;
            }

            if (
                (int) $page->ID === $currentPageId ||
                (int) $page->post_parent !== 0 ||
                GoneUrls::isGonePageId((int) $page->ID)
            ) {
                continue;
            }

            $entries[] = [
                'name' => get_the_title($page),
                'url' => get_permalink($page),
            ];
        }

        if (post_type_exists('blog')) {
            $blogType = get_post_type_object('blog');
            $blogArchiveUrl = get_post_type_archive_link('blog');

            if ($blogType && $blogArchiveUrl) {
                $entries[] = [
                    'name' => $blogType->labels->name ?? 'Блог',
                    'url' => $blogArchiveUrl,
                ];
            }
        }

        foreach (['service', 'district', 'rail_station', 'nationality', 'hair_color'] as $taxonomy) {
            if (!taxonomy_exists($taxonomy)) {
                continue;
            }

            $terms = get_terms([
                'taxonomy' => $taxonomy,
                'hide_empty' => true,
                'parent' => 0,
            ]);

            if (is_wp_error($terms)) {
                continue;
            }

            foreach ($terms as $term) {
                if (!$term instanceof WP_Term) {
                    continue;
                }

                if (GoneUrls::isGoneTerm($term)) {
                    continue;
                }

                $termUrl = get_term_link($term);
                if (is_wp_error($termUrl)) {
                    continue;
                }

                $entries[] = [
                    'name' => $term->name,
                    'url' => $termUrl,
                ];
            }
        }

        return array_values($entries);
    }

    private static function breadcrumbTrail(): array
    {
        if (is_front_page() || is_404()) {
            return [];
        }

        $blogArchiveUrl = get_post_type_archive_link('blog');
        $paged = max(1, (int) get_query_var('paged'), (int) get_query_var('page'));
        $trail = [];
        $trail[] = ['label' => 'Главная', 'url' => home_url('/')];

        if (is_home()) {
            $blogId = (int) get_option('page_for_posts');
            $trail[] = [
                'label' => $blogId ? get_the_title($blogId) : self::blogLabel(),
                'url' => null,
                'current' => true,
            ];

            return $trail;
        }

        if (is_post_type_archive('blog')) {
            if ($paged > 1) {
                $trail[] = ['label' => self::blogLabel(), 'url' => $blogArchiveUrl];
                $trail[] = ['label' => 'Страница ' . $paged, 'url' => null, 'current' => true];
            } else {
                $trail[] = ['label' => self::blogLabel(), 'url' => null, 'current' => true];
            }

            return $trail;
        }

        if (is_tax() || is_category() || is_tag()) {
            $term = get_queried_object();
            if (!$term instanceof WP_Term) {
                return $trail;
            }

            $taxonomy = get_taxonomy($term->taxonomy);
            if ($taxonomy && in_array('blog', (array) $taxonomy->object_type, true)) {
                $trail[] = ['label' => self::blogLabel(), 'url' => $blogArchiveUrl];
            } else {
                global $wp_query;
                if (
                    $wp_query instanceof \WP_Query &&
                    !empty($wp_query->posts[0]) &&
                    self::isUnderBlogUrl($wp_query->posts[0])
                ) {
                    $trail[] = ['label' => self::blogLabel(), 'url' => $blogArchiveUrl];
                }
            }

            $indexPageIds = get_posts([
                'post_type' => 'page',
                'posts_per_page' => 1,
                'fields' => 'ids',
                'meta_key' => '_tax_index',
                'meta_value' => $term->taxonomy,
                'suppress_filters' => true,
            ]);

            if (!empty($indexPageIds[0])) {
                $indexPageId = (int) $indexPageIds[0];
                $trail[] = ['label' => get_the_title($indexPageId), 'url' => get_permalink($indexPageId)];
            }

            $ancestors = array_reverse(get_ancestors($term->term_id, $term->taxonomy, 'taxonomy'));
            foreach ($ancestors as $ancestorId) {
                $ancestor = get_term($ancestorId, $term->taxonomy);
                if ($ancestor && !is_wp_error($ancestor)) {
                    $trail[] = ['label' => $ancestor->name, 'url' => get_term_link($ancestor)];
                }
            }

            $trail[] = ['label' => $term->name, 'url' => null, 'current' => true];

            return $trail;
        }

        if (is_page()) {
            $pageId = get_queried_object_id();
            foreach (array_reverse(get_post_ancestors($pageId)) as $ancestorId) {
                $trail[] = ['label' => get_the_title($ancestorId), 'url' => get_permalink($ancestorId)];
            }

            $trail[] = ['label' => get_the_title($pageId), 'url' => null, 'current' => true];

            return $trail;
        }

        if (is_singular()) {
            $post = get_post();

            if (self::isBlogSingle($post)) {
                $trail[] = ['label' => self::blogLabel(), 'url' => $blogArchiveUrl];
            }

            if ($post instanceof WP_Post) {
                $trail[] = ['label' => get_the_title($post), 'url' => null, 'current' => true];
            }

            return $trail;
        }

        if (is_search()) {
            $trail[] = ['label' => 'Поиск', 'url' => null, 'current' => true];

            return $trail;
        }

        if (is_post_type_archive() || is_author() || is_date()) {
            $trail[] = ['label' => get_the_archive_title(), 'url' => null, 'current' => true];
        }

        return $trail;
    }

    private static function blogLabel(): string
    {
        $object = get_post_type_object('blog');

        return $object && !empty($object->labels->name) ? $object->labels->name : 'Блог';
    }

    private static function isBlogSingle(?WP_Post $post = null): bool
    {
        $post = $post instanceof WP_Post ? $post : get_post();

        if (!$post instanceof WP_Post) {
            return false;
        }

        if ($post->post_type === 'blog') {
            return true;
        }

        return $post->post_type === 'post' && self::isUnderBlogUrl($post);
    }

    private static function isUnderBlogUrl(?WP_Post $post): bool
    {
        if (!$post instanceof WP_Post) {
            return false;
        }

        $postPath = '/' . ltrim((string) parse_url(get_permalink($post), PHP_URL_PATH), '/');
        $archivePath = '/' . trim((string) parse_url((string) get_post_type_archive_link('blog'), PHP_URL_PATH), '/');

        return $postPath === $archivePath || str_starts_with($postPath, $archivePath . '/');
    }

    private static function isAboutPage(): bool
    {
        if (!is_page()) {
            return false;
        }

        $slug = (string) get_post_field('post_name', get_queried_object_id());

        return in_array($slug, ['o-nas', 'about'], true);
    }

    private static function isLegalPage(): bool
    {
        if (!is_page()) {
            return false;
        }

        $slug = (string) get_post_field('post_name', get_queried_object_id());

        return in_array($slug, ['privacy', 'terms'], true);
    }

    private static function homeCrumbLabel(): string
    {
        return 'Индивидуалки Химки';
    }

    private static function canonical(): string
    {
        if (function_exists('wp_get_canonical_url')) {
            $url = wp_get_canonical_url();
            if ($url) {
                return $url;
            }
        }

        $scheme = is_ssl() ? 'https://' : 'http://';
        $host = (string) ($_SERVER['HTTP_HOST'] ?? parse_url(home_url('/'), PHP_URL_HOST));
        $requestUri = (string) ($_SERVER['REQUEST_URI'] ?? '/');

        return $scheme . $host . $requestUri;
    }

    private static function excerptForPost(WP_Post $post): string
    {
        $excerpt = get_the_excerpt($post);
        if ($excerpt === '') {
            $excerpt = $post->post_content;
        }

        return self::trimText($excerpt, 160);
    }

    private static function trimText(string $text, int $limit): string
    {
        $text = preg_replace('~\s+~u', ' ', trim(wp_strip_all_tags($text)));
        if ($text === null || $text === '') {
            return '';
        }

        if (mb_strlen($text) <= $limit) {
            return $text;
        }

        $cut = mb_substr($text, 0, $limit);
        $rest = mb_substr($text, $limit);

        if (preg_match('~^\S+\b~u', $rest, $matches)) {
            $cut .= $matches[0];
        }

        return rtrim($cut) . '...';
    }

    private static function contact(string $key): ?string
    {
        $value = get_theme_mod('contact_' . $key);
        if ($value) {
            return is_string($value) ? trim($value) : (string) $value;
        }

        $value = get_option('contact_' . $key);

        return $value ? (is_string($value) ? trim($value) : (string) $value) : null;
    }

    private static function primaryContactUrl(): ?string
    {
        foreach (['telegram_url', 'whatsapp_url', 'viber_url', 'instagram_url', 'vk_url'] as $key) {
            $value = self::contact($key);
            if ($value) {
                return $value;
            }
        }

        return null;
    }

    private static function prune($value)
    {
        if (!is_array($value)) {
            if ($value === null) {
                return null;
            }

            return $value === '' ? null : $value;
        }

        $clean = [];
        foreach ($value as $key => $item) {
            $item = self::prune($item);

            if ($item === null) {
                continue;
            }

            if (is_array($item) && $item === []) {
                continue;
            }

            $clean[$key] = $item;
        }

        return $clean;
    }
}
