@php
    // Контекст
    $qo = get_queried_object();
    $isTax = $qo instanceof \WP_Term;
    $currentTerm = $isTax ? $qo : null;

    // Заголовок по умолчанию для не-таксономий
    $defaultTitle =
        isset($title) && $title !== ''
            ? (string) $title
            : ((is_archive() || is_home()) && function_exists('get_the_archive_title')
                ? get_the_archive_title()
                : get_the_title());

    // Базовые значения
    $termName = $isTax ? (string) $currentTerm->name : (string) $defaultTitle;

    // Метаполя (для обратной совместимости ищем и _term_h1, и term_h1)
    $metaH1 = $isTax
        ? (string) (get_term_meta($currentTerm->term_id, '_term_h1', true) ?:
        get_term_meta($currentTerm->term_id, 'term_h1', true))
        : '';
    $metaH2 = $isTax
        ? (string) (get_term_meta($currentTerm->term_id, '_term_h2', true) ?:
        get_term_meta($currentTerm->term_id, 'term_h2', true))
        : '';

    // Подготовка входных параметров
    $custom_h1 = isset($custom_h1) ? trim((string) $custom_h1) : null;
    $custom_h2 = isset($custom_h2) ? trim((string) $custom_h2) : null;

    // Выбираем первую НЕПУСТУЮ строку
    $pick = function (...$vals) {
        foreach ($vals as $v) {
            $v = is_string($v) ? trim($v) : $v;
            if (is_string($v) && $v !== '') {
                return $v;
            }
        }
        return '';
    };

    $h1Text = $pick($custom_h1, $metaH1, $termName);
    $h2Text = $pick($custom_h2, $metaH2);

    // Лейбл таксономии
    $taxLabel = null;
    if ($isTax) {
        $tax = get_taxonomy($currentTerm->taxonomy);
        $taxLabel = $tax?->labels?->singular_name ?: $tax?->label ?: ucfirst($currentTerm->taxonomy);
    }

    // Флаг показа заголовка
    $renderHeader = !isset($show_h1) || $show_h1 !== false;
@endphp

@if ($renderHeader)
    <header class="page-header mb-6">
        <h1 class="entry-title text-3xl font-semibold">{{ $taxLabel . ' ' . e($h1Text) }}</h1>

        @if ($h2Text !== '')
            <p class="mt-2 text-base text-slate-600">{{ e($h2Text) }}</p>
        @endif

        @if ($isTax)
            <p class="mt-2 text-base text-slate-600">
                {{ e($taxLabel) }} · найдено: {{ number_format_i18n((int) $currentTerm->count) }}
            </p>
        @endif
    </header>
@endif
