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

    // Таксономии
    foreach ((array) ($cfg['tax'] ?? []) as $tax => $vals) {
        if (!empty($vals)) {
            $req->set_param($tax, array_values((array) $vals));
        }
    }

    // Цена
    if (array_key_exists('price', $cfg)) {
        if (isset($cfg['price']['min'])) {
            $req->set_param('price_min', (int) $cfg['price']['min']);
        }
        if (isset($cfg['price']['max'])) {
            $req->set_param('price_max', (int) $cfg['price']['max']);
        }
    }

    // Прочие мета (диапазоны и bool)
    foreach ((array) ($cfg['meta'] ?? []) as $k => $v) {
        if (is_array($v) && isset($v[0], $v[1])) {
            if ($k === 'age') {
                $req->set_param('age_min', (int) $v[0]);
                $req->set_param('age_max', (int) $v[1]);
            }
            if ($k === 'height') {
                $req->set_param('height_min', (int) $v[0]);
                $req->set_param('height_max', (int) $v[1]);
            }
            if ($k === 'weight') {
                $req->set_param('weight_min', (int) $v[0]);
                $req->set_param('weight_max', (int) $v[1]);
            }
        } elseif (is_bool($v)) {
            $req->set_param($k, $v ? 1 : 0);
        }
    }

    $resp = rest_do_request($req);
    $data = $resp instanceof \WP_REST_Response ? $resp->get_data() : (array) $resp;
    $ssrItems = (array) ($data['items'] ?? []);
    $hasSSR = count($ssrItems) > 0;
    $firstImg = null;
    foreach ($ssrItems as $it) {
        if (!empty($it['thumb'])) {
            $firstImg = $it['thumb'];
            break;
        }
    }
@endphp

@push('head')
    @if ($firstImg)
        <link rel="preload" as="image" href="{{ esc_url($firstImg) }}" fetchpriority="high">
    @endif
@endpush

