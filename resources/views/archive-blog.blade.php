@extends('layouts.app')

@section('content')
    @php
        $page = get_page_by_path('blog-seo');
        $h1 = $page ? (get_post_meta($page->ID, '_page_h1', true) ?: 'Блог') : 'Блог';
        $h2 = $page ? (string) get_post_meta($page->ID, '_page_h2', true) : '';
        $pageText = $page ? (string) get_post_meta($page->ID, '_page_text', true) : '';
        $text = trim($pageText) !== ''
            ? $pageText
            : ($page ? apply_filters('the_content', $page->post_content) : '');
    @endphp

    <section class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <header class="mb-10 overflow-hidden rounded-[2rem] p-6 shadow-sm sm:p-8">
            <div class="max-w-3xl mx-auto space-y-4">
                <h1 class="text-3xl font-semibold leading-tight text-center text-slate-950 sm:text-4xl">{{ $h1 }}</h1>
                @if ($h2 !== '')
                    <p class="text-center text-base text-slate-600 sm:text-lg">{{ $h2 }}</p>
                @endif
                @if ($text !== '')
                    <div class="prose max-w-none prose-slate prose-p:text-slate-600 prose-headings:text-slate-900">
                        {!! $text !!}
                    </div>
                @endif
            </div>
        </header>

        @if (have_posts())
            <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
                @while (have_posts())
                    @php(the_post())
                    @include('partials.post-card')
                @endwhile
            </div>

            <nav class="blog-pagination mt-10 flex justify-center" aria-label="Пагинация блога">
                {!! get_the_posts_pagination([
                    'prev_text' => '←',
                    'next_text' => '→',
                    'type' => 'list',
                    'screen_reader_text' => '',
                ]) !!}
            </nav>
        @else
            <div class="rounded-[2rem] border border-dashed border-slate-300 bg-slate-50 px-6 py-12 text-center">
                <p class="text-lg font-medium text-slate-700">Публикаций пока нет.</p>
                <p class="mt-2 text-sm text-slate-500">Когда появятся новые материалы, они будут показаны здесь.</p>
            </div>
        @endif
    </section>
@endsection
