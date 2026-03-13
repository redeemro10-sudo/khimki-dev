// resources/js/modules/models-grid.js

function qs(el, sel) { return el.querySelector(sel); }

async function fetchModels(endpoint, params) {
  const url = new URL(endpoint, window.location.origin);
  Object.entries(params).forEach(([key, value]) => {
    if (value === null || value === undefined || value === '') return;
    if (Array.isArray(value)) {
      value.forEach(val => {
        if (val !== null && val !== undefined && val !== '') {
          url.searchParams.append(key + '[]', val);
        }
      });
    } else {
      url.searchParams.set(key, String(value));
    }
  });
  const res = await fetch(url.toString(), {
    credentials: 'same-origin',
    headers: { 'Accept': 'application/json' }
  });
  if (!res.ok) throw new Error('HTTP ' + res.status);
  return await res.json();
}

function parseConfig(el) {
  try {
    const cfg = JSON.parse(el.dataset.config || '{}');
    return { per_page: 12, page: 1, order: 'date', tax: {}, meta: {}, ...cfg };
  } catch {
    return { per_page: 12, page: 1, order: 'date', tax: {}, meta: {} };
  }
}

function formToParams(form) {
  const fd = new FormData(form);
  const params = {};
  for (const [key, value] of fd.entries()) {
    if (key.endsWith('[]')) {
      const cleanKey = key.slice(0, -2);
      if (!params[cleanKey]) params[cleanKey] = [];
      params[cleanKey].push(value);
    } else {
      params[key] = value;
    }
  }
  return params;
}

function clampNumber(value, min, max) {
  if (!Number.isFinite(value)) return null;
  return Math.min(Math.max(value, min), max);
}

function normalizeNumberInput(input) {
  if (!input) return null;

  const raw = String(input.value || '').trim();
  if (raw === '') return null;

  const min = Number(input.min || Number.NEGATIVE_INFINITY);
  const max = Number(input.max || Number.POSITIVE_INFINITY);
  const step = Number(input.step || 1);
  const parsed = Number(raw);

  if (!Number.isFinite(parsed)) {
    input.value = '';
    return null;
  }

  let normalized = clampNumber(parsed, min, max);
  if (Number.isFinite(step) && step > 0) {
    normalized = Math.round(normalized / step) * step;
    normalized = clampNumber(normalized, min, max);
  }

  input.value = String(normalized);
  return normalized;
}

function normalizeRangePair(minInput, maxInput) {
  if (!minInput || !maxInput) return;

  const minValue = normalizeNumberInput(minInput);
  const maxValue = normalizeNumberInput(maxInput);

  if (minValue === null || maxValue === null) return;

  if (minValue > maxValue) {
    minInput.value = String(maxValue);
    maxInput.value = String(minValue);
  }
}

function normalizeFilterNumericFields(form) {
  if (!form) return;

  form.querySelectorAll('input[type="number"]').forEach((input) => {
    normalizeNumberInput(input);
  });

  [
    ['price_min', 'price_max'],
    ['age_min', 'age_max'],
    ['height_min', 'height_max'],
    ['weight_min', 'weight_max'],
  ].forEach(([minName, maxName]) => {
    normalizeRangePair(
      form.querySelector(`[name="${minName}"]`),
      form.querySelector(`[name="${maxName}"]`),
    );
  });
}

function applyOrderParams(uiValue, params) {
  switch (uiValue) {
    case 'date':
      params.order = 'date';
      break;
    case 'price_asc':
      params.order = 'meta_value_num';
      params.meta_key = '_price';
      params.order_dir = 'asc';
      break;
    case 'price_desc':
      params.order = 'meta_value_num';
      params.meta_key = '_price';
      params.order_dir = 'desc';
      break;
    case 'popular':
      params.order = 'rand'; // или ваш meta_key просмотров, если появится
      break;
    default:
      params.order = 'date';
  }
}

function renderBadges(tags) {
  if (!tags) return '';
  const b = [];
  if (tags.video) b.push('<span class="absolute top-2 left-2 px-2 py-1 rounded-full text-[10px] font-bold bg-red-500 text-white">ВИДЕО</span>');
  if (tags.verified) b.push('<span class="absolute top-2 left-14 px-2 py-1 rounded-full text-[10px] font-bold bg-green-500 text-white">✓</span>');
  if (tags.online) b.push('<span class="absolute top-2 right-2 px-2 py-1 rounded-full text-[10px] font-bold bg-blue-500 text-white">ONLINE</span>');
  if (tags.vip) b.push('<span class="absolute top-2 right-16 px-2 py-1 rounded-full text-[10px] font-bold bg-yellow-500 text-white">VIP</span>');
  return b.join('');
}

