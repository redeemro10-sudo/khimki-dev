{{-- Template Name: Taxonomy Index --}}
@extends('layouts.app')

@section('content')
    @php
        $pageId = get_queried_object_id();

        // H1/H2 из мета
        $h1 = (string) get_post_meta($pageId, '_page_h1', true);
        $h2 = (string) get_post_meta($pageId, '_page_h2', true);

        // Слаг таксономии для индекса: из метаполя _tax_index или ?tax=...
        $taxSlug = (string) get_post_meta($pageId, '_tax_index', true);
        if ($taxSlug === '') {
            $taxSlug = sanitize_key(get_query_var('tax') ?: '');
        }

        $tax = $taxSlug && taxonomy_exists($taxSlug) ? get_taxonomy($taxSlug) : null;

        // Заголовок страницы
        $title = $h1 !== '' ? $h1 : ($tax ? $tax->labels->name ?? ($tax->label ?? $taxSlug) : get_the_title($pageId));
    @endphp

    {{-- Шапка страницы --}}
    @include('partials.page-header', ['title' => $title, 'subtitle' => $h2 ?? null])

    @if ($tax)
        @php
            // верхнеуровневые термы выбранной таксы
            $terms = get_terms([
                'taxonomy' => $taxSlug,
                'hide_empty' => true,
                'parent' => 0,
            ]);
        @endphp

        @if (!is_wp_error($terms) && !empty($terms))
            <x-terms-list :terms="$terms" :title="$h2 ?? $tax->labels->name" />
        @else
            <p>Термов пока нет.</p>
        @endif
    @else
        <p class="text-red-600">
            Не выбрана таксономия для этой страницы. Укажите слаг в метаполе <code>_tax_index</code>
            (например: <code>rail_station</code>, <code>bust_size</code>, <code>hair_color</code>).
        </p>
    @endif

    @include('partials.content-page')
@endsection
