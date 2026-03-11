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

    $footerContacts = [
        ['label' => 'Телеграм', 'value' => '@prostitutkikhimki'],
        ['label' => 'Email', 'value' => 'info@prostitutkikhimki.com'],
        ['label' => null, 'value' => 'Химки, Московская область'],
        ['label' => null, 'value' => '24/7 (Круглосуточно)'],
    ];
@endphp

<footer class="content-info mt-10 border-t">
    <div class="mx-auto grid max-w-7xl gap-10 px-4 py-8 text-sm text-slate-600 lg:grid-cols-[minmax(0,1.2fr)_minmax(0,2fr)] lg:items-start">
        <div class="max-w-md space-y-5">
            <a class="brand inline-flex items-center gap-3 text-lg font-semibold text-slate-900" href="{{ home_url('/') }}">
                <span
                    class="flex h-10 w-10 items-center justify-center rounded-2xl bg-gradient-to-br from-blue-500 to-purple-600 text-sm font-bold text-white shadow-md">
                    {{ substr($siteName, 0, 1) }}
                </span>
                <span>{!! $siteName !!}</span>
            </a>

            <div class="text-sm leading-6 text-slate-500">
                Каталог проверенных индивидуалок в Химках. Реальные фото, отзывы клиентов, все районы города.
            </div>

            <ul class="space-y-3 text-sm leading-6 text-slate-500">
                @foreach ($footerContacts as $contact)
                    <li class="flex items-start gap-3">
                        <span class="mt-2 inline-flex h-2.5 w-2.5 flex-shrink-0 rounded-full bg-gradient-to-br from-blue-500 to-purple-600"></span>
                        <span>
                            @if (!empty($contact['label']))
                                <span class="font-medium text-slate-700">{{ $contact['label'] }}:</span>
                            @endif
                            <span class="{{ !empty($contact['label']) ? 'font-semibold text-slate-800' : '' }}">{{ $contact['value'] }}</span>
                        </span>
                    </li>
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

        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 text-sm leading-6 text-slate-500 shadow-sm">
            <span class="font-semibold text-slate-700">[18+]</span>
            Сайт содержит контент, не предназначенный для лиц младше 18 лет. Продолжая, вы подтверждаете свой возраст.
        </div>
    </div>
</footer>
