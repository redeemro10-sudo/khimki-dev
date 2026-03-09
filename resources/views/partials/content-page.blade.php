@php
    $id = get_the_ID();
    $pageText = $id ? get_post_meta($id, '_page_text', true) : '';
@endphp

@if (!empty($pageText))
    <article class="prose mt-2 max-w-none">
        {!! $pageText !!}
    </article>
@else
    @php(the_content())
@endif

@if ($pagination())
    <nav class="page-nav" aria-label="Page">
        {!! $pagination !!}
    </nav>
@endif
