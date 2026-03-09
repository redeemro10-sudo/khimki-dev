@props([
    'title' => null,
    'id' => null,
    'showFilters' => true,
    'config' => [],
])

@php
    $id = $id ?? 'models-grid-' . wp_generate_uuid4();
    $cfg = is_array($config ?? null) ? $config : [];
    $showFilters = (bool) ($showFilters ?? true);

    // SSR через REST-контроллер
    $req = new \WP_REST_Request('GET', '/site/v1/models');
    $req->set_param('page', 1);
    $req->set_param('per_page', (int) ($cfg['per_page'] ?? 12));
    $req->set_param('order', $cfg['order'] ?? 'date');

    foreach ((array) ($cfg['tax'] ?? []) as $tax => $vals) {
        if (!empty($vals)) {
            $req->set_param($tax, array_values((array) $vals));
        }
    }

    $resp = rest_do_request($req);
    $data = $resp instanceof \WP_REST_Response ? $resp->get_data() : (array) $resp;
    $ssrItems = (array) ($data['items'] ?? []);
    $hasSSR = count($ssrItems) > 0;
@endphp

<section id="{{ $id }}" class="models-grid js-models-grid" data-ssr="{{ $hasSSR ? '1' : '0' }}"
    data-endpoint="{{ esc_url(rest_url('site/v1/models')) }}" data-config='@json($config ?? [])'>
    @isset($title)
        <header class="mb-6">
            <h2 class="text-2xl md:text-3xl font-bold text-slate-900">{{ $title }}</h2>
        </header>
    @endisset

    <div class="flex flex-col lg:flex-row gap-6">
        @if ($showFilters)
            {{-- FAB открыть фильтры (мобилы) --}}
            <button type="button" id="filters-toggle-{{ $id }}"
                class="lg:hidden fixed bottom-6 right-6 z-50 flex items-center gap-2 px-5 py-3 bg-blue-600 text-white rounded-full shadow-lg hover:bg-blue-700 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4M6 18a2 2 0 100-4 2 2 0 100 4m0-6V4m12 14a2 2 0 100-4 2 2 0 100 4m0-6V4" />
                </svg>
                <span class="font-medium">Фильтры</span>
            </button>

            {{-- Оверлей --}}
            <div id="filters-overlay-{{ $id }}"
                class="fixed inset-0 bg-black/50 backdrop-blur-sm z-40 opacity-0 pointer-events-none transition-opacity duration-300 lg:hidden">
            </div>

            {{-- Сайдбар: drawer на мобилках, статичный на десктопе --}}
            <aside id="filters-sidebar-{{ $id }}"
                class="fixed inset-y-0 left-0 z-50 w-full max-w-sm bg-white border-r border-slate-200 shadow-xl
         transform -translate-x-full transition-transform duration-300 ease-out
         lg:static lg:translate-x-0 lg:w-80 lg:shadow-none
         lg:sticky lg:top-4 lg:h-[calc(100vh-2rem)] lg:overflow-hidden"
                aria-label="Фильтры">
                <div class="flex flex-col h-full">
                    <div class="flex items-center justify-between px-4 py-3 border-b border-slate-200">
                        <h3 class="text-base font-semibold text-slate-900">Фильтры</h3>
                        <button type="button" class="lg:hidden p-2 rounded hover:bg-slate-100" data-close>
                            <svg class="w-5 h-5 text-slate-600" viewBox="0 0 24 24" fill="currentColor">
                                <path
                                    d="M6.225 4.811a1 1 0 011.414 0L12 9.172l4.361-4.361a1 1 0 111.414 1.414L13.414 10.586l4.361 4.361a1 1 0 01-1.414 1.414L12 12l-4.361 4.361a1 1 0 01-1.414-1.414l4.361-4.361-4.361-4.361a1 1 0 010-1.414z" />
                            </svg>
                        </button>
                    </div>

                    <form class="p-4 space-y-3 js-models-grid__filters flex-1 overflow-y-auto min-h-0"
                        data-for="{{ $id }}">
                        @include('components.filters-drawer-light')
                    </form>
                </div>
            </aside>
        @endif

        {{-- Контент (грид) --}}
        <div class="flex-1">
            <div class="models-grid__root">
                <ul class="models-grid__list grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                    @forelse($ssrItems as $it)
                        <li>
                            <article
                                class="border rounded-2xl overflow-hidden bg-white shadow-sm hover:shadow-md transition-shadow">
                                <a href="{{ $it['link'] ?? '#' }}" class="block" rel="bookmark">
                                    <div class="relative h-72 overflow-hidden bg-slate-100">
                                        @if (!empty($it['thumb']))
                                            <img src="{{ esc_url($it['thumb']) }}"
                                                alt="{{ esc_attr($it['title'] ?? '') }}" loading="lazy"
                                                decoding="async" class="w-full h-full object-cover aspect-square">
                                        @endif
                                        @if (!empty($it['tags']['video']))
                                            <span
                                                class="absolute top-2 left-2 px-2 py-1 rounded-full text-[10px] font-bold bg-red-500 text-white">ВИДЕО</span>
                                        @endif
                                        @if (!empty($it['tags']['online']))
                                            <span
                                                class="absolute top-2 right-2 px-2 py-1 rounded-full text-[10px] font-bold bg-blue-500 text-white">ONLINE</span>
                                        @endif
                                        <div
                                            class="absolute inset-x-0 bottom-0 h-20 bg-gradient-to-t from-black/60 to-transparent">
                                        </div>
                                    </div>
                                    <div class="p-4">
                                        <h3 class="font-medium text-slate-900 mb-2 truncate">{{ $it['title'] ?? '' }}
                                        </h3>
                                        <div class="flex items-center gap-2 mb-3 flex-wrap">
                                            @if (!empty($it['price']))
                                                <span class="text-sm font-semibold text-slate-700">от
                                                    {{ number_format((int) $it['price'], 0, '', ' ') }} ₽</span>
                                            @endif
                                        </div>
                                        <div class="flex items-center gap-2 text-slate-600 text-xs">
                                            <svg class="w-4 h-4 flex-shrink-0" viewBox="0 0 24 24" fill="currentColor">
                                                <path
                                                    d="M12 2a7 7 0 0 0-7 7c0 5.25 7 13 7 13s7-7.75 7-13a7 7 0 0 0-7-7zm0 9.5A2.5 2.5 0 1 1 12 6a2.5 2.5 0 0 1 0 5.5z">
                                                </path>
                                            </svg>
                                            <div class="truncate">{{ $it['station'] ?? ($it['district'] ?? '') }}</div>
                                        </div>
                                    </div>
                                </a>
                            </article>
                        </li>
                    @empty
                        <li>
                            <p>No results</p>
                        </li>
                    @endforelse
                </ul>
            </div>

            <div class="text-center mt-4">
                <button type="button" data-more class="px-6 py-2 border rounded-md hover:bg-slate-50">Показать
                    ещё</button>
            </div>

            <p class="models-grid__empty hidden">No results</p>
        </div>
    </div>

    {{-- локальный JS для открытия/закрытия фильтров (ID-изолирован) --}}
    <script>
        (() => {
            const id = @json($id);
            const sidebar = document.getElementById('filters-sidebar-' + id);
            const overlay = document.getElementById('filters-overlay-' + id);
            const toggle = document.getElementById('filters-toggle-' + id);
            if (!sidebar || !overlay || !toggle) return;

            const open = () => {
                sidebar.classList.remove('-translate-x-full');
                overlay.classList.remove('opacity-0', 'pointer-events-none');
            };
            const close = () => {
                sidebar.classList.add('-translate-x-full');
                overlay.classList.add('opacity-0', 'pointer-events-none');
            };

            toggle.addEventListener('click', open);
            overlay.addEventListener('click', close);
            sidebar.querySelector('[data-close]')?.addEventListener('click', close);
        })();
    </script>
</section>
