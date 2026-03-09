<?php

namespace App\Support;

class Breadcrumbs
{
    /** Вернёт массив элементов хлебных крошек: [['label'=>..., 'url'=>...], ...] */
    public static function trail(): array
    {
        $items   = [];
        $homeUrl = home_url('/');
        $items[] = ['label' => __('Главная', 'app'), 'url' => $homeUrl];

        if (is_front_page()) {
            return $items; // только Домой
        }

        // Классическая "страница записей"
        if (is_home()) {
            $blog_id = (int) get_option('page_for_posts');
            $items[] = ['label' => $blog_id ? get_the_title($blog_id) : __('Блог', 'app'), 'url' => null];
            return $items;
        }

        // Архив CPT blog
        if (is_post_type_archive('blog')) {
            $items[] = ['label' => self::blogLabel(), 'url' => null];
            return $items;
        }

        // Таксономии
        if (is_tax() || is_category() || is_tag()) {
            /** @var \WP_Term $term */
            $term = get_queried_object();
            if ($term instanceof \WP_Term) {
                $txObj = get_taxonomy($term->taxonomy);
                if ($txObj && in_array('blog', (array) $txObj->object_type, true)) {
                    $items[] = ['label' => self::blogLabel(), 'url' => get_post_type_archive_link('blog')];
                } else { // ★ эвристика: если в ленте первого поста URL под /blog/, добавим "Блог"
                    global $wp_query;
                    if (!empty($wp_query->posts[0]) && self::isUnderBlogUrl($wp_query->posts[0])) {
                        $items[] = ['label' => self::blogLabel(), 'url' => get_post_type_archive_link('blog')];
                    }
                }

                // Предки (сверху вниз)
                $anc = array_reverse(get_ancestors($term->term_id, $term->taxonomy, 'taxonomy'));
                foreach ($anc as $tid) {
                    $t = get_term($tid, $term->taxonomy);
                    if ($t && !is_wp_error($t)) {
                        $items[] = ['label' => $t->name, 'url' => get_term_link($t)];
                    }
                }
                $items[] = ['label' => $term->name, 'url' => null];
                return $items;
            }
        }

        // Прочие архивы/автор/даты
        if (is_post_type_archive()) {
            $items[] = ['label' => post_type_archive_title('', false), 'url' => null];
            return $items;
        }
        if (is_author()) {
            $items[] = ['label' => get_the_author(), 'url' => null];
            return $items;
        }
        if (is_date()) {
            $items[] = ['label' => get_the_archive_title(), 'url' => null];
            return $items;
        }
        if (is_search()) {
            $items[] = ['label' => sprintf(__('Поиск: %s', 'app'), get_search_query()), 'url' => null];
            return $items;
        }
        if (is_404()) {
            $items[] = ['label' => '404', 'url' => null];
            return $items;
        }

        // Страницы
        if (is_singular('page')) {
            $id   = get_queried_object_id();
            $ancs = array_reverse(get_post_ancestors($id));
            foreach ($ancs as $aid) {
                $items[] = ['label' => get_the_title($aid), 'url' => get_permalink($aid)];
            }
            $items[] = ['label' => get_the_title($id), 'url' => null];
            return $items;
        }

        // Записи/CPT
        if (is_singular()) {
            $post = get_post();

            // ★ Вставляем "Блог" для:
            // 1) сингла CPT blog,
            // 2) обычного post, чей URL реально под /blog/
            if (self::isBlogSingle($post)) {
                $items[] = [
                    'label' => self::blogLabel(),
                    'url'   => get_post_type_archive_link('blog'),
                ];
            }

            // Цепочка по первому найденному термину (если есть)
            $tax = [
                'district',
                'service',
                'rail_station',
                'hair_color',
                'aye_color',
                'nationality',
                'bust_size',
                'massage',
                'physique',
                'intimate_haircut',
                'striptease_services',
                'extreme_services',
                'sado_maso',
                'category',
                'post_tag',
            ];
            foreach ($tax as $tx) {
                $terms = $post ? get_the_terms($post, $tx) : null;
                if ($terms && !is_wp_error($terms)) {
                    $primary = array_shift($terms);
                    $anc = array_reverse(get_ancestors($primary->term_id, $primary->taxonomy, 'taxonomy'));
                    foreach ($anc as $tid) {
                        $t = get_term($tid, $primary->taxonomy);
                        if ($t && !is_wp_error($t)) {
                            $items[] = ['label' => $t->name, 'url' => get_term_link($t)];
                        }
                    }
                    $items[] = ['label' => $primary->name, 'url' => get_term_link($primary)];
                    break;
                }
            }

            // Сам пост
            $items[] = ['label' => get_the_title($post), 'url' => null];
            return $items;
        }

        return $items;
    }

    private static function blogLabel(): string
    {
        $o = get_post_type_object('blog');
        return $o && !empty($o->labels->name) ? $o->labels->name : __('Блог', 'app');
    }

    // ★ сингл относится к блогу?
    private static function isBlogSingle(?\WP_Post $post): bool
    {
        if (!$post) return false;
        if ($post->post_type === 'blog') return true;
        if ($post->post_type === 'post' && self::isUnderBlogUrl($post)) return true;
        return false;
    }

    // ★ permalink поста лежит под базой архива блога?
    private static function isUnderBlogUrl(?\WP_Post $post): bool
    {
        if (!$post) return false;
        $p = (string) parse_url(get_permalink($post), PHP_URL_PATH);
        $p = '/' . ltrim($p, '/');

        // база архива CPT blog (не хардкодим 'blog')
        $archive = (string) parse_url(get_post_type_archive_link('blog'), PHP_URL_PATH);
        $archive = '/' . trim($archive, '/'); // типа '/blog'

        return $p === $archive || strpos($p, $archive . '/') === 0;
    }
}
