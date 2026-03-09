@extends('layouts.app')

@section('content')
    @php
        // Берём «SEO-контент» поста, если заполнен (см. блок 3)
        $seoText = get_post_meta(get_the_ID(), '_post_text', true);
    @endphp

    <article {{ post_class('mx-auto max-w-[760px] px-6 py-8') }}>
        <header class="mb-6">
            <h1 class="text-3xl font-bold leading-tight mb-3 break-words">{{ get_the_title() }}</h1>
            <div class="text-sm text-muted flex items-center gap-3">
                <time datetime="{{ get_post_time('c', true) }}">{{ get_the_date() }}</time>
            </div>
        </header>

        @if (has_post_thumbnail())
            <figure class="mb-6 overflow-hidden rounded-sm">
                {!! get_the_post_thumbnail(null, 'large', ['class' => 'block max-w-full h-auto']) !!}
            </figure>
        @endif

        <div class="entry-content prose prose-invert max-w-none break-words">
            @if (!empty($seoText))
                {!! wpautop(wp_kses_post($seoText)) !!}
            @else
                @php(the_content())
            @endif
        </div>
    </article>

    @include('partials.comments')
@endsection
