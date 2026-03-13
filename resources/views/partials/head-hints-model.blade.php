@php
    $pid = get_queried_object_id();

    // First profile image for early loading in the head.
    $heroImg = get_the_post_thumbnail_url($pid, 'large') ?: null;
@endphp

@push('head')
    <link rel="dns-prefetch" href="//cdnjs.cloudflare.com">
    <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
    <link rel="dns-prefetch" href="//cdn.jsdelivr.net">
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>

    @if ($heroImg)
        <link rel="preload" as="image" href="{{ esc_url($heroImg) }}" fetchpriority="high">
    @endif
@endpush
