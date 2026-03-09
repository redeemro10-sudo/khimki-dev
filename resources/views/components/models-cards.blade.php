@php
    $mapModel = function ($it) {
        $id = is_object($it) ? $it->ID ?? null : (is_array($it) ? $it['ID'] ?? null : null);
        // Если уже пришёл нормализованный массив - вернём как есть
        if (is_array($it) && isset($it['link'])) {
            return $it;
        }
        if (!$id) {
            return null;
        }

        return [
            'link' => get_permalink($id),
            'thumb' => get_the_post_thumbnail_url($id, 'medium') ?: null,
            'service' => wp_get_post_terms($id, 'service', ['fields' => 'names'])[0] ?? null,
            'district' => wp_get_post_terms($id, 'district', ['fields' => 'names'])[0] ?? null,
            'station' => wp_get_post_terms($id, 'rail_station', ['fields' => 'names'])[0] ?? null,
            'tags' => [
                'video' => (int) get_post_meta($id, '_has_video', true) === 1 ? 1 : 0,
                'online' => (int) get_post_meta($id, '_online', true) === 1 ? 1 : 0,
                'verified' => has_term('verified', 'feature', $id) ? 1 : 0,
                'vip' => has_term('vip', 'feature', $id) ? 1 : 0,
            ],
        ];
    };
@endphp

<ul class="models-grid__list grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
    @forelse($items as $it)
        @php $ctx = $mapModel($it); @endphp
        @if ($ctx)
            <li>@include('components.model-card', $ctx)</li>
        @endif
    @empty
        <li>
            <p>No results</p>
        </li>
    @endforelse
</ul>
