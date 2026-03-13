{{-- resources/views/components/filters-accordion.blade.php --}}

@props([
    'id' => 'filters-' . wp_generate_uuid4(),
    'config' => [],
])

@php
    // Конфигурация аккордеон-секций
    $accordionSections = [
        'services' => [
            'title' => 'Услуги',
            'icon' =>
                'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2',
            'taxonomy' => 'service',
            'expanded' => false,
        ],
        'location' => [
            'title' => 'Локация',
            'icon' => 'M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z',
            'items' => [
                ['taxonomy' => 'district', 'label' => 'Районы'],
                ['taxonomy' => 'rail_station', 'label' => 'Метро'],
            ],
            'expanded' => false,
        ],
        'appearance' => [
            'title' => 'Внешность',
            'icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',
            'items' => [
                ['taxonomy' => 'hair_color', 'label' => 'Цвет волос'],
                ['taxonomy' => 'nationality', 'label' => 'Национальность'],
                ['taxonomy' => 'bust_size', 'label' => 'Размер груди'],
                ['taxonomy' => 'physique', 'label' => 'Телосложение'],
            ],
            'expanded' => false,
        ],
        'additional' => [
            'title' => 'Дополнительно',
            'icon' =>
                'M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4',
            'items' => [
                ['taxonomy' => 'massage', 'label' => 'Массаж'],
                ['taxonomy' => 'intimate_haircut', 'label' => 'Интимная стрижка'],
                ['taxonomy' => 'striptease_services', 'label' => 'Стриптиз'],
                ['taxonomy' => 'extreme_services', 'label' => 'Экстрим'],
                ['taxonomy' => 'sado_maso', 'label' => 'БДСМ'],
            ],
            'expanded' => false,
        ],
    ];
@endphp