function renderCardBadges(tags) {
  if (!tags) return '';

  const left = [];
  const right = [];

  if (tags.video) left.push('<span class="rounded-full bg-red-500 px-2 py-1 text-[10px] font-bold text-white">ВИДЕО</span>');
  if (tags.verified) left.push('<span class="rounded-full bg-green-500 px-2 py-1 text-[10px] font-bold text-white">✓</span>');
  if (tags.vip) right.push('<span class="rounded-full bg-yellow-500 px-2 py-1 text-[10px] font-bold text-white">VIP</span>');
  if (tags.online) right.push('<span class="rounded-full bg-blue-500 px-2 py-1 text-[10px] font-bold text-white">ONLINE</span>');

  return [
    left.length ? `<div class="absolute left-2 top-2 z-10 flex max-w-[calc(100%-1rem)] flex-wrap gap-2">${left.join('')}</div>` : '',
    right.length ? `<div class="absolute right-2 top-2 z-10 flex max-w-[calc(100%-1rem)] flex-wrap justify-end gap-2">${right.join('')}</div>` : '',
  ].join('');
}

function buildModelImageMeta(item) {
  const altParts = [
    item.title ? `Имя: ${item.title}` : '',
    item.age ? `Возраст: ${item.age}` : '',
    item.bust ? `Грудь: ${item.bust}` : '',
  ].filter(Boolean);

  return {
    alt: altParts.join(', '),
    title: item.title ? `Эскортница ${item.title}${item.age ? `, ${item.age}` : ''}` : '',
  };
}

function renderCards(root, items) {
  let list = root.querySelector('ul.models-grid__list');
  if (!list) {
    list = document.createElement('ul');
    list.className = 'models-grid__list grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4';
    root.appendChild(list);
  }
  for (const [index, it] of (items || []).entries()) {
    const li = document.createElement('li');
    const badges = renderCardBadges(it.tags);
    const imageMeta = buildModelImageMeta(it);
    const isPriorityImage = list.children.length === 0 && index === 0;
    li.innerHTML = `
      <article class="card border rounded-2xl overflow-hidden bg-white shadow-sm hover:shadow-md transition-shadow">
        <a href="${it.link}" class="block" rel="bookmark">
          <div class="card-image relative h-72 overflow-hidden bg-gray-100">
            ${it.thumb ? `<img src="${it.thumb}" alt="${imageMeta.alt}"${imageMeta.title ? ` title="${imageMeta.title}"` : ''} loading="${isPriorityImage ? 'eager' : 'lazy'}" fetchpriority="${isPriorityImage ? 'high' : 'low'}" decoding="${isPriorityImage ? 'auto' : 'async'}" class="w-full h-full object-cover aspect-square">` : ''}
            ${badges}
            <div class="absolute inset-x-0 bottom-0 h-20 bg-gradient-to-t from-black/60 to-transparent"></div>
          </div>
          <div class="card-content p-4">
            <h3 class="font-medium text-gray-900 mb-2 truncate">${it.title || 'Model'}</h3>
            <div class="flex items-center gap-2 mb-3 flex-wrap">
              ${it.price ? `<span class="text-sm font-semibold text-gray-700">от ${it.price} ₽</span>` : ''}
            </div>
            <div class="flex items-center gap-2 text-gray-600 text-xs">
              <svg class="w-4 h-4 flex-shrink-0" viewBox="0 0 24 24" fill="currentColor">
                <path d="M12 2a7 7 0 0 0-7 7c0 5.25 7 13 7 13s7-7.75 7-13a7 7 0 0 0-7-7zm0 9.5A2.5 2.5 0 1 1 12 6a2.5 2.5 0 0 1 0 5.5z"/>
              </svg>
              <div class="truncate">
                ${it.station ? `<div class="font-medium">${it.station}</div>` : ''}
                ${it.district ? `<div class="opacity-80">${it.district}</div>` : ''}
              </div>
            </div>
          </div>
        </a>
      </article>`;
    list.appendChild(li);
  }
}

