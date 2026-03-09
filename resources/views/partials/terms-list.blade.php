@props(['terms' => [], 'title' => null])

@php
    $items = [];
    if (!is_wp_error($terms)) {
        foreach ((array) $terms as $t) {
            if ($t instanceof \WP_Term) {
                $items[] = $t;
            }
        }
    }
@endphp

@if (count($items))
    <section class="mb-8">
        @if ($title)
            <h2 class="text-xl font-semibold mb-3">{{ $title }}</h2>
        @endif
        <ul class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
            @foreach ($items as $t)
                <li>
                    <a class="block border rounded-2xl p-4 hover:bg-gray-50 transition" href="{{ get_term_link($t) }}">
                        <div class="font-medium truncate">{{ $t->name }}</div>
                        <div class="text-sm opacity-70">Анкет: {{ number_format_i18n((int) $t->count) }}</div>
                    </a>
                </li>
            @endforeach
        </ul>
    </section>
@endif
