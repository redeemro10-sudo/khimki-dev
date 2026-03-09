@php
    // Не выводим на главной и 404
    if (is_front_page() || is_404()) {
        return;
    }

    // helpers
    $blogArchiveUrl = get_post_type_archive_link('blog'); // реальная база архива
    $blogLabel = (function () {
        $o = get_post_type_object('blog');
        return $o && !empty($o->labels->name) ? $o->labels->name : __('Блог', 'app');
    })();
    $underBlog = function ($post) use ($blogArchiveUrl) {
        if (!$post) {
            return false;
        }
        $p = '/' . ltrim((string) parse_url(get_permalink($post), PHP_URL_PATH), '/');
        $a = '/' . trim((string) parse_url($blogArchiveUrl, PHP_URL_PATH), '/'); // напр. '/blog'
        return $p === $a || str_starts_with($p, $a . '/');
    };

    $trail = [];
    $trail[] = ['label' => 'Главная', 'url' => home_url('/')];

    // Классическая "страница записей"
    if (is_home()) {
        $blog_id = (int) get_option('page_for_posts');
        $trail[] = ['label' => $blog_id ? get_the_title($blog_id) : $blogLabel, 'url' => null, 'current' => true];
    }
    // Архив CPT blog
    elseif (is_post_type_archive('blog')) {
        $trail[] = ['label' => $blogLabel, 'url' => null, 'current' => true];
    }
    // Таксономии
    elseif (is_tax() || is_category() || is_tag()) {
        /** @var \WP_Term $term */
        $term = get_queried_object();

        if ($term instanceof \WP_Term) {
            $tx = get_taxonomy($term->taxonomy);

            // если такса прикреплена к blog — добавим узел "Блог"
            if ($tx && in_array('blog', (array) $tx->object_type, true)) {
                $trail[] = ['label' => $blogLabel, 'url' => $blogArchiveUrl];
            } else {
                // эвристика: если первый пост в ленте терма из /blog/ — тоже добавим "Блог"
                global $wp_query;
                if (!empty($wp_query->posts[0]) && $underBlog($wp_query->posts[0])) {
                    $trail[] = ['label' => $blogLabel, 'url' => $blogArchiveUrl];
                }
            }

            // страница-индекс таксы (твой _tax_index)
            $index_page_id = 0;
            $maybe = get_posts([
                'post_type' => 'page',
                'posts_per_page' => 1,
                'fields' => 'ids',
                'meta_key' => '_tax_index',
                'meta_value' => $term->taxonomy,
                'suppress_filters' => true,
            ]);
            if (!empty($maybe[0])) {
                $index_page_id = (int) $maybe[0];
                $trail[] = ['label' => get_the_title($index_page_id), 'url' => get_permalink($index_page_id)];
            } else {
                $taxLabel = $tx?->labels?->name ?: ucfirst($term->taxonomy);
                $trail[] = ['label' => $taxLabel, 'url' => null];
            }

            // предки терма (иерархич.)
            $anc = array_reverse(get_ancestors($term->term_id, $term->taxonomy, 'taxonomy'));
            foreach ($anc as $tid) {
                $t = get_term($tid, $term->taxonomy);
                if ($t && !is_wp_error($t)) {
                    $trail[] = ['label' => $t->name, 'url' => get_term_link($t)];
                }
            }

            // текущий терм
            $trail[] = ['label' => $term->name, 'url' => null, 'current' => true];
        }
    }
    // Страницы
    elseif (is_page()) {
        $id = get_queried_object_id();
        foreach (array_reverse(get_post_ancestors($id)) as $aid) {
            $trail[] = ['label' => get_the_title($aid), 'url' => get_permalink($aid)];
        }
        $trail[] = ['label' => get_the_title($id), 'url' => null, 'current' => true];
    }
    // Синглы
    elseif (is_singular()) {
        $post = get_post();

        // Вставляем "Блог" для: a) CPT blog; b) обычных post, если URL под /blog/
        if ($post && ($post->post_type === 'blog' || ($post->post_type === 'post' && $underBlog($post)))) {
            $trail[] = ['label' => $blogLabel, 'url' => $blogArchiveUrl];
        }

        // (опционально) можно вывести цепочку по категории блога
        // $cats = get_the_terms($post, 'category'); ... — пропустим, чтобы не перегружать.

        $trail[] = ['label' => get_the_title($post), 'url' => null, 'current' => true];
    }
    // Поиск
    elseif (is_search()) {
        $trail[] = ['label' => 'Поиск', 'url' => null, 'current' => true];
    }
    // Прочие архивы (дата/автор/CPT ≠ blog)
    elseif (is_post_type_archive() || is_author() || is_date()) {
        $trail[] = ['label' => get_the_archive_title(), 'url' => null, 'current' => true];
    }
@endphp

@if (!empty($trail))
    <nav class="max-w-7xl mx-auto px-4 pt-3 text-xs text-slate-600" aria-label="breadcrumb">
        <ol class="flex flex-wrap gap-x-2 gap-y-1 items-center">
            @foreach ($trail as $i => $c)
                <li class="inline">
                    @if (!empty($c['url']))
                        <a href="{{ esc_url($c['url']) }}"
                            class="hover:text-slate-900 underline-offset-2 hover:underline">
                            {{ $c['label'] }}
                        </a>
                    @else
                        <span class="{{ !empty($c['current']) ? 'text-slate-900' : '' }}">{{ $c['label'] }}</span>
                    @endif
                    @if ($i < count($trail) - 1)
                        <span class="mx-1 opacity-50">/</span>
                    @endif
                </li>
            @endforeach
        </ol>
    </nav>
@endif
