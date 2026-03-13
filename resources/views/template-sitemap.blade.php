{{--
  Template Name: Sitemap
--}}

@extends('layouts.app')

@section('content')
  @while(have_posts()) @php(the_post())
    @php
      $pageByTemplate = static function (string $template): ?\WP_Post {
          $pages = get_posts([
              'post_type' => 'page',
              'posts_per_page' => 1,
              'post_status' => 'publish',
              'meta_key' => '_wp_page_template',
              'meta_value' => $template,
              'suppress_filters' => true,
          ]);

          return $pages[0] ?? null;
      };

      $resolvePageLink = static function (array $paths, string $label, ?string $fallbackUrl = null): ?array {
          foreach ($paths as $path) {
              $page = get_page_by_path($path);
              if ($page instanceof \WP_Post) {
                  return [
                      'label' => $label,
                      'url' => get_permalink($page),
                  ];
              }
          }

          if ($fallbackUrl) {
              return [
                  'label' => $label,
                  'url' => $fallbackUrl,
              ];
          }

          return null;
      };

      $infoLinks = array_values(array_filter([
          $resolvePageLink(['o-nas', 'about'], 'О нас'),
          (function () {
              $blogUrl = get_post_type_archive_link('blog');

              return $blogUrl ? [
                  'label' => 'Блог',
                  'url' => $blogUrl,
              ] : null;
          })(),
          (function () use ($pageByTemplate, $resolvePageLink) {
              $faqPage = $pageByTemplate('template-faq.blade.php');
              if ($faqPage instanceof \WP_Post) {
                  return [
                      'label' => get_the_title($faqPage) ?: 'FAQ',
                      'url' => get_permalink($faqPage),
                  ];
              }

              return $resolvePageLink(['faq'], 'FAQ');
          })(),
      ]));

      $sectionLinks = array_values(array_filter([
          $resolvePageLink(['proverennye'], 'Проверенные', home_url('/proverennye/')),
          $resolvePageLink(['deshovyye'], 'Дешёвые', home_url('/deshovyye/')),
          $resolvePageLink(['elitnye'], 'Элитные', home_url('/elitnye/')),
          $resolvePageLink(['na-vyyezd'], 'Выезд', home_url('/na-vyyezd/')),
          $resolvePageLink(['apartamenty'], 'Апартаменты', home_url('/apartamenty/')),
          $resolvePageLink(['uslugi'], 'Услуги', home_url('/uslugi/')),
          $resolvePageLink(['rajony'], 'Районы', home_url('/rajony/')),
      ]));

      $districtTerms = [];
      if (taxonomy_exists('district')) {
          $districtTerms = get_terms([
              'taxonomy' => 'district',
              'hide_empty' => true,
              'parent' => 0,
              'orderby' => 'name',
              'order' => 'ASC',
          ]);

          if (is_wp_error($districtTerms)) {
              $districtTerms = [];
          }
      }

      $serviceTerms = [];
      if (taxonomy_exists('service')) {
          $serviceTerms = get_terms([
              'taxonomy' => 'service',
              'hide_empty' => true,
              'parent' => 0,
              'orderby' => 'name',
              'order' => 'ASC',
          ]);

          if (is_wp_error($serviceTerms)) {
              $serviceTerms = [];
          }
      }
    @endphp

    @include('partials.page-header')

    <section class="mx-auto max-w-7xl px-4 py-4">
      <div class="space-y-8">
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8">
          @include('partials.content-page', ['contentClass' => 'catalog-copy'])
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
          <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-2xl font-semibold text-slate-900">Информация</h2>

            @if (!empty($infoLinks))
              <ul class="mt-4 space-y-3">
                @foreach ($infoLinks as $link)
                  <li>
                    <a class="text-base text-slate-700 transition hover:text-slate-950 hover:underline" href="{{ $link['url'] }}">
                      {{ $link['label'] }}
                    </a>
                  </li>
                @endforeach
              </ul>
            @endif
          </section>

          <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-2xl font-semibold text-slate-900">Разделы</h2>

            @if (!empty($sectionLinks))
              <ul class="mt-4 space-y-3">
                @foreach ($sectionLinks as $link)
                  <li>
                    <a class="text-base text-slate-700 transition hover:text-slate-950 hover:underline" href="{{ $link['url'] }}">
                      {{ $link['label'] }}
                    </a>
                  </li>
                @endforeach
              </ul>
            @endif
          </section>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
          <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-2xl font-semibold text-slate-900">Районы</h2>

            @if (!empty($districtTerms))
              <ul class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
                @foreach ($districtTerms as $term)
                  <li>
                    <a class="text-base text-slate-700 transition hover:text-slate-950 hover:underline" href="{{ get_term_link($term) }}">
                      {{ $term->name }}
                    </a>
                  </li>
                @endforeach
              </ul>
            @endif
          </section>

          <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-2xl font-semibold text-slate-900">Услуги</h2>

            @if (!empty($serviceTerms))
              <ul class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2">
                @foreach ($serviceTerms as $term)
                  <li>
                    <a class="text-base text-slate-700 transition hover:text-slate-950 hover:underline" href="{{ get_term_link($term) }}">
                      {{ $term->name }}
                    </a>
                  </li>
                @endforeach
              </ul>
            @endif
          </section>
        </div>
      </div>
    </section>
  @endwhile
@endsection
