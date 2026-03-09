@php
    $pid = get_queried_object_id();

    // 1-я картинка анкеты (hero)
    $heroImg = get_the_post_thumbnail_url($pid, 'large') ?: null;

    // Если есть видео
    $videoUrl = (string) get_post_meta($pid, '_video_src', true); // пример
    $isMp4 = $videoUrl && preg_match('~\.mp4(?:$|\?)~i', $videoUrl);
@endphp

@push('head')
    {{-- CDN только здесь (страницы анкет) --}}
    <link rel="dns-prefetch" href="//cdnjs.cloudflare.com">
    <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
    <link rel="dns-prefetch" href="//cdn.jsdelivr.net">
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>

    {{-- Прелоад первого изображения анкеты --}}
    @if ($heroImg)
        <link rel="preload" as="image" href="{{ esc_url($heroImg) }}" fetchpriority="high">
    @endif

    {{-- Прелоад видео, если есть --}}
    @if ($isMp4)
        <link rel="preload" as="video" href="{{ esc_url($videoUrl) }}" type="video/mp4" crossorigin>
    @endif
@endpush
