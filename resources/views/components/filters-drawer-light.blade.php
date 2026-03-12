@props([
    'id' => 'filters-' . wp_generate_uuid4(),
    // какие секции открываются «поверх» (вложенная панель)
    'drilldown' => ['services', 'location', 'appearance', 'additional'],
])

@php
    // Конфиг секций
    $sections = [
        'services' => [
            'title' => 'Услуги',
            'taxonomy' => 'service',
        ],
        'location' => [
            'title' => 'Локация',
            'items' => [
                ['taxonomy' => 'district', 'label' => 'Районы'],
                ['taxonomy' => 'rail_station', 'label' => 'Метро'],
            ],
        ],
        'appearance' => [
            'title' => 'Внешность',
            'items' => [
                ['taxonomy' => 'hair_color', 'label' => 'Цвет волос'],
                ['taxonomy' => 'nationality', 'label' => 'Национальность'],
                ['taxonomy' => 'bust_size', 'label' => 'Размер груди'],
                ['taxonomy' => 'physique', 'label' => 'Телосложение'],
            ],
        ],
        'additional' => [
            'title' => 'Дополнительно',
            'items' => [
                ['taxonomy' => 'massage', 'label' => 'Массаж'],
                ['taxonomy' => 'intimate_haircut', 'label' => 'Интимная стрижка'],
                ['taxonomy' => 'striptease_services', 'label' => 'Стриптиз'],
                ['taxonomy' => 'extreme_services', 'label' => 'Экстрим'],
                ['taxonomy' => 'sado_maso', 'label' => 'БДСМ'],
            ],
        ],
    ];
@endphp

