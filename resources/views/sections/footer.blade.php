<footer class="content-info mt-10 border-t">
    <div class="max-w-7xl mx-auto px-4 py-8 text-sm text-slate-600">
        <a class="brand text-lg font-semibold" href="{{ home_url('/') }}">
            {!! $siteName !!}
        </a>
        @if (has_nav_menu('footer_navigation'))
            <nav class="nav-footer" aria-label="{{ wp_get_nav_menu_name('footer_navigation') }}">
                {!! wp_nav_menu([
                    'theme_location' => 'footer_navigation',
                    'menu_class' => 'flex gap-4 text-sm',
                    'echo' => false,
                ]) !!}
            </nav>
        @endif
    </div>
</footer>
