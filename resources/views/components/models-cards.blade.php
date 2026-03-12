@php
    $mapModel = function ($item) {
        $id = is_object($item) ? $item->ID ?? null : (is_array($item) ? $item['ID'] ?? null : null);

        if (is_array($item) && isset($item['link'])) {
            return $item;
        }

        if (!$id) {
            return null;
        }

        return [
            'id' => $id,
            'title' => get_the_title($id),
            'link' => get_permalink($id),
            'thumb' => get_the_post_thumbnail_url($id, 'medium') ?: null,
            'age' => (int) get_post_meta($id, 'age', true),
            'bust' => wp_get_post_terms($id, 'bust_size', ['fields' => 'names'])[0] ?? null,
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

<ul class="models-grid__list grid grid-cols-2 gap-4 md:grid-cols-3 lg:grid-cols-4">
    @forelse($items as $item)
        @php $context = $mapModel($item); @endphp
        @if ($context)
            <li>@include('components.model-card', array_merge($context, ['isPriorityImage' => $loop->first]))</li>
        @endif
    @empty
        <li>
            <p>No results</p>
        </li>
    @endforelse
</ul>
