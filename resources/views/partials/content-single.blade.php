@php
    $id = get_the_ID();
    $h1 = $id ? get_post_meta($id, '_page_h1', true) : '';
    $h2 = $id ? get_post_meta($id, '_page_h2', true) : '';
@endphp
<article @php(post_class('h-entry'))>
    <header>
        <h1 class="p-name">
            {!! $title !!}
        </h1>

        @include('partials.entry-meta')
    </header>

    <div class="e-content">
        @php(the_content())
    </div>

    @if ($pagination())
        <footer>
            <nav class="page-nav" aria-label="Page">
                {!! $pagination !!}
            </nav>
        </footer>
    @endif

    @php(comments_template())
</article>
