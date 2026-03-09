{{-- Проверяем и выводим метаполя для таксономии --}}
@extends('layouts.app')

@section('content')
    @php
        /** @var \WP_Term|null $term */
        $term = get_queried_object();
        if (!$term instanceof \WP_Term) {
            echo '<p>Term not found</p>';
            return;
        }

        $tax = get_taxonomy($term->taxonomy);
        $taxLabel = $tax?->labels?->singular_name ?: $tax?->label ?: ucfirst($term->taxonomy);

        // Заголовок для грида
        $gridTitle = 'Химки - ' . $taxLabel . ': ' . $term->name;

        // Получаем H1 и H2 метаполя для терма
        $customH1 = get_term_meta($term->term_id, '_term_h1', true);
        $customH2 = get_term_meta($term->term_id, '_term_h2', true);

        // Получаем текст для терма
        $termText = get_term_meta($term->term_id, '_term_text', true);

        $h1 = '';
        $taxLabel = null;
        $termText = '';
        $isTax = $term instanceof \WP_Term;
        // Заголовок по умолчанию для не-таксономий
        $defaultTitle =
            isset($title) && $title !== ''
                ? $title
                : ((is_archive() || is_home()) && function_exists('get_the_archive_title')
                    ? get_the_archive_title()
                    : get_the_title());

        if ($isTax) {
            /** @var \WP_Term $currentTerm */
            $currentTerm = $term;

            $metaH1 = trim(
                (string) (get_term_meta($currentTerm->term_id, '_term_h1', true) ?:
                get_term_meta($currentTerm->term_id, 'term_h1', true)),
            );
            $h1 = $metaH1 !== '' ? $metaH1 : $currentTerm->name;

            $tax = get_taxonomy($currentTerm->taxonomy);
            $taxLabel = $tax?->labels?->singular_name ?: $tax?->label ?: ucfirst($currentTerm->taxonomy);

            // Получаем текст из метаполя _term_text
            $termText = get_term_meta($currentTerm->term_id, '_term_text', true);
        }
    @endphp

    <header class="page-header mb-6">
        {{-- Используем переданный custom_h1, если он есть --}}
        <h1 class="entry-title text-3xl font-semibold">{{ $customH1 }}</h1>

        @if ($custom_h2 ?? $customH2 !== '')
            <p class="mt-2 text-base text-slate-600">{{ $customH2 ?? $customH2 }}</p>
        @endif
    </header>

    {{--     <x-models-grid-dark :title="$gridTitle" id="grid" :show-filters="true" :config="[
        'per_page' => 12,
        'order' => 'date',
        'tax' => [$term->taxonomy => [$term->slug]],
    ]" /> --}}

    {{-- Выводим текст для терма, если есть --}}
    @if ($termText)
        <div class="prose mt-2 max-w-none">
            {!! $termText !!}
        </div>
    @else
        {{-- Если текста нет — выводим описание таксономии --}}
        @php(the_content())
    @endif
@endsection
