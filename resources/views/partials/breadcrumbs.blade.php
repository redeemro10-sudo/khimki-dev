<?php
if (is_front_page()) {
    return;
}

$responseCode = (int) http_response_code();
$blogArchiveUrl = get_post_type_archive_link('blog');
$blogLabel = (function () {
    $object = get_post_type_object('blog');

    return $object && !empty($object->labels->name) ? $object->labels->name : __('Блог', 'app');
})();

$underBlog = function ($post) use ($blogArchiveUrl) {
    if (!$post || !$blogArchiveUrl) {
        return false;
    }

    $postPath = '/' . ltrim((string) parse_url(get_permalink($post), PHP_URL_PATH), '/');
    $archivePath = '/' . trim((string) parse_url($blogArchiveUrl, PHP_URL_PATH), '/');

    return $postPath === $archivePath || str_starts_with($postPath, $archivePath . '/');
};

$paged = max(1, (int) get_query_var('paged'), (int) get_query_var('page'));

$trail = [];
$trail[] = ['label' => 'Главная', 'url' => home_url('/')];

if (is_404()) {
    $trail[] = [
        'label' => $responseCode === 410 ? 'Ошибка 410' : 'Ошибка 404',
        'url' => null,
        'current' => true,
    ];
} elseif (is_home()) {
    $blogId = (int) get_option('page_for_posts');
    $trail[] = ['label' => $blogId ? get_the_title($blogId) : $blogLabel, 'url' => null, 'current' => true];
} elseif (is_post_type_archive('blog')) {
    if ($paged > 1) {
        $trail[] = ['label' => $blogLabel, 'url' => $blogArchiveUrl];
        $trail[] = ['label' => 'Страница ' . $paged, 'url' => null, 'current' => true];
    } else {
        $trail[] = ['label' => $blogLabel, 'url' => null, 'current' => true];
    }
} elseif (is_tax() || is_category() || is_tag()) {
    /** @var \WP_Term $term */
    $term = get_queried_object();

    if ($term instanceof \WP_Term) {
        $taxonomy = get_taxonomy($term->taxonomy);

        if ($taxonomy && in_array('blog', (array) $taxonomy->object_type, true)) {
            $trail[] = ['label' => $blogLabel, 'url' => $blogArchiveUrl];
        } else {
            global $wp_query;
            if (!empty($wp_query->posts[0]) && $underBlog($wp_query->posts[0])) {
                $trail[] = ['label' => $blogLabel, 'url' => $blogArchiveUrl];
            }
        }

        $maybe = get_posts([
            'post_type' => 'page',
            'posts_per_page' => 1,
            'fields' => 'ids',
            'meta_key' => '_tax_index',
            'meta_value' => $term->taxonomy,
            'suppress_filters' => true,
        ]);

        if (!empty($maybe[0])) {
            $indexPageId = (int) $maybe[0];
            $trail[] = ['label' => get_the_title($indexPageId), 'url' => get_permalink($indexPageId)];
        } else {
            $trail[] = ['label' => $taxonomy?->labels?->name ?: ucfirst($term->taxonomy), 'url' => null];
        }

        $ancestors = array_reverse(get_ancestors($term->term_id, $term->taxonomy, 'taxonomy'));
        foreach ($ancestors as $ancestorId) {
            $ancestor = get_term($ancestorId, $term->taxonomy);
            if ($ancestor && !is_wp_error($ancestor)) {
                $trail[] = ['label' => $ancestor->name, 'url' => get_term_link($ancestor)];
            }
        }

        $trail[] = ['label' => $term->name, 'url' => null, 'current' => true];
    }
} elseif (is_page()) {
    $pageId = get_queried_object_id();

    foreach (array_reverse(get_post_ancestors($pageId)) as $ancestorId) {
        $trail[] = ['label' => get_the_title($ancestorId), 'url' => get_permalink($ancestorId)];
    }

    $trail[] = ['label' => get_the_title($pageId), 'url' => null, 'current' => true];
} elseif (is_singular()) {
    $post = get_post();

    if ($post && ($post->post_type === 'blog' || ($post->post_type === 'post' && $underBlog($post)))) {
        $trail[] = ['label' => $blogLabel, 'url' => $blogArchiveUrl];
    }

    $trail[] = ['label' => get_the_title($post), 'url' => null, 'current' => true];
} elseif (is_search()) {
    $trail[] = ['label' => 'Поиск', 'url' => null, 'current' => true];
} elseif (is_post_type_archive() || is_author() || is_date()) {
    $trail[] = ['label' => get_the_archive_title(), 'url' => null, 'current' => true];
}
?>

@if (!empty($trail))
    <nav class="mx-auto max-w-7xl px-4 pt-3 text-xs text-slate-600" aria-label="breadcrumb">
        <ol class="flex flex-wrap items-center gap-x-2 gap-y-1">
            @foreach ($trail as $index => $crumb)
                <li class="inline">
                    @if (!empty($crumb['url']))
                        <a href="{{ esc_url($crumb['url']) }}" class="underline-offset-2 hover:text-slate-900 hover:underline">
                            {{ $crumb['label'] }}
                        </a>
                    @else
                        <span class="{{ !empty($crumb['current']) ? 'text-slate-900' : '' }}">{{ $crumb['label'] }}</span>
                    @endif

                    @if ($index < count($trail) - 1)
                        <span class="mx-1 opacity-50">/</span>
                    @endif
                </li>
            @endforeach
        </ol>
    </nav>
@endif
