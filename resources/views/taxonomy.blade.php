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

        $gridTitle = 'Химки - ' . $taxLabel . ': ' . $term->name;

        $customH1 = (string) get_term_meta($term->term_id, '_term_h1', true);
        $customH2 = (string) get_term_meta($term->term_id, '_term_h2', true);

        $termText = (string) get_term_meta($term->term_id, '_term_text', true);
    @endphp

    {{-- Здесь НЕ отключаем H1, он рендерится в partial --}}
    @include('partials.term-header', [
        'custom_h1' => $customH1,
        'custom_h2' => $customH2,
        // 'show_h1' => false, // ← удалить/не передавать
    ])

    <x-models-grid-dark :title="$gridTitle" id="grid" :show-filters="true" :config="[
        'per_page' => 48,
        'order' => 'date',
        'tax' => [$term->taxonomy => [$term->slug]],
    ]" />

    {{-- Текст для терма или описание таксономии --}}
    <section class="catalog-copy-wrap mx-auto mt-8 max-w-5xl px-4 pb-6 sm:px-6 lg:px-8">
        <div class="catalog-copy rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm sm:p-8 lg:p-10">
            @if (trim($termText) !== '')
                {!! $termText !!}
            @else
                {!! term_description($term) !!}
            @endif
        </div>
    </section>
@endsection
