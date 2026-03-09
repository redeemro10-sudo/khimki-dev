@php
    $thumb =
        get_the_post_thumbnail_url(get_the_ID(), 'post-card') ?:
        get_theme_file_uri('resources/images/web-app-manifest-512x512.png');

    // Аннотация: 150 символов из excerpt/контента, без HTML/шорткодов
    $raw = get_the_excerpt();
    if ($raw === '') {
        $raw = wp_strip_all_tags(strip_shortcodes(get_the_content('')), true);
    }
    $summary = function_exists('mb_strimwidth')
        ? mb_strimwidth($raw, 0, 150, '…', 'UTF-8')
        : wp_html_excerpt($raw, 150, '…');
@endphp

<article {{ post_class('border border-line rounded-sm overflow-hidden bg-white/5') }}>
    <a href="{{ get_permalink() }}" class="block">
        <figure class="aspect-[4/3] overflow-hidden">
            <img src="{{ esc_url($thumb) }}" alt="{{ esc_attr(get_the_title()) }}" class="w-full h-full object-cover"
                loading="lazy" decoding="async">
        </figure>
    </a>

    <div class="p-4">
        <h3 class="text-lg font-semibold mb-2 break-words">
            <a href="{{ get_permalink() }}" class="hover:underline">{{ get_the_title() }}</a>
        </h3>

        <p class="text-sm text-muted mb-3 break-words whitespace-normal">{{ $summary }}</p>

        <div class="flex items-center justify-between text-xs text-muted">
            <time datetime="{{ get_post_time('c', true) }}">{{ get_the_date() }}</time>
        </div>
    </div>
</article>