/** Тумблер боковой панели фильтров для конкретного gridId */
function initFiltersToggle(gridId) {
  const toggleBtn = document.getElementById(`filters-toggle-${gridId}`);
  const closeBtn = document.getElementById(`filters-close-${gridId}`);
  const sidebar = document.getElementById(`filters-sidebar-${gridId}`);
  const overlay = document.getElementById(`filters-overlay-${gridId}`);
  if (!toggleBtn || !sidebar || !overlay) {
    // для отладки:
    console.warn('[models-grid] toggle init skipped:', { gridId, toggleBtn: !!toggleBtn, sidebar: !!sidebar, overlay: !!overlay });
    return;
  }

  const open = () => {
    sidebar.classList.remove('-translate-x-full');
    overlay.classList.remove('opacity-0', 'pointer-events-none');
    overlay.classList.add('opacity-100');
    document.body.classList.add('overflow-hidden');
  };
  const close = () => {
    sidebar.classList.add('-translate-x-full');
    overlay.classList.add('opacity-0', 'pointer-events-none');
    overlay.classList.remove('opacity-100');
    document.body.classList.remove('overflow-hidden');
  };

  toggleBtn.addEventListener('click', open);
  closeBtn?.addEventListener('click', close);
  overlay.addEventListener('click', close);
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && !sidebar.classList.contains('-translate-x-full')) close();
  });
}