<div id="{{ $id }}" class="filters-drawer h-full">
    <div class="relative h-full overflow-hidden">

        {{-- ПАНЕЛЬ: ROOT --}}
        <div class="filter-panel absolute inset-0 bg-white translate-x-0 overflow-y-auto" data-panel="root"
            aria-labelledby="filters-title-{{ $id }}">
            {{-- Поиск по моделям (опционально) --}}
            <div class="p-4 border-b">
                <div class="relative">
                    <input type="search" name="q" placeholder="Поиск модели"
                        class="w-full border rounded-lg px-10 py-2.5 text-sm" autocomplete="off">
                    <svg class="absolute left-3 top-2.5 w-5 h-5 text-slate-400" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
            </div>

            {{-- Список «дриллдаун» секций --}}
            <div class="divide-y">
                @foreach ($sections as $key => $sec)
                    @php $isDrill = in_array($key, $drilldown, true); @endphp
                    <button type="button" class="w-full flex items-center justify-between px-4 py-3 hover:bg-slate-50"
                        @if ($isDrill) data-open="{{ $key }}" @endif>
                        <span class="text-sm font-medium text-slate-900">{{ $sec['title'] }}</span>
                        <span class="flex items-center gap-3">
                            <span class="text-xs text-slate-500" data-badge="{{ $key }}">Любые</span>
                            @if ($isDrill)
                                <svg class="w-4 h-4 text-slate-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path
                                        d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 111.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" />
                                </svg>
                            @endif
                        </span>
                    </button>
                @endforeach
            </div>

            {{-- «Простые» секции на корне: Цена, Параметры, Особенности --}}
            <div class="p-4 space-y-4">
                {{-- ЦЕНА --}}
                <details class="group border rounded-xl">
                    <summary class="cursor-pointer list-none px-4 py-3 flex items-center justify-between">
                        <span class="text-sm font-medium">Цена</span>
                        <svg class="w-4 h-4 text-slate-400 group-open:rotate-180 transition-transform" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </summary>
                    <div class="px-4 pb-4 space-y-3">
                        <div class="flex items-center gap-2">
                            <input type="number" name="price_min" min="0" max="50000" step="500"
                                class="w-1/2 border rounded-lg px-3 py-2 text-sm" placeholder="От">
                            <span class="text-slate-400">—</span>
                            <input type="number" name="price_max" min="0" max="50000" step="500"
                                class="w-1/2 border rounded-lg px-3 py-2 text-sm" placeholder="До">
                        </div>
                    </div>
                </details>

                {{-- ПАРАМЕТРЫ (возраст/рост/вес) --}}
                <details class="group border rounded-xl">
                    <summary class="cursor-pointer list-none px-4 py-3 flex items-center justify-between">
                        <span class="text-sm font-medium">Параметры</span>
                        <svg class="w-4 h-4 text-slate-400 group-open:rotate-180 transition-transform" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </summary>
                    <div class="px-4 pb-4 space-y-4 text-sm">
                        <div>
                            <div class="text-slate-500 mb-1">Возраст</div>
                            <div class="flex items-center gap-2">
                                <input type="number" name="age_min" min="18" max="49"
                                    class="w-1/2 border rounded-lg px-3 py-2" placeholder="От">
                                <span class="text-slate-400">—</span>
                                <input type="number" name="age_max" min="18" max="49"
                                    class="w-1/2 border rounded-lg px-3 py-2" placeholder="До">
                            </div>
                        </div>
                        <div>
                            <div class="text-slate-500 mb-1">Рост (см)</div>
                            <div class="flex items-center gap-2">
                                <input type="number" name="height_min" min="140" max="200"
                                    class="w-1/2 border rounded-lg px-3 py-2" placeholder="От">
                                <span class="text-slate-400">—</span>
                                <input type="number" name="height_max" min="140" max="200"
                                    class="w-1/2 border rounded-lg px-3 py-2" placeholder="До">
                            </div>
                        </div>
                        <div>
                            <div class="text-slate-500 mb-1">Вес (кг)</div>
                            <div class="flex items-center gap-2">
                                <input type="number" name="weight_min" min="40" max="120"
                                    class="w-1/2 border rounded-lg px-3 py-2" placeholder="От">
                                <span class="text-slate-400">—</span>
                                <input type="number" name="weight_max" min="40" max="120"
                                    class="w-1/2 border rounded-lg px-3 py-2" placeholder="До">
                            </div>
                        </div>
                    </div>
                </details>

                {{-- ОСОБЕННОСТИ --}}
                <details class="group border rounded-xl">
                    <summary class="cursor-pointer list-none px-4 py-3 flex items-center justify-between">
                        <span class="text-sm font-medium">Особенности</span>
                        <svg class="w-4 h-4 text-slate-400 group-open:rotate-180 transition-transform" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 9l-7 7-7-7" />
                        </svg>
                    </summary>
                    <div class="px-4 pb-4 grid gap-2 text-sm">
                        @foreach ([['has_video', 'Только с видео'], ['verified', 'Проверенные'], ['vip', 'VIP'], ['online', 'Сейчас онлайн']] as [$name, $label])
                            <label class="flex items-center gap-3">
                                <input type="checkbox" name="{{ $name }}" value="1"
                                    class="w-4 h-4 rounded border-slate-300">
                                <span>{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                </details>

                <div class="pt-2 grid gap-2">
                    <button type="submit"
                        class="w-full rounded-lg bg-blue-600 text-white py-2.5 text-sm font-medium hover:bg-blue-700">Применить</button>
                    <button type="reset"
                        class="w-full rounded-lg border py-2.5 text-sm font-medium hover:bg-slate-50">Сбросить</button>
                </div>
            </div>
        </div>

        {{-- ПАНЕЛИ: ВЛОЖЕННЫЕ --}}
        @foreach ($sections as $key => $sec)
            @if (in_array($key, $drilldown, true))
                <div class="filter-panel absolute inset-0 bg-white translate-x-full overflow-y-auto"
                    data-panel="{{ $key }}">
                    <div class="flex items-center justify-between px-4 py-3 border-b">
                        <button type="button" class="flex items-center gap-2 text-slate-600" data-back>
                            <svg class="w-5 h-5" viewBox="0 0 20 20" fill="currentColor">
                                <path
                                    d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" />
                            </svg>
                            <span class="text-sm">Назад</span>
                        </button>
                        <div class="text-sm font-semibold">{{ $sec['title'] }}</div>
                        <div class="w-8"></div>
                    </div>

                    <div class="p-4">
                        @if (isset($sec['taxonomy']))
                            @php $terms = get_terms(['taxonomy' => $sec['taxonomy'], 'hide_empty' => true]); @endphp
                            @if (!is_wp_error($terms) && $terms)
                                <div class="max-h-[60vh] overflow-y-auto divide-y rounded-lg border">
                                    @foreach ($terms as $term)
                                        <label class="flex items-center justify-between px-4 py-2 text-sm">
                                            <span class="flex items-center gap-3">
                                                <input type="checkbox" name="{{ $sec['taxonomy'] }}[]"
                                                    value="{{ $term->slug }}"
                                                    class="w-4 h-4 rounded border-slate-300">
                                                <span>{{ $term->name }}</span>
                                            </span>
                                            @if ($term->count)
                                                <span class="text-xs text-slate-500">{{ $term->count }}</span>
                                            @endif
                                        </label>
                                    @endforeach
                                </div>
                            @endif
                        @elseif(isset($sec['items']))
                            <div class="space-y-6">
                                @foreach ($sec['items'] as $item)
                                    @php $terms = get_terms(['taxonomy' => $item['taxonomy'], 'hide_empty' => true]); @endphp
                                    @if (!is_wp_error($terms) && $terms)
                                        <div>
                                            <div class="text-xs uppercase tracking-wide text-slate-500 mb-2">
                                                {{ $item['label'] }}</div>
                                            <div class="max-h-48 overflow-y-auto divide-y rounded-lg border">
                                                @foreach ($terms as $term)
                                                    <label class="flex items-center justify-between px-4 py-2 text-sm">
                                                        <span class="flex items-center gap-3">
                                                            <input type="checkbox" name="{{ $item['taxonomy'] }}[]"
                                                                value="{{ $term->slug }}"
                                                                class="w-4 h-4 rounded border-slate-300">
                                                            <span>{{ $term->name }}</span>
                                                        </span>
                                                        @if ($term->count)
                                                            <span
                                                                class="text-xs text-slate-500">{{ $term->count }}</span>
                                                        @endif
                                                    </label>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        @endif

                        <div class="mt-4 grid gap-2">
                            <button type="button"
                                class="rounded-lg bg-blue-600 text-white py-2.5 text-sm font-medium hover:bg-blue-700"
                                data-apply="{{ $key }}">Выбрать</button>
                            <button type="button" class="rounded-lg border py-2.5 text-sm hover:bg-slate-50"
                                data-clear="{{ $key }}">Сбросить</button>
                        </div>
                    </div>
                </div>
            @endif
        @endforeach
    </div>
</div>

<style>
    .filter-panel {
        transition: transform .28s ease;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const root = document.getElementById(@json($id));
        if (!root) return;

        const go = (from, to) => {
            const a = root.querySelector(`[data-panel="${from}"]`);
            const b = root.querySelector(`[data-panel="${to}"]`);
            if (!a || !b) return;
            a.style.transform = 'translateX(-100%)';
            b.style.transform = 'translateX(0%)';
        };
        const back = (toRootOf) => {
            const a = root.querySelector(`[data-panel="${toRootOf}"]`);
            const r = root.querySelector('[data-panel="root"]');
            if (!a || !r) return;
            a.style.transform = 'translateX(100%)';
            r.style.transform = 'translateX(0%)';
        };

        // открыть нужную вложенную панель
        root.querySelectorAll('[data-open]').forEach(btn => {
            btn.addEventListener('click', () => go('root', btn.dataset.open));
        });

        // кнопки «назад»
        root.querySelectorAll('[data-back]').forEach(btn => {
            btn.addEventListener('click', () => {
                const panel = btn.closest('[data-panel]');
                if (panel && panel.dataset.panel) back(panel.dataset.panel);
            });
        });

        // «Выбрать» — обновляем бейдж на корне и назад
        root.querySelectorAll('[data-apply]').forEach(btn => {
            btn.addEventListener('click', () => {
                const key = btn.dataset.apply;
                const panel = root.querySelector(`[data-panel="${key}"]`);
                const checks = panel.querySelectorAll('input[type="checkbox"]:checked');
                const badge = root.querySelector(`[data-badge="${key}"]`);
                badge.textContent = checks.length ? `Выбрано: ${checks.length}` : 'Любые';
                back(key);
            });
        });

        // «Сбросить» только для этой панели
        root.querySelectorAll('[data-clear]').forEach(btn => {
            btn.addEventListener('click', () => {
                const key = btn.dataset.clear;
                const panel = root.querySelector(`[data-panel="${key}"]`);
                panel.querySelectorAll('input[type="checkbox"]').forEach(i => i.checked =
                    false);
                const badge = root.querySelector(`[data-badge="${key}"]`);
                if (badge) badge.textContent = 'Любые';
            });
        });
    });
