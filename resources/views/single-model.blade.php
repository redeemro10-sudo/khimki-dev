{{-- resources/views/single-model.blade.php --}}
@extends('layouts.app')

@section('content')
    @php
        $id = get_the_ID();
        $name = get_the_title() ?: '';
        $districtTerms = wp_get_post_terms($id, 'district', ['fields' => 'all']);
        $district = array_values(array_map(static function ($term) {
            return $term->name;
        }, $districtTerms));
        $districtName = $districtTerms[0]->name ?? null;
        $districtUrl = ! empty($districtTerms[0]) ? get_term_link($districtTerms[0]) : null;
        $districtUrl = is_wp_error($districtUrl) ? null : $districtUrl;
        $station = wp_get_post_terms($id, 'rail_station', ['fields' => 'names']);
        /* $service = wp_get_post_terms($id, 'service', ['fields' => 'names']); */
        $services = wp_get_post_terms($id, 'service', ['fields' => 'all']); // массив WP_Term
        $services = array_map(function ($t) {
            $link = get_term_link($t);
            return [
                'name' => $t->name,
                'url' => is_wp_error($link) ? '#' : $link,
            ];
        }, $services);
        $features = wp_get_post_terms($id, 'feature', ['fields' => 'slugs']);
        $isVip = in_array('vip', $features);
        $isVerified = in_array('verified', $features);

        // Метаданные
        $age = (int) get_post_meta($id, 'age', true);
        $height = (int) get_post_meta($id, 'height', true);
        $weight = (int) get_post_meta($id, 'weight', true);
        $price = (int) get_post_meta($id, '_price', true);
        $hair = wp_get_post_terms($id, 'hair_color', ['fields' => 'names'])[0] ?? null;
        $nat = wp_get_post_terms($id, 'nationality', ['fields' => 'names'])[0] ?? null;
        $bust = wp_get_post_terms($id, 'bust_size', ['fields' => 'names'])[0] ?? null;
        $massage = wp_get_post_terms($id, 'massage', ['fields' => 'names'])[0] ?? null;
        $physique = wp_get_post_terms($id, 'physique', ['fields' => 'names'])[0] ?? null;
        $intimate = wp_get_post_terms($id, 'intimate_haircut', ['fields' => 'names'])[0] ?? null;
        $striptease = wp_get_post_terms($id, 'striptease_services', ['fields' => 'names'])[0] ?? null;
        $extreme = wp_get_post_terms($id, 'extreme_services', ['fields' => 'names'])[0] ?? null;
        $sado = wp_get_post_terms($id, 'sado_maso', ['fields' => 'names'])[0] ?? null;
        $online = (int) get_post_meta($id, '_online', true) === 1;
        $profileText = get_post_meta($id, '_profile_text', true);
        $districtTermIds = array_values(
            array_filter(
                array_map(static function ($term) {
                    return $term instanceof \WP_Term ? (int) $term->term_id : 0;
                }, $districtTerms),
            ),
        );
        $relatedModelsArgs = [
            'post_type' => 'model',
            'post_status' => 'publish',
            'posts_per_page' => 8,
            'post__not_in' => [$id],
            'orderby' => 'rand',
            'ignore_sticky_posts' => true,
            'no_found_rows' => true,
        ];
        if (!empty($districtTermIds)) {
            $relatedModelsArgs['tax_query'] = [
                [
                    'taxonomy' => 'district',
                    'field' => 'term_id',
                    'terms' => $districtTermIds,
                ],
            ];
        }
        $relatedModelsQuery = new \WP_Query($relatedModelsArgs);
        $relatedModels = $relatedModelsQuery->posts;
        if (empty($relatedModels) && !empty($districtTermIds)) {
            unset($relatedModelsArgs['tax_query']);
            $relatedModelsQuery = new \WP_Query($relatedModelsArgs);
            $relatedModels = $relatedModelsQuery->posts;
        }
        wp_reset_postdata();

        // Галерея и видео
        $galleryIds = array_filter(array_map('intval', (array) get_post_meta($id, '_gallery_ids', true)));
        if (empty($galleryIds)) {
            $thumb = get_post_thumbnail_id();
            if ($thumb) {
                $galleryIds = [$thumb];
            }
        }

        $videos = get_post_meta($id, '_videos', true);
        $videos = is_array($videos) ? array_values($videos) : [];

        // SEO заголовок
        $bits = array_filter([$name, $districtName, $station[0] ?? null]);
        $h1 = implode(' · ', $bits);
        $modelImageAltParts = array_filter([
            $name ? 'Имя: ' . $name : null,
            $age ? 'Возраст: ' . $age : null,
            $bust ? 'Грудь: ' . $bust : null,
        ]);
        $modelImageAlt = implode(', ', $modelImageAltParts);
        $modelImageTitle = $name ? 'Эскортница ' . $name . ($age ? ', ' . $age : '') : null;
    @endphp

    <article itemscope itemtype="https://schema.org/Person" class="single-model">
        {{-- Hero секция с градиентом --}}
        <div class="relative bg-gradient-to-br from-blue-50 via-white to-purple-50 overflow-hidden">
            <div class="absolute inset-0 bg-white/40 backdrop-blur-3xl"></div>
            <div class="relative container max-w-7xl mx-auto px-4 py-8 lg:py-12">

                {{-- Header с badges --}}
                <header class="mb-8">
                    <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-6">
                        <div class="flex-1">
                            <h1 itemprop="name" class="text-3xl lg:text-4xl font-bold text-gray-900 mb-4">
                                {{ $h1 }}</h1>

                            {{-- Badges --}}
                            <div class="flex flex-wrap items-center gap-2 mb-4">
                                @if ($isVerified)
                                    <span
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-green-100 text-green-800 border border-green-200">
                                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                clip-rule="evenodd" />
                                        </svg>
                                        Проверенная
                                    </span>
                                @endif
                                @if ($isVip)
                                    <span
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-gradient-to-r from-yellow-100 to-orange-100 text-orange-800 border border-orange-200">
                                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                            <path
                                                d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                        </svg>
                                        VIP
                                    </span>
                                @endif
                                @if ($online)
                                    <span
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-800 border border-blue-200">
                                        <span class="relative flex h-2 w-2">
                                            <span
                                                class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span>
                                            <span class="relative inline-flex rounded-full h-2 w-2 bg-blue-500"></span>
                                        </span>
                                        Онлайн
                                    </span>
                                @endif
                                @if (!empty($videos))
                                    <span
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold bg-purple-100 text-purple-800 border border-purple-200">
                                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                            <path
                                                d="M2 6a2 2 0 012-2h6a2 2 0 012 2v8a2 2 0 01-2 2H4a2 2 0 01-2-2V6zM14.553 7.106A1 1 0 0014 8v4a1 1 0 00.553.894l2 1A1 1 0 0018 13V7a1 1 0 00-1.447-.894l-2 1z" />
                                        </svg>
                                        Видео
                                    </span>
                                @endif
                            </div>

                            {{-- Локация для мобильных --}}
                            <div class="flex flex-wrap items-center gap-4 text-sm lg:hidden">
                                @if (!empty($station[0]))
                                    <div class="flex items-center gap-1.5 text-gray-700">
                                        <svg class="w-4 h-4 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path
                                                d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" />
                                        </svg>
                                        {{ $station[0] }}
                                    </div>
                                @endif
                                @if ($districtName)
                                    <div class="flex items-center gap-1.5 text-gray-700">
                                        <svg class="w-4 h-4 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z"
                                                clip-rule="evenodd" />
                                        </svg>
                                        @if ($districtUrl)
                                            <a href="{{ esc_url($districtUrl) }}" class="transition-colors hover:text-blue-700">
                                                {{ $districtName }}
                                            </a>
                                        @else
                                            {{ $districtName }}
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Цена --}}
                        @if ($price)
                            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 lg:min-w-[200px]">
                                <div class="text-sm text-gray-600 mb-1">Стоимость от</div>
                                <div
                                    class="text-3xl font-bold bg-gradient-to-r from-blue-600 to-purple-600 bg-clip-text text-transparent">
                                    {{ number_format($price, 0, '', ' ') }} ₽
                                </div>
                                <div class="text-xs text-gray-500 mt-2">за час</div>
                            </div>
                        @endif
                    </div>
                </header>
            </div>
        </div>

        {{-- Основной контент --}}
        <div class="container max-w-7xl mx-auto px-4 py-8 lg:py-12">
            <div class="grid lg:grid-cols-3 gap-8 lg:gap-12">
                {{-- Левая колонка: галерея и основной контент --}}
                <div class="lg:col-span-2">
                    {{-- Галерея с Swiper --}}
                    @if (!empty($galleryIds) || !empty($videos))
                        <div class="model-gallery mb-10">
                            {{-- Главный слайдер --}}
                            <div class="gallery-main-wrapper relative group">
                                <div
                                    class="swiper gallery-main rounded-2xl overflow-hidden bg-gradient-to-br from-gray-100 to-gray-50 shadow-xl">
                                    <div class="swiper-wrapper">
                                        @foreach ($videos as $v)
                                            @php
                                                $vid = isset($v['id']) ? intval($v['id']) : 0;
                                                $url = isset($v['url']) ? (string) $v['url'] : '';
                                                $src = $vid ? wp_get_attachment_url($vid) : $url;
                                            @endphp
                                            @if ($src)
                                                <div class="swiper-slide">
                                                    <div
                                                        class="aspect-[3/4] lg:aspect-[4/5] flex items-center justify-center bg-black">
                                                        @if (preg_match('~^(https?:)?//(www\.)?(youtube|youtu\.be|vimeo)~i', $src))
                                                            <div class="w-full h-full">{!! wp_oembed_get($src) !!}</div>
                                                        @else
                                                            <video controls playsinline preload="metadata"
                                                                class="w-full h-full object-contain">
                                                                <source src="{{ esc_url($src) }}"
                                                                    type="{{ wp_check_filetype($src)['type'] ?: 'video/mp4' }}">
                                                            </video>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endif
                                        @endforeach

                                        @foreach ($galleryIds as $imgIndex => $imgId)
                                            <div class="swiper-slide">
                                                <a href="{{ wp_get_attachment_image_url($imgId, 'full') }}"
                                                    data-lightbox="gallery"
                                                    data-title="{{ esc_attr($modelImageAlt) }}">
                                                    <div class="relative overflow-hidden aspect-[3/4] lg:aspect-[4/5]">
                                                        @php
                                                            // Создаем правильный атрибут sizes без 'auto'
                                                            $sizes = '(max-width: 576px) 100vw, 576px';

                                                            // Генерируем изображение с корректными атрибутами
                                                            $img_html = wp_get_attachment_image(
                                                                $imgId,
                                                                'large',
                                                                false,
                                                                [
                                                                    'class' =>
                                                                        ($imgIndex === 0 && empty($videos)
                                                                            ? 'is-lcp '
                                                                            : '') .
                                                                        'w-full h-full object-cover hover:scale-105 transition-transform duration-500',
                                                                    'alt' => $modelImageAlt,
                                                                    'title' => $modelImageTitle,
                                                                    'sizes' => $sizes,
                                                                    'loading' =>
                                                                        $imgIndex === 0 && empty($videos)
                                                                            ? 'eager'
                                                                            : 'lazy',
                                                                    'fetchpriority' =>
                                                                        $imgIndex === 0 && empty($videos)
                                                                            ? 'high'
                                                                            : 'low',
                                                                    'decoding' =>
                                                                        $imgIndex === 0 && empty($videos)
                                                                            ? 'auto'
                                                                            : 'async',
                                                                ],
                                                            );

                                                            // Дополнительная очистка на случай, если WordPress все еще добавляет 'auto'
                                                            $img_html = str_replace(
                                                                'sizes="auto,',
                                                                'sizes="',
                                                                $img_html,
                                                            );
                                                            $img_html = str_replace(
                                                                'auto, (max-width:',
                                                                '(max-width:',
                                                                $img_html,
                                                            );
                                                        @endphp

                                                        {!! $img_html !!}
                                                        <div
                                                            class="absolute inset-0 bg-gradient-to-t from-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                                                        </div>
                                                    </div>
                                                </a>
                                            </div>
                                        @endforeach
                                    </div>

                                    {{-- Кнопки навигации --}}
                                    <div
                                        class="swiper-button-next !w-12 !h-12 !bg-white/90 !text-gray-700 !shadow-lg hover:!bg-white transition-all !rounded-full">
                                    </div>
                                    <div
                                        class="swiper-button-prev !w-12 !h-12 !bg-white/90 !text-gray-700 !shadow-lg hover:!bg-white transition-all !rounded-full">
                                    </div>

                                    {{-- Пагинация --}}
                                    <div class="swiper-pagination !bottom-4"></div>

                                    {{-- Счетчик изображений --}}
                                    @if (count($galleryIds) + count($videos) > 1)
                                        <div
                                            class="absolute top-4 right-4 z-10 bg-black/60 backdrop-blur-sm text-white px-3 py-1.5 rounded-full text-sm font-medium">
                                            <span class="current-slide">1</span> / {{ count($galleryIds) + count($videos) }}
                                        </div>
                                    @endif
                                </div>
                            </div>

                            {{-- Миниатюры --}}
                            @if (count($galleryIds) + count($videos) > 1)
                                <div class="mt-4">
                                    <div class="swiper gallery-thumbs">
                                        <div class="swiper-wrapper">
                                            @foreach ($videos as $v)
                                                <div class="swiper-slide cursor-pointer">
                                                    <div
                                                        class="aspect-square rounded-xl overflow-hidden bg-gray-900 relative group">
                                                        <div
                                                            class="absolute inset-0 flex items-center justify-center bg-black/50 group-hover:bg-black/30 transition-colors">
                                                            <div
                                                                class="w-8 h-8 lg:w-12 lg:h-12 rounded-full bg-white/90 flex items-center justify-center">
                                                                <svg class="w-4 h-4 lg:w-6 lg:h-6 text-gray-900 ml-0.5"
                                                                    fill="currentColor" viewBox="0 0 20 20">
                                                                    <path
                                                                        d="M6.3 2.841A1.5 1.5 0 004 4.11V15.89a1.5 1.5 0 002.3 1.269l9.344-5.89a1.5 1.5 0 000-2.538L6.3 2.84z" />
                                                                </svg>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach

                                            @foreach ($galleryIds as $imgId)
                                                <div class="swiper-slide cursor-pointer">
                                                    <div class="aspect-square rounded-xl overflow-hidden group">
                                                        @php
                                                            // Правильный sizes для миниатюр
                                                            $thumb_sizes =
                                                                '(max-width: 640px) 25vw, (max-width: 768px) 16vw, (max-width: 1024px) 14vw, 120px';

                                                            $thumb_html = wp_get_attachment_image(
                                                                $imgId,
                                                                'medium',
                                                                false,
                                                                [
                                                                    'class' =>
                                                                        'w-full h-full object-cover group-hover:scale-110 transition-transform duration-300',
                                                                    'alt' => $modelImageAlt,
                                                                    'title' => $modelImageTitle,
                                                                    'sizes' => $thumb_sizes,
                                                                    'loading' => 'lazy',
                                                                    'fetchpriority' => 'low',
                                                                    'decoding' => 'async',
                                                                ],
                                                            );

                                                            // Очистка от 'auto'
                                                            $thumb_html = str_replace(
                                                                'sizes="auto,',
                                                                'sizes="',
                                                                $thumb_html,
                                                            );
                                                            $thumb_html = str_replace(
                                                                'auto, (max-width:',
                                                                '(max-width:',
                                                                $thumb_html,
                                                            );
                                                        @endphp

                                                        {!! $thumb_html !!}
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif

                    {{-- Описание --}}
                    @if (!empty($profileText))
                        <section class="mb-10">
                            <div class="bg-white rounded-2xl border border-gray-200 p-6 lg:p-8 shadow-sm">
                                <h2 class="text-2xl font-bold text-gray-900 mb-4 flex items-center gap-2">
                                    <svg class="w-6 h-6 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    О себе
                                </h2>
                                <div class="prose prose-lg max-w-none text-gray-700">
                                    {!! wp_kses_post($profileText) !!}
                                </div>
                            </div>
                        </section>
                    @endif

                    {{-- Контент из редактора --}}
                    @if (get_the_content())
                        <section class="mb-10">
                            <div class="prose prose-lg max-w-none text-gray-700">
                                {!! apply_filters('the_content', get_the_content()) !!}
                            </div>
                        </section>
                    @endif

                    {{-- Услуги --}}
                    @if (!empty($services))
                        <section class="mb-10">
                            <div
                                class="bg-gradient-to-br from-blue-50 to-purple-50 rounded-2xl p-6 lg:p-8 border border-blue-100">
                                <h2 class="text-2xl font-bold text-gray-900 mb-6 flex items-center gap-2">
                                    <svg class="w-6 h-6 text-blue-600" fill="currentColor" viewBox="0 0 20 20"
                                        aria-hidden="true">
                                        <path
                                            d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z" />
                                    </svg>
                                    Услуги
                                </h2>

                                <!-- xs: flex-wrap; sm+: grid с авто-высотой рядов -->
                                <div class="flex flex-wrap gap-2 sm:grid sm:grid-cols-3 sm:gap-3 sm:auto-rows-max">
                                    @foreach ($services as $s)
                                        <span
                                            class="service-chip inline-flex items-center justify-center
                  min-w-0 max-w-full whitespace-normal break-words text-center leading-tight
                  bg-white rounded-xl px-4 py-3 text-sm font-medium text-gray-700
                  border border-gray-200
                  transition-all duration-200">
                                            {{ e($s['name']) }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>
                        </section>
                    @endif

                    {{-- Дополнительные услуги --}}
                    @php
                        $additionalServices = array_filter([
                            'Массаж' => $massage,
                            'Стриптиз' => $striptease,
                            'Экстрим' => $extreme,
                            'BDSM' => $sado,
                        ]);
                    @endphp

                    @if (!empty($additionalServices))
                        <section class="mb-10">
                            <h3 class="text-xl font-bold text-gray-900 mb-4">Дополнительные услуги</h3>
                            <div class="grid grid-cols-2 gap-4">
                                @foreach ($additionalServices as $label => $value)
                                    <div class="flex items-center gap-3 p-4 bg-gray-50 rounded-xl">
                                        <svg class="w-5 h-5 text-green-600 flex-shrink-0" fill="currentColor"
                                            viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                clip-rule="evenodd" />
                                        </svg>
                                        <div>
                                            <div class="text-xs text-gray-600">{{ $label }}</div>
                                            <div class="font-medium text-gray-900">{{ $value }}</div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </section>
                    @endif
                </div>

                {{-- Правая колонка: характеристики и контакты --}}
                <div class="lg:col-span-1">
                    <div class="sticky top-24 space-y-6">
                        {{-- Характеристики --}}
                        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm overflow-hidden">
                            <div class="bg-gradient-to-r from-blue-500 to-purple-600 p-6">
                                <h3 class="text-lg font-bold text-white flex items-center gap-2">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    Характеристики
                                </h3>
                            </div>
                            <div class="p-6">
                                <dl class="space-y-4">
                                    @if ($age)
                                        <div class="flex items-center justify-between py-3 border-b border-gray-100">
                                            <dt class="text-gray-600 flex items-center gap-2">
                                                <svg class="w-4 h-4 text-gray-400" fill="currentColor"
                                                    viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd"
                                                        d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                                Возраст
                                            </dt>
                                            <dd class="font-semibold text-gray-900">{{ $age }} лет</dd>
                                        </div>
                                    @endif
                                    @if ($height)
                                        <div class="flex items-center justify-between py-3 border-b border-gray-100">
                                            <dt class="text-gray-600 flex items-center gap-2">
                                                <svg class="w-4 h-4 text-gray-400" fill="currentColor"
                                                    viewBox="0 0 20 20">
                                                    <path
                                                        d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z" />
                                                </svg>
                                                Рост
                                            </dt>
                                            <dd class="font-semibold text-gray-900">{{ $height }} см</dd>
                                        </div>
                                    @endif
                                    @if ($weight)
                                        <div class="flex items-center justify-between py-3 border-b border-gray-100">
                                            <dt class="text-gray-600 flex items-center gap-2">
                                                <svg class="w-4 h-4 text-gray-400" fill="currentColor"
                                                    viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd"
                                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zM7 9a1 1 0 000 2h6a1 1 0 100-2H7z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                                Вес
                                            </dt>
                                            <dd class="font-semibold text-gray-900">{{ $weight }} кг</dd>
                                        </div>
                                    @endif
                                    @if ($bust)
                                        <div class="flex items-center justify-between py-3 border-b border-gray-100">
                                            <dt class="text-gray-600">Размер груди</dt>
                                            <dd class="font-semibold text-gray-900">{{ $bust }}</dd>
                                        </div>
                                    @endif
                                    @if ($hair)
                                        <div class="flex items-center justify-between py-3 border-b border-gray-100">
                                            <dt class="text-gray-600">Цвет волос</dt>
                                            <dd class="font-semibold text-gray-900">{{ $hair }}</dd>
                                        </div>
                                    @endif
                                    @if ($nat)
                                        <div class="flex items-center justify-between py-3 border-b border-gray-100">
                                            <dt class="text-gray-600">Национальность</dt>
                                            <dd class="font-semibold text-gray-900">{{ $nat }}</dd>
                                        </div>
                                    @endif
                                    @if ($physique)
                                        <div class="flex items-center justify-between py-3">
                                            <dt class="text-gray-600">Телосложение</dt>
                                            <dd class="font-semibold text-gray-900">{{ $physique }}</dd>
                                        </div>
                                    @endif
                                </dl>
                            </div>
                        </div>

                        {{-- Локация для десктопа --}}
                        @if (!empty($station[0]) || $districtName)
                            <div
                                class="hidden lg:block bg-gradient-to-br from-blue-50 to-purple-50 rounded-2xl p-6 border border-blue-100">
                                <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2">
                                    <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    Локация
                                </h3>
                                <div class="space-y-3">
                                    @if (!empty($station[0]))
                                        <div class="flex items-start gap-3">
                                            <div
                                                class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center flex-shrink-0">
                                                <svg class="w-5 h-5 text-blue-600" fill="currentColor"
                                                    viewBox="0 0 20 20">
                                                    <path
                                                        d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" />
                                                </svg>
                                            </div>
                                            <div>
                                                <div class="text-xs text-gray-600 mb-0.5">Метро/Станция</div>
                                                <div class="font-semibold text-gray-900">{{ $station[0] }}</div>
                                            </div>
                                        </div>
                                    @endif
                                    @if ($districtName)
                                        <div class="flex items-start gap-3">
                                            <div
                                                class="w-10 h-10 rounded-lg bg-purple-100 flex items-center justify-center flex-shrink-0">
                                                <svg class="w-5 h-5 text-purple-600" fill="currentColor"
                                                    viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd"
                                                        d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                            </div>
                                            <div>
                                                <div class="text-xs text-gray-600 mb-0.5">Район</div>
                                                <div class="font-semibold text-gray-900">
                                                    @if ($districtUrl)
                                                        <a href="{{ esc_url($districtUrl) }}" class="transition-colors hover:text-blue-700">
                                                            {{ $districtName }}
                                                        </a>
                                                    @else
                                                        {{ $districtName }}
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif

                        {{-- 3. БЛОК КОНТАКТОВ (MAX VERTICAL С ПРОВЕРКОЙ НА ПУСТОТУ) --}}
                        @php
                            $max_data = \App\ContactData::modelLinks();

                            // Проверяем, заполнено ли хотя бы одно поле
                            $has_any_contact = !empty($max_data['encoded_phone']) || 
                                            !empty($max_data['encoded_tg']) || 
                                            !empty($max_data['encoded_wa']) || 
                                            !empty($max_data['encoded_max']);
                        @endphp

                        @if($has_any_contact)
                            <section class="relative overflow-hidden bg-gradient-to-br from-blue-600 to-purple-600 rounded-2xl p-6 text-white shadow-xl my-8">
                                <div class="absolute inset-0 bg-black/10"></div>
                                <div class="relative">
                                    <div class="flex items-center gap-2 mb-3">
                                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z" />
                                        </svg>
                                        <h3 class="text-lg font-bold uppercase tracking-tight">Контакты</h3>
                                    </div>
                                    
                                    <p class="text-sm text-white/90 mb-6">
                                        Контактная информация предоставлена ниже
                                    </p>

                                    <div class="flex flex-col gap-3">

                                        {{-- Кнопка: Telegram --}}
                                        @if($max_data['encoded_tg'])
                                            <button type="button" 
                                                data-contact-link="{{ $max_data['encoded_tg'] }}"
                                                class="flex items-center justify-center gap-3 w-full py-4 px-6 bg-white/10 hover:bg-white/20 rounded-xl transition-all duration-200 border border-white/20 outline-none cursor-pointer">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 512 512"><path fill="#ffffff" d="M470.435 45.423L16.827 221.249c-18.254 8.188-24.428 24.585-4.412 33.484l116.37 37.173l281.368-174.79c15.363-10.973 31.091-8.047 17.557 4.024L186.053 341.075l-7.591 93.076c7.031 14.371 19.905 14.438 28.117 7.295l66.858-63.589l114.505 86.187c26.595 15.826 41.066 5.613 46.788-23.394l75.105-357.47c7.798-35.705-5.5-51.437-39.4-37.757z"></path></svg>
                                                Telegram
                                            </button>
                                        @endif

                                        {{-- Кнопка: WhatsApp --}}
                                        @if($max_data['encoded_wa'])
                                            <button type="button" 
                                                data-contact-link="{{ $max_data['encoded_wa'] }}"
                                                class="flex items-center justify-center gap-3 w-full py-4 px-6 bg-white/10 hover:bg-white/20 rounded-xl transition-all duration-200 border border-white/20 outline-none cursor-pointer">
                                                <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24">
                                                    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.008-.57-.008-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z" />
                                                </svg>
                                                WhatsApp
                                            </button>
                                        @endif

                                        {{-- Кнопка: Телефон --}}
                                        @if($max_data['encoded_phone'])
                                            <button type="button" 
                                                data-contact-link="{{ $max_data['encoded_phone'] }}"
                                                class="flex items-center justify-center gap-3 w-full py-4 px-6 bg-white/10 hover:bg-white/20 rounded-xl transition-all duration-200 border border-white/20 outline-none cursor-pointer">
                                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                                </svg>
                                                Позвонить
                                            </button>
                                        @endif             
                                                                  
                                        {{-- Кнопка: MAX (Акцентная) --}}
                                        @if($max_data['encoded_max'])
                                            <button type="button" 
                                                data-contact-link="{{ $max_data['encoded_max'] }}"
                                                class="flex items-center justify-center gap-3 w-full py-4 px-6 bg-white/10 hover:bg-white/20 rounded-xl transition-all duration-200 border border-white/20 outline-none cursor-pointer">
                                                <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 1000 1000">
                                                    <path d="M508.2 878.3c-75 0-109.8-11-170.4-54.7-38.3 49.3-159.7 87.8-165 21.9 0-49.5-11-91.2-23.4-136.9-14.8-56.2-31.6-118.8-31.6-209.5 0-216.6 177.8-379.6 388.4-379.6 210.8 0 376 171 376 381.6.7 207.3-166.6 376.1-374 377.2m3.1-571.6c-102.6-5.3-182.5 65.7-200.2 177-14.6 92.2 11.3 204.4 33.4 210.2 10.6 2.6 37.2-19 53.8-35.6a190 190 0 0 0 92.7 33c106.3 5.1 197.1-75.8 204.2-182 4.2-106.3-77.7-196.5-184-202.6Z"/>
                                                </svg>
                                                Написать в MAX
                                            </button>
                                        @endif
                                    </div>

                                    <div class="mt-6 pt-4 border-t border-white/20 flex items-center justify-center gap-2 text-xs text-white/80">
                                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                        </svg>
                                        Конфиденциально и безопасно
                                    </div>
                                </div>
                            </section>
                        @endif

                        {{-- Быстрые действия --}}
                        <div class="grid grid-cols-2 gap-3">
                            <button
                                class="flex items-center justify-center gap-2 p-3 bg-gray-50 hover:bg-gray-100 rounded-xl border border-gray-200 transition-colors">
                                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                                </svg>
                                <span class="text-sm font-medium text-gray-700">В избранное</span>
                            </button>
                            <button
                                class="flex items-center justify-center gap-2 p-3 bg-gray-50 hover:bg-gray-100 rounded-xl border border-gray-200 transition-colors">
                                <svg class="w-5 h-5 text-gray-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path
                                        d="M15 8a3 3 0 10-2.977-2.63l-4.94 2.47a3 3 0 100 4.319l4.94 2.47a3 3 0 10.895-1.789l-4.94-2.47a3.027 3.027 0 000-.74l4.94-2.47C13.456 7.68 14.19 8 15 8z" />
                                </svg>
                                <span class="text-sm font-medium text-gray-700">Поделиться</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- FAQ секция --}}
        @if(false)
        <div class="bg-gray-50 py-12 mt-12">
            <div class="container max-w-7xl mx-auto px-4">
                <div class="max-w-3xl mx-auto">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6 text-center">Часто задаваемые вопросы</h2>
                    <div class="space-y-4">
                        <details class="bg-white rounded-xl p-6 group">
                            <summary class="flex items-center justify-between cursor-pointer list-none">
                                <span class="font-medium text-gray-900">Как получить контакты?</span>
                                <svg class="w-5 h-5 text-gray-400 group-open:rotate-180 transition-transform"
                                    fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                        clip-rule="evenodd" />
                                </svg>
                            </summary>
                            <p class="mt-4 text-gray-600">Нажмите на кнопку "Получить контакты" и следуйте инструкциям
                                администрации сайта.</p>
                        </details>
                        <details class="bg-white rounded-xl p-6 group">
                            <summary class="flex items-center justify-between cursor-pointer list-none">
                                <span class="font-medium text-gray-900">Все фотографии реальные?</span>
                                <svg class="w-5 h-5 text-gray-400 group-open:rotate-180 transition-transform"
                                    fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                        clip-rule="evenodd" />
                                </svg>
                            </summary>
                            <p class="mt-4 text-gray-600">Мы тщательно проверяем все анкеты. Проверенные анкеты отмечены
                                специальным значком.</p>
                        </details>
                    </div>
                </div>
            </div>
        </div>
        @endif
        @if (!empty($relatedModels))
            <section class="mt-12">
                <div class="rounded-3xl border border-slate-200 bg-gradient-to-br from-slate-50 via-white to-blue-50 p-6 shadow-sm lg:p-8">
                    <div class="mb-6">
                        <h2 class="text-2xl font-bold text-gray-900">Похожие анкеты</h2>
                        @if ($districtName)
                            <p class="mt-2 text-sm text-gray-600">Случайные анкеты из того же района: {{ $districtName }}</p>
                        @else
                            <p class="mt-2 text-sm text-gray-600">Случайные анкеты, которые могут вам подойти</p>
                        @endif
                    </div>

                    @include('components.models-cards', ['items' => $relatedModels])
                </div>
            </section>
        @endif
    </article>

    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>

    {{-- Swiper CSS --}}
    <script src="https://cdn.jsdelivr.net/npm/swiper@11.2.10/swiper-bundle.min.js"
        integrity="sha256-mF8SJMDu7JnTZ6nbNeWORLIefrnORYMbFbTBCOQf2X8=" crossorigin="anonymous"></script>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11.2.10/swiper-bundle.min.css"
        integrity="sha256-dMpqrlRo28kkeQw7TSGaCJuQo0utU6D3yjpz5ztvWrg=" crossorigin="anonymous">

    <script src="https://cdn.jsdelivr.net/npm/lightbox2@2.11.5/dist/js/lightbox.min.js"
        integrity="sha256-A6jI5V9s1JznkWwsBaRK8kSeXLgIqQfxfnvdDOZEURY=" crossorigin="anonymous"></script>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/lightbox2@2.11.5/dist/css/lightbox.min.css"
        integrity="sha256-uypRbsAiJcFInM/ndyI/JHpzNe6DtUNXaWEUWEPfMGo=" crossorigin="anonymous">

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof Swiper === 'undefined') {
                console.error('Swiper не загружен');
                return;
            }

            // Инициализация галереи миниатюр
            const galleryThumbs = document.querySelector('.gallery-thumbs');
            let thumbsSwiper = null;

            if (galleryThumbs) {
                thumbsSwiper = new Swiper('.gallery-thumbs', {
                    spaceBetween: 8,
                    slidesPerView: 4,
                    freeMode: true,
                    watchSlidesProgress: true,
                    centeredSlides: false,
                    slideToClickedSlide: true,
                    breakpoints: {
                        640: {
                            slidesPerView: 5,
                            spaceBetween: 10
                        },
                        768: {
                            slidesPerView: 6,
                            spaceBetween: 12
                        },
                        1024: {
                            slidesPerView: 7,
                            spaceBetween: 12
                        }
                    },
                    // Центрирование активного слайда
                    on: {
                        click: function(swiper, event) {
                            const clickedIndex = swiper.clickedIndex;
                            if (clickedIndex !== undefined) {
                                // Прокручиваем к центру
                                swiper.slideTo(clickedIndex);
                            }
                        }
                    }
                });
            }

            // Инициализация основной галереи
            const galleryMain = document.querySelector('.gallery-main');
            if (galleryMain) {
                const mainSwiper = new Swiper('.gallery-main', {
                    spaceBetween: 10,
                    navigation: {
                        nextEl: '.swiper-button-next',
                        prevEl: '.swiper-button-prev',
                    },
                    pagination: {
                        el: '.swiper-pagination',
                        clickable: true,
                    },
                    thumbs: thumbsSwiper ? {
                        swiper: thumbsSwiper
                    } : null,
                    loop: false,
                    keyboard: {
                        enabled: true,
                        onlyInViewport: true,
                    },
                    mousewheel: {
                        forceToAxis: true,
                    },
                    autoplay: {
                        delay: 5000,
                        disableOnInteraction: false,
                        pauseOnMouseEnter: true,
                    },
                    effect: 'fade',
                    fadeEffect: {
                        crossFade: true
                    },
                    on: {
                        slideChange: function() {
                            // Обновление счетчика
                            const currentSlideEl = document.querySelector('.current-slide');
                            if (currentSlideEl) {
                                currentSlideEl.textContent = this.activeIndex + 1;
                            }

                            // Останавливаем все видео
                            document.querySelectorAll('.gallery-main video').forEach(video => {
                                video.pause();
                            });

                            // Воспроизводим видео на активном слайде
                            const activeSlide = this.slides[this.activeIndex];
                            if (activeSlide) {
                                const video = activeSlide.querySelector('video');
                                if (video) {
                                    video.play().catch(e => console.log(
                                        'Автовоспроизведение заблокировано:', e));
                                }
                            }

                            // Центрируем активную миниатюру
                            if (thumbsSwiper) {
                                const activeIndex = this.activeIndex;
                                const thumbsPerView = thumbsSwiper.params.slidesPerView;
                                const centerIndex = Math.max(0, activeIndex - Math.floor(thumbsPerView /
                                    2));
                                thumbsSwiper.slideTo(centerIndex);
                            }
                        }
                    }
                });

                // Добавляем поддержку свайпа для десктопа
                let startX = 0;
                let isDown = false;

                galleryMain.addEventListener('mousedown', (e) => {
                    isDown = true;
                    startX = e.pageX;
                    galleryMain.style.cursor = 'grabbing';
                });

                galleryMain.addEventListener('mousemove', (e) => {
                    if (!isDown) return;
                    e.preventDefault();
                });

                galleryMain.addEventListener('mouseup', (e) => {
                    isDown = false;
                    galleryMain.style.cursor = 'grab';

                    const endX = e.pageX;
                    const diff = startX - endX;

                    if (Math.abs(diff) > 50) {
                        if (diff > 0) {
                            mainSwiper.slideNext();
                        } else {
                            mainSwiper.slidePrev();
                        }
                    }
                });

                galleryMain.addEventListener('mouseleave', () => {
                    isDown = false;
                    galleryMain.style.cursor = 'grab';
                });
            }

            // Lightbox настройки
            if (typeof lightbox !== 'undefined') {
                lightbox.option({
                    'resizeDuration': 200,
                    'wrapAround': true,
                    'albumLabel': 'Изображение %1 из %2',
                    'fadeDuration': 300,
                    'imageFadeDuration': 300,
                    'positionFromTop': 50,
                    'disableScrolling': true,
                    'maxWidth': window.innerWidth > 768 ? 1200 : window.innerWidth - 40,
                    'maxHeight': window.innerHeight > 768 ? 800 : window.innerHeight - 120
                });

                // Дополнительная обработка для мобильных устройств
                $(document).on('click', '[data-lightbox]', function(e) {
                    if (window.innerWidth <= 768) {
                        // Настройки специально для мобильных
                        lightbox.option({
                            'maxWidth': window.innerWidth - 40,
                            'maxHeight': window.innerHeight - 120,
                            'positionFromTop': 30
                        });
                    }
                });
            }

            // Обновление размеров при изменении ориентации
            window.addEventListener('orientationchange', function() {
                setTimeout(function() {
                    if (typeof lightbox !== 'undefined') {
                        lightbox.option({
                            'maxWidth': window.innerWidth > 768 ? 1200 : window.innerWidth -
                                40,
                            'maxHeight': window.innerHeight > 768 ? 800 : window
                                .innerHeight - 120
                        });
                    }
                }, 100);
            });
        });
    </script>
@endsection
