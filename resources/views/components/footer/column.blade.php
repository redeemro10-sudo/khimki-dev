{{-- resources/views/components/footer/column.blade.php --}}
@props(['title', 'location'])

<div {{ $attributes->merge(['class' => 'flex flex-col']) }}>
    @if ($title)
        <h3 class="text-white font-bold uppercase mb-6 tracking-wider text-sm">
            {!! $title !!}
        </h3>
    @endif

    @if (has_nav_menu($location))
        <nav aria-label="{{ $title }}">
            {!! wp_nav_menu([
                'theme_location' => $location,
                'container' => false,
                'menu_class' => 'flex flex-col space-y-3 text-slate-400 text-sm',
                'item_class' => 'hover:text-white transition-colors duration-200', // Кастомный класс (потребует walker или CSS селектор, ниже CSS подход)
                'echo' => false,
                'fallback_cb' => '__return_false',
            ]) !!}
        </nav>
    @endif

    {{ $slot }}
</div>
