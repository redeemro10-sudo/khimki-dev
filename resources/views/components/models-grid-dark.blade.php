{{-- resources/views/components/models-grid-dark.blade.php --}}

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
    $req->set_param('sort', $cfg['sort'] ?? ($cfg['order'] ?? 'date'));

    // Применяем пресеты
    foreach ((array) ($cfg['tax'] ?? []) as $tax => $vals) {
        if (!empty($vals)) {
            $req->set_param($tax, array_values((array) $vals));
        }
    }
    if (array_key_exists('price', $cfg)) {
        if (isset($cfg['price']['min'])) {
            $req->set_param('price_min', (int) $cfg['price']['min']);
        }
        if (isset($cfg['price']['max'])) {
            $req->set_param('price_max', (int) $cfg['price']['max']);
        }
    }
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
@endphp

@php
    $ssrPage = 1;
    $ssrPerPage = (int) ($cfg['per_page'] ?? 12);
    $ssrFound = isset($data['total']) ? (int) $data['total'] : count($ssrItems);
    $ssrMaxPages = isset($data['pages']) ? (int) $data['pages'] : (int) ceil($ssrFound / max(1, $ssrPerPage));
    $hasMoreSSR = $ssrPage < $ssrMaxPages;
@endphp

<section id="{{ $id }}" class="models-grid js-models-grid" data-ssr="{{ $hasSSR ? '1' : '0' }}"
    data-endpoint="{{ esc_url(rest_url('site/v1/models')) }}" data-config='@json($config ?? [])'
    data-pages="{{ $ssrMaxPages }}" data-page="{{ $ssrPage }}">

    @isset($title)
        <header class="mb-6">
            <h2 class="text-2xl md:text-3xl font-bold text-gray-900">{{ $title }}</h2>
        </header>
    @endisset

    <div class="grid grid-cols-1 lg:grid-cols-[20rem_1fr] lg:items-start gap-4 lg:gap-6">
        @if ($showFilters)
            {{-- Мобильная кнопка открытия фильтров --}}
            <button type="button" id="filters-toggle-{{ $id }}"
                class="lg:hidden fixed bottom-6 right-6 z-30 flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-orange-500 to-orange-600 text-white rounded-full shadow-lg hover:shadow-xl transform hover:scale-105 transition-all duration-200">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4">
                    </path>
                </svg>
                <span class="font-medium">Фильтры</span>
            </button>

            {{-- Оверлей для модального окна --}}
            <div id="filters-overlay-{{ $id }}"
                class="fixed inset-0 bg-black/70 backdrop-blur-sm z-40 opacity-0 pointer-events-none transition-opacity duration-300 lg:hidden">
            </div>

            {{-- Фильтры: модальное окно на мобильных, боковая панель на десктопе --}}
            <aside id="filters-sidebar-{{ $id }}"
                class="fixed inset-y-0 left-0 z-9999 lg:z-1 w-full bg-gray-950 shadow-2xl -translate-x-full transition-transform duration-300 ease-out
         lg:static lg:translate-x-0 lg:w-80 lg:shadow-none lg:bg-transparent lg:h-auto lg:overflow-visible lg:sticky lg:top-24">

                {{-- Форма фильтров --}}
                <form class="models-grid__form p-4 lg:p-0 h-full lg:h-auto overflow-y-auto lg:overflow-visible">
                    <div class="lg:pb-0 form__div">
                        <div class="lg:pb-0 form__div">
                            {{-- Скрытые пресеты --}}
                            @if (!empty($cfg['tax']) && is_array($cfg['tax']))
                                @foreach ($cfg['tax'] as $taxKey => $vals)
                                    @foreach ((array) $vals as $val)
                                        <input type="hidden" name="{{ $taxKey }}[]" value="{{ esc_attr($val) }}"
                                            data-preset-lock>
                                    @endforeach
                                @endforeach
                            @endif

                            {{-- Подключаем компонент аккордеон-фильтров --}}
                            @include ('components.filters-multilevel')
                        </div>
                    </div>
                </form>
            </aside>
        @endif

        {{-- Основной контент --}}
        <div class="flex-1">
            {{-- Сортировка и счетчик --}}
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6 pb-4 border-b">
                <div class="text-sm text-gray-600">
                    Найдено: <span class="font-semibold text-gray-900"
                        id="models-count-{{ $id }}">{{ $ssrFound }}</span> моделей
                </div>

                <div class="flex items-center gap-3">
                    <label class="text-sm text-gray-600">Сортировка:</label>
                    <select title="Сортировать" name="sort"
                        class="px-4 py-2 bg-white border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-orange-500 focus:border-transparent cursor-pointer">
                        <option title="Новые" value="date">Новые</option>
                        <option title="Цена: по возрастанию" value="price_asc">Цена: по возрастанию</option>
                        <option title="Цена: по убыванию" value="price_desc">Цена: по убыванию</option>
                        <option title="Популярные" value="popular">Популярные</option>
                    </select>
                </div>
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
            <div class="text-center mt-8 {{ $hasMoreSSR ? '' : 'hidden' }}" data-more-wrap>
                <button type="button" data-more
                    class="px-8 py-3 bg-white border-2 border-gray-300 text-gray-700 font-medium rounded-lg hover:border-orange-500 hover:text-orange-600 hover:shadow-md transform hover:scale-[1.02] transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed">
                    Показать ещё
                </button>
            </div>
        </div>
    </div>
</section>
