{{-- Template Name: No Models --}}
@extends('layouts.app')

@section('content')
    @php
        $pageId = get_queried_object_id();
        $h1 = (string) get_post_meta($pageId, '_page_h1', true);
        $h2 = (string) get_post_meta($pageId, '_page_h2', true);
    @endphp

    @include('partials.page-header')

    <section class="catalog-copy-wrap mx-auto mt-8 max-w-5xl px-4 pb-6 sm:px-6 lg:px-8">
        <div class="catalog-copy rounded-[2rem] border border-slate-200 bg-white p-6 shadow-sm sm:p-8 lg:p-10">
            @include('partials.content-page', ['contentClass' => 'catalog-copy'])
        </div>
    </section>
@endsection
