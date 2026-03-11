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
    <div class="mx-auto grid max-w-7xl gap-8 px-4 py-8 text-sm text-slate-600 lg:grid-cols-[minmax(0,16rem)_minmax(0,1fr)] lg:items-start">
        <div>
            <a class="brand text-lg font-semibold text-slate-900" href="{{ home_url('/') }}">
                {!! $siteName !!}
            </a>
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
