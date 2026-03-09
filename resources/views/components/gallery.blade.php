@php
    // Видео (массив элементов вида ['id'=>int|null, 'url'=>string|null])
    $videos = get_post_meta(get_the_ID(), '_videos', true);
    $videos = is_array($videos) ? array_values($videos) : [];

    // Галерея изображений
    $ids = array_values(array_filter(array_map('intval', get_post_meta(get_the_ID(), '_gallery_ids', true) ?: [])));
    if (empty($ids)) {
        $thumb = get_post_thumbnail_id();
        if ($thumb) {
            $ids = [$thumb];
        }
    }
@endphp

@if (!empty($videos))
    <div class="mb-4 space-y-4">
        @foreach ($videos as $v)
            @php
                $vid = isset($v['id']) ? intval($v['id']) : 0;
                $url = isset($v['url']) ? (string) $v['url'] : '';
                $src = $vid ? wp_get_attachment_url($vid) : $url;
            @endphp
            @if ($src)
                @if (preg_match('~^(https?:)?//(www\.)?(youtube|youtu\.be|vimeo)~i', $src))
                    {!! wp_oembed_get($src) !!}
                @else
                    @php $mime = wp_check_filetype($src)['type'] ?: 'video/mp4'; @endphp
                    <video controls playsinline preload="metadata" style="width:100%;max-height:60vh">
                        <source src="{{ esc_url($src) }}" type="{{ esc_attr($mime) }}">
                    </video>
                @endif
            @endif
        @endforeach
    </div>
@endif

@if (!empty($ids))
    <div class="gallery grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
        @foreach ($ids as $i => $imgId)
            {!! wp_get_attachment_image($imgId, 'large', false, [
                'class' => $i === 0 ? 'is-lcp w-full h-auto' : 'w-full h-auto',
            ]) !!}
        @endforeach
    </div>
@endif