<div class="filters-accordion space-y-2">
    {{-- Поиск --}}
    <div class="bg-gray-900 rounded-xl p-4">
        <div class="relative">
            <input type="search" name="q" placeholder="Поиск модели..."
                class="w-full bg-gray-800 text-white placeholder-gray-400 border border-gray-700 rounded-lg pl-10 pr-4 py-3 focus:outline-none focus:border-orange-500 transition-colors">
            <svg class="absolute left-3 top-3.5 w-4 h-4 text-gray-400" fill="none" stroke="currentColor"
                viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
        </div>
    </div>

    {{-- Цена с улучшенным слайдером --}}
    <div class="bg-gray-900 rounded-xl overflow-hidden">
        <button type="button"
            class="accordion-trigger w-full px-4 py-3 flex items-center justify-between text-left hover:bg-gray-800 transition-colors"
            data-accordion="price">
            <span class="text-white font-medium">Цена</span>
            <svg class="accordion-icon w-5 h-5 text-gray-400 transform transition-transform duration-200" fill="none"
                stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>
        </button>
        <div class="accordion-content hidden" data-content="price">
            <div class="px-4 pb-4 space-y-3">
                <div class="relative h-12 flex items-center">
                    {{-- Track background --}}
                    <div class="absolute w-full h-1.5 bg-gray-700 rounded-full"></div>
                    {{-- Active track --}}
                    <div class="price-track absolute h-1.5 bg-gradient-to-r from-orange-500 to-orange-600 rounded-full"
                        style="left: 0%; width: 100%;"></div>
                    {{-- Min slider --}}
                    <input type="range" min="8000" max="50000" step="500" value="8000"
                        class="price-slider absolute w-full h-1.5 bg-transparent appearance-none pointer-events-none [&::-webkit-slider-thumb]:appearance-none [&::-webkit-slider-thumb]:pointer-events-auto [&::-webkit-slider-thumb]:w-5 [&::-webkit-slider-thumb]:h-5 [&::-webkit-slider-thumb]:rounded-full [&::-webkit-slider-thumb]:bg-orange-500 [&::-webkit-slider-thumb]:shadow-lg [&::-webkit-slider-thumb]:cursor-pointer [&::-webkit-slider-thumb]:border-2 [&::-webkit-slider-thumb]:border-gray-900"
                        data-min-range>
                    {{-- Max slider --}}
                    <input type="range" min="8000" max="50000" step="500" value="50000"
                        class="price-slider absolute w-full h-1.5 bg-transparent appearance-none pointer-events-none [&::-webkit-slider-thumb]:appearance-none [&::-webkit-slider-thumb]:pointer-events-auto [&::-webkit-slider-thumb]:w-5 [&::-webkit-slider-thumb]:h-5 [&::-webkit-slider-thumb]:rounded-full [&::-webkit-slider-thumb]:bg-orange-500 [&::-webkit-slider-thumb]:shadow-lg [&::-webkit-slider-thumb]:cursor-pointer [&::-webkit-slider-thumb]:border-2 [&::-webkit-slider-thumb]:border-gray-900"
                        data-max-range>
                </div>
                <div class="flex items-center gap-2">
                    <input type="number" name="price_min" min="8000" max="50000" step="500" value="8000"
                        class="flex-1 bg-gray-800 text-white border border-gray-700 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-orange-500"
                        data-min-input placeholder="От">
                    <span class="text-gray-500">—</span>
                    <input type="number" name="price_max" min="8000" max="50000" step="500" value="50000"
                        class="flex-1 bg-gray-800 text-white border border-gray-700 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-orange-500"
                        data-max-input placeholder="До">
                </div>
            </div>
        </div>
    </div>

    {{-- Параметры --}}
    <div class="bg-gray-900 rounded-xl overflow-hidden">
        <button type="button"
            class="accordion-trigger w-full px-4 py-3 flex items-center justify-between text-left hover:bg-gray-800 transition-colors"
            data-accordion="params">
            <span class="text-white font-medium">Параметры</span>
            <svg class="accordion-icon w-5 h-5 text-gray-400 transform transition-transform duration-200" fill="none"
                stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>
        </button>
        <div class="accordion-content hidden" data-content="params">
            <div class="px-4 pb-4 space-y-4">
                {{-- Возраст --}}
                <div>
                    <label class="block text-gray-400 text-xs mb-2">Возраст</label>
                    <div class="flex items-center gap-2">
                        <input type="number" name="age_min" min="18" max="49" placeholder="От"
                            class="flex-1 bg-gray-800 text-white border border-gray-700 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-orange-500">
                        <span class="text-gray-500">—</span>
                        <input type="number" name="age_max" min="18" max="49" placeholder="До"
                            class="flex-1 bg-gray-800 text-white border border-gray-700 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-orange-500">
                    </div>
                </div>

                {{-- Рост --}}
                <div>
                    <label class="block text-gray-400 text-xs mb-2">Рост (см)</label>
                    <div class="flex items-center gap-2">
                        <input type="number" name="height_min" min="140" max="200" placeholder="От"
                            class="flex-1 bg-gray-800 text-white border border-gray-700 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-orange-500">
                        <span class="text-gray-500">—</span>
                        <input type="number" name="height_max" min="140" max="200" placeholder="До"
                            class="flex-1 bg-gray-800 text-white border border-gray-700 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-orange-500">
                    </div>
                </div>

                {{-- Вес --}}
                <div>
                    <label class="block text-gray-400 text-xs mb-2">Вес (кг)</label>
                    <div class="flex items-center gap-2">
                        <input type="number" name="weight_min" min="40" max="120" placeholder="От"
                            class="flex-1 bg-gray-800 text-white border border-gray-700 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-orange-500">
                        <span class="text-gray-500">—</span>
                        <input type="number" name="weight_max" min="40" max="120" placeholder="До"
                            class="flex-1 bg-gray-800 text-white border border-gray-700 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-orange-500">
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Особенности --}}
    <div class="bg-gray-900 rounded-xl overflow-hidden">
        <button type="button"
            class="accordion-trigger w-full px-4 py-3 flex items-center justify-between text-left hover:bg-gray-800 transition-colors"
            data-accordion="features">
            <span class="text-white font-medium">Особенности</span>
            <svg class="accordion-icon w-5 h-5 text-gray-400 transform transition-transform duration-200"
                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>
        </button>
        <div class="accordion-content hidden" data-content="features">
            <div class="px-4 pb-4 space-y-2">
                <label
                    class="flex items-center gap-3 p-2 hover:bg-gray-800 rounded-lg cursor-pointer transition-colors">
                    <input type="checkbox" name="has_video" value="1"
                        class="w-4 h-4 bg-gray-800 border-gray-600 rounded text-orange-500 focus:ring-orange-500 focus:ring-offset-0">
                    <span class="text-gray-300 text-sm">Только с видео</span>
                </label>
                <label
                    class="flex items-center gap-3 p-2 hover:bg-gray-800 rounded-lg cursor-pointer transition-colors">
                    <input type="checkbox" name="verified" value="1"
                        class="w-4 h-4 bg-gray-800 border-gray-600 rounded text-orange-500 focus:ring-orange-500 focus:ring-offset-0">
                    <span class="text-gray-300 text-sm">Проверенные</span>
                </label>
                <label
                    class="flex items-center gap-3 p-2 hover:bg-gray-800 rounded-lg cursor-pointer transition-colors">
                    <input type="checkbox" name="vip" value="1"
                        class="w-4 h-4 bg-gray-800 border-gray-600 rounded text-orange-500 focus:ring-orange-500 focus:ring-offset-0">
                    <span class="text-gray-300 text-sm">VIP</span>
                </label>
                <label
                    class="flex items-center gap-3 p-2 hover:bg-gray-800 rounded-lg cursor-pointer transition-colors">
                    <input type="checkbox" name="online" value="1"
                        class="w-4 h-4 bg-gray-800 border-gray-600 rounded text-orange-500 focus:ring-orange-500 focus:ring-offset-0">
                    <span class="text-gray-300 text-sm">Сейчас онлайн</span>
                </label>
            </div>
        </div>
    </div>

    {{-- Динамические аккордеоны с таксономиями --}}
    @foreach ($accordionSections as $sectionKey => $section)
        <div class="bg-gray-900 rounded-xl overflow-hidden">
            <button type="button"
                class="accordion-trigger w-full px-4 py-3 flex items-center justify-between text-left hover:bg-gray-800 transition-colors"
                data-accordion="{{ $sectionKey }}">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="{{ $section['icon'] }}"></path>
                    </svg>
                    <span class="text-white font-medium">{{ $section['title'] }}</span>
                </div>
                <svg class="accordion-icon w-5 h-5 text-gray-400 transform transition-transform duration-200"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>
            <div class="accordion-content hidden" data-content="{{ $sectionKey }}">
                <div class="px-4 pb-4">
                    @if (isset($section['taxonomy']))
                        {{-- Одна таксономия --}}
                        @php
                            $terms = get_terms(['taxonomy' => $section['taxonomy'], 'hide_empty' => true]);
                        @endphp
                        @if (!is_wp_error($terms) && !empty($terms))
                            <div class="space-y-1 max-h-64 overflow-y-auto custom-scrollbar">
                                @foreach ($terms as $term)
                                    <label
                                        class="flex items-center justify-between p-2 hover:bg-gray-800 rounded-lg cursor-pointer transition-colors">
                                        <div class="flex items-center gap-3">
                                            <input type="checkbox" name="{{ $section['taxonomy'] }}[]"
                                                value="{{ $term->slug }}"
                                                class="w-4 h-4 bg-gray-800 border-gray-600 rounded text-orange-500 focus:ring-orange-500 focus:ring-offset-0">
                                            <span class="text-gray-300 text-sm">{{ $term->name }}</span>
                                        </div>
                                        @if ($term->count > 0)
                                            <span
                                                class="text-xs text-gray-500 bg-gray-800 px-2 py-0.5 rounded">{{ $term->count }}</span>
                                        @endif
                                    </label>
                                @endforeach
                            </div>
                        @endif
                    @elseif (isset($section['items']))
                        {{-- Несколько таксономий в одной секции --}}
                        <div class="space-y-3">
                            @foreach ($section['items'] as $item)
                                @php
                                    $terms = get_terms(['taxonomy' => $item['taxonomy'], 'hide_empty' => true]);
                                @endphp
                                @if (!is_wp_error($terms) && !empty($terms))
                                    <div>
                                        <h4 class="text-gray-400 text-xs uppercase tracking-wider mb-2">
                                            {{ $item['label'] }}</h4>
                                        <div class="space-y-1 max-h-48 overflow-y-auto custom-scrollbar">
                                            @foreach ($terms as $term)
                                                <label
                                                    class="flex items-center justify-between p-2 hover:bg-gray-800 rounded-lg cursor-pointer transition-colors">
                                                    <div class="flex items-center gap-3">
                                                        <input type="checkbox" name="{{ $item['taxonomy'] }}[]"
                                                            value="{{ $term->slug }}"
                                                            class="w-4 h-4 bg-gray-800 border-gray-600 rounded text-orange-500 focus:ring-orange-500 focus:ring-offset-0">
                                                        <span class="text-gray-300 text-sm">{{ $term->name }}</span>
                                                    </div>
                                                    @if ($term->count > 0)
                                                        <span
                                                            class="text-xs text-gray-500 bg-gray-800 px-2 py-0.5 rounded">{{ $term->count }}</span>
                                                    @endif
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endforeach

    {{-- Кнопки действий --}}
    <div class="space-y-3 pt-2">
        <button type="submit"
            class="w-full bg-gradient-to-r from-orange-500 to-orange-600 text-white font-medium py-3 px-6 rounded-xl hover:from-orange-600 hover:to-orange-700 transform hover:scale-[1.02] transition-all duration-200 shadow-lg">
            Применить
        </button>
        <button type="reset"
            class="w-full bg-gray-800 text-gray-300 font-medium py-3 px-6 rounded-xl hover:bg-gray-700 transition-colors">
            Сбросить
        </button>
    </div>
