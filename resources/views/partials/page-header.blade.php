@php
    $pageId = get_queried_object_id();
    $customH1 = (string) get_post_meta($pageId, '_page_h1', true);
    $customH2 = (string) get_post_meta($pageId, '_page_h2', true);
@endphp

<header class="page-header mb-6">
    <h1 class="entry-title text-3xl font-semibold">
        {{ $customH1 !== '' ? $customH1 : (isset($title) ? $title : get_the_title()) }}
    </h1>

    @if ($customH2 !== '')
        <p class="mt-2 text-base text-slate-600">{{ $customH2 }}</p>
    @endif
</header>
