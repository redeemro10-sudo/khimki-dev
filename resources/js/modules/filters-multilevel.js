// ПОЛНЫЙ JAVASCRIPT ДЛЯ ФИЛЬТРОВ

// Инициализация многоуровневых фильтров
export async function initFiltersMultilevel(container, gridId) {
  if (!container) {
    console.warn('Filter container not found');
    return;
  }

  console.log('Initializing filters for grid:', gridId);

  // Найти все необходимые элементы
  const backBtn = container.querySelector('.back-btn');
  const closeBtn = container.querySelector('.close-filters');
  const titleEl = container.querySelector('.filter-header h3');
  const levels = container.querySelectorAll('.filter-level');
  const navItems = container.querySelectorAll('.filter-nav-item');
  const applyBtn = container.querySelector('.apply-btn');
  const resetBtn = container.querySelector('.reset-btn');
  const form = container.closest('form');
  const sidebar = container.closest('aside');
  const overlay = document.getElementById(`filters-overlay-${gridId}`);

  // Проверить что все элементы найдены
  console.log('Elements found:', {
    backBtn: !!backBtn,
    closeBtn: !!closeBtn,
    titleEl: !!titleEl,
    levels: levels.length,
    navItems: navItems.length,
    form: !!form,
    sidebar: !!sidebar,
    overlay: !!overlay
  });


  // Состояние фильтров
  let currentLevel = 'main';
  const history = [];

  // Функция показа уровня
  function showLevel(name) {
    console.log('Showing level:', name);

    // Скрыть все уровни
    levels.forEach(level => {
      level.classList.remove('active');
    });

    // Показать нужный уровень
    const targetLevel = container.querySelector(`[data-level="${name}"]`);
    if (targetLevel) {
      // Для основного уровня - показываем сразу
      if (name === 'main') {
        targetLevel.classList.add('active');
      } else {
        // Для подуровней - с анимацией
        requestAnimationFrame(() => {
          targetLevel.classList.add('active');
        });
      }
    } else {
      console.warn('Level not found:', name);
    }
  }

  // Применить пресеты (заблокированные фильтры)
  function applyPresetLocks() {
    if (!form) return;

    const presets = Array.from(form.querySelectorAll('[data-preset-lock]'));
    if (!presets.length) return;

    console.log('Applying presets:', presets.length);

    // Собрать карту пресетов
    const presetsMap = new Map();
    presets.forEach(input => {
      const name = input.getAttribute('name');
      const value = String(input.value);
      const taxonomy = name.endsWith('[]') ? name.slice(0, -2) : name;

      if (!presetsMap.has(taxonomy)) {
        presetsMap.set(taxonomy, new Set());
      }
      presetsMap.get(taxonomy).add(value);
    });

    // Отметить соответствующие чекбоксы
    presetsMap.forEach((values, taxonomy) => {
      values.forEach(value => {
        const selector = `input[name="${taxonomy}[]"][value="${value}"]`;
        container.querySelectorAll(selector).forEach(checkbox => {
          checkbox.checked = true;
          checkbox.disabled = true;
          checkbox.setAttribute('data-locked', '1');

          const label = checkbox.closest('label');
          if (label) {
            label.classList.add('opacity-70');
            label.style.pointerEvents = 'none';
          }
        });
      });
    });

    updateSelectedCounts();
  }

  // Обновить счетчики
  function updateSelectedCounts() {
    // Услуги
    const servicesCount = container.querySelectorAll('input[name="service[]"]:checked').length;
    const servicesCounter = container.querySelector('[data-target="services"] .selected-count');
    if (servicesCounter) {
      servicesCounter.textContent = servicesCount ? `Выбрано: ${servicesCount}` : 'Любые';
    }

    // Локация
    const locationCount = container.querySelectorAll('.location-checkbox:checked').length;
    const locationCounter = container.querySelector('[data-target="location"] .selected-count');
    if (locationCounter) {
      locationCounter.textContent = locationCount ? `Выбрано: ${locationCount}` : 'Любая';
    }

    // Внешность
    const appearanceCount = container.querySelectorAll('.appearance-checkbox:checked').length;
    const appearanceCounter = container.querySelector('[data-target="appearance"] .selected-count');
    if (appearanceCounter) {
      appearanceCounter.textContent = appearanceCount ? `Выбрано: ${appearanceCount}` : 'Любая';
    }
  }

  // Закрыть мобильные фильтры
  function closeMobileFilters() {
    if (window.innerWidth >= 1024) return;

    console.log('Closing mobile filters');

    // Скрыть сайдбар
    if (sidebar) {
      sidebar.classList.add('-translate-x-full');
      sidebar.classList.remove('translate-x-0');
    }

    // Убрать блокировку скролла
    document.body.classList.remove('overflow-hidden');
    document.body.style.overflow = '';

    // Скрыть оверлей
    if (overlay) {
      overlay.classList.add('opacity-0', 'pointer-events-none');
      overlay.classList.remove('opacity-100', 'pointer-events-auto');
    }

    // Вернуться на главный уровень
    resetToMainLevel();
  }

  // Открыть мобильные фильтры
  function openMobileFilters() {
    if (window.innerWidth >= 1024) return;

    console.log('Opening mobile filters');

    // Показать сайдбар
    if (sidebar) {
      sidebar.classList.remove('-translate-x-full');
      sidebar.classList.add('translate-x-0');
    }

    // Заблокировать скролл
    document.body.classList.add('overflow-hidden');

    // Показать оверлей
    if (overlay) {
      overlay.classList.remove('opacity-0', 'pointer-events-none');
      overlay.classList.add('opacity-100', 'pointer-events-auto');
    }
  }

  // Вернуться на главный уровень
  function resetToMainLevel() {
    currentLevel = 'main';
    history.length = 0;
    showLevel('main');

    if (backBtn) backBtn.classList.add('hidden');
    if (titleEl) titleEl.textContent = 'Фильтры';
  }

  // Обработчики навигации между уровнями
  navItems.forEach(item => {
    item.addEventListener('click', (e) => {
      e.preventDefault();
      e.stopPropagation();

      const target = item.dataset.target;
      if (!target) return;

      console.log('Navigating to level:', target);

      // Сохранить текущий уровень в историю
      history.push(currentLevel);
      currentLevel = target;

      // Показать новый уровень
      showLevel(target);

      // Обновить заголовок
      const titles = {
        services: 'Услуги',
        location: 'Локация',
        appearance: 'Внешность'
      };

      if (titleEl) {
        titleEl.textContent = titles[target] || 'Фильтры';
      }

      // Показать кнопку "Назад"
      if (backBtn) {
        backBtn.classList.remove('hidden');
      }
    });
  });

  // Кнопка "Назад"
  if (backBtn) {
    backBtn.addEventListener('click', (e) => {
      e.preventDefault();
      e.stopPropagation();

      console.log('Going back from level:', currentLevel);

      const previousLevel = history.pop() || 'main';
      currentLevel = previousLevel;
      showLevel(previousLevel);

      // Обновить UI
      if (titleEl) {
        titleEl.textContent = 'Фильтры';
      }

      if (previousLevel === 'main') {
        backBtn.classList.add('hidden');
      }

      updateSelectedCounts();
    });
  }

  // Кнопка закрытия
  if (closeBtn) {
    closeBtn.addEventListener('click', (e) => {
      e.preventDefault();
      e.stopPropagation();
      closeMobileFilters();
    });
  }

  // Закрытие по клику на оверлей
  if (overlay) {
    overlay.addEventListener('click', (e) => {
      if (e.target === overlay) {
        closeMobileFilters();
      }
    });
  }

  // Обработчики "Выбрать все"
  const selectAllBoxes = container.querySelectorAll('.select-all');
  selectAllBoxes.forEach(selectAllBox => {
    selectAllBox.addEventListener('change', function () {
      const level = this.closest('.filter-level');
      if (!level) return;

      const checkboxes = level.querySelectorAll(
        'input[type="checkbox"]:not(.select-all):not([data-locked="1"]):not(:disabled)'
      );

      checkboxes.forEach(checkbox => {
        checkbox.checked = this.checked;
      });

      updateSelectedCounts();
    });
  });

  // Отслеживание изменений в чекбоксах
  container.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
    checkbox.addEventListener('change', () => {
      updateSelectedCounts();

      // Анимация для счетчика
      const level = checkbox.closest('.filter-level');
      if (level) {
        const counters = document.querySelectorAll('.selected-count');
        counters.forEach(counter => {
          counter.classList.add('updated');
          setTimeout(() => counter.classList.remove('updated'), 300);
        });
      }
    });
  });

  // Кнопка "Применить"
  if (applyBtn) {
    applyBtn.addEventListener('click', (e) => {
      e.preventDefault();

      console.log('Applying filters');

      // Вернуться на главный уровень
      resetToMainLevel();

      // Закрыть мобильные фильтры
      closeMobileFilters();

      // Отправить форму
      if (form) {
        form.dispatchEvent(new Event('submit', { bubbles: true, cancelable: true }));
      }
    });
  }

  // Кнопка "Сбросить"
  if (resetBtn) {
    resetBtn.addEventListener('click', (e) => {
      e.preventDefault();

      console.log('Resetting filters');

      // Сбросить форму
      if (form) {
        form.reset();
      }

      // Восстановить пресеты после сброса
      setTimeout(() => {
        applyPresetLocks();
        updateSelectedCounts();
      }, 50);
    });
  }

  // Предотвратить закрытие при клике внутри сайдбара
  if (sidebar) {
    sidebar.addEventListener('click', (e) => {
      e.stopPropagation();
    });
  }

  // Закрытие по Escape
  const handleEscape = (e) => {
    if (e.key !== 'Escape') return;

    const isFiltersOpen = sidebar?.classList.contains('translate-x-0');
    if (!isFiltersOpen) return;

    if (currentLevel !== 'main') {
      // Если не на главном уровне - вернуться назад
      if (backBtn) backBtn.click();
    } else {
      // Если на главном уровне - закрыть модалку
      closeMobileFilters();
    }
  };

  document.addEventListener('keydown', handleEscape);

  // Инициализация
  showLevel('main');
  applyPresetLocks();
  updateSelectedCounts();

  // Скрыть кнопку "Назад" по умолчанию
  if (backBtn) {
    backBtn.classList.add('hidden');
  }

  console.log('Filters multilevel initialized successfully');

  // Возврат функции очистки для удаления обработчиков
  return function cleanup() {
    document.removeEventListener('keydown', handleEscape);
  };
}

