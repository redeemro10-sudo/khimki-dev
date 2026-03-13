{{-- resources/views/components/filters-multilevel.blade.php --}}

<div class="filters-container bg-white rounded-lg shadow-lg overflow-hidden" data-filter-id="{{ $id ?? 'filter-1' }}">
    {{-- Р—Р°РіРѕР»РѕРІРѕРє С„РёР»СЊС‚СЂРѕРІ --}}
    <div class="filter-header bg-gray-100 px-4 py-3 border-b flex items-center justify-between">
        <button type="button" class="back-btn hidden text-gray-600 hover:text-gray-900 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
        </button>
        <h3 class="text-lg font-semibold flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4">
                </path>
            </svg>
            Р¤РёР»СЊС‚СЂС‹
        </h3>
        <button type="button" title="Р—Р°РєСЂС‹С‚СЊ С„РёР»СЊС‚СЂ"
            class="close-filters text-gray-400 hover:text-gray-600 transition-colors lg:hidden">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
    </div>

    {{-- РљРѕРЅС‚РµРЅС‚ С„РёР»СЊС‚СЂРѕРІ --}}
    <div class="filter-content relative min-h-[400px]">
        {{-- РћСЃРЅРѕРІРЅРѕР№ СѓСЂРѕРІРµРЅСЊ --}}
        <div class="filter-level active overflow-y-auto " data-level="main">
            <div class="p-4 overflow-y-auto space-y-2">
                {{-- РџРѕРёСЃРє --}}
                <div class="pb-2 border-b">
                    <div class="relative">
                        <input type="search" name="q" placeholder="РџРѕРёСЃРє..."
                            class="w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg focus:outline-none focus:border-blue-500 focus:bg-white transition-all">
                    </div>
                </div>

                {{-- РЈСЃР»СѓРіРё --}}
                <button type="button"
                    class="filter-nav-item w-full px-4 py-3 flex items-center justify-between hover:bg-gray-50 rounded-lg transition-colors"
                    data-target="services">
                    <span class="text-gray-700 font-medium">РЈСЃР»СѓРіРё</span>
                    <span class="flex items-center gap-2">
                        <span class="selected-count text-xs text-gray-500">Р›СЋР±С‹Рµ</span>
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7">
                            </path>
                        </svg>
                    </span>
                </button>

                {{-- Р›РѕРєР°С†РёСЏ --}}
                <button type="button"
                    class="filter-nav-item w-full px-4 py-3 flex items-center justify-between hover:bg-gray-50 rounded-lg transition-colors"
                    data-target="location">
                    <span class="text-gray-700 font-medium">Р›РѕРєР°С†РёСЏ</span>
                    <span class="flex items-center gap-2">
                        <span class="selected-count text-xs text-gray-500">Р›СЋР±Р°СЏ</span>
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7">
                            </path>
                        </svg>
                    </span>
                </button>

                {{-- Р’РЅРµС€РЅРѕСЃС‚СЊ --}}
                <button type="button"
                    class="filter-nav-item w-full px-4 py-3 flex items-center justify-between hover:bg-gray-50 rounded-lg transition-colors"
                    data-target="appearance">
                    <span class="text-gray-700 font-medium">Р’РЅРµС€РЅРѕСЃС‚СЊ</span>
                    <span class="flex items-center gap-2">
                        <span class="selected-count text-xs text-gray-500">Р›СЋР±Р°СЏ</span>
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7">
                            </path>
                        </svg>
                    </span>
                </button>

                {{-- РџР°СЂР°РјРµС‚СЂС‹ --}}
                <div class="pt-2 space-y-3">
                    <h4 class="text-sm font-semibold text-gray-600 uppercase tracking-wider">РџР°СЂР°РјРµС‚СЂС‹</h4>

                    {{-- Р’РѕР·СЂР°СЃС‚ --}}
                    <div>
                        <label class="text-xs text-gray-500 mb-1 block">Р’РѕР·СЂР°СЃС‚</label>
                        <div class="flex items-center gap-2">
                            <input type="number" name="age_min"
                                class="flex-1 px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-blue-500"
                                placeholder="РћС‚" min="18" max="49">
                            <span class="text-gray-400">вЂ”</span>
                            <input type="number" name="age_max"
                                class="flex-1 px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-blue-500"
                                placeholder="Р”Рѕ" min="18" max="49">
                        </div>
                    </div>

                    {{-- Р¦РµРЅР° --}}
                    <div>
                        <label class="text-xs text-gray-500 mb-1 block">Р¦РµРЅР° (в‚Ѕ)</label>
                        <div class="flex items-center gap-2">
                            <input type="number" name="price_min"
                                class="flex-1 px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-blue-500"
                                placeholder="РћС‚" min="8000" max="50000" step="500">
                            <span class="text-gray-400">вЂ”</span>
                            <input type="number" name="price_max"
                                class="flex-1 px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-sm focus:outline-none focus:border-blue-500"
                                placeholder="Р”Рѕ" min="8000" max="50000" step="500">
                        </div>
                    </div>
                </div>

                {{-- РћСЃРѕР±РµРЅРЅРѕСЃС‚Рё --}}
                <div class="pt-3 space-y-2">
                    <h4 class="text-sm font-semibold text-gray-600 uppercase tracking-wider">РћСЃРѕР±РµРЅРЅРѕСЃС‚Рё</h4>
                    <label class="flex items-center gap-3 p-2 hover:bg-gray-50 rounded-lg cursor-pointer">
                        <input type="checkbox" name="has_video" value="1"
                            class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                        <span class="text-gray-700 text-sm">РЎ РІРёРґРµРѕ</span>
                    </label>
                    <label class="flex items-center gap-3 p-2 hover:bg-gray-50 rounded-lg cursor-pointer">
                        <input type="checkbox" name="feature[]" value="proverennyye"
                            class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                        <span class="text-gray-700 text-sm">РџСЂРѕРІРµСЂРµРЅРЅС‹Рµ</span>
                    </label>
                    <label class="flex items-center gap-3 p-2 hover:bg-gray-50 rounded-lg cursor-pointer">
                        <input type="checkbox" name="feature[]" value="vip"
                            class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                        <span class="text-gray-700 text-sm">VIP</span>
                    </label>
                </div>
            </div>
        </div>

        {{-- РЈСЂРѕРІРµРЅСЊ СѓСЃР»СѓРі --}}
        <div class="filter-level overflow-y-auto absolute inset-0 bg-white min-h-[400px]" data-level="services"
            style="display: none;">
            <div class="p-4 overflow-y-auto">
                <div class="mb-3">
                    <label class="flex items-center gap-3 p-2 bg-blue-50 rounded-lg">
                        <input type="checkbox" class="select-all w-4 h-4 text-blue-600 border-gray-300 rounded">
                        <span class="text-gray-700 text-sm font-medium">Р’С‹Р±СЂР°С‚СЊ РІСЃРµ</span>
                    </label>
                </div>
                <fieldset class="space-y-1">
                    @php
                        $services = get_terms(['taxonomy' => 'service', 'hide_empty' => true]);
                    @endphp
                    @if (!is_wp_error($services) && !empty($services))
                        @foreach ($services as $term)
                            <label
                                class="flex items-center justify-between p-2 hover:bg-gray-50 rounded-lg cursor-pointer">
                                <span class="flex items-center gap-3">
                                    <input type="checkbox" name="service[]" value="{{ $term->slug }}"
                                        class="service-checkbox w-4 h-4 text-blue-600 border-gray-300 rounded">
                                    <span class="text-gray-700 text-sm">{{ $term->name }}</span>
                                </span>
                                @if ($term->count > 0)
                                    <span
                                        class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded">{{ $term->count }}</span>
                                @endif
                            </label>
                        @endforeach
                    @endif
                </fieldset>
            </div>
        </div>

        {{-- РЈСЂРѕРІРµРЅСЊ Р»РѕРєР°С†РёРё --}}
        <div class="filter-level overflow-y-auto absolute inset-0 bg-white min-h-[400px]" data-level="location"
            style="display: none;">
            <div class="p-4 overflow-y-auto">
                {{-- Р Р°Р№РѕРЅС‹ --}}
                <div class="mb-4">
                    <h4 class="text-sm font-semibold text-gray-600 uppercase tracking-wider mb-2">Р Р°Р№РѕРЅС‹</h4>
                    <fieldset class="space-y-1">
                        @php
                            $districts = get_terms(['taxonomy' => 'district', 'hide_empty' => true]);
                        @endphp
                        @if (!is_wp_error($districts) && !empty($districts))
                            @foreach ($districts as $term)
                                <label
                                    class="flex items-center justify-between p-2 hover:bg-gray-50 rounded-lg cursor-pointer">
                                    <span class="flex items-center gap-3">
                                        <input type="checkbox" name="district[]" value="{{ $term->slug }}"
                                            class="location-checkbox w-4 h-4 text-blue-600 border-gray-300 rounded">
                                        <span class="text-gray-700 text-sm">{{ $term->name }}</span>
                                    </span>
                                    @if ($term->count > 0)
                                        <span
                                            class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded">{{ $term->count }}</span>
                                    @endif
                                </label>
                            @endforeach
                        @endif
                    </fieldset>
                </div>

                {{-- РњРµС‚СЂРѕ --}}
                {{-- <div>
                    <h4 class="text-sm font-semibold text-gray-600 uppercase tracking-wider mb-2">РњРµС‚СЂРѕ</h4>
                    <div class="space-y-1">
                        @php
                            $stations = get_terms(['taxonomy' => 'rail_station', 'hide_empty' => true]);
                        @endphp
                        @if (!is_wp_error($stations) && !empty($stations))
                            @foreach ($stations as $term)
                                <label
                                    class="flex items-center justify-between p-2 hover:bg-gray-50 rounded-lg cursor-pointer">
                                    <div class="flex items-center gap-3">
                                        <input type="checkbox" name="rail_station[]" value="{{ $term->slug }}"
                                            class="location-checkbox w-4 h-4 text-blue-600 border-gray-300 rounded">
                                        <span class="text-gray-700 text-sm">{{ $term->name }}</span>
                                    </div>
                                    @if ($term->count > 0)
                                        <span
                                            class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded">{{ $term->count }}</span>
                                    @endif
                                </label>
                            @endforeach
                        @endif
                    </div>
                </div> --}}
            </div>
        </div>

        {{-- РЈСЂРѕРІРµРЅСЊ РІРЅРµС€РЅРѕСЃС‚Рё --}}
        <div class="filter-level overflow-y-auto absolute inset-0 bg-white min-h-[400px]" data-level="appearance"
            style="display: none;">
            <div class="p-4 overflow-y-auto">
                @php
                    $appearanceFilters = [
                        ['taxonomy' => 'hair_color', 'label' => 'Р¦РІРµС‚ РІРѕР»РѕСЃ'],
                        ['taxonomy' => 'nationality', 'label' => 'РќР°С†РёРѕРЅР°Р»СЊРЅРѕСЃС‚СЊ'],
                        ['taxonomy' => 'bust_size', 'label' => 'Р Р°Р·РјРµСЂ РіСЂСѓРґРё'],
                        ['taxonomy' => 'aye_color', 'label' => 'Р¦РІРµС‚ РіР»Р°Р·'],
                        ['taxonomy' => 'physique', 'label' => 'РўРµР»РѕСЃР»РѕР¶РµРЅРёРµ'],
                    ];
                @endphp

                @foreach ($appearanceFilters as $filter)
                    <div class="mb-4">
                        <h4 class="text-sm font-semibold text-gray-600 uppercase tracking-wider mb-2">
                            {{ $filter['label'] }}</h4>
                        <fieldset class="space-y-1">
                            @php
                                $terms = get_terms(['taxonomy' => $filter['taxonomy'], 'hide_empty' => true]);
                            @endphp
                            @if (!is_wp_error($terms) && !empty($terms))
                                @foreach ($terms as $term)
                                    <label
                                        class="flex items-center justify-between p-2 hover:bg-gray-50 rounded-lg cursor-pointer">
                                        <span class="flex items-center gap-3">
                                            <input type="checkbox" name="{{ $filter['taxonomy'] }}[]"
                                                value="{{ $term->slug }}"
                                                class="appearance-checkbox w-4 h-4 text-blue-600 border-gray-300 rounded">
                                            <span class="text-gray-700 text-sm">{{ $term->name }}</span>
                                        </span>
                                        @if ($term->count > 0)
                                            <span
                                                class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded">{{ $term->count }}</span>
                                        @endif
                                    </label>
                                @endforeach
                            @endif
                        </fieldset>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- РљРЅРѕРїРєРё РґРµР№СЃС‚РІРёР№ --}}
    <div class="filter-footer bg-gray-50 px-4 py-3 border-t flex gap-3">
        <button type="reset"
            class="reset-btn flex-1 px-4 py-2.5 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors font-medium">
            РЎР±СЂРѕСЃРёС‚СЊ
        </button>
        <button type="submit"
            class="apply-btn flex-1 px-4 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium">
            РџСЂРёРјРµРЅРёС‚СЊ
        </button>
    </div>
</div>
