@php
    $id = get_the_ID();
    $pageText = $id ? get_post_meta($id, '_page_text', true) : '';
    $contentClass = $contentClass ?? 'catalog-copy';
@endphp

@if (!empty($pageText))
    <article class="{{ $contentClass }}">
        {!! $pageText !!}
    </article>
@else
    <article class="{{ $contentClass }}">
        @php(the_content())
    </article>
@endif

@if ($pagination())
    <nav class="page-nav" aria-label="Page">
        {!! $pagination !!}
    </nav>
@endif
