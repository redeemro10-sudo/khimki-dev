@extends('layouts.app')

@section('content')
    @php
        $seoText = get_post_meta(get_the_ID(), '_post_text', true);
        $categoryTerms = get_the_terms(get_the_ID(), 'category');
        $categoryTerms = !is_wp_error($categoryTerms) && !empty($categoryTerms) ? $categoryTerms : [];
        $archiveUrl = post_type_exists('blog') ? get_post_type_archive_link('blog') : null;
        $articleHtml = !empty($seoText) ? wp_kses_post($seoText) : apply_filters('the_content', get_the_content());
        $articleHtml = preg_replace('/\sitemprop=(["\']).*?\1/iu', '', $articleHtml);
        $articleHtml = preg_replace('/\sitemtype=(["\']).*?\1/iu', '', $articleHtml);
        $articleHtml = preg_replace('/\sitemscope\b/iu', '', $articleHtml);
    @endphp

    <article {{ post_class('mx-auto max-w-5xl px-4 py-8 sm:px-6 lg:px-8') }}>
        <div class="mx-auto max-w-3xl">
            @if ($archiveUrl)
                <a href="{{ $archiveUrl }}"
                    class="mb-6 inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 transition hover:border-slate-300 hover:bg-slate-50">
                    <span aria-hidden="true">←</span>
                    Ко всем записям
                </a>
            @endif

            <header class="mb-8">
                <div class="mb-4 flex flex-wrap items-center gap-2">
                    <span class="rounded-full bg-slate-900 px-3 py-1 text-xs font-semibold uppercase tracking-[0.2em] text-white">
                        Блог
                    </span>
                    @foreach ($categoryTerms as $term)
                        <span class="rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-xs font-medium text-slate-600">
                            {{ $term->name }}
                        </span>
                    @endforeach
                </div>

                <h1 class="mb-4 text-3xl font-semibold leading-tight text-slate-950 sm:text-4xl lg:text-5xl">
                    {{ get_the_title() }}
                </h1>

                <div class="flex flex-wrap items-center gap-x-4 gap-y-2 text-sm text-slate-500">
                    <time class="font-medium text-slate-700" datetime="{{ get_post_time('c', true) }}">{{ get_the_date('j F Y') }}</time>
                    <span>Обновлено {{ get_the_modified_date('j F Y') }}</span>
                </div>
            </header>
        </div>

        @if (has_post_thumbnail())
            <figure class="mb-10 overflow-hidden rounded-[2rem] border border-slate-200 bg-slate-100 shadow-sm">
                {!! get_the_post_thumbnail(null, 'full', ['class' => 'block h-auto max-h-[640px] w-full object-cover']) !!}
            </figure>
        @endif

        <div class="mx-auto max-w-3xl rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm sm:p-8 lg:p-10">
            <div class="blog-entry-content prose max-w-none prose-slate prose-headings:font-semibold prose-headings:text-slate-950 prose-p:text-slate-700 prose-a:text-blue-700 prose-a:no-underline hover:prose-a:text-blue-800 prose-strong:text-slate-900 prose-li:text-slate-700 prose-blockquote:border-slate-300 prose-blockquote:text-slate-700 prose-img:rounded-2xl">
                {!! $articleHtml !!}
            </div>
        </div>
    </article>

    @include('partials.comments')
@endsection
