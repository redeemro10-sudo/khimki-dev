@php
    $id = $id ?? null;
    $title = $title ?? null;
    $tags = $tags ?? [];
    $link = $link ?? '#';
    $thumb = $thumb ?? null;
    $age = isset($age) ? (int) $age : 0;
    $bust = $bust ?? null;
    $service = $service ?? null;
    $district = $district ?? null;
    $station = $station ?? null;
    $isPriorityImage = !empty($isPriorityImage);

    $imageAltParts = array_filter([
        $title ? 'Имя: ' . $title : null,
        $age ? 'Возраст: ' . $age : null,
        $bust ? 'Грудь: ' . $bust : null,
    ]);

    $imageAlt = implode(', ', $imageAltParts);
    $imageTitle = $title ? 'Эскортница ' . $title . ($age ? ', ' . $age : '') : null;
@endphp

<article class="card border rounded-2xl overflow-hidden bg-white shadow-sm hover:shadow-md transition-shadow">
    <a href="{{ $link }}" class="block" rel="bookmark">
        <div class="card-image relative h-72 overflow-hidden bg-gray-100">
            @if (!empty($thumb))
                <img
                    src="{{ esc_url($thumb) }}"
                    alt="{{ esc_attr($imageAlt) }}"
                    @if ($imageTitle) title="{{ esc_attr($imageTitle) }}" @endif
                    loading="{{ $isPriorityImage ? 'eager' : 'lazy' }}"
                    fetchpriority="{{ $isPriorityImage ? 'high' : 'low' }}"
                    decoding="{{ $isPriorityImage ? 'auto' : 'async' }}"
                    class="w-full h-full object-cover aspect-auto">
            @endif

            @if (!empty($tags))
                @if (!empty($tags['video']) || !empty($tags['verified']))
                    <div class="absolute left-2 top-2 z-10 flex max-w-[calc(100%-1rem)] flex-wrap gap-2">
                        @if (!empty($tags['video']))
                            <span class="rounded-full bg-red-500 px-2 py-1 text-[10px] font-bold text-white">ВИДЕО</span>
                        @endif
                        @if (!empty($tags['verified']))
                            <span class="rounded-full bg-green-500 px-2 py-1 text-[10px] font-bold text-white">✓</span>
                        @endif
                    </div>
                @endif

                @if (!empty($tags['online']) || !empty($tags['vip']))
                    <div class="absolute right-2 top-2 z-10 flex max-w-[calc(100%-1rem)] flex-wrap justify-end gap-2">
                        @if (!empty($tags['vip']))
                            <span class="rounded-full bg-yellow-500 px-2 py-1 text-[10px] font-bold text-white">VIP</span>
                        @endif
                        @if (!empty($tags['online']))
                            <span class="rounded-full bg-blue-500 px-2 py-1 text-[10px] font-bold text-white">ONLINE</span>
                        @endif
                    </div>
                @endif
            @endif

            <div class="absolute inset-x-0 bottom-0 h-20 bg-gradient-to-t from-black/60 to-transparent"></div>
        </div>

        <div class="card-content p-4">
            @if (!empty($title))
                <h3 class="mb-2 truncate font-medium text-gray-900">{{ $title }}</h3>
            @endif

            <div class="mb-3 flex flex-wrap items-center gap-2">
                @if (!empty($price))
                    <span class="text-sm font-semibold text-gray-700">от {{ $price }} ₽</span>
                @endif
            </div>

            <div class="flex items-center gap-2 text-xs text-gray-600">
                <svg class="h-4 w-4 flex-shrink-0" viewBox="0 0 24 24" aria-hidden="true" fill="currentColor">
                    <path
                        d="M12 2a7 7 0 0 0-7 7c0 5.25 7 13 7 13s7-7.75 7-13a7 7 0 0 0-7-7zm0 9.5A2.5 2.5 0 1 1 12 6a2.5 2.5 0 0 1 0 5.5z" />
                </svg>
                <div class="truncate">
                    @if (!empty($station))
                        <div class="font-medium">{{ $station }}</div>
                    @endif
                    @if (!empty($district))
                        <div class="opacity-80">{{ $district }}</div>
                    @endif
                </div>
            </div>
        </div>
    </a>
</article>
