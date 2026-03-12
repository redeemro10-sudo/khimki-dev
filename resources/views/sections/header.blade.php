<header class="banner site-header sticky top-0 z-[120] border-b border-gray-200/50 bg-white/90 backdrop-blur header-shadow">
    <div class="site-header-inner mx-auto flex max-w-7xl items-center justify-between gap-6 px-4 py-4">
        @php($siteName = get_bloginfo('name'))
        @php($isFrontPage = is_front_page())

        @if ($isFrontPage)
            <span class="brand site-brand group flex items-center gap-3 text-lg font-bold text-gray-900">
                <div class="site-brand-mark flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-br from-blue-500 to-purple-600 shadow-md">
                    <span class="text-sm font-bold text-white uppercase">{{ substr($siteName, 0, 1) }}</span>
                </div>
                <span class="site-brand-text">{!! $siteName !!}</span>
            </span>
        @else
            <a class="brand site-brand group flex items-center gap-3 text-lg font-bold text-gray-900 hover:text-blue-600 nav-transition"
                href="{{ home_url('/') }}">
                <div
                    class="site-brand-mark flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-br from-blue-500 to-purple-600 shadow-md group-hover:scale-110 nav-transition">
                    <span class="text-sm font-bold text-white uppercase">{{ substr($siteName, 0, 1) }}</span>
                </div>
                <span class="site-brand-text">{!! $siteName !!}</span>
            </a>
        @endif

        <button id="navToggle"
            class="site-nav-toggle relative inline-flex h-10 w-10 items-center justify-center rounded-lg border border-gray-200 bg-gray-50 hover:bg-gray-100 nav-transition focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
            aria-controls="siteNav" aria-expanded="false">
            <span class="sr-only">Меню</span>
            <span class="burger-icon">
                <span class="burger-line"></span>
                <span class="burger-line"></span>
                <span class="burger-line"></span>
            </span>
        </button>

        <div id="navOverlay"
            class="fixed inset-0 z-[90] bg-black/50 backdrop-blur-sm opacity-0 pointer-events-none transition-all duration-300 lg:hidden">
        </div>

        @if (has_nav_menu('primary_navigation'))
            <nav id="siteNav"
                class="site-nav-panel fixed left-0 top-0 z-[100] h-dvh w-80 -translate-x-full bg-white shadow-2xl transition-transform duration-300 ease-out lg:static lg:z-auto lg:h-auto lg:w-auto lg:translate-x-0 lg:bg-transparent lg:shadow-none"
                aria-label="{{ wp_get_nav_menu_name('primary_navigation') }}">
                {!! wp_nav_menu([
                    'theme_location' => 'primary_navigation',
                    'menu_class' => 'nav site-nav-list flex flex-col gap-1 p-6 lg:flex-row lg:items-center lg:gap-8 lg:p-0',
                    'container' => false,
                    'echo' => false,
                    'fallback_cb' => false,
                    'walker' => new \App\Navigation\BurgerWalker(),
                    'depth' => 3,
                ]) !!}
            </nav>
        @endif
    </div>
</header>
