{{--
  Template Name: Sitemap
--}}

@extends('layouts.app')

@section('content')
  @while(have_posts()) @php(the_post())
    <?php
      $currentPageId = get_the_ID();

      $pages = get_pages([
          'sort_column' => 'menu_order,post_title',
          'sort_order' => 'ASC',
          'post_status' => 'publish',
      ]);

      $pageLinks = array_values(array_filter($pages, static function ($page) use ($currentPageId) {
          return $page instanceof \WP_Post
              && (int) $page->ID !== (int) $currentPageId
              && (int) $page->post_parent === 0;
      }));

      $archiveLinks = [];

      if (post_type_exists('blog')) {
          $blogType = get_post_type_object('blog');
          $blogArchiveUrl = get_post_type_archive_link('blog');

          if ($blogType && $blogArchiveUrl) {
              $archiveLinks[] = [
                  'title' => $blogType->labels->name ?? 'Блог',
                  'url' => $blogArchiveUrl,
                  'description' => 'Архив статей и публикаций',
              ];
          }
      }

      $taxonomySections = [];

      foreach ([
          ['slug' => 'service', 'title' => 'Услуги'],
          ['slug' => 'district', 'title' => 'Районы'],
          ['slug' => 'rail_station', 'title' => 'Станции'],
          ['slug' => 'nationality', 'title' => 'Национальность'],
          ['slug' => 'hair_color', 'title' => 'Цвет волос'],
      ] as $section) {
          if (! taxonomy_exists($section['slug'])) {
              continue;
          }

          $terms = get_terms([
              'taxonomy' => $section['slug'],
              'hide_empty' => true,
              'parent' => 0,
          ]);

          if (is_wp_error($terms) || empty($terms)) {
              continue;
          }

          $taxonomySections[] = [
              'title' => $section['title'],
              'terms' => $terms,
          ];
      }
    ?>

    @include('partials.page-header')

    <section class="mx-auto max-w-7xl px-4 py-4">
      <div class="space-y-8">
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
          @include('partials.content-page', ['contentClass' => 'catalog-copy'])
        </div>

        @if (!empty($pageLinks))
          <section class="space-y-4">
            <div class="flex items-center justify-between gap-4">
              <h2 class="text-2xl font-semibold text-slate-900">Основные страницы</h2>
              <span class="text-sm text-slate-400">{{ count($pageLinks) }} ссылок</span>
            </div>

            <ul class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
              @foreach ($pageLinks as $page)
                <li>
                  <a class="block rounded-2xl border border-slate-200 bg-white p-4 transition hover:bg-slate-50" href="{{ get_permalink($page) }}">
                    <h3 class="font-medium text-slate-900">{{ get_the_title($page) }}</h3>
                  </a>
                </li>
              @endforeach
            </ul>
          </section>
        @endif

        @if (!empty($archiveLinks))
          <section class="space-y-4">
            <h2 class="text-2xl font-semibold text-slate-900">Разделы сайта</h2>

            <ul class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
              @foreach ($archiveLinks as $link)
                <li>
                  <a class="block rounded-2xl border border-slate-200 bg-white p-4 transition hover:bg-slate-50" href="{{ $link['url'] }}">
                    <h3 class="font-medium text-slate-900">{{ $link['title'] }}</h3>
                    <div class="mt-1 text-sm text-slate-500">{{ $link['description'] }}</div>
                  </a>
                </li>
              @endforeach
            </ul>
          </section>
        @endif

        @foreach ($taxonomySections as $section)
          <x-terms-list :terms="$section['terms']" :title="$section['title']" />
        @endforeach
      </div>
    </section>
  @endwhile
@endsection