<section id="{{ $id }}" class="models-grid js-models-grid" data-ssr="{{ $hasSSR ? '1' : '0' }}"
    data-endpoint="{{ esc_url(rest_url('site/v1/models')) }}" data-config='@json($config ?? [])'>

    @isset($title)
        <header class="mb-6">
            <h2 class="text-2xl md:text-3xl font-bold text-gray-900">{{ $title }}</h2>
            @isset($subtitle)
                <p class="text-sm text-gray-600 mt-2">{{ $subtitle }}</p>
            @endisset
        </header>
    @endisset

    <div class="flex flex-col lg:flex-row gap-6">
        @if ($showFilters)
            {{-- Мобильная кнопка открытия фильтров --}}
            <button type="button" id="filters-toggle-{{ $id }}"
                class="lg:hidden fixed bottom-6 right-6 z-30 flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-full shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-200">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4">
                    </path>
                </svg>
                <span class="font-medium">Фильтры</span>
            </button>

            {{-- Оверлей для модального окна --}}
            <div id="filters-overlay-{{ $id }}"
                class="fixed inset-0 bg-black/50 z-40 opacity-0 pointer-events-none transition-opacity duration-300 lg:hidden">
            </div>

            {{-- Фильтры: модальное окно на мобильных, боковая панель на десктопе --}}
            <aside id="filters-sidebar-{{ $id }}"
                class="fixed inset-y-0 left-0 z-50 w-full max-w-sm bg-white shadow-2xl transform -translate-x-full transition-transform duration-300 ease-out
                       lg:static lg:transform-none lg:w-80 lg:shadow-none lg:bg-transparent">

                {{-- Мобильный заголовок фильтров --}}
                <div class="lg:hidden sticky top-0 bg-white border-b px-4 py-4 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">Фильтры</h3>
                    <button type="button" id="filters-close-{{ $id }}"
                        class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                {{-- Форма фильтров --}}
                <form class="models-grid__form p-4 lg:p-0 h-full lg:h-auto overflow-y-auto lg:overflow-visible">
                    <div class="space-y-6 pb-20 lg:pb-0">

                        {{-- Скрытые пресеты --}}
                        @if (!empty($cfg['tax']) && is_array($cfg['tax']))
                            @foreach ($cfg['tax'] as $taxKey => $vals)
                                @foreach ((array) $vals as $val)
                                    <input type="hidden" name="{{ $taxKey }}[]" value="{{ esc_attr($val) }}"
                                        data-preset-lock>
                                @endforeach
                            @endforeach
                        @endif

                        {{-- Поиск --}}
                        {{--                         <div class="bg-white lg:bg-gray-50 rounded-xl p-4 lg:border lg:border-gray-200">
                            <label class="block text-sm font-semibold text-gray-700 mb-3">Поиск</label>
                            <div class="relative">
                                <input type="search" name="q" placeholder="Имя или ID модели"
                                    class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                                    autocomplete="off">
                                <svg class="absolute left-3 top-3 w-4 h-4 text-gray-400" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </div>
                        </div> --}}

                        {{-- Цена --}}
                        <div class="bg-white lg:bg-gray-50 rounded-xl p-4 lg:border lg:border-gray-200">
                            <label class="block text-sm font-semibold text-gray-700 mb-3">Цена (₽)</label>
                            <div class="space-y-3">
                                <div class="relative">
                                    <input type="range" min="0" max="100000" step="500" value="0"
                                        class="h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer slider-thumb"
                                        data-min-range>
                                    <input type="range" min="0" max="100000" step="500" value="100000"
                                        class="h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer slider-thumb absolute top-0"
                                        data-max-range>
                                </div>
                                <div class="flex items-center gap-2">
                                    <input type="number" name="price_min" min="0" step="100"
                                        class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                        value="0" data-min-input placeholder="От">
                                    <span class="text-gray-500">—</span>
                                    <input type="number" name="price_max" min="0" step="100"
                                        class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                        value="100000" data-max-input placeholder="До">
                                </div>
                                <button type="button" class="text-xs text-blue-600 hover:text-blue-700 font-medium"
                                    data-clear-price>
                                    Сбросить диапазон
                                </button>
                            </div>
                        </div>

                        {{-- Параметры (возраст, рост, вес) --}}
                        <div class="bg-white lg:bg-gray-50 rounded-xl p-4 lg:border lg:border-gray-200 space-y-4">
                            <h4 class="text-sm font-semibold text-gray-700">Параметры</h4>

                            {{-- Возраст --}}
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-2">Возраст</label>
                                <div class="flex items-center gap-2">
                                    <input type="number" name="age_min" min="18" max="99" placeholder="От"
                                        class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
                                    <span class="text-gray-500">—</span>
                                    <input type="number" name="age_max" min="18" max="99" placeholder="До"
                                        class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
                                </div>
                            </div>

                            {{-- Рост --}}
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-2">Рост (см)</label>
                                <div class="flex items-center gap-2">
                                    <input type="number" name="height_min" min="120" max="220"
                                        placeholder="От"
                                        class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
                                    <span class="text-gray-500">—</span>
                                    <input type="number" name="height_max" min="120" max="220"
                                        placeholder="До"
                                        class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
                                </div>
                            </div>

                            {{-- Вес --}}
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-2">Вес (кг)</label>
                                <div class="flex items-center gap-2">
                                    <input type="number" name="weight_min" min="35" max="160"
                                        placeholder="От"
                                        class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
                                    <span class="text-gray-500">—</span>
                                    <input type="number" name="weight_max" min="35" max="160"
                                        placeholder="До"
                                        class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
                                </div>
                            </div>
                        </div>

                        {{-- Особенности --}}
                        <div class="bg-white lg:bg-gray-50 rounded-xl p-4 lg:border lg:border-gray-200">
                            <h4 class="text-sm font-semibold text-gray-700 mb-3">Особенности</h4>
                            <label
                                class="flex items-center gap-3 cursor-pointer hover:bg-gray-100 p-2 rounded-lg transition-colors">
                                <input type="checkbox" name="has_video" value="1"
                                    class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                                <span class="text-sm text-gray-700">Только с видео</span>
                            </label>
                        </div>

                        {{-- Таксономии --}}
                        @php
                            $taxFilters = [
                                'service' => [
                                    'label' => 'Услуги',
                                    'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
                                ],
                                'district' => [
                                    'label' => 'Районы',
                                    'icon' =>
                                        'M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z',
                                ],
                                'rail_station' => ['label' => 'Метро', 'icon' => 'M8 7h8m-8 5h8m-8 5h8M3 3h18v18H3z'],
                                'feature' => [
                                    'label' => 'Избранное',
                                    'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
                                ],
                                'hair_color' => [
                                    'label' => 'Цвет волос',
                                    'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
                                ],
                                'nationality' => [
                                    'label' => 'Национальность',
                                    'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
                                ],
                                'bust_size' => [
                                    'label' => 'Размер груди',
                                    'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
                                ],
                                'massage' => [
                                    'label' => 'Массаж',
                                    'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
                                ],
                                'physique' => [
                                    'label' => 'Телосложение',
                                    'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
                                ],
                                'intimate_haircut' => [
                                    'label' => 'Интимная стрижка',
                                    'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
                                ],
                                'striptease_services' => [
                                    'label' => 'Услуги стриптиза',
                                    'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
                                ],
                                'extreme_services' => [
                                    'label' => 'Экстрим услуги',
                                    'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
                                ],
                                'sado_maso' => [
                                    'label' => 'БДСМ (Садо-мазо)',
                                    'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
                                ],
                            ];
                        @endphp

                        @foreach ($taxFilters as $tax => $taxData)
                            @php
                                $terms = get_terms([
                                    'taxonomy' => $tax,
                                    'hide_empty' => true,
                                ]);
                            @endphp
                            @if (!is_wp_error($terms) && !empty($terms))
                                <div class="bg-white lg:bg-gray-50 rounded-xl p-4 lg:border lg:border-gray-200">
                                    <div class="flex items-center gap-2 mb-3">
                                        <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="{{ $taxData['icon'] }}"></path>
                                        </svg>
                                        <h4 class="text-sm font-semibold text-gray-700">{{ $taxData['label'] }}</h4>
                                    </div>
                                    <div class="space-y-2 max-h-60 overflow-y-auto custom-scrollbar">
                                        @foreach ($terms as $t)
                                            <label
                                                class="flex items-center gap-3 cursor-pointer hover:bg-gray-100 p-2 rounded-lg transition-colors">
                                                <input type="checkbox" name="{{ $tax }}[]"
                                                    value="{{ esc_attr($t->slug) }}"
                                                    class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                                                    @checked(!empty($cfg['tax'][$tax]) && in_array($t->slug, (array) $cfg['tax'][$tax], true))>
                                                <span
                                                    class="text-sm text-gray-700 flex-1">{{ esc_html($t->name) }}</span>
                                                @if ($t->count > 0)
                                                    <span
                                                        class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded-full">{{ $t->count }}</span>
                                                @endif
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        @endforeach

                        {{-- Кнопки действий --}}
                        <div
                            class="sticky bottom-0 bg-white lg:bg-transparent p-4 lg:p-0 border-t lg:border-0 -mx-4 lg:mx-0 space-y-3">
                            <button type="submit"
                                class="w-full px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-medium rounded-lg hover:from-blue-700 hover:to-blue-800 transform hover:scale-[1.02] transition-all duration-200 shadow-lg">
                                Применить фильтры
                            </button>
                            <button type="reset"
                                class="w-full px-6 py-3 bg-white border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition-colors">
                                Сбросить все
                            </button>
                        </div>
                    </div>
                </form>
            </aside>
        @endif

        {{-- Основной контент --}}
        <div class="flex-1">
            {{-- Сортировка и счетчик --}}
            <div class="flex items-center justify-between mb-6 pb-4 border-b">
                <div class="text-sm text-gray-600">
                    Найдено: <span class="font-semibold text-gray-900"
                        id="models-count-{{ $id }}">{{ count($ssrItems) }}</span> моделей
                </div>
                <select name="sort"
                    class="px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="date">Новые</option>
                    <option value="price_asc">Цена: по возрастанию</option>
                    <option value="price_desc">Цена: по убыванию</option>
                    <option value="popular">Популярные</option>
                </select>
            </div>

            {{-- Грид моделей --}}
            <div class="models-grid__root">
                @if ($hasSSR)
                    @include('components.models-cards', ['items' => $ssrItems])
                @endif
            </div>

            {{-- Пустой результат --}}
            <div class="models-grid__empty {{ $hasSSR ? 'hidden' : '' }} text-center py-12">
                <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <p class="text-gray-500 text-lg">Модели не найдены</p>
                <p class="text-gray-400 text-sm mt-2">Попробуйте изменить параметры поиска</p>
            </div>

            {{-- Кнопка "Показать ещё" --}}
            <div class="text-center mt-8">
                <button type="button" data-more
                    class="px-8 py-3 bg-white border-2 border-gray-300 text-gray-700 font-medium rounded-lg hover:border-gray-400 hover:shadow-md transform hover:scale-[1.02] transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed">
                    Показать ещё
                </button>
            </div>
        </div>
    </div>