// Обработчик кнопки открытия фильтров
function initFiltersToggle() {
  // Делегирование событий для кнопок открытия
  document.addEventListener('click', (e) => {
    const toggleBtn = e.target.closest('[id^="filters-toggle-"]');
    if (!toggleBtn) return;

    e.preventDefault();
    e.stopPropagation();

    console.log('Filter toggle clicked:', toggleBtn.id);

    // Получить ID грида из ID кнопки
    const gridId = toggleBtn.id.replace('filters-toggle-', '');
    const sidebar = document.getElementById(`filters-sidebar-${gridId}`);
    const overlay = document.getElementById(`filters-overlay-${gridId}`);

    if (!sidebar) {
      console.warn('Sidebar not found for grid:', gridId);
      return;
    }

    // Показать сайдбар
    sidebar.classList.remove('-translate-x-full');
    sidebar.classList.add('translate-x-0');

    // Показать оверлей
    if (overlay) {
      overlay.classList.remove('opacity-0', 'pointer-events-none');
      overlay.classList.add('opacity-100', 'pointer-events-auto');
    }

    // Заблокировать скролл body
    document.body.classList.add('overflow-hidden');

    console.log('Filters opened');
  });
}

// Инициализация всех фильтров на странице
function initAllFilters() {
  const grids = document.querySelectorAll('.js-models-grid');

  console.log('Found grids:', grids.length);

  const cleanupFunctions = [];

  grids.forEach(grid => {
    const gridId = grid.id || 'grid-' + Date.now();
    const filtersContainer = grid.querySelector('.filters-container');

    if (filtersContainer) {
      const cleanup = initFiltersMultilevel(filtersContainer, gridId);
      if (cleanup) {
        cleanupFunctions.push(cleanup);
      }
    } else {
      console.warn('Filters container not found in grid:', gridId);
    }
  });

  // Возврат функции очистки всех фильтров
  return function cleanupAll() {
    cleanupFunctions.forEach(cleanup => cleanup());
  };
}

