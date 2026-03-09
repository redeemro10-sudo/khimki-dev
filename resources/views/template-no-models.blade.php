{{-- Template Name: No Models --}}
@extends('layouts.app')

@section('content')
    @php
        $pageId = get_queried_object_id();
        $h1 = (string) get_post_meta($pageId, '_page_h1', true);
        $h2 = (string) get_post_meta($pageId, '_page_h2', true);
    @endphp

    @include('partials.page-header')

    @include('partials.content-page')
@endsection
