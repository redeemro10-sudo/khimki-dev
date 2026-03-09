<!doctype html>
<html @php(language_attributes())>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @stack('head')
    <link rel="preconnect" href="https://prostitutkikhimki.com" crossorigin>
    <link rel="preconnect" href="https://www.googletagmanager.com" crossorigin>
    <link rel="preconnect" href="https://mc.yandex.ru" crossorigin>
    <link rel="preconnect" href="https://www.clarity.ms" crossorigin>

    <link rel="dns-prefetch" href="//www.googletagmanager.com">
    <link rel="dns-prefetch" href="//mc.yandex.ru">
    <link rel="dns-prefetch" href="//www.clarity.ms">
    <link rel="dns-prefetch" href="//prostitutkikhimki.com">

    @php(do_action('get_header'))
    @php(wp_head())

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body @php(body_class())>
    @php(wp_body_open())

    <div id="app">
        <a class="sr-only focus:not-sr-only" href="#main">
            {{ __('Skip to content', 'sage') }}
        </a>

        @include('sections.header')

        <main id="main" class="main max-w-7xl mx-auto px-4 py-6">
            @include('partials.breadcrumbs')
            @yield('content')
        </main>

        @hasSection('sidebar')
            <aside class="sidebar">
                @yield('sidebar')
            </aside>
        @endif

        @include('sections.footer')

        @include('partials.fab-contacts')
    </div>

    @php(do_action('get_footer'))
    @php(wp_footer())
</body>

</html>
