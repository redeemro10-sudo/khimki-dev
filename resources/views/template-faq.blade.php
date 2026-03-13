{{--
  Template Name: FAQ
--}}

@extends('layouts.app')

@section('content')
  @while(have_posts()) @php(the_post())
    <?php
      $faqItems = \App\get_faq_items(get_the_ID());
    ?>

    @include('partials.page-header')

    <section class="mx-auto max-w-5xl px-4 py-4">
      <div class="space-y-4">
          <?php
            ob_start();
          ?>
          @include('partials.content-page')
          <?php
            $pageContent = trim(ob_get_clean());
          ?>

          @if (!empty($pageContent))
            <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm prose max-w-none prose-slate">
              {!! $pageContent !!}
            </article>
          @endif

          @if (!empty($faqItems))
            <div class="space-y-3" itemscope itemtype="https://schema.org/FAQPage">
              @foreach ($faqItems as $item)
                <details class="group overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm" itemprop="mainEntity" itemscope itemtype="https://schema.org/Question">
                  <summary class="flex cursor-pointer list-none items-center justify-between gap-4 px-5 py-4 text-left">
                    <span class="text-base font-semibold text-slate-900" itemprop="name">
                      {{ $item['question'] }}
                    </span>
                    <span class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-full bg-slate-100 text-slate-500 transition-transform duration-200 group-open:rotate-180">
                      <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                        <path fill-rule="evenodd"
                          d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.24a.75.75 0 01-1.06 0L5.21 8.27a.75.75 0 01.02-1.06z"
                          clip-rule="evenodd" />
                      </svg>
                    </span>
                  </summary>

                  <div class="border-t border-slate-100 px-5 py-4 text-sm leading-7 text-slate-600" itemprop="acceptedAnswer" itemscope itemtype="https://schema.org/Answer">
                    <div itemprop="text">
                      {!! wpautop($item['answer']) !!}
                    </div>
                  </div>
                </details>
              @endforeach
            </div>
          @else
            <div class="rounded-3xl border border-dashed border-slate-300 bg-slate-50 p-6 text-sm leading-6 text-slate-500">
              Вопросы для FAQ пока не добавлены. Откройте страницу в админке WordPress, выберите шаблон `FAQ` и заполните
              блок с вопросами под редактором.
            </div>
          @endif
      </div>
    </section>
  @endwhile
@endsection