</section>

{{-- Скрипт управления фильтрами --}}
@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const gridId = '{{ $id }}';
            const toggleBtn = document.getElementById(`filters-toggle-${gridId}`);
            const closeBtn = document.getElementById(`filters-close-${gridId}`);
            const sidebar = document.getElementById(`filters-sidebar-${gridId}`);
            const overlay = document.getElementById(`filters-overlay-${gridId}`);

            if (toggleBtn && sidebar && overlay) {
                // Открытие фильтров
                toggleBtn.addEventListener('click', function() {
                    sidebar.classList.remove('-translate-x-full');
                    overlay.classList.remove('opacity-0', 'pointer-events-none');
                    overlay.classList.add('opacity-100');
                    document.body.classList.add('overflow-hidden');
                });

                // Закрытие фильтров
                const closeFilters = function() {
                    sidebar.classList.add('-translate-x-full');
                    overlay.classList.add('opacity-0', 'pointer-events-none');
                    overlay.classList.remove('opacity-100');
                    document.body.classList.remove('overflow-hidden');
                };

                closeBtn?.addEventListener('click', closeFilters);
                overlay.addEventListener('click', closeFilters);

                // Закрытие по Escape
                document.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape' && !sidebar.classList.contains('-translate-x-full')) {
                        closeFilters();
                    }
                });
            }
        });
    </script>