</div>

<style>
    /* Кастомный скроллбар для темной темы */
    .custom-scrollbar::-webkit-scrollbar {
        width: 4px;
    }

    .custom-scrollbar::-webkit-scrollbar-track {
        background: #374151;
        border-radius: 2px;
    }

    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #6b7280;
        border-radius: 2px;
    }

    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: #9ca3af;
    }

    /* Анимация для аккордеонов */
    .accordion-content {
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.3s ease-out;
    }

    .accordion-content.active {
        max-height: 800px;
        transition: max-height 0.3s ease-in;
    }

    /* Стили для чекбоксов */
    input[type="checkbox"]:checked {
        background-color: #f97316;
        border-color: #f97316;
    }

    input[type="checkbox"]:checked::before {
        content: '✓';
        display: block;
        text-align: center;
        color: white;
        font-weight: bold;
        font-size: 12px;
        line-height: 14px;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const container = document.querySelector('.filters-accordion');

        if (!container) return;

        // Управление аккордеонами
        const triggers = container.querySelectorAll('.accordion-trigger');

        triggers.forEach(trigger => {
            trigger.addEventListener('click', () => {
                const accordionId = trigger.dataset.accordion;
                const content = container.querySelector(`[data-content="${accordionId}"]`);
                const icon = trigger.querySelector('.accordion-icon');

                if (!content) return;

                const isActive = content.classList.contains('active');

                if (isActive) {
                    content.classList.remove('active');
                    content.classList.add('hidden');
                    icon.classList.remove('rotate-180');
                } else {
                    content.classList.remove('hidden');
                    setTimeout(() => content.classList.add('active'), 10);
                    icon.classList.add('rotate-180');
                }
            });
        });

        // Управление слайдерами цены
        const minRange = container.querySelector('[data-min-range]');
        const maxRange = container.querySelector('[data-max-range]');
        const minInput = container.querySelector('[data-min-input]');
        const maxInput = container.querySelector('[data-max-input]');
        const priceTrack = container.querySelector('.price-track');

        if (minRange && maxRange && minInput && maxInput && priceTrack) {
            const minLimit = Number(minRange.min || 8000);
            const maxLimit = Number(maxRange.max || 50000);
            const priceStep = Number(minRange.step || 500);

            const updatePriceTrack = () => {
                const min = parseInt(minRange.value);
                const max = parseInt(maxRange.value);
                const span = Math.max(maxLimit - minLimit, 1);
                const minPercent = ((min - minLimit) / span) * 100;
                const maxPercent = ((max - minLimit) / span) * 100;

                priceTrack.style.left = minPercent + '%';
                priceTrack.style.width = (maxPercent - minPercent) + '%';
            };

            const syncRanges = () => {
                let minVal = parseInt(minRange.value);
                let maxVal = parseInt(maxRange.value);

                if (minVal > maxVal - priceStep) {
                    if (event.target === minRange) {
                        minRange.value = maxVal - priceStep;
                    } else {
                        maxRange.value = minVal + priceStep;
                    }
                }

                minInput.value = minRange.value;
                maxInput.value = maxRange.value;
                updatePriceTrack();
            };

            const syncInputs = () => {
                minRange.value = minInput.value || minLimit;
                maxRange.value = maxInput.value || maxLimit;
                updatePriceTrack();
            };

            minRange.addEventListener('input', syncRanges);
            maxRange.addEventListener('input', syncRanges);
            minInput.addEventListener('change', syncInputs);
            maxInput.addEventListener('change', syncInputs);

            updatePriceTrack();
        }
    });
</script>
