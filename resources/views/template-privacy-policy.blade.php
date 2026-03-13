{{--
  Template Name: Privacy Policy
--}}

@extends('layouts.app')

@section('content')
  @while(have_posts()) @php(the_post())
    @include('partials.page-header')

    <section class="mx-auto max-w-4xl px-4 py-4">
      <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm md:p-8">
        @include('partials.content-page', ['contentClass' => 'catalog-copy'])
      </article>
    </section>
  @endwhile
@endsection
