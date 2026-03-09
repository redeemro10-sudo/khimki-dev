<header class="banner sticky top-0 z-40 bg-white/90 backdrop-blur border-b border-gray-200/50 header-shadow">
    <div class="max-w-7xl mx-auto px-4 py-4 flex items-center justify-between gap-6">
        @php($siteName = get_bloginfo('name'))

        {{-- Логотип --}}
        <a class="brand flex items-center gap-3 text-lg font-bold text-gray-900 hover:text-blue-600 nav-transition group"
            href="{{ home_url('/') }}">
            <div
                class="w-8 h-8 bg-gradient-to-br from-blue-500 to-purple-600 rounded-lg flex items-center justify-center group-hover:scale-110 nav-transition shadow-md">
                <span class="text-white font-bold text-sm">{{ substr($siteName, 0, 1) }}</span>
            </div>
            {!! $siteName !!}
        </a>

        {{-- Бургер-кнопка --}}
        <button id="navToggle"
            class="relative inline-flex items-center justify-center w-10 h-10 rounded-lg
                       bg-gray-50 hover:bg-gray-100 border border-gray-200 nav-transition
                       focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
            aria-controls="siteNav" aria-expanded="false">
            <span class="sr-only">Меню</span>
            <span class="burger-icon">
                <span class="burger-line"></span>
                <span class="burger-line"></span>
                <span class="burger-line"></span>
            </span>
        </button>

        {{-- Оверлей --}}
        <div id="navOverlay"
            class="fixed inset-0 z-[90] bg-black/50 backdrop-blur-sm opacity-0 pointer-events-none
                    transition-all duration-300 md:hidden">
        </div>

        @if (has_nav_menu('primary_navigation'))
            <nav id="siteNav"
                class="fixed left-0 top-0 z-[100] h-dvh w-80 -translate-x-full bg-white shadow-2xl
                        transition-transform duration-300 ease-out
                        md:static md:z-auto md:h-auto md:w-auto md:translate-x-0 md:bg-transparent md:shadow-none"
                aria-label="{{ wp_get_nav_menu_name('primary_navigation') }}">

                {{-- Шапка мобильного меню --}}
                <div class="md:hidden flex items-center justify-between p-6 border-b border-gray-100 bg-gray-50/50">
                    <div class="flex items-center gap-3">
                        <div
                            class="w-6 h-6 bg-gradient-to-br from-blue-500 to-purple-600 rounded flex items-center justify-center">
                            <span class="text-white font-bold text-xs">{{ substr($siteName, 0, 1) }}</span>
                        </div>
                        <span class="font-semibold text-gray-900">Меню</span>
                    </div>
                    <button id="navClose"
                        class="p-2 rounded-lg hover:bg-gray-100 nav-transition
                                   focus:outline-none focus:ring-2 focus:ring-blue-500"
                        aria-label="Закрыть">
                        <svg class="h-5 w-5 text-gray-600" viewBox="0 0 24 24" fill="currentColor">
                            <path
                                d="M18.3 5.7 12 12l6.3 6.3-1.4 1.4L10.6 13.4 4.3 19.7 2.9 18.3 9.2 12 2.9 5.7 4.3 4.3l6.3 6.3 6.3-6.3z" />
                        </svg>
                    </button>
                </div>

                {{-- Меню навигации --}}
                {!! wp_nav_menu([
                    'theme_location' => 'primary_navigation',
                    'menu_class' => 'nav flex flex-col gap-1 p-6 lg:flex-row lg:items-center lg:gap-8 lg:p-0',
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
