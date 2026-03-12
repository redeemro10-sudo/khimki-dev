@extends('layouts.app')

@section('content')
    @php
        $page = get_page_by_path('blog-seo');
        $h1 = $page ? (get_post_meta($page->ID, '_page_h1', true) ?: 'Блог') : 'Блог';
        $text = $page ? (string) get_post_meta($page->ID, '_page_text', true) : '';
    @endphp

    <section class="wrap mx-auto max-w-[1200px] px-6 py-6">
        <header class="mb-6">
            <h1 class="break-words text-2xl font-bold">{{ $h1 }}</h1>
            @if ($text !== '')
                <div class="prose prose-invert max-w-none break-words">{!! wpautop(wp_kses_post($text)) !!}</div>
            @endif
        </header>

        @if (have_posts())
            <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                @while (have_posts())
                    @php(the_post())
                    @include('partials.post-card')
                @endwhile
            </div>

            <nav class="blog-pagination mt-8 flex justify-center" aria-label="Пагинация блога">
                {!! get_the_posts_pagination(['prev_text' => '←', 'next_text' => '→', 'type' => 'list']) !!}
            </nav>
        @else
            <p class="text-center text-muted">Публикаций пока нет.</p>
        @endif
    </section>
@endsection