export function initModelsGrids() {
  document.querySelectorAll('.js-models-grid').forEach((wrap) => {
    const endpoint = wrap.dataset.endpoint || '/wp-json/site/v1/models';
    const cfg = parseConfig(wrap);
    const form = qs(wrap, '.models-grid__form');
    const root = qs(wrap, '.models-grid__root');
    const empty = qs(wrap, '.models-grid__empty');
    const moreBtn = wrap.querySelector('[data-more]');
    const moreWrap = wrap.querySelector('[data-more-wrap]');
    const countEl = document.getElementById(`models-count-${wrap.id}`);
    const sortSelect = wrap.querySelector('select[name="sort"]');
    const hasSSR = wrap.dataset.ssr === '1';

    form?.querySelectorAll('input[type="number"]').forEach((input) => {
      input.addEventListener('change', () => normalizeFilterNumericFields(form));
      input.addEventListener('blur', () => normalizeFilterNumericFields(form));
    });

    // 1) Выставляем селект из текущего cfg.order (если есть такой option)
    if (sortSelect) {
      const opt = [...sortSelect.options].find(o => o.value === (cfg.order || 'date'));
      if (opt) sortSelect.value = opt.value;
    }

    // 2) Живой обработчик изменения селекта
    sortSelect?.addEventListener('change', () => {
      cfg.order = sortSelect.value;   // 'date' | 'price_asc' | 'price_desc' | 'popular'
      cfg.page = 1;
      load(true);
    });

    // 3) Обратная совместимость с твоим кастомным событием
    document.addEventListener('sort-change', (e) => {
      const detail = e.detail || {};
      if (detail.gridId && detail.gridId !== wrap.id) return; // если событие для другого грида — игнор
      const val = detail.value;
      if (!val) return;
      cfg.order = val;
      cfg.page = 1;
      load(true);
    });

    // 1) Инициализируем панель фильтров ДЛЯ ЭТОГО ГРИДА
    initFiltersToggle(wrap.id);

    // 2) Подгружаем модуль многоуровневых фильтров при наличии контейнера
    const multi = wrap.querySelector('.filters-container');
    if (multi) {
      import('./filters-multilevel.js')
        .then(({ initFiltersMultilevel }) => initFiltersMultilevel(multi, wrap.id))
        .catch((e) => console.error('filters-multilevel chunk failed:', e));
    }

    let busy = false;
    let pages = 1;
    let pagesFromSSR = Number(wrap.dataset.pages || 0);  // общее кол-во страниц
    let currentPageSSR = Number(wrap.dataset.page || 1);  // текущая страница (SSR = 1)

    if (hasSSR) {
      pages = pagesFromSSR || 1;
      cfg.page = currentPageSSR + 1; // следующая страница к загрузке

      const noMore = cfg.page > pages;
      if (moreBtn) {
        moreBtn.disabled = noMore;
        moreBtn.classList.toggle('hidden', noMore);
      }
      moreWrap?.classList.toggle('hidden', noMore);
    } else {
      load(true);
    }

    async function load(reset = false) {
      if (busy) return; busy = true;
      if (reset) { cfg.page = 1; root.innerHTML = ''; }

      normalizeFilterNumericFields(form);

      const params = { page: cfg.page, per_page: cfg.per_page };
      applyOrderParams(cfg.order || 'date', params);

      if (form) {
        const formData = formToParams(form);
        if (formData.q) params.q = formData.q;

        if (formData.price_min !== undefined && formData.price_min !== '') params.price_min = formData.price_min;
        if (formData.price_max !== undefined && formData.price_max !== '') params.price_max = formData.price_max;

        if (formData.age_min !== undefined && formData.age_min !== '') params.age_min = formData.age_min;
        if (formData.age_max !== undefined && formData.age_max !== '') params.age_max = formData.age_max;

        if (formData.height_min !== undefined && formData.height_min !== '') params.height_min = formData.height_min;
        if (formData.height_max !== undefined && formData.height_max !== '') params.height_max = formData.height_max;

        if (formData.weight_min !== undefined && formData.weight_min !== '') params.weight_min = formData.weight_min;
        if (formData.weight_max !== undefined && formData.weight_max !== '') params.weight_max = formData.weight_max;

        if (formData.has_video) params.has_video = 1;

        const taxonomies = ['service', 'district', 'feature', 'rail_station', 'hair_color', 'aye_color', 'nationality', 'bust_size', 'massage', 'physique', 'intimate_haircut', 'striptease_services', 'extreme_services', 'sado_maso'];
        taxonomies.forEach(tax => {
          const fromForm = Array.isArray(formData[tax]) ? formData[tax] : [];
          const fromCfg = Array.isArray(cfg.tax?.[tax]) ? cfg.tax[tax] : [];
          const merged = [...new Set([...fromCfg, ...fromForm])];
          if (merged.length) params[tax] = merged;
        });
      } else if (cfg.tax) {
        Object.entries(cfg.tax).forEach(([k, v]) => { if (v && v.length) params[k] = v; });
      }

      try {
        const data = await fetchModels(endpoint, params);
        pages = data.pages || data.max_pages || Math.ceil((data.total || 0) / (params.per_page || 12)) || 1;

        if (reset && (!data.items || data.items.length === 0)) {
          empty?.classList.remove('hidden');
          if (countEl) countEl.textContent = '0';
          if (moreBtn) { moreBtn.disabled = true; moreBtn.classList.add('hidden'); }
          moreWrap?.classList.add('hidden');
          busy = false;
          return;
        } else {
          empty?.classList.add('hidden');
        }

        renderCards(root, data.items);

        if (countEl) {
          countEl.textContent = String(
            data.total ?? data.found ?? root.querySelectorAll('ul.models-grid__list > li').length
          );
        }
        cfg.page += 1;

        const noMore = cfg.page > pages;
        if (moreBtn) {
          moreBtn.disabled = noMore;
          moreBtn.classList.toggle('hidden', noMore);
        }
        moreWrap?.classList.toggle('hidden', noMore);
      } finally {
        busy = false;
      }
    }

    // сортировка
    sortSelect?.addEventListener('change', () => {
      cfg.order = sortSelect.value;
      load(true);
    });

    // submit формы
    form?.addEventListener('submit', (e) => { e.preventDefault(); load(true); });

    // «Показать ещё»
    moreBtn?.addEventListener('click', () => load(false));

    // reset формы
    form?.addEventListener('reset', () => setTimeout(() => load(true), 10));

    // синхронизация цены
    const minRange = form?.querySelector('[data-min-range]');
    const maxRange = form?.querySelector('[data-max-range]');
    const minInput = form?.querySelector('[data-min-input]');
    const maxInput = form?.querySelector('[data-max-input]');
    const clearBtn = form?.querySelector('[data-clear-price]');

    function syncFromRanges() {
      if (!minRange || !maxRange || !minInput || !maxInput) return;
      const minLimit = Number(minRange.min || 8000);
      const maxLimit = Number(maxRange.max || 50000);
      let min = Number(minRange.value || minLimit);
      let max = Number(maxRange.value || maxLimit);
      if (min > max) [min, max] = [max, min];
      minInput.value = min; maxInput.value = max;
    }
    function syncFromInputs() {
      if (!minRange || !maxRange || !minInput || !maxInput) return;
      const minLimit = Number(minInput.min || minRange.min || 8000);
      const maxLimit = Number(maxInput.max || maxRange.max || 50000);
      let min = Number(minInput.value || minLimit);
      let max = Number(maxInput.value || maxLimit);
      if (min > max) [min, max] = [max, min];
      minRange.value = min; maxRange.value = max;
    }

    minRange?.addEventListener('input', syncFromRanges);
    maxRange?.addEventListener('input', syncFromRanges);
    minInput?.addEventListener('input', syncFromInputs);
    maxInput?.addEventListener('input', syncFromInputs);
    clearBtn?.addEventListener('click', () => {
      if (!minRange || !maxRange || !minInput || !maxInput) return;
      const minLimit = Number(minRange.min || 8000);
      const maxLimit = Number(maxRange.max || 50000);
      minRange.value = minLimit; maxRange.value = maxLimit;
      minInput.value = minLimit; maxInput.value = maxLimit;
    });
  });
}
