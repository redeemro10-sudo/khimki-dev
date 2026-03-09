<?php

namespace App;

class StructuredData
{
    public static function boot(): void
    {
        add_action('wp_head', [__CLASS__, 'render'], 20);
    }

    /** Главный выводчик */
    public static function render(): void
    {
        if (is_admin()) {
            return;
        }

        $graphs = [];

        // Базовые сущности
        $graphs[] = self::orgSchema();
        $lb = self::localBusinessSchema();
        if ($lb) {
            $graphs[] = $lb;
        }
        $graphs[] = self::websiteSchema([
            // выключить searchbox, если не нужен:
            'sitelinks' => false,
        ]);

        // Текущая страница + хлебные
        $breadcrumbs = self::breadcrumbsSchema();
        if ($breadcrumbs) {
            $graphs[] = $breadcrumbs;
        }

        $webpage = self::webPageSchema([
            'breadcrumbId' => $breadcrumbs['@id'] ?? null,
        ]);
        if ($webpage) {
            $graphs[] = $webpage;
        }

        // Печать JSON-LD
        $out = [
            '@context' => 'https://schema.org',
            '@graph'   => array_values(array_filter($graphs)),
        ];
        echo "<script type=\"application/ld+json\">" . wp_json_encode($out, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "</script>\n";
    }

    /** Organization */
    private static function orgSchema(): array
    {
        $siteName = get_bloginfo('name');
        $url      = home_url('/');
        $logoId   = get_theme_mod('custom_logo');
        $logoUrl  = $logoId ? wp_get_attachment_image_url($logoId, 'full') : null;

        $sameAs = array_values(array_filter([
            self::contact('telegram_url'),
            self::contact('whatsapp_url'),
            self::contact('viber_url'),
            self::contact('instagram_url'),
            self::contact('vk_url'),
        ]));

        return [
            '@type' => 'Organization',
            '@id'   => $url . '#organization',
            'name'  => $siteName,
            'url'   => $url,
            'logo'  => $logoUrl ? [
                '@type' => 'ImageObject',
                'url'   => $logoUrl,
            ] : null,
            'email'     => self::contact('email') ?: null,
            'telephone' => self::contact('phone') ?: null,
            'sameAs'    => $sameAs ?: null,
        ];
    }

    /** LocalBusiness (покажем только если есть адрес/телефон) */
    private static function localBusinessSchema(): ?array
    {
        $url   = home_url('/');
        $name  = get_bloginfo('name');

        $phone = self::contact('phone');
        $addr  = self::contact('address');
        $city  = self::contact('city');
        $zip   = self::contact('postcode');
        $country = self::contact('country');

        if (!$phone && !$addr && !$city) {
            return null;
        }

        $lat = self::contact('lat');
        $lng = self::contact('lng');

        // Простой парсер расписания: Пн-Вс 10:00-20:00 -> Mo-Su 10:00-20:00
        $hours = self::openingHoursSpec(self::contact('hours'));

        return array_filter([
            '@type' => 'LocalBusiness', // при желании: 'AdultEntertainment'
            '@id'   => $url . '#localbusiness',
            'name'  => $name,
            'url'   => $url,
            'image' => get_theme_mod('custom_logo') ? wp_get_attachment_image_url(get_theme_mod('custom_logo'), 'full') : null,
            'telephone' => $phone ?: null,
            'email'     => self::contact('email') ?: null,
            'address'   => ($addr || $city || $zip || $country) ? [
                '@type'           => 'PostalAddress',
                'streetAddress'   => $addr ?: null,
                'addressLocality' => $city ?: null,
                'postalCode'      => $zip ?: null,
                'addressCountry'  => $country ?: null,
            ] : null,
            'geo'       => ($lat && $lng) ? [
                '@type' => 'GeoCoordinates',
                'latitude'  => (float) $lat,
                'longitude' => (float) $lng,
            ] : null,
            'openingHoursSpecification' => $hours ?: null,
            'sameAs' => array_values(array_filter([
                self::contact('telegram_url'),
                self::contact('whatsapp_url'),
                self::contact('viber_url'),
                self::contact('instagram_url'),
                self::contact('vk_url'),
            ])) ?: null,
        ]);
    }

    /** WebSite (+опциональный Sitelinks Search Box) */
    private static function websiteSchema(array $opts = []): array
    {
        $url  = home_url('/');
        $name = get_bloginfo('name');
        $withSearch = $opts['sitelinks'] ?? false;

        $out = [
            '@type' => 'WebSite',
            '@id'   => $url . '#website',
            'url'   => $url,
            'name'  => $name,
            'publisher' => ['@id' => $url . '#organization'],
            'inLanguage' => get_bloginfo('language') ?: 'ru-RU',
        ];

        if ($withSearch) {
            // если не нужен — просто не включай этот блок
            $out['potentialAction'] = [
                '@type' => 'SearchAction',
                'target' => $url . '?s={search_term_string}',
                'query-input' => 'required name=search_term_string',
            ];
        }

        return $out;
    }

    /** WebPage (текущая) */
    private static function webPageSchema(array $opts = []): ?array
    {
        $url   = esc_url_raw(self::canonical());
        $title = wp_get_document_title();
        $desc  = self::metaDescription();

        $type = is_front_page() ? 'WebPage' : (is_singular() ? 'WebPage' : 'CollectionPage');

        $data = [
            '@type' => $type,
            '@id'   => $url . '#webpage',
            'url'   => $url,
            'name'  => $title ?: null,
            'isPartOf' => ['@id' => home_url('/') . '#website'],
            'inLanguage' => get_bloginfo('language') ?: 'ru-RU',
            'description' => $desc ?: null,
        ];

        if (!empty($opts['breadcrumbId'])) {
            $data['breadcrumb'] = ['@id' => $opts['breadcrumbId']];
        }

        // Можно добавить primaryImage (например, для single model)
        if (is_singular('model')) {
            $img = get_post_thumbnail_id();
            if ($img) {
                $src = wp_get_attachment_image_url($img, 'large');
                if ($src) {
                    $data['primaryImageOfPage'] = [
                        '@type' => 'ImageObject',
                        'url'   => $src,
                    ];
                }
            }
        }

        return $data;
    }

    /** BreadcrumbList */
    private static function breadcrumbsSchema(): ?array
    {
        if (is_front_page()) {
            return null;
        }

        $items = [];
        $pos   = 1;
        $home  = home_url('/');

        $add = function (string $name, string $url = '') use (&$items, &$pos) {
            $items[] = array_filter([
                '@type' => 'ListItem',
                'position' => $pos++,
                'name' => wp_strip_all_tags($name),
                'item' => $url ?: null,
            ]);
        };

        // Home
        $add(__('Главная', 'sage'), $home);

        if (is_tax() || is_category() || is_tag()) {
            /** @var \WP_Term $term */
            $term = get_queried_object();
            if ($term instanceof \WP_Term) {
                // цепочка родителей
                $anc = array_reverse(get_ancestors($term->term_id, $term->taxonomy, 'taxonomy'));
                foreach ($anc as $tid) {
                    $t = get_term($tid, $term->taxonomy);
                    if ($t && !is_wp_error($t)) {
                        $add($t->name, get_term_link($t));
                    }
                }
                $add($term->name); // текущий без URL
            }
        } elseif (is_singular()) {
            $post = get_post();
            if ($post) {
                // Для CPT: ссылка на архив CPT, если он публичный
                $pto = get_post_type_object($post->post_type);
                if ($pto && !empty($pto->has_archive)) {
                    $add($pto->labels->name ?? ucfirst($post->post_type), get_post_type_archive_link($post->post_type));
                }

                // Для pages — цепочка предков
                if ($post->post_type === 'page') {
                    $anc = array_reverse(get_post_ancestors($post->ID));
                    foreach ($anc as $pid) {
                        $add(get_the_title($pid), get_permalink($pid));
                    }
                }

                // Для записей можно добавить основную рубрику (опционально)

                $add(get_the_title($post)); // текущая
            }
        } else {
            // Архивы/поиск и т.п.
            $add(trim(wp_get_document_title()));
        }

        if (!$items || count($items) < 2) {
            return null;
        }

        return [
            '@type' => 'BreadcrumbList',
            '@id'   => self::canonical() . '#breadcrumb',
            'itemListElement' => $items,
        ];
    }

    /* ===== helpers ===== */

    /** Канонический URL */
    private static function canonical(): string
    {
        if (function_exists('wp_get_canonical_url')) {
            $u = wp_get_canonical_url();
            if ($u) {
                return $u;
            }
        }
        return (is_ssl() ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    }

    /** meta description: берём из твоего SEO-мета, иначе обрезаем контент */
    private static function metaDescription(): ?string
    {
        // страница: кастомное поле _seo_description
        if (is_singular('page')) {
            $d = (string) get_post_meta(get_the_ID(), '_seo_description', true);
            if ($d !== '') {
                return $d;
            }
        }
        // таксы: тоже можно завести метаполе, если понадобится

        // fallback
        $raw = wp_strip_all_tags(get_the_excerpt() ?: get_the_content(''));
        $raw = preg_replace('~\s+~u', ' ', $raw);
        $lim = 160;
        if (mb_strlen($raw) > $lim) {
            $cut  = mb_substr($raw, 0, $lim);
            $rest = mb_substr($raw, $lim);
            if (preg_match('~^\S+\b~u', $rest, $m)) {
                $cut .= $m[0];
            }
            $raw = rtrim($cut) . '…';
        }
        return $raw ?: null;
    }

    /** Достаём контакты из кастомайзера/опций (подстрой под свои ключи) */
    private static function contact(string $key): ?string
    {
        // 1) кастомайзер (theme_mod)
        $val = get_theme_mod('contact_' . $key);
        if ($val) {
            return is_string($val) ? trim($val) : $val;
        }

        // 2) опция ACF/Options page
        $val = get_option('contact_' . $key);
        return $val ? (is_string($val) ? trim($val) : $val) : null;
    }

    /** Преобразуем строку расписания в OpeningHoursSpecification (простая версия) */
    private static function openingHoursSpec(?string $str): array
    {
        // пример ожидаемой строки: "Mo-Fr 10:00-20:00; Sa 11:00-18:00; Su off"
        if (!$str) {
            return [];
        }
        $parts = array_filter(array_map('trim', explode(';', $str)));
        $spec  = [];
        foreach ($parts as $p) {
            if (preg_match('~^(Mo|Tu|We|Th|Fr|Sa|Su)(?:-(Mo|Tu|We|Th|Fr|Sa|Su))?\s+(\d{2}:\d{2})-(\d{2}:\d{2})~', $p, $m)) {
                $spec[] = [
                    '@type'   => 'OpeningHoursSpecification',
                    'dayOfWeek' => isset($m[2]) ? [$m[1], $m[2]] : [$m[1]],
                    'opens'  => $m[3],
                    'closes' => $m[4],
                ];
            }
        }
        return $spec;
    }
}