// Основная функция инициализации системы фильтров
export function initFiltersSystem() {
  console.log('Initializing filters system...');

  try {
    // Инициализировать все фильтры
    const cleanupFilters = initAllFilters();

    // Инициализировать кнопки открытия
    initFiltersToggle();

    // Обработчик изменения размера окна
    let resizeTimeout;
    const handleResize = () => {
      clearTimeout(resizeTimeout);
      resizeTimeout = setTimeout(() => {
        // Закрыть мобильные фильтры при переходе на десктоп
        if (window.innerWidth >= 1024) {
          const openSidebars = document.querySelectorAll('[id^="filters-sidebar-"].translate-x-0');
          openSidebars.forEach(sidebar => {
            sidebar.classList.add('-translate-x-full');
            sidebar.classList.remove('translate-x-0');
            document.body.classList.remove('overflow-hidden');

            const overlays = document.querySelectorAll('[id^="filters-overlay-"].opacity-100');
            overlays.forEach(overlay => {
              overlay.classList.add('opacity-0', 'pointer-events-none');
              overlay.classList.remove('opacity-100', 'pointer-events-auto');
            });
          });
        }
      }, 100);
    };

    window.addEventListener('resize', handleResize);

    console.log('Filters system initialized successfully');

    // Возврат функции полной очистки
    return function cleanup() {
      cleanupFilters();
      window.removeEventListener('resize', handleResize);
    };

  } catch (error) {
    console.error('Error initializing filters system:', error);
  }
}

