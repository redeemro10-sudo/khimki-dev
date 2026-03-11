@props(['title', 'location'])

<div {{ $attributes->merge(['class' => 'flex flex-col']) }}>
    @if ($title)
        <h3 class="mb-4 border-b border-black w-fit pb-3 text-sm font-semibold uppercase tracking-wider text-slate-900">
            {!! $title !!}
        </h3>
    @endif

    @if (has_nav_menu($location))
        <nav aria-label="{{ $title }}">
            {!! wp_nav_menu([
                'theme_location' => $location,
                'container' => false,
                'menu_class' => 'flex flex-col text-sm text-slate-600',
                'echo' => false,
                'fallback_cb' => '__return_false',
            ]) !!}
        </nav>
    @endif

    {{ $slot }}
</div>
