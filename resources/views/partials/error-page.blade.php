<?php
$statusCode = (int) ($statusCode ?? 404);
$isGone = $statusCode === 410;

$content = $isGone
    ? [
        'title' => 'СТРАНИЦА УДАЛЕНА',
        'lead' => 'Похоже, эта страница была удалена и больше недоступна.',
        'sublead' => 'Но вы можете перейти на главную и продолжить просмотр актуальных разделов сайта.',
    ]
    : [
        'title' => 'СТРАНИЦА НЕ НАЙДЕНА',
        'lead' => 'Похоже, вы перешли по устаревшей ссылке.',
        'sublead' => 'Но не расстраивайтесь, на сайте есть и другие актуальные страницы.',
    ];
?>

<section class="py-6 sm:py-10">
    <div class="overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-[0_24px_70px_-40px_rgba(15,23,42,0.45)]">
        <div class="relative isolate px-6 py-12 text-center sm:px-10 sm:py-16 lg:px-16 lg:py-20">
            <div
                class="pointer-events-none absolute inset-x-0 -top-2 bg-gradient-to-b from-slate-100/80 via-white/30 to-transparent text-[7rem] font-black leading-none tracking-[-0.08em] text-slate-100 sm:-top-4 sm:text-[9rem] lg:-top-6 lg:text-[12rem]">
                {{ $statusCode }}
            </div>

            <div class="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-slate-200 to-transparent"></div>
            <div class="absolute inset-x-0 bottom-0 h-px bg-gradient-to-r from-transparent via-slate-200 to-transparent"></div>

            <div class="relative mx-auto max-w-3xl pt-20 sm:pt-24 lg:pt-28">
                <div
                    class="mx-auto mb-8 inline-flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-blue-500 to-purple-600 text-xl font-black text-white shadow-lg shadow-blue-500/20">
                    {{ $statusCode }}
                </div>

                <h1 class="text-3xl font-black uppercase tracking-[0.04em] text-slate-950 sm:text-4xl lg:text-5xl">
                    {{ $content['title'] }}
                </h1>

                <div class="mx-auto mt-6 max-w-2xl space-y-2 text-base leading-7 text-slate-600 sm:text-lg">
                    <p>{{ $content['lead'] }}</p>
                    <p>{{ $content['sublead'] }}</p>
                </div>

                <div class="mt-10 flex items-center justify-center">
                    <a href="{{ esc_url(home_url('/')) }}"
                        class="inline-flex min-w-[180px] items-center justify-center rounded-2xl border border-slate-200 bg-white px-8 py-4 text-sm font-extrabold uppercase tracking-[0.12em] text-slate-900 transition hover:border-slate-300 hover:bg-slate-50">
                        На главную
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
