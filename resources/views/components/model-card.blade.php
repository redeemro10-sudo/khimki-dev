@php
    $tags = $tags ?? [];
    $link = $link ?? '#';
    $thumb = $thumb ?? null;
    $service = $service ?? null;
    $district = $district ?? null;
    $station = $station ?? null;
@endphp

<article class="card border rounded-2xl overflow-hidden bg-white shadow-sm hover:shadow-md transition-shadow">
    <a href="{{ $link }}" class="block" rel="bookmark">
        <div class="card-image relative h-72 overflow-hidden bg-gray-100">
            @if (!empty($thumb))
                <img src="{{ esc_url($thumb) }}" alt="{{ isset($title) ? e($title) : '' }}" loading="lazy" decoding="async"
                    class="w-full h-full object-cover aspect-auto">
            @endif

            {{-- Бейджи как в JS --}}
            @if (!empty($tags))
                @if (!empty($tags['video']))
                    <span
                        class="absolute top-2 left-2 px-2 py-1 rounded-full text-[10px] font-bold bg-red-500 text-white">ВИДЕО</span>
                @endif
                @if (!empty($tags['verified']))
                    <span
                        class="absolute top-2 left-14 px-2 py-1 rounded-full text-[10px] font-bold bg-green-500 text-white">✓</span>
                @endif
                @if (!empty($tags['online']))
                    <span
                        class="absolute top-2 right-2 px-2 py-1 rounded-full text-[10px] font-bold bg-blue-500 text-white">ONLINE</span>
                @endif
                @if (!empty($tags['vip']))
                    <span
                        class="absolute top-2 right-16 px-2 py-1 rounded-full text-[10px] font-bold bg-yellow-500 text-white">VIP</span>
                @endif
            @endif

            {{-- Градиент как в JS --}}
            <div class="absolute inset-x-0 bottom-0 h-20 bg-gradient-to-t from-black/60 to-transparent"></div>
        </div>

        <div class="card-content p-4">
            @if (!empty($title))
                <h3 class="font-medium text-gray-900 mb-2 truncate">{{ $title }}</h3>
            @endif

            <div class="flex items-center gap-2 mb-3 flex-wrap">
                @if (!empty($price))
                    <span class="text-sm font-semibold text-gray-700">от {{ $price }} ₽</span>
                @endif
            </div>

            <div class="flex items-center gap-2 text-gray-600 text-xs">
                <svg class="w-4 h-4 flex-shrink-0" viewBox="0 0 24 24" aria-hidden="true" fill="currentColor">
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