</script>
<script>
    (function initFiltersDrawer() {
        const run = () => {
            const root = document.getElementById(@json($id));
            if (!root) return;

            const go = (from, to) => {
                const a = root.querySelector(`[data-panel="${from}"]`);
                const b = root.querySelector(`[data-panel="${to}"]`);
                if (!a || !b) return;
                a.style.transform = 'translateX(-100%)';
                b.style.transform = 'translateX(0%)';
            };
            const back = (toRootOf) => {
                const a = root.querySelector(`[data-panel="${toRootOf}"]`);
                const r = root.querySelector('[data-panel="root"]');
                if (!a || !r) return;
                a.style.transform = 'translateX(100%)';
                r.style.transform = 'translateX(0%)';
            };

            root.querySelectorAll('[data-open]').forEach(btn => {
                btn.addEventListener('click', () => go('root', btn.dataset.open));
            });
            root.querySelectorAll('[data-back]').forEach(btn => {
                btn.addEventListener('click', () => {
                    const p = btn.closest('[data-panel]');
                    if (p && p.dataset.panel) back(p.dataset.panel);
                });
            });
            root.querySelectorAll('[data-apply]').forEach(btn => {
                btn.addEventListener('click', () => {
                    const key = btn.dataset.apply;
                    const panel = root.querySelector(`[data-panel="${key}"]`);
                    const checks = panel.querySelectorAll('input[type="checkbox"]:checked');
                    const badge = root.querySelector(`[data-badge="${key}"]`);
                    if (badge) badge.textContent = checks.length ? `Выбрано: ${checks.length}` :
                        'Любые';
                    back(key);
                });
            });
            root.querySelectorAll('[data-clear]').forEach(btn => {
                btn.addEventListener('click', () => {
                    const key = btn.dataset.clear;
                    const panel = root.querySelector(`[data-panel="${key}"]`);
                    panel.querySelectorAll('input[type="checkbox"]').forEach(i => i.checked =
                        false);
                    const badge = root.querySelector(`[data-badge="${key}"]`);
                    if (badge) badge.textContent = 'Любые';
                });
            });
        };

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', run, {
                once: true
            });
        } else {
            run();
        }
    })();
    document.addEventListener('DOMContentLoaded', () => {
        const root = document.getElementById(@json($id));
        if (!root) return;

        // стартовое состояние
        root.querySelectorAll('.filter-panel').forEach(p => {
            p.style.transform = (p.dataset.panel === 'root') ? 'translateX(0%)' : 'translateX(100%)';
        });

        const go = (from, to) => {
            const a = root.querySelector(`[data-panel="${from}"]`);
            const b = root.querySelector(`[data-panel="${to}"]`);
            if (!a || !b) return;
            a.style.transform = 'translateX(-100%)';
            b.style.transform = 'translateX(0%)';
        };
        const back = toRootOf => {
            const a = root.querySelector(`[data-panel="${toRootOf}"]`);
            const r = root.querySelector('[data-panel="root"]');
            if (!a || !r) return;
            a.style.transform = 'translateX(100%)';
            r.style.transform = 'translateX(0%)';
        };

        // ВАЖНО: data-open без лишних кавычек
        root.querySelectorAll('[data-open]').forEach(btn => {
            // должно быть data-open="services", а не data-open="'services'"
            btn.addEventListener('click', () => go('root', btn.dataset.open));
        });

        root.querySelectorAll('[data-back]').forEach(btn => {
            btn.addEventListener('click', () => {
                const panel = btn.closest('[data-panel]');
                if (panel?.dataset.panel) back(panel.dataset.panel);
            });
        });
    });
</script>
