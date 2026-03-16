@php
    $footerMenus = collect([
        [
            'title' => 'Разделы',
            'location' => 'footer_navigation',
        ],
        [
            'title' => 'Информация',
            'location' => 'footer_community',
        ],
        [
            'title' => 'Правовая информация',
            'location' => 'footer_legal',
        ],
    ])->filter(fn ($menu) => has_nav_menu($menu['location']));

    $footerContacts = \App\ContactData::footerContacts();

    $sitemapPage = get_page_by_path('sitemap');
    $sitemapUrl = $sitemapPage ? get_permalink($sitemapPage) : home_url('/sitemap/');
    $copyrightYears = '2025-' . wp_date('Y');
    $isFrontPage = is_front_page();
@endphp

<footer class="content-info mt-10 border-t">
    <div class="mx-auto grid max-w-7xl gap-10 px-4 py-8 text-sm text-slate-600 lg:grid-cols-[minmax(0,1.2fr)_minmax(0,2fr)] lg:items-start">
        <div class="max-w-md space-y-5">
            @if ($isFrontPage)
                <span class="brand inline-flex items-center gap-3 text-lg font-semibold text-slate-900">
                    <span
                        class="uppercase flex h-10 w-10 items-center justify-center rounded-2xl bg-gradient-to-br from-blue-500 to-purple-600 text-sm font-bold text-white shadow-md">
                        {{ substr($siteName, 0, 1) }}
                    </span>
                    <span>{!! $siteName !!}</span>
                </span>
            @else
                <a class="brand inline-flex items-center gap-3 text-lg font-semibold text-slate-900" href="{{ home_url('/') }}">
                    <span
                        class="uppercase flex h-10 w-10 items-center justify-center rounded-2xl bg-gradient-to-br from-blue-500 to-purple-600 text-sm font-bold text-white shadow-md">
                        {{ substr($siteName, 0, 1) }}
                    </span>
                    <span>{!! $siteName !!}</span>
                </a>
            @endif

            <div class="max-w-xs text-sm leading-6 text-slate-500">
                Каталог проверенных индивидуалок в Химках. Реальные фото, отзывы клиентов, все районы города.
            </div>

            <ul class="space-y-3 text-sm leading-6 text-slate-500">
                @foreach ($footerContacts as $contact)
                    @if (($contact['kind'] ?? null) === 'telegram' && !empty($contact['encoded_url']))
                        <li>
                            <a class="inline-flex items-center gap-3 rounded-xl bg-gradient-to-r from-blue-600 to-purple-600 px-4 py-3 text-sm font-semibold text-white shadow-md transition hover:-translate-y-0.5 hover:shadow-lg"
                                data-contact-link="{{ $contact['encoded_url'] }}"
                                aria-label="Telegram">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 512 512" class="h-5 w-5 flex-shrink-0">
                                    <path fill="currentColor" d="M470.435 45.423L16.827 221.249c-18.254 8.188-24.428 24.585-4.412 33.484l116.37 37.173l281.368-174.79c15.363-10.973 31.091-8.047 17.557 4.024L186.053 341.075l-7.591 93.076c7.031 14.371 19.905 14.438 28.117 7.295l66.858-63.589l114.505 86.187c26.595 15.826 41.066 5.613 46.788-23.394l75.105-357.47c7.798-35.705-5.5-51.437-39.4-37.757z"></path>
                                </svg>
                                Telegram
                            </a>
                        </li>
                    @else
                        <li class="flex items-start gap-3">
                            <span class="mt-2 inline-flex h-2.5 w-2.5 flex-shrink-0 rounded-full bg-gradient-to-br from-blue-500 to-purple-600"></span>
                            <span>
                                @if (!empty($contact['label']))
                                    <span class="font-medium text-slate-700">{{ $contact['label'] }}:</span>
                                @endif
                                @if (!empty($contact['encoded_url']))
                                    <a class="{{ !empty($contact['label']) ? 'font-semibold text-slate-800 hover:text-blue-600' : 'hover:text-blue-600' }} transition"
                                        data-contact-link="{{ $contact['encoded_url'] }}"
                                        data-contact-text="{{ $contact['encoded_value'] }}"
                                        aria-label="{{ $contact['label'] ?: 'Контакт' }}">
                                        Показать
                                    </a>
                                @else
                                    <span class="{{ !empty($contact['label']) ? 'font-semibold text-slate-800' : '' }}">{{ $contact['value'] }}</span>
                                @endif
                            </span>
                        </li>
                    @endif
                @endforeach
            </ul>
        </div>

        @if ($footerMenus->isNotEmpty())
            <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($footerMenus as $menu)
                    <x-footer.column :title="$menu['title']" :location="$menu['location']" />
                @endforeach
            </div>
        @endif
    </div>

    <div class="mx-auto max-w-7xl px-4 pb-6">
        <div class="lg:max-w-xs rounded-2xl border border-slate-200 bg-slate-50 p-4 text-sm leading-6 text-slate-500 shadow-sm">
            <span class="font-semibold text-slate-700">[18+]</span>
            Сайт содержит контент, не предназначенный для лиц младше 18 лет. Продолжая, вы подтверждаете свой возраст.
        </div>
    </div>

    <div class="border-t border-slate-200">
        <div class="mx-auto flex max-w-7xl flex-col gap-2 px-4 py-4 text-sm text-slate-400 sm:flex-row sm:items-center sm:justify-between">
            <div>&copy; {{ $copyrightYears }} {!! $siteName !!}. Все права защищены.</div>
            <a class="transition hover:text-slate-600" href="{{ $sitemapUrl }}">Карта сайта</a>
        </div>
    </div>
</footer>
