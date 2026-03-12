{{-- Template Name: Models --}}
@extends('layouts.app')

@section('content')
    @php
        $pageId = get_queried_object_id();
        $h1 = (string) get_post_meta($pageId, '_page_h1', true);
        $h2 = (string) get_post_meta($pageId, '_page_h2', true);

        $slug = get_post_field('post_name', $pageId); // безопаснее, чем парсить URL
        $tax = [];
        $price = [];

        switch ($slug) {
            case 'na-vyyezd':
                $tax['service'] = ['prostitutki-po-vyzovu'];
                break;

            case 'deshovyye':
                $price['max'] = 14999;
                break;

            case 'proverennye':
                $tax['feature'] = ['proverennyye']; // используем ваш фактический слаг
                break;

            case 'massazh':
                // ВАРИАНТ A: перечислить все massage-термы (без правки API)
                $massage = get_terms([
                    'taxonomy' => 'massage',
                    'hide_empty' => true,
                    'fields' => 'slugs',
                ]);
                if (!is_wp_error($massage) && $massage) {
                    $tax['massage'] = $massage; // ['klassicheskiy','eroticheskiy',...]
                }
                // ВАРИАНТ B (предпочтительно): если внедрите поддержку EXISTS — просто:
                // $tax['massage'] = ['__any'];
                break;

            case 'elitnye':
                $tax['feature'] = ['vip'];
                break;
        }

        $gridConfig = [
            'per_page' => 48,
            'order' => 'date',
            'tax' => $tax, // базовый фильтр страницы
            'price' => $price,
        ];
    @endphp

    @include('partials.page-header')

    <x-models-grid-dark title="{{ $h2 ?: 'Verified models' }}" id="grid" :show-filters="true" :config="$gridConfig" />

    <section class="catalog-copy-wrap mx-auto mt-8 max-w-5xl px-4 pb-6 sm:px-6 lg:px-8">
        <div class="rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm sm:p-8 lg:p-10">
            @include('partials.content-page', ['contentClass' => 'catalog-copy'])
        </div>
    </section>
@endsection