@endpush

<style>
    /* Кастомная полоса прокрутки */
    .custom-scrollbar::-webkit-scrollbar {
        width: 6px;
    }

    .custom-scrollbar::-webkit-scrollbar-track {
        background: #f3f4f6;
        border-radius: 3px;
    }

    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #d1d5db;
        border-radius: 3px;
    }

    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: #9ca3af;
    }

    /* Стили для range слайдеров */
    .slider-thumb::-webkit-slider-thumb {
        appearance: none;
        width: 20px;
        height: 20px;
        background: #3b82f6;
        border-radius: 50%;
        cursor: pointer;
        border: 2px solid white;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    }

    .slider-thumb::-moz-range-thumb {
        width: 20px;
        height: 20px;
        background: #3b82f6;
        border-radius: 50%;
        cursor: pointer;
        border: 2px solid white;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
    }

    /* Анимация появления карточек */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .models-grid__list li {
        animation: fadeInUp 0.4s ease-out;
        animation-fill-mode: both;
    }

    .models-grid__list li:nth-child(1) {
        animation-delay: 0.05s;
    }

    .models-grid__list li:nth-child(2) {
        animation-delay: 0.1s;
    }

    .models-grid__list li:nth-child(3) {
        animation-delay: 0.15s;
    }

    .models-grid__list li:nth-child(4) {
        animation-delay: 0.2s;
    }
</style>
