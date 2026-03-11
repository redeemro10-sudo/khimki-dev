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
@endphp

<footer class="content-info mt-10 border-t">
    <div class="mx-auto grid max-w-7xl gap-10 px-4 py-8 text-sm text-slate-600 lg:grid-cols-[minmax(0,1.2fr)_minmax(0,2fr)] lg:items-start">
        <div class="max-w-md space-y-4">
            <a class="brand inline-flex items-center gap-3 text-lg font-semibold text-slate-900" href="{{ home_url('/') }}">
                <span
                    class="flex h-10 w-10 items-center justify-center rounded-2xl bg-gradient-to-br from-blue-500 to-purple-600 text-sm font-bold text-white shadow-md">
                    {{ substr($siteName, 0, 1) }}
                </span>
                <span>{!! $siteName !!}</span>
            </a>

            <p class="text-sm leading-6 text-slate-500">
                Основные разделы сайта, полезная информация и правовые страницы редактируются через отдельные меню
                WordPress.
            </p>
        </div>

        @if ($footerMenus->isNotEmpty())
            <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($footerMenus as $menu)
                    <x-footer.column :title="$menu['title']" :location="$menu['location']" />
                @endforeach
            </div>
        @endif
    </div>
</footer>
