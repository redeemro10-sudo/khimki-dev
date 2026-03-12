@php
    $thumb =
        get_the_post_thumbnail_url(get_the_ID(), 'large') ?:
        get_theme_file_uri('resources/images/web-app-manifest-512x512.png');

    $raw = get_the_excerpt();
    if ($raw === '') {
        $raw = wp_strip_all_tags(strip_shortcodes(get_the_content('')), true);
    }

    $summary = function_exists('mb_strimwidth')
        ? mb_strimwidth($raw, 0, 160, '...', 'UTF-8')
        : wp_html_excerpt($raw, 160, '...');

    $primaryCategory = get_the_terms(get_the_ID(), 'category');
    $primaryCategory = !is_wp_error($primaryCategory) && !empty($primaryCategory) ? $primaryCategory[0] : null;
@endphp

<article {{ post_class('group overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm transition-all duration-300 hover:-translate-y-1 hover:shadow-xl') }}>
    <a href="{{ get_permalink() }}" class="block" rel="bookmark">
        <figure class="relative aspect-[4/3] overflow-hidden bg-slate-100">
            <img src="{{ esc_url($thumb) }}" alt="{{ esc_attr(get_the_title()) }}" class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
                loading="lazy" decoding="async">

            <div class="absolute inset-x-0 bottom-0 h-24 bg-gradient-to-t from-slate-950/55 via-slate-950/10 to-transparent"></div>

            <div class="absolute left-4 top-4 flex flex-wrap items-center gap-2">
                <span class="rounded-full bg-white/92 px-3 py-1 text-xs font-semibold text-slate-800 shadow-sm">
                    {{ get_the_date('d.m.Y') }}
                </span>
                @if ($primaryCategory)
                    <span class="rounded-full bg-blue-600/92 px-3 py-1 text-xs font-semibold text-white shadow-sm">
                        {{ $primaryCategory->name }}
                    </span>
                @endif
            </div>
        </figure>
    </a>

    <div class="flex min-h-[220px] flex-col p-5">
        <h3 class="mb-3 text-lg font-semibold leading-snug text-slate-900">
            <a href="{{ get_permalink() }}" class="transition hover:text-blue-700">{{ get_the_title() }}</a>
        </h3>

        @if ($summary !== '')
            <p class="mb-5 text-sm leading-6 text-slate-600">{{ $summary }}</p>
        @endif

        <div class="mt-auto flex items-center justify-between gap-3 border-t border-slate-100 pt-4">
            <time class="text-xs font-medium uppercase tracking-[0.18em] text-slate-400" datetime="{{ get_post_time('c', true) }}">
                {{ get_the_date('j F Y') }}
            </time>

            <a href="{{ get_permalink() }}"
                class="inline-flex items-center gap-2 rounded-full border border-slate-200 px-4 py-2 text-sm font-medium text-slate-800 transition hover:border-slate-300 hover:bg-slate-50">
                Читать
                <span aria-hidden="true">→</span>
            </a>
        </div>
    </div>
</article>
