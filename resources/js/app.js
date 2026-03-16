import.meta.glob([
  '../images/**',
  '../fonts/**',
]);

(() => {
  const NAV_DEBUG = false; // true — вернуть логи
  const console = NAV_DEBUG ? window.console : new Proxy(window.console, { get: () => () => { } });

  document.addEventListener('DOMContentLoaded', () => {
    const hasGrid = document.querySelector('.js-models-grid');
    if (!hasGrid) return;

    import('./modules/models-grid.js')
      .then(({ initModelsGrids }) => initModelsGrids())
      .catch(console.error);
  });

  // Модели - фильтрация и загрузка
  (function () {
    const form = document.getElementById('models-filter');
    const root = document.getElementById('models-root');
    const empty = document.getElementById('models-empty');
    const more = document.getElementById('more');
    if (!form || !root) return;

    let page = 1, pages = 1, busy = false;

    function toParams(fd) {
      const params = new URLSearchParams();
      for (const [k, v] of fd.entries()) params.append(k, v);
      params.set('page', String(page));
      params.set('per_page', '12');
      return params;
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

    async function load(reset = false) {
      if (busy) return;
      busy = true;
      if (reset) { page = 1; root.innerHTML = ''; }
      const params = toParams(new FormData(form));
      try {
        const res = await fetch(`/wp-json/site/v1/models?${params.toString()}`, { credentials: 'same-origin' });
        const data = await res.json();
        pages = data.pages || 1;
        if (reset && (!data.items || data.items.length === 0)) {
          empty.classList.remove('hidden');
          busy = false; return;
        } else {
          empty.classList.add('hidden');
        }
        for (const [index, it] of (data.items || []).entries()) {
          const card = document.createElement('article');
          card.className = 'border p-2';
          const imageMeta = buildModelImageMeta(it);
          const isPriorityImage = root.children.length === 0 && index === 0;
          const img = it.thumb
            ? `<img src="${it.thumb}" alt="${imageMeta.alt}"${imageMeta.title ? ` title="${imageMeta.title}"` : ''} loading="${isPriorityImage ? 'eager' : 'lazy'}" decoding="${isPriorityImage ? 'auto' : 'async'}" fetchpriority="${isPriorityImage ? 'high' : 'low'}" width="360" height="480">`
            : '';
          card.innerHTML = `
          <a href="${it.link}" class="block">
            ${img}
            <h3 class="mt-2 text-sm font-medium">${it.title}</h3>
            ${it.price ? `<p class="text-xs opacity-70">from ${it.price}</p>` : ''}
          </a>`;
          root.appendChild(card);
        }
        more.disabled = page >= pages;
      } catch (e) {
        console.error(e);
      } finally {
        busy = false;
      }
    }

    form.addEventListener('submit', (e) => {
      e.preventDefault();
      load(true);
    });

    more?.addEventListener('click', () => {
      if (page < pages) { page += 1; load(false); }
    });

    // начальная загрузка
    load(true);
  })();

  // ЕДИНСТВЕННЫЙ ОБРАБОТЧИК НАВИГАЦИИ
  document.addEventListener('DOMContentLoaded', function () {
    console.log('Navigation DEBUG mode enabled');

    const navToggle = document.getElementById('navToggle');
    const navClose = document.getElementById('navClose');
    const siteNav = document.getElementById('siteNav');
    const navOverlay = document.getElementById('navOverlay');
    const body = document.body;

    console.log('Navigation elements check:', {
      navToggle: !!navToggle,
      navClose: !!navClose,
      siteNav: !!siteNav,
      navOverlay: !!navOverlay
    });

    if (!navToggle || !siteNav) {
      console.error('Critical navigation elements missing!');
      return;
    }

    // Проверяем структуру бургера
    const burgerLines = navToggle.querySelectorAll('.burger-line');
    console.log('Burger lines found:', burgerLines.length);

    function openMobileNav() {
      console.log('Opening mobile nav...');

      siteNav.classList.remove('-translate-x-full');
      siteNav.classList.add('translate-x-0');
      navToggle.classList.add('burger-open');

      if (navOverlay) {
        navOverlay.classList.remove('pointer-events-none', 'opacity-0');
        navOverlay.classList.add('pointer-events-auto', 'opacity-100');
      }

      navToggle.setAttribute('aria-expanded', 'true');
      body.style.overflow = 'hidden';

      console.log('Mobile nav opened');
    }

    function closeMobileNav() {
      console.log('Closing mobile nav...');

      siteNav.classList.add('-translate-x-full');
      siteNav.classList.remove('translate-x-0');
      navToggle.classList.remove('burger-open');

      if (navOverlay) {
        navOverlay.classList.add('pointer-events-none', 'opacity-0');
        navOverlay.classList.remove('pointer-events-auto', 'opacity-100');
      }

      navToggle.setAttribute('aria-expanded', 'false');
      body.style.overflow = '';

      // Закрываем подменю
      const openSubmenus = document.querySelectorAll('.submenu-mobile.open');
      openSubmenus.forEach(submenu => {
        submenu.classList.remove('open');
        submenu.classList.add('hidden');
      });

      const expandedToggles = document.querySelectorAll('.submenu-toggle[aria-expanded="true"]');
      expandedToggles.forEach(toggle => {
        toggle.setAttribute('aria-expanded', 'false');
      });

      console.log('Mobile nav closed');
    }

    function isMenuOpen() {
      return siteNav.classList.contains('translate-x-0');
    }

    // Основные события
    navToggle.addEventListener('click', function (e) {
      e.preventDefault();
      console.log('Burger clicked, current state:', isMenuOpen() ? 'open' : 'closed');

      if (isMenuOpen()) {
        closeMobileNav();
      } else {
        openMobileNav();
      }
    });

    if (navClose) {
      navClose.addEventListener('click', function (e) {
        e.preventDefault();
        console.log('Close button clicked');
        closeMobileNav();
      });
    }

    if (navOverlay) {
      navOverlay.addEventListener('click', function () {
        console.log('Overlay clicked');
        closeMobileNav();
      });
    }

    // Escape
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && isMenuOpen()) {
        console.log('Escape pressed');
        closeMobileNav();
      }
    });

    // Инициализация подменю
    function initSubmenuToggles() {
      const submenuToggles = document.querySelectorAll('.submenu-toggle');
      console.log('Found submenu toggles:', submenuToggles.length);

      submenuToggles.forEach((toggle, index) => {
        const targetId = toggle.getAttribute('data-target');
        const submenu = document.getElementById(targetId);

        console.log(`Toggle ${index + 1}:`, {
          targetId,
          submenuExists: !!submenu,
          expanded: toggle.getAttribute('aria-expanded')
        });

        // Убираем старые обработчики
        const newToggle = toggle.cloneNode(true);
        toggle.parentNode.replaceChild(newToggle, toggle);

        // Добавляем новый обработчик
        newToggle.addEventListener('click', function (e) {
          e.preventDefault();
          e.stopPropagation();

          const targetId = this.getAttribute('data-target');
          const submenu = document.getElementById(targetId);
          const isExpanded = this.getAttribute('aria-expanded') === 'true';

          console.log('Submenu toggle clicked:', {
            targetId,
            isExpanded,
            submenuFound: !!submenu
          });

          if (!submenu) {
            console.error('Submenu not found:', targetId);
            return;
          }

          if (isExpanded) {
            // Закрываем подменю
            submenu.classList.remove('open');
            submenu.classList.add('hidden');
            this.setAttribute('aria-expanded', 'false');
            console.log('Submenu closed');
          } else {
            // Закрываем другие открытые подменю на том же уровне
            const parentUl = this.closest('ul');
            if (parentUl) {
              const siblingToggles = parentUl.querySelectorAll('.submenu-toggle[aria-expanded="true"]');
              siblingToggles.forEach(siblingToggle => {
                if (siblingToggle !== this) {
                  const siblingTargetId = siblingToggle.getAttribute('data-target');
                  const siblingSubmenu = document.getElementById(siblingTargetId);
                  if (siblingSubmenu) {
                    siblingSubmenu.classList.remove('open');
                    siblingSubmenu.classList.add('hidden');
                    siblingToggle.setAttribute('aria-expanded', 'false');
                  }
                }
              });
            }

            // Открываем текущее подменю
            submenu.classList.add('open');
            submenu.classList.remove('hidden');
            this.setAttribute('aria-expanded', 'true');
            console.log('Submenu opened');
          }
        });
      });

      // Обработчики для ссылок с подменю на мобильных
      const linksWithSubmenu = document.querySelectorAll('.menu-item-has-children > div > .nav-link');
      linksWithSubmenu.forEach(link => {
        link.addEventListener('click', function (e) {
          // На мобильных устройствах предотвращаем переход по ссылке
          if (window.innerWidth < 1024) {
            e.preventDefault();

            // Находим кнопку toggle и эмулируем клик
            const toggleButton = this.parentNode.querySelector('.submenu-toggle');
            if (toggleButton) {
              toggleButton.click();
            }
          }
        });
      });
    }

    // Инициализируем через 100ms
    setTimeout(() => {
      initSubmenuToggles();
    }, 100);

    // Закрытие при изменении размера
    window.addEventListener('resize', function () {
      if (window.innerWidth >= 1024 && isMenuOpen()) {
        console.log('Window resized, closing mobile menu');
        closeMobileNav();
      }
    });

    // Закрытие меню при клике по ссылке (только на мобильных)
    siteNav.addEventListener('click', function (e) {
      if (window.innerWidth < 1024 && e.target.tagName === 'A' && e.target.getAttribute('href') && e.target.getAttribute('href') !== '#') {
        setTimeout(closeMobileNav, 150);
      }
    });

    console.log('Navigation initialization complete');
  });

  // Кнопка "Наверх"
  document.addEventListener('DOMContentLoaded', () => {
    const btn = document.getElementById('backToTop');
    if (!btn) return;

    const showAt = 300;

    const setVisible = (vis) => {
      if (vis) {
        btn.classList.remove('opacity-0', 'translate-y-2', 'pointer-events-none');
      } else {
        btn.classList.add('opacity-0', 'translate-y-2', 'pointer-events-none');
      }
    };

    const onScroll = () => setVisible(window.scrollY > showAt);
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();

    btn.addEventListener('click', (e) => {
      e.preventDefault();
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });
  });

  document.addEventListener('DOMContentLoaded', () => {
    const decodeContactValue = (value) => {
      if (!value) return '';

      try {
        return window.atob(value);
      } catch (error) {
        console.error('Failed to decode contact value', error);
        return '';
      }
    };

    let contactToast = null;
    let contactToastTimeout = null;

    const showContactToast = (message) => {
      if (!message) return;

      if (!contactToast) {
        contactToast = document.createElement('div');
        contactToast.setAttribute('role', 'status');
        contactToast.setAttribute('aria-live', 'polite');
        contactToast.className = 'pointer-events-none fixed bottom-6 left-1/2 z-[140] -translate-x-1/2 rounded-full bg-slate-900 px-4 py-2 text-sm font-medium text-white opacity-0 shadow-lg transition-opacity duration-200';
        document.body.appendChild(contactToast);
      }

      contactToast.textContent = message;
      contactToast.classList.remove('opacity-0');
      contactToast.classList.add('opacity-100');

      if (contactToastTimeout) {
        window.clearTimeout(contactToastTimeout);
      }

      contactToastTimeout = window.setTimeout(() => {
        contactToast?.classList.remove('opacity-100');
        contactToast?.classList.add('opacity-0');
      }, 2200);
    };

    const copyTextToClipboard = async (text) => {
      if (!text) return false;

      if (navigator.clipboard?.writeText) {
        try {
          await navigator.clipboard.writeText(text);
          return true;
        } catch (error) {
          console.warn('Clipboard API is unavailable', error);
        }
      }

      const fallbackField = document.createElement('textarea');
      fallbackField.value = text;
      fallbackField.setAttribute('readonly', '');
      fallbackField.className = 'fixed left-[-9999px] top-0';
      document.body.appendChild(fallbackField);
      fallbackField.select();

      const isCopied = document.execCommand('copy');
      document.body.removeChild(fallbackField);

      return isCopied;
    };

    const isTelephoneLink = (value) => /^tel:/i.test(value);
    const isProbablyMobileDevice = () =>
      Boolean(navigator.userAgentData?.mobile) ||
      /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini|Windows Phone/i.test(navigator.userAgent);

    const phoneNumberFromLink = (value) => {
      const rawValue = value.replace(/^tel:/i, '').trim();

      if (/^\d{11}$/.test(rawValue) && rawValue.startsWith('7')) {
        return `+${rawValue}`;
      }

      return rawValue;
    };

    const handleTelephoneFallback = async (phoneLink) => {
      const phoneNumber = phoneNumberFromLink(phoneLink);
      const isCopied = await copyTextToClipboard(phoneNumber);

      if (isCopied) {
        showContactToast('Номер телефона скопирован');
        return;
      }

      window.prompt('Скопируйте номер телефона:', phoneNumber);
    };

    document.querySelectorAll('[data-contact-text]').forEach((element) => {
      const decodedText = decodeContactValue(element.getAttribute('data-contact-text'));

      if (decodedText) {
        element.textContent = decodedText;
      }
    });

    document.querySelectorAll('[data-contact-link]').forEach((element) => {
      const decodedLink = decodeContactValue(element.getAttribute('data-contact-link'));

      if (!decodedLink) return;

      const isExternalLink = /^https?:\/\//i.test(decodedLink);
      const isPhoneLink = isTelephoneLink(decodedLink);

      if (element.tagName === 'A') {
        element.setAttribute('href', decodedLink);

        if (isExternalLink) {
          element.setAttribute('target', '_blank');
          element.setAttribute('rel', 'noopener');
        }

        if (isPhoneLink) {
          element.addEventListener('click', async (event) => {
            if (isProbablyMobileDevice()) return;

            event.preventDefault();
            await handleTelephoneFallback(decodedLink);
          });
        }

        return;
      }

      element.addEventListener('click', async () => {
        if (isExternalLink) {
          window.open(decodedLink, '_blank', 'noopener');
          return;
        }

        if (isPhoneLink) {
          if (isProbablyMobileDevice()) {
            window.location.href = decodedLink;
            return;
          }

          await handleTelephoneFallback(decodedLink);
          return;
        }

        window.location.href = decodedLink;
      });
    });
  });

  // Добавьте этот код в консоль браузера для отладки фильтров

  // 1. Проверка инициализации
  function debugFilters() {
    console.log('=== ОТЛАДКА ФИЛЬТРОВ ===');

    const grids = document.querySelectorAll('.js-models-grid');
    console.log(`Найдено ${grids.length} грида(ов)`);

    grids.forEach((grid, index) => {
      console.log(`\nГрид ${index + 1}:`);
      console.log('- ID:', grid.id);

      const container = grid.querySelector('.filters-container');
      console.log('- Контейнер фильтров:', !!container);

      const sidebar = grid.querySelector('[id^="filters-sidebar-"]');
      console.log('- Сайдбар:', !!sidebar);

      const toggle = document.querySelector(`#filters-toggle-${grid.id.replace('models-grid-', '')}`);
      console.log('- Кнопка открытия:', !!toggle);

      const overlay = document.querySelector(`#filters-overlay-${grid.id.replace('models-grid-', '')}`);
      console.log('- Оверлей:', !!overlay);

      if (container) {
        const levels = container.querySelectorAll('.filter-level');
        console.log('- Уровни фильтров:', levels.length);

        const navItems = container.querySelectorAll('.filter-nav-item');
        console.log('- Навигационные элементы:', navItems.length);

        const buttons = container.querySelectorAll('.filter-footer button');
        console.log('- Кнопки в футере:', buttons.length);
      }
    });
  }

  // 2. Проверка мобильного режима
  function debugMobile() {
    console.log('=== МОБИЛЬНЫЙ РЕЖИМ ===');
    console.log('Ширина экрана:', window.innerWidth);
    console.log('Мобильный режим:', window.innerWidth < 1024);

    const sidebar = document.querySelector('[id^="filters-sidebar-"]');
    if (sidebar) {
      const computedStyle = window.getComputedStyle(sidebar);
      console.log('Позиционирование сайдбара:', computedStyle.position);
      console.log('Z-index сайдбара:', computedStyle.zIndex);
      console.log('Transform сайдбара:', computedStyle.transform);
      console.log('Классы сайдбара:', sidebar.className);
    }
  }

  // 3. Тест открытия/закрытия
  function testToggle() {
    console.log('=== ТЕСТ ОТКРЫТИЯ/ЗАКРЫТИЯ ===');

    const toggle = document.querySelector('[id^="filters-toggle-"]');
    if (toggle) {
      console.log('Эмуляция клика по кнопке...');
      toggle.click();

      setTimeout(() => {
        const sidebar = document.querySelector('[id^="filters-sidebar-"]');
        console.log('Состояние после открытия:', sidebar?.classList.contains('translate-x-0'));

        // Закрыть через 2 секунды
        setTimeout(() => {
          const closeBtn = document.querySelector('.close-filters');
          if (closeBtn) {
            console.log('Закрытие...');
            closeBtn.click();
          }
        }, 2000);
      }, 500);
    } else {
      console.log('Кнопка открытия не найдена');
    }
  }

  // 4. Проверка CSS
  function debugCSS() {
    console.log('=== ПРОВЕРКА CSS ===');

    const sidebar = document.querySelector('[id^="filters-sidebar-"]');
    if (sidebar) {
      const styles = window.getComputedStyle(sidebar);
      console.log('Display:', styles.display);
      console.log('Position:', styles.position);
      console.log('Width:', styles.width);
      console.log('Height:', styles.height);
      console.log('Transform:', styles.transform);
      console.log('Z-index:', styles.zIndex);
    }

    const container = document.querySelector('.filters-container');
    if (container) {
      const containerStyles = window.getComputedStyle(container);
      console.log('\nКонтейнер фильтров:');
      console.log('Display:', containerStyles.display);
      console.log('Flex-direction:', containerStyles.flexDirection);
      console.log('Height:', containerStyles.height);
    }
  }

  // 5. Автоматическая диагностика
  function autoDebug() {
    console.log('🔍 АВТОМАТИЧЕСКАЯ ДИАГНОСТИКА ФИЛЬТРОВ');

    debugFilters();
    debugMobile();
    debugCSS();

    // Проверка на частые проблемы
    console.log('\n❗ ПРОВЕРКА ПРОБЛЕМ:');

    const issues = [];

    if (!document.querySelector('.js-models-grid')) {
      issues.push('Не найден элемент .js-models-grid');
    }

    if (!document.querySelector('.filters-container')) {
      issues.push('Не найден элемент .filters-container');
    }

    if (!document.querySelector('[id^="filters-toggle-"]')) {
      issues.push('Не найдена кнопка открытия фильтров');
    }

    const sidebar = document.querySelector('[id^="filters-sidebar-"]');
    if (sidebar) {
      const styles = window.getComputedStyle(sidebar);
      if (styles.position !== 'fixed' && window.innerWidth < 1024) {
        issues.push('Сайдбар не имеет position: fixed на мобильном');
      }
    }

    if (issues.length > 0) {
      console.log('Найдены проблемы:');
      issues.forEach(issue => console.log('❌', issue));
    } else {
      console.log('✅ Проблемы не обнаружены');
    }
  }

  // Запуск автодиагностики
  autoDebug();

  // Глобальные функции для ручного тестирования
  window.debugFilters = debugFilters;
  window.debugMobile = debugMobile;
  window.testToggle = testToggle;
  window.debugCSS = debugCSS;
  window.autoDebug = autoDebug;
})();