// Автоматическая инициализация при загрузке DOM
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', () => {
    const hasGrid = document.querySelector('.js-models-grid');
    if (hasGrid) {
      initFiltersSystem();
    }
  });
} else {
  // DOM уже загружен
  const hasGrid = document.querySelector('.js-models-grid');
  if (hasGrid) {
    initFiltersSystem();
  }
}

// Экспорт для использования в других модулях
export default {
  initFiltersSystem,
  initFiltersMultilevel
};

// Глобальные функции для отладки (только в development)
if (typeof window !== 'undefined' && window.location.hostname === 'localhost') {
  window.debugFilters = () => {
    console.log('=== DEBUG FILTERS ===');

    const grids = document.querySelectorAll('.js-models-grid');
    console.log('Grids found:', grids.length);

    grids.forEach((grid, index) => {
      console.log(`Grid ${index + 1}:`, {
        id: grid.id,
        hasContainer: !!grid.querySelector('.filters-container'),
        hasSidebar: !!grid.querySelector('[id^="filters-sidebar-"]'),
        hasToggle: !!document.querySelector(`#filters-toggle-${grid.id.replace('models-grid-', '')}`),
        hasOverlay: !!document.querySelector(`#filters-overlay-${grid.id.replace('models-grid-', '')}`)
      });
    });

    const sidebar = document.querySelector('[id^="filters-sidebar-"]');
    if (sidebar) {
      const rect = sidebar.getBoundingClientRect();
      console.log('Sidebar position:', {
        isVisible: rect.width > 0 && rect.height > 0,
        transform: getComputedStyle(sidebar).transform,
        classes: sidebar.className
      });
    }
  };

  window.testFilters = () => {
    const toggle = document.querySelector('[id^="filters-toggle-"]');
    if (toggle) {
      console.log('Testing filter toggle...');
      toggle.click();

      setTimeout(() => {
        const close = document.querySelector('.close-filters');
        if (close) {
          console.log('Closing filters...');
          close.click();
        }
      }, 2000);
    }
  };
}
