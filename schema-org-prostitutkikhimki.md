# Документация по внедрению JSON-LD разметки Schema.org

**Проект:** prostitutkikhimki.com
**Версия:** 1.0
**Дата:** 2026-03-12

---

## Оглавление

1. [Общие правила](#1-общие-правила)
2. [Глобальные константы](#2-глобальные-константы)
3. [Шаблоны страниц](#3-шаблоны-страниц)
   - 3.1 Главная
   - 3.2 Категория моделей (Проверенные / Дешёвые / Элитные / Выезд / Апартаменты)
   - 3.3 Услуги (список)
   - 3.4 Страница конкретной услуги
   - 3.5 Районы (список)
   - 3.6 Страница конкретного района
   - 3.7 Анкета модели
   - 3.8 Блог (список статей)
   - 3.9 Статья блога
   - 3.10 О нас
   - 3.11 FAQ
   - 3.12 Политика конфиденциальности
   - 3.13 Условия использования
   - 3.14 Карта сайта
4. [Справочник переменных](#4-справочник-переменных)

---

## 1. Общие правила

- Все переменные обозначены как `{{variable_name}}`. При рендеринге заменяются на реальные значения.
- Значения строк должны быть экранированы для JSON (кавычки → `\"`, переносы строк → `\n`).
- Массивы переменной длины обозначены комментарием `/* повторяется для каждого элемента */`. В рабочем коде комментарии из JSON удаляются.
- Блоки `WebSite` и `Organization` одинаковы на всех страницах — вынести в общий хелпер.
- Даты в формате ISO 8601 с таймзоной Москвы: `2026-03-12T00:00:00+03:00`.
- **Adult-ниша:** Google не покажет rich results ни для одного типа разметки (подтверждено Мюллером, 2020). Разметка работает для семантического понимания контента и AI Overviews.
- **Запрещённые типы:** Product, Offer, AggregateRating, Review, Event — не использовать нигде на сайте.

---

## 2. Глобальные константы

| Константа | Значение |
|-----------|----------|
| `SITE_URL` | `https://prostitutkikhimki.com/` |
| `SITE_NAME` | `prostitutkikhimki.com` |
| `LOGO_URL` | `https://prostitutkikhimki.com/images/logo.svg` |
| `LOGO_WIDTH` | `36` |
| `LOGO_HEIGHT` | `36` |
| `TELEGRAM` | `@prostitutkikhimki` |
| `EMAIL` | `info@prostitutkikhimki.com` |
| `LANGUAGE` | `ru-RU` |
| `CITY` | `Химки` |
| `REGION` | `Московская область` |
| `COUNTRY` | `RU` |

---

## 3. Шаблоны страниц

---

### 3.1 Главная

**URL:** `https://prostitutkikhimki.com/`
**Описание:** Каталог всех моделей. L1 Матриарх кокона.

```json
{
  "@context": "https://schema.org",
  "@graph": [
    {
      "@type": "WebSite",
      "@id": "https://prostitutkikhimki.com/#website",
      "url": "https://prostitutkikhimki.com/",
      "name": "prostitutkikhimki.com",
      "description": "Каталог проверенных индивидуалок в Химках. Реальные фото, отзывы, все районы.",
      "inLanguage": "ru-RU",
      "publisher": {
        "@id": "https://prostitutkikhimki.com/#organization"
      }
    },
    {
      "@type": "Organization",
      "@id": "https://prostitutkikhimki.com/#organization",
      "name": "prostitutkikhimki.com",
      "url": "https://prostitutkikhimki.com/",
      "logo": {
        "@type": "ImageObject",
        "url": "https://prostitutkikhimki.com/images/logo.svg",
        "width": 36,
        "height": 36
      },
      "email": "info@prostitutkikhimki.com",
      "contactPoint": {
        "@type": "ContactPoint",
        "contactType": "customer service",
        "areaServed": {
          "@type": "City",
          "name": "Химки",
          "sameAs": "https://ru.wikipedia.org/wiki/Химки"
        },
        "availableLanguage": ["Russian"]
      },
      "areaServed": {
        "@type": "City",
        "name": "Химки",
        "sameAs": "https://ru.wikipedia.org/wiki/Химки"
      }
    },
    {
      "@type": "CollectionPage",
      "@id": "https://prostitutkikhimki.com/#webpage",
      "url": "https://prostitutkikhimki.com/",
      "name": "{{page_title}}",
      "description": "{{page_description}}",
      "inLanguage": "ru-RU",
      "isPartOf": {
        "@id": "https://prostitutkikhimki.com/#website"
      },
      "about": {
        "@id": "https://prostitutkikhimki.com/#organization"
      },
      "mainEntity": {
        "@id": "https://prostitutkikhimki.com/#model-list"
      },
      "dateModified": "{{date_modified}}"
    },
    {
      "@type": "ItemList",
      "@id": "https://prostitutkikhimki.com/#model-list",
      "name": "{{list_title}}",
      "description": "{{list_description}}",
      "numberOfItems": {{models_total_count}},
      "itemListElement": [
        {
          "@type": "ListItem",
          "position": {{model_position}},
          "item": {
            "@type": "Person",
            "name": "{{model_name}}",
            "url": "{{model_url}}",
            "image": {
              "@type": "ImageObject",
              "url": "{{model_image_url}}",
              "caption": "{{model_image_caption}}"
            }
          }
        }
        /* повторяется для каждой модели */
      ]
    }
  ]
}
```

**Переменные:**

| Переменная | Источник данных |
|------------|-----------------|
| `{{page_title}}` | SEO title страницы (тег `<title>`) |
| `{{page_description}}` | Meta description страницы |
| `{{list_title}}` | Заголовок списка, например: `"Каталог индивидуалок Химки"` |
| `{{list_description}}` | Описание списка, например: `"Проверенные анкеты индивидуалок с реальными фото. Все районы Химок."` |
| `{{models_total_count}}` | Общее количество моделей в каталоге (число из БД) |
| `{{model_position}}` | Порядковый номер модели в списке (начиная с 1) |
| `{{model_name}}` | Имя модели из БД (поле `name`) |
| `{{model_url}}` | Полный URL анкеты, например: `https://prostitutkikhimki.com/model/id-1/` |
| `{{model_image_url}}` | URL главного фото модели из БД |
| `{{model_image_caption}}` | Подпись: `"{{model_name}}, {{model_age}}, {{model_district}}"` (генерируется) |
| `{{date_modified}}` | Дата последнего обновления каталога (ISO 8601) |

> **Примечание:** На главной НЕТ BreadcrumbList — это корневая страница. Блок `itemListElement` повторяется для каждой модели.

---

### 3.2 Категория моделей

**URL:** `/proverennye/`, `/nedorogie/`, `/elitnye/`, `/na-vyezd/`, `/apartamenty/`
**Описание:** Страницы L2 кокона. Каталог моделей, отфильтрованный по категории. Шаблон одинаковый, отличаются переменные.

```json
{
  "@context": "https://schema.org",
  "@graph": [
    {
      "@type": "WebSite",
      "@id": "https://prostitutkikhimki.com/#website",
      "url": "https://prostitutkikhimki.com/",
      "name": "prostitutkikhimki.com",
      "inLanguage": "ru-RU",
      "publisher": {
        "@id": "https://prostitutkikhimki.com/#organization"
      }
    },
    {
      "@type": "Organization",
      "@id": "https://prostitutkikhimki.com/#organization",
      "name": "prostitutkikhimki.com",
      "url": "https://prostitutkikhimki.com/",
      "logo": {
        "@type": "ImageObject",
        "url": "https://prostitutkikhimki.com/images/logo.svg"
      }
    },
    {
      "@type": "CollectionPage",
      "@id": "{{page_url}}#webpage",
      "url": "{{page_url}}",
      "name": "{{page_title}}",
      "description": "{{page_description}}",
      "inLanguage": "ru-RU",
      "isPartOf": {
        "@id": "https://prostitutkikhimki.com/#website"
      },
      "breadcrumb": {
        "@id": "{{page_url}}#breadcrumb"
      },
      "mainEntity": {
        "@id": "{{page_url}}#list"
      },
      "datePublished": "{{date_published}}",
      "dateModified": "{{date_modified}}"
    },
    {
      "@type": "ItemList",
      "@id": "{{page_url}}#list",
      "name": "{{list_title}}",
      "description": "{{list_description}}",
      "itemListOrder": "https://schema.org/ItemListOrderDescending",
      "numberOfItems": {{models_total_count}},
      "itemListElement": [
        {
          "@type": "ListItem",
          "position": {{model_position}},
          "item": {
            "@type": "Person",
            "name": "{{model_name}}",
            "url": "{{model_url}}",
            "image": {
              "@type": "ImageObject",
              "url": "{{model_image_url}}",
              "caption": "{{model_image_caption}}"
            }
          }
        }
        /* повторяется для каждой модели */
      ]
    },
    {
      "@type": "BreadcrumbList",
      "@id": "{{page_url}}#breadcrumb",
      "itemListElement": [
        {
          "@type": "ListItem",
          "position": 1,
          "name": "Индивидуалки Химки",
          "item": "https://prostitutkikhimki.com/"
        },
        {
          "@type": "ListItem",
          "position": 2,
          "name": "{{breadcrumb_name}}",
          "item": "{{page_url}}"
        }
      ]
    }
  ]
}
```

**Переменные:**

| Переменная | Источник данных |
|------------|-----------------|
| `{{page_url}}` | Полный URL категории, например: `https://prostitutkikhimki.com/proverennye/` |
| `{{page_title}}` | SEO title страницы категории |
| `{{page_description}}` | Meta description страницы категории |
| `{{list_title}}` | Заголовок списка, например: `"Проверенные индивидуалки Химки"` |
| `{{list_description}}` | Описание списка |
| `{{models_total_count}}` | Количество моделей в категории (число из БД) |
| `{{model_position}}` | Порядковый номер модели (начиная с 1) |
| `{{model_name}}` | Имя модели из БД |
| `{{model_url}}` | Полный URL анкеты модели |
| `{{model_image_url}}` | URL главного фото модели |
| `{{model_image_caption}}` | Подпись: `"{{model_name}}, {{model_age}}, {{model_district}}"` |
| `{{breadcrumb_name}}` | Название категории в хлебных крошках: `"Проверенные"`, `"Дешёвые"`, `"Элитные"`, `"Выезд"`, `"Апартаменты"` |
| `{{date_published}}` | Дата первой публикации (ISO 8601) |
| `{{date_modified}}` | Дата последнего обновления (ISO 8601) |

---

### 3.3 Услуги (список)

**URL:** `https://prostitutkikhimki.com/uslugi/`
**Описание:** Хаб услуг (L2). Каталог с перечислением всех доступных услуг.

```json
{
  "@context": "https://schema.org",
  "@graph": [
    {
      "@type": "WebSite",
      "@id": "https://prostitutkikhimki.com/#website",
      "url": "https://prostitutkikhimki.com/",
      "name": "prostitutkikhimki.com",
      "inLanguage": "ru-RU",
      "publisher": {
        "@id": "https://prostitutkikhimki.com/#organization"
      }
    },
    {
      "@type": "Organization",
      "@id": "https://prostitutkikhimki.com/#organization",
      "name": "prostitutkikhimki.com",
      "url": "https://prostitutkikhimki.com/",
      "logo": {
        "@type": "ImageObject",
        "url": "https://prostitutkikhimki.com/images/logo.svg"
      }
    },
    {
      "@type": "CollectionPage",
      "@id": "https://prostitutkikhimki.com/uslugi/#webpage",
      "url": "https://prostitutkikhimki.com/uslugi/",
      "name": "{{page_title}}",
      "description": "{{page_description}}",
      "inLanguage": "ru-RU",
      "isPartOf": {
        "@id": "https://prostitutkikhimki.com/#website"
      },
      "breadcrumb": {
        "@id": "https://prostitutkikhimki.com/uslugi/#breadcrumb"
      },
      "mainEntity": {
        "@id": "https://prostitutkikhimki.com/uslugi/#list"
      },
      "datePublished": "{{date_published}}",
      "dateModified": "{{date_modified}}"
    },
    {
      "@type": "ItemList",
      "@id": "https://prostitutkikhimki.com/uslugi/#list",
      "name": "{{list_title}}",
      "itemListOrder": "https://schema.org/ItemListOrderAscending",
      "numberOfItems": {{services_total_count}},
      "itemListElement": [
        {
          "@type": "ListItem",
          "position": {{service_position}},
          "item": {
            "@type": "Thing",
            "name": "{{service_name}}",
            "url": "{{service_url}}"
          }
        }
        /* повторяется для каждой услуги */
      ]
    },
    {
      "@type": "BreadcrumbList",
      "@id": "https://prostitutkikhimki.com/uslugi/#breadcrumb",
      "itemListElement": [
        {
          "@type": "ListItem",
          "position": 1,
          "name": "Индивидуалки Химки",
          "item": "https://prostitutkikhimki.com/"
        },
        {
          "@type": "ListItem",
          "position": 2,
          "name": "Услуги",
          "item": "https://prostitutkikhimki.com/uslugi/"
        }
      ]
    }
  ]
}
```

**Переменные:**

| Переменная | Источник данных |
|------------|-----------------|
| `{{page_title}}` | SEO title страницы услуг |
| `{{page_description}}` | Meta description страницы услуг |
| `{{list_title}}` | Заголовок списка, например: `"Все услуги индивидуалок Химки"` |
| `{{services_total_count}}` | Общее количество услуг (число) |
| `{{service_position}}` | Порядковый номер услуги (начиная с 1) |
| `{{service_name}}` | Название услуги, например: `"Классический секс"` |
| `{{service_url}}` | Полный URL страницы услуги, например: `https://prostitutkikhimki.com/uslugi/klassika/` |
| `{{date_published}}` | Дата первой публикации (ISO 8601) |
| `{{date_modified}}` | Дата последнего обновления (ISO 8601) |

> **Примечание:** Тип `Thing` вместо `Service` в ItemList — осознанный выбор. `Service` + `makesOffer` создаёт косвенные FOSTA-SESTA риски. `Thing` с `name` и `url` достаточен для семантической связи.

---

### 3.4 Страница конкретной услуги

**URL:** `https://prostitutkikhimki.com/uslugi/{{service_slug}}/`
**Описание:** L3 страница. Список моделей, предоставляющих конкретную услугу.

```json
{
  "@context": "https://schema.org",
  "@graph": [
    {
      "@type": "WebSite",
      "@id": "https://prostitutkikhimki.com/#website",
      "url": "https://prostitutkikhimki.com/",
      "name": "prostitutkikhimki.com",
      "inLanguage": "ru-RU",
      "publisher": {
        "@id": "https://prostitutkikhimki.com/#organization"
      }
    },
    {
      "@type": "Organization",
      "@id": "https://prostitutkikhimki.com/#organization",
      "name": "prostitutkikhimki.com",
      "url": "https://prostitutkikhimki.com/",
      "logo": {
        "@type": "ImageObject",
        "url": "https://prostitutkikhimki.com/images/logo.svg"
      }
    },
    {
      "@type": "CollectionPage",
      "@id": "{{page_url}}#webpage",
      "url": "{{page_url}}",
      "name": "{{page_title}}",
      "description": "{{page_description}}",
      "inLanguage": "ru-RU",
      "isPartOf": {
        "@id": "https://prostitutkikhimki.com/#website"
      },
      "breadcrumb": {
        "@id": "{{page_url}}#breadcrumb"
      },
      "mainEntity": {
        "@id": "{{page_url}}#list"
      },
      "datePublished": "{{date_published}}",
      "dateModified": "{{date_modified}}"
    },
    {
      "@type": "ItemList",
      "@id": "{{page_url}}#list",
      "name": "{{list_title}}",
      "itemListOrder": "https://schema.org/ItemListOrderDescending",
      "numberOfItems": {{models_total_count}},
      "itemListElement": [
        {
          "@type": "ListItem",
          "position": {{model_position}},
          "item": {
            "@type": "Person",
            "name": "{{model_name}}",
            "url": "{{model_url}}",
            "image": {
              "@type": "ImageObject",
              "url": "{{model_image_url}}",
              "caption": "{{model_image_caption}}"
            }
          }
        }
        /* повторяется для каждой модели */
      ]
    },
    {
      "@type": "BreadcrumbList",
      "@id": "{{page_url}}#breadcrumb",
      "itemListElement": [
        {
          "@type": "ListItem",
          "position": 1,
          "name": "Индивидуалки Химки",
          "item": "https://prostitutkikhimki.com/"
        },
        {
          "@type": "ListItem",
          "position": 2,
          "name": "Услуги",
          "item": "https://prostitutkikhimki.com/uslugi/"
        },
        {
          "@type": "ListItem",
          "position": 3,
          "name": "{{service_name}}",
          "item": "{{page_url}}"
        }
      ]
    }
  ]
}
```

**Переменные:**

| Переменная | Источник данных |
|------------|-----------------|
| `{{page_url}}` | Полный URL, например: `https://prostitutkikhimki.com/uslugi/klassika/` |
| `{{page_title}}` | SEO title страницы услуги |
| `{{page_description}}` | Meta description страницы услуги |
| `{{list_title}}` | Заголовок списка, например: `"Классический секс — индивидуалки Химки"` |
| `{{models_total_count}}` | Количество моделей с данной услугой (число из БД) |
| `{{model_position}}` | Порядковый номер модели (начиная с 1) |
| `{{model_name}}` | Имя модели из БД |
| `{{model_url}}` | Полный URL анкеты модели |
| `{{model_image_url}}` | URL главного фото модели |
| `{{model_image_caption}}` | Подпись к фото |
| `{{service_name}}` | Название услуги для хлебных крошек, например: `"Классический секс"` |
| `{{date_published}}` | Дата первой публикации (ISO 8601) |
| `{{date_modified}}` | Дата последнего обновления (ISO 8601) |

---

### 3.5 Районы (список)

**URL:** `https://prostitutkikhimki.com/rajony/`
**Описание:** L2 страница. Каталог всех районов Химок с моделями.

```json
{
  "@context": "https://schema.org",
  "@graph": [
    {
      "@type": "WebSite",
      "@id": "https://prostitutkikhimki.com/#website",
      "url": "https://prostitutkikhimki.com/",
      "name": "prostitutkikhimki.com",
      "inLanguage": "ru-RU",
      "publisher": {
        "@id": "https://prostitutkikhimki.com/#organization"
      }
    },
    {
      "@type": "Organization",
      "@id": "https://prostitutkikhimki.com/#organization",
      "name": "prostitutkikhimki.com",
      "url": "https://prostitutkikhimki.com/",
      "logo": {
        "@type": "ImageObject",
        "url": "https://prostitutkikhimki.com/images/logo.svg"
      }
    },
    {
      "@type": "CollectionPage",
      "@id": "https://prostitutkikhimki.com/rajony/#webpage",
      "url": "https://prostitutkikhimki.com/rajony/",
      "name": "{{page_title}}",
      "description": "{{page_description}}",
      "inLanguage": "ru-RU",
      "isPartOf": {
        "@id": "https://prostitutkikhimki.com/#website"
      },
      "breadcrumb": {
        "@id": "https://prostitutkikhimki.com/rajony/#breadcrumb"
      },
      "mainEntity": {
        "@id": "https://prostitutkikhimki.com/rajony/#list"
      },
      "datePublished": "{{date_published}}",
      "dateModified": "{{date_modified}}"
    },
    {
      "@type": "ItemList",
      "@id": "https://prostitutkikhimki.com/rajony/#list",
      "name": "{{list_title}}",
      "itemListOrder": "https://schema.org/ItemListOrderAscending",
      "numberOfItems": {{districts_total_count}},
      "itemListElement": [
        {
          "@type": "ListItem",
          "position": {{district_position}},
          "item": {
            "@type": "Place",
            "name": "{{district_name}}",
            "url": "{{district_url}}",
            "containedInPlace": {
              "@type": "City",
              "name": "Химки",
              "sameAs": "https://ru.wikipedia.org/wiki/Химки"
            }
          }
        }
        /* повторяется для каждого района */
      ]
    },
    {
      "@type": "BreadcrumbList",
      "@id": "https://prostitutkikhimki.com/rajony/#breadcrumb",
      "itemListElement": [
        {
          "@type": "ListItem",
          "position": 1,
          "name": "Индивидуалки Химки",
          "item": "https://prostitutkikhimki.com/"
        },
        {
          "@type": "ListItem",
          "position": 2,
          "name": "Районы",
          "item": "https://prostitutkikhimki.com/rajony/"
        }
      ]
    }
  ]
}
```

**Переменные:**

| Переменная | Источник данных |
|------------|-----------------|
| `{{page_title}}` | SEO title страницы районов |
| `{{page_description}}` | Meta description страницы районов |
| `{{list_title}}` | Заголовок списка, например: `"Районы Химок"` |
| `{{districts_total_count}}` | Общее количество районов (число) |
| `{{district_position}}` | Порядковый номер района (начиная с 1) |
| `{{district_name}}` | Название района из БД, например: `"Сходня"` |
| `{{district_url}}` | Полный URL, например: `https://prostitutkikhimki.com/rajony/shodnya/` |
| `{{date_published}}` | Дата первой публикации (ISO 8601) |
| `{{date_modified}}` | Дата последнего обновления (ISO 8601) |

---

### 3.6 Страница конкретного района

**URL:** `https://prostitutkikhimki.com/rajony/{{district_slug}}/`
**Описание:** L3 страница. Список моделей в конкретном районе Химок.

```json
{
  "@context": "https://schema.org",
  "@graph": [
    {
      "@type": "WebSite",
      "@id": "https://prostitutkikhimki.com/#website",
      "url": "https://prostitutkikhimki.com/",
      "name": "prostitutkikhimki.com",
      "inLanguage": "ru-RU",
      "publisher": {
        "@id": "https://prostitutkikhimki.com/#organization"
      }
    },
    {
      "@type": "Organization",
      "@id": "https://prostitutkikhimki.com/#organization",
      "name": "prostitutkikhimki.com",
      "url": "https://prostitutkikhimki.com/",
      "logo": {
        "@type": "ImageObject",
        "url": "https://prostitutkikhimki.com/images/logo.svg"
      }
    },
    {
      "@type": "CollectionPage",
      "@id": "{{page_url}}#webpage",
      "url": "{{page_url}}",
      "name": "{{page_title}}",
      "description": "{{page_description}}",
      "inLanguage": "ru-RU",
      "isPartOf": {
        "@id": "https://prostitutkikhimki.com/#website"
      },
      "breadcrumb": {
        "@id": "{{page_url}}#breadcrumb"
      },
      "mainEntity": {
        "@id": "{{page_url}}#list"
      },
      "datePublished": "{{date_published}}",
      "dateModified": "{{date_modified}}"
    },
    {
      "@type": "ItemList",
      "@id": "{{page_url}}#list",
      "name": "{{list_title}}",
      "itemListOrder": "https://schema.org/ItemListOrderDescending",
      "numberOfItems": {{models_total_count}},
      "itemListElement": [
        {
          "@type": "ListItem",
          "position": {{model_position}},
          "item": {
            "@type": "Person",
            "name": "{{model_name}}",
            "url": "{{model_url}}",
            "image": {
              "@type": "ImageObject",
              "url": "{{model_image_url}}",
              "caption": "{{model_image_caption}}"
            }
          }
        }
        /* повторяется для каждой модели */
      ]
    },
    {
      "@type": "BreadcrumbList",
      "@id": "{{page_url}}#breadcrumb",
      "itemListElement": [
        {
          "@type": "ListItem",
          "position": 1,
          "name": "Индивидуалки Химки",
          "item": "https://prostitutkikhimki.com/"
        },
        {
          "@type": "ListItem",
          "position": 2,
          "name": "Районы",
          "item": "https://prostitutkikhimki.com/rajony/"
        },
        {
          "@type": "ListItem",
          "position": 3,
          "name": "{{district_name}}",
          "item": "{{page_url}}"
        }
      ]
    }
  ]
}
```

**Переменные:**

| Переменная | Источник данных |
|------------|-----------------|
| `{{page_url}}` | Полный URL, например: `https://prostitutkikhimki.com/rajony/shodnya/` |
| `{{page_title}}` | SEO title страницы района |
| `{{page_description}}` | Meta description страницы района |
| `{{list_title}}` | Заголовок списка, например: `"Индивидуалки Сходня"` |
| `{{models_total_count}}` | Количество моделей в районе (число из БД) |
| `{{model_position}}` | Порядковый номер модели (начиная с 1) |
| `{{model_name}}` | Имя модели из БД |
| `{{model_url}}` | Полный URL анкеты модели |
| `{{model_image_url}}` | URL главного фото модели |
| `{{model_image_caption}}` | Подпись к фото |
| `{{district_name}}` | Название района для хлебных крошек, например: `"Сходня"` |
| `{{date_published}}` | Дата первой публикации (ISO 8601) |
| `{{date_modified}}` | Дата последнего обновления (ISO 8601) |

---

### 3.7 Анкета модели

**URL:** `https://prostitutkikhimki.com/model/id-{{model_id}}/`
**Описание:** Профиль конкретной модели. Консервативная разметка: ProfilePage + Person, без цен и рейтингов.

```json
{
  "@context": "https://schema.org",
  "@graph": [
    {
      "@type": "WebSite",
      "@id": "https://prostitutkikhimki.com/#website",
      "url": "https://prostitutkikhimki.com/",
      "name": "prostitutkikhimki.com",
      "inLanguage": "ru-RU",
      "publisher": {
        "@id": "https://prostitutkikhimki.com/#organization"
      }
    },
    {
      "@type": "Organization",
      "@id": "https://prostitutkikhimki.com/#organization",
      "name": "prostitutkikhimki.com",
      "url": "https://prostitutkikhimki.com/",
      "logo": {
        "@type": "ImageObject",
        "url": "https://prostitutkikhimki.com/images/logo.svg"
      }
    },
    {
      "@type": "ProfilePage",
      "@id": "{{page_url}}#webpage",
      "url": "{{page_url}}",
      "name": "{{page_title}}",
      "description": "{{page_description}}",
      "inLanguage": "ru-RU",
      "isPartOf": {
        "@id": "https://prostitutkikhimki.com/#website"
      },
      "mainEntity": {
        "@id": "{{page_url}}#person"
      },
      "breadcrumb": {
        "@id": "{{page_url}}#breadcrumb"
      },
      "primaryImageOfPage": {
        "@type": "ImageObject",
        "url": "{{model_main_image_url}}"
      },
      "datePublished": "{{date_published}}",
      "dateModified": "{{date_modified}}"
    },
    {
      "@type": "Person",
      "@id": "{{page_url}}#person",
      "name": "{{model_name}}",
      "gender": "Female",
      "description": "{{model_about}}",
      "image": [{{model_images_array}}],
      "height": {
        "@type": "QuantitativeValue",
        "value": {{model_height}},
        "unitCode": "CMT"
      },
      "weight": {
        "@type": "QuantitativeValue",
        "value": {{model_weight}},
        "unitCode": "KGM"
      },
      "homeLocation": {
        "@type": "Place",
        "name": "{{model_district}}",
        "containedInPlace": {
          "@type": "City",
          "name": "Химки"
        }
      }
    },
    {
      "@type": "BreadcrumbList",
      "@id": "{{page_url}}#breadcrumb",
      "itemListElement": [
        {
          "@type": "ListItem",
          "position": 1,
          "name": "Индивидуалки Химки",
          "item": "https://prostitutkikhimki.com/"
        },
        {
          "@type": "ListItem",
          "position": 2,
          "name": "{{model_name}}",
          "item": "{{page_url}}"
        }
      ]
    }
  ]
}
```

**Переменные:**

| Переменная | Источник данных |
|------------|-----------------|
| `{{page_url}}` | Полный URL анкеты, например: `https://prostitutkikhimki.com/model/id-1/` |
| `{{page_title}}` | SEO title, например: `"Анабель — индивидуалка, Сходня, Химки"` |
| `{{page_description}}` | Meta description страницы |
| `{{model_name}}` | Имя модели из БД (поле `name`) |
| `{{model_about}}` | Текст «О себе» из профиля модели. Экранировать спецсимволы для JSON |
| `{{model_main_image_url}}` | URL первого (главного) фото модели |
| `{{model_images_array}}` | Массив URL всех фото в формате JSON-строк: `"https://...img1.webp", "https://...img2.webp"` |
| `{{model_height}}` | Рост модели в сантиметрах (число). Из поля `height` |
| `{{model_weight}}` | Вес модели в килограммах (число). Из поля `weight` |
| `{{model_district}}` | Район модели, например: `"Сходня"`. Из поля `district` |
| `{{date_published}}` | Дата добавления анкеты (ISO 8601) |
| `{{date_modified}}` | Дата последнего обновления анкеты (ISO 8601) |

> **Примечание:** Если `model_about` отсутствует (пустое поле) — не выводить свойство `description`. Если `model_district` отсутствует — не выводить блок `homeLocation`.

---

### 3.8 Блог (список статей)

**URL:** `https://prostitutkikhimki.com/blog/`
**Описание:** Главная страница блога. Категорий нет — все статьи в одном списке.

```json
{
  "@context": "https://schema.org",
  "@graph": [
    {
      "@type": "WebSite",
      "@id": "https://prostitutkikhimki.com/#website",
      "url": "https://prostitutkikhimki.com/",
      "name": "prostitutkikhimki.com",
      "inLanguage": "ru-RU",
      "publisher": {
        "@id": "https://prostitutkikhimki.com/#organization"
      }
    },
    {
      "@type": "Organization",
      "@id": "https://prostitutkikhimki.com/#organization",
      "name": "prostitutkikhimki.com",
      "url": "https://prostitutkikhimki.com/",
      "logo": {
        "@type": "ImageObject",
        "url": "https://prostitutkikhimki.com/images/logo.svg"
      }
    },
    {
      "@type": "CollectionPage",
      "@id": "https://prostitutkikhimki.com/blog/#webpage",
      "url": "https://prostitutkikhimki.com/blog/",
      "name": "{{page_title}}",
      "description": "{{page_description}}",
      "inLanguage": "ru-RU",
      "isPartOf": {
        "@id": "https://prostitutkikhimki.com/#website"
      },
      "breadcrumb": {
        "@id": "https://prostitutkikhimki.com/blog/#breadcrumb"
      },
      "mainEntity": {
        "@id": "https://prostitutkikhimki.com/blog/#article-list"
      }
    },
    {
      "@type": "ItemList",
      "@id": "https://prostitutkikhimki.com/blog/#article-list",
      "name": "{{list_title}}",
      "itemListOrder": "https://schema.org/ItemListOrderDescending",
      "numberOfItems": {{articles_total_count}},
      "itemListElement": [
        {
          "@type": "ListItem",
          "position": {{article_position}},
          "item": {
            "@type": "BlogPosting",
            "@id": "{{article_url}}#blogposting",
            "url": "{{article_url}}",
            "headline": "{{article_headline}}",
            "description": "{{article_excerpt}}",
            "image": "{{article_cover_image_url}}",
            "datePublished": "{{article_date_published}}",
            "author": {
              "@id": "https://prostitutkikhimki.com/#organization"
            },
            "publisher": {
              "@id": "https://prostitutkikhimki.com/#organization"
            }
          }
        }
        /* повторяется для каждой статьи */
      ]
    },
    {
      "@type": "BreadcrumbList",
      "@id": "https://prostitutkikhimki.com/blog/#breadcrumb",
      "itemListElement": [
        {
          "@type": "ListItem",
          "position": 1,
          "name": "Индивидуалки Химки",
          "item": "https://prostitutkikhimki.com/"
        },
        {
          "@type": "ListItem",
          "position": 2,
          "name": "Блог",
          "item": "https://prostitutkikhimki.com/blog/"
        }
      ]
    }
  ]
}
```

**Переменные:**

| Переменная | Источник данных |
|------------|-----------------|
| `{{page_title}}` | SEO title страницы блога |
| `{{page_description}}` | Meta description страницы блога |
| `{{list_title}}` | Заголовок списка, например: `"Блог"` |
| `{{articles_total_count}}` | Общее количество опубликованных статей (число из БД) |
| `{{article_position}}` | Порядковый номер статьи (начиная с 1, от новых к старым) |
| `{{article_url}}` | Полный URL статьи, например: `https://prostitutkikhimki.com/blog/kak-vybrat-individualnuyu/` |
| `{{article_headline}}` | Заголовок статьи из БД |
| `{{article_excerpt}}` | Краткое описание статьи из БД |
| `{{article_cover_image_url}}` | URL обложки статьи из БД |
| `{{article_date_published}}` | Дата публикации статьи (ISO 8601) |

> **Примечание:** `@id` каждой статьи — `{{article_url}}#blogposting`. Этот же `@id` используется на странице самой статьи (шаблон 3.9).

---

### 3.9 Статья блога

**URL:** `https://prostitutkikhimki.com/blog/{{article_slug}}/`
**Описание:** Отдельная статья блога.

```json
{
  "@context": "https://schema.org",
  "@graph": [
    {
      "@type": "WebSite",
      "@id": "https://prostitutkikhimki.com/#website",
      "url": "https://prostitutkikhimki.com/",
      "name": "prostitutkikhimki.com",
      "inLanguage": "ru-RU",
      "publisher": {
        "@id": "https://prostitutkikhimki.com/#organization"
      }
    },
    {
      "@type": "Organization",
      "@id": "https://prostitutkikhimki.com/#organization",
      "name": "prostitutkikhimki.com",
      "url": "https://prostitutkikhimki.com/",
      "logo": {
        "@type": "ImageObject",
        "url": "https://prostitutkikhimki.com/images/logo.svg"
      }
    },
    {
      "@type": "WebPage",
      "@id": "{{page_url}}#webpage",
      "url": "{{page_url}}",
      "name": "{{page_title}}",
      "description": "{{page_description}}",
      "inLanguage": "ru-RU",
      "isPartOf": {
        "@id": "https://prostitutkikhimki.com/#website"
      },
      "primaryImageOfPage": {
        "@id": "{{page_url}}#primaryimage"
      },
      "breadcrumb": {
        "@id": "{{page_url}}#breadcrumb"
      }
    },
    {
      "@type": "BlogPosting",
      "@id": "{{page_url}}#blogposting",
      "headline": "{{article_headline}}",
      "description": "{{article_excerpt}}",
      "mainEntityOfPage": {
        "@id": "{{page_url}}#webpage"
      },
      "datePublished": "{{article_date_published}}",
      "dateModified": "{{article_date_modified}}",
      "author": {
        "@id": "https://prostitutkikhimki.com/#organization"
      },
      "publisher": {
        "@id": "https://prostitutkikhimki.com/#organization"
      },
      "image": {
        "@id": "{{page_url}}#primaryimage"
      },
      "inLanguage": "ru-RU",
      "isAccessibleForFree": true,
      "keywords": [{{article_keywords_array}}]
    },
    {
      "@type": "ImageObject",
      "@id": "{{page_url}}#primaryimage",
      "url": "{{article_cover_image_url}}",
      "width": {{article_cover_width}},
      "height": {{article_cover_height}},
      "caption": "{{article_cover_caption}}"
    },
    {
      "@type": "BreadcrumbList",
      "@id": "{{page_url}}#breadcrumb",
      "itemListElement": [
        {
          "@type": "ListItem",
          "position": 1,
          "name": "Индивидуалки Химки",
          "item": "https://prostitutkikhimki.com/"
        },
        {
          "@type": "ListItem",
          "position": 2,
          "name": "Блог",
          "item": "https://prostitutkikhimki.com/blog/"
        },
        {
          "@type": "ListItem",
          "position": 3,
          "name": "{{breadcrumb_article_name}}",
          "item": "{{page_url}}"
        }
      ]
    }
  ]
}
```

**Переменные:**

| Переменная | Источник данных |
|------------|-----------------|
| `{{page_url}}` | Полный URL статьи |
| `{{page_title}}` | SEO title страницы (тег `<title>`) |
| `{{page_description}}` | Meta description страницы |
| `{{article_headline}}` | Заголовок статьи (H1) из БД |
| `{{article_excerpt}}` | Краткое описание из поля `excerpt` |
| `{{article_date_published}}` | Дата публикации (ISO 8601) из `published_at` |
| `{{article_date_modified}}` | Дата редактирования (ISO 8601) из `updated_at` |
| `{{article_keywords_array}}` | Массив ключей: `"ключ1", "ключ2"`. Из поля `tags` |
| `{{article_cover_image_url}}` | URL обложки из медиатеки |
| `{{article_cover_width}}` | Ширина обложки (px) |
| `{{article_cover_height}}` | Высота обложки (px) |
| `{{article_cover_caption}}` | Подпись к обложке (alt-текст) |
| `{{breadcrumb_article_name}}` | Короткое название для хлебных крошек |

---

### 3.10 О нас

**URL:** `https://prostitutkikhimki.com/o-nas/`
**Описание:** Trust Page. Статическая страница с информацией о сайте.

```json
{
  "@context": "https://schema.org",
  "@graph": [
    {
      "@type": "WebSite",
      "@id": "https://prostitutkikhimki.com/#website",
      "url": "https://prostitutkikhimki.com/",
      "name": "prostitutkikhimki.com",
      "inLanguage": "ru-RU",
      "publisher": {
        "@id": "https://prostitutkikhimki.com/#organization"
      }
    },
    {
      "@type": "Organization",
      "@id": "https://prostitutkikhimki.com/#organization",
      "name": "prostitutkikhimki.com",
      "url": "https://prostitutkikhimki.com/",
      "logo": {
        "@type": "ImageObject",
        "url": "https://prostitutkikhimki.com/images/logo.svg"
      },
      "foundingDate": "2025",
      "email": "info@prostitutkikhimki.com",
      "address": {
        "@type": "PostalAddress",
        "addressLocality": "Химки",
        "addressRegion": "Московская область",
        "addressCountry": "RU"
      },
      "contactPoint": {
        "@type": "ContactPoint",
        "contactType": "customer service",
        "areaServed": {
          "@type": "City",
          "name": "Химки",
          "sameAs": "https://ru.wikipedia.org/wiki/Химки"
        },
        "availableLanguage": ["Russian"]
      },
      "areaServed": {
        "@type": "City",
        "name": "Химки",
        "sameAs": "https://ru.wikipedia.org/wiki/Химки"
      },
      "openingHoursSpecification": {
        "@type": "OpeningHoursSpecification",
        "dayOfWeek": [
          "Monday", "Tuesday", "Wednesday", "Thursday",
          "Friday", "Saturday", "Sunday"
        ],
        "opens": "00:00",
        "closes": "23:59"
      }
    },
    {
      "@type": "AboutPage",
      "@id": "https://prostitutkikhimki.com/o-nas/#webpage",
      "url": "https://prostitutkikhimki.com/o-nas/",
      "name": "{{page_title}}",
      "description": "{{page_description}}",
      "inLanguage": "ru-RU",
      "isPartOf": {
        "@id": "https://prostitutkikhimki.com/#website"
      },
      "about": {
        "@id": "https://prostitutkikhimki.com/#organization"
      },
      "mainEntity": {
        "@id": "https://prostitutkikhimki.com/#organization"
      },
      "breadcrumb": {
        "@id": "https://prostitutkikhimki.com/o-nas/#breadcrumb"
      },
      "datePublished": "{{date_published}}",
      "dateModified": "{{date_modified}}"
    },
    {
      "@type": "BreadcrumbList",
      "@id": "https://prostitutkikhimki.com/o-nas/#breadcrumb",
      "itemListElement": [
        {
          "@type": "ListItem",
          "position": 1,
          "name": "Индивидуалки Химки",
          "item": "https://prostitutkikhimki.com/"
        },
        {
          "@type": "ListItem",
          "position": 2,
          "name": "О нас",
          "item": "https://prostitutkikhimki.com/o-nas/"
        }
      ]
    }
  ]
}
```

**Переменные:**

| Переменная | Источник данных |
|------------|-----------------|
| `{{page_title}}` | SEO title страницы «О нас» |
| `{{page_description}}` | Meta description страницы |
| `{{date_published}}` | Дата первой публикации (ISO 8601) |
| `{{date_modified}}` | Дата последнего обновления (ISO 8601) |

> **Примечание:** Organization на этой странице содержит расширенные данные (foundingDate, address, openingHours). На остальных страницах Organization сокращённый.

---

### 3.11 FAQ

**URL:** `https://prostitutkikhimki.com/faq/`
**Описание:** Trust Page. Страница с вопросами и ответами.

```json
{
  "@context": "https://schema.org",
  "@graph": [
    {
      "@type": "WebSite",
      "@id": "https://prostitutkikhimki.com/#website",
      "url": "https://prostitutkikhimki.com/",
      "name": "prostitutkikhimki.com",
      "inLanguage": "ru-RU",
      "publisher": {
        "@id": "https://prostitutkikhimki.com/#organization"
      }
    },
    {
      "@type": "Organization",
      "@id": "https://prostitutkikhimki.com/#organization",
      "name": "prostitutkikhimki.com",
      "url": "https://prostitutkikhimki.com/",
      "logo": {
        "@type": "ImageObject",
        "url": "https://prostitutkikhimki.com/images/logo.svg"
      }
    },
    {
      "@type": "FAQPage",
      "@id": "https://prostitutkikhimki.com/faq/#webpage",
      "url": "https://prostitutkikhimki.com/faq/",
      "name": "{{page_title}}",
      "description": "{{page_description}}",
      "inLanguage": "ru-RU",
      "isPartOf": {
        "@id": "https://prostitutkikhimki.com/#website"
      },
      "breadcrumb": {
        "@id": "https://prostitutkikhimki.com/faq/#breadcrumb"
      },
      "mainEntity": [
        {
          "@type": "Question",
          "name": "{{faq_question}}",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "{{faq_answer_html}}"
          }
        }
        /* повторяется для каждого вопроса */
      ]
    },
    {
      "@type": "BreadcrumbList",
      "@id": "https://prostitutkikhimki.com/faq/#breadcrumb",
      "itemListElement": [
        {
          "@type": "ListItem",
          "position": 1,
          "name": "Индивидуалки Химки",
          "item": "https://prostitutkikhimki.com/"
        },
        {
          "@type": "ListItem",
          "position": 2,
          "name": "FAQ",
          "item": "https://prostitutkikhimki.com/faq/"
        }
      ]
    }
  ]
}
```

**Переменные:**

| Переменная | Источник данных |
|------------|-----------------|
| `{{page_title}}` | SEO title страницы FAQ |
| `{{page_description}}` | Meta description страницы FAQ |
| `{{faq_question}}` | Текст вопроса. Обычный текст, без HTML |
| `{{faq_answer_html}}` | HTML-текст ответа. Может содержать `<p>`, `<ul>`, `<li>`, `<a>`, `<b>`. Экранировать кавычки |

> **Примечание:** Rich results для FAQ не покажутся (adult-домен), но FAQPage полезна для AI Overviews — страницы с такой разметкой попадают туда в 3,2 раза чаще.

---

### 3.12 Политика конфиденциальности

**URL:** `https://prostitutkikhimki.com/privacy/`
**Описание:** Юридический документ.

```json
{
  "@context": "https://schema.org",
  "@graph": [
    {
      "@type": "WebSite",
      "@id": "https://prostitutkikhimki.com/#website",
      "url": "https://prostitutkikhimki.com/",
      "name": "prostitutkikhimki.com",
      "inLanguage": "ru-RU",
      "publisher": {
        "@id": "https://prostitutkikhimki.com/#organization"
      }
    },
    {
      "@type": "Organization",
      "@id": "https://prostitutkikhimki.com/#organization",
      "name": "prostitutkikhimki.com",
      "url": "https://prostitutkikhimki.com/",
      "logo": {
        "@type": "ImageObject",
        "url": "https://prostitutkikhimki.com/images/logo.svg"
      }
    },
    {
      "@type": "WebPage",
      "@id": "https://prostitutkikhimki.com/privacy/#webpage",
      "url": "https://prostitutkikhimki.com/privacy/",
      "name": "{{page_title}}",
      "description": "{{page_description}}",
      "inLanguage": "ru-RU",
      "isPartOf": {
        "@id": "https://prostitutkikhimki.com/#website"
      },
      "about": {
        "@id": "https://prostitutkikhimki.com/#organization"
      },
      "mainEntity": {
        "@id": "https://prostitutkikhimki.com/privacy/#legal-document"
      },
      "breadcrumb": {
        "@id": "https://prostitutkikhimki.com/privacy/#breadcrumb"
      },
      "datePublished": "{{date_published}}",
      "dateModified": "{{date_modified}}"
    },
    {
      "@type": "DigitalDocument",
      "@id": "https://prostitutkikhimki.com/privacy/#legal-document",
      "name": "{{document_name}}",
      "publisher": {
        "@id": "https://prostitutkikhimki.com/#organization"
      },
      "datePublished": "{{date_published}}",
      "dateModified": "{{date_modified}}",
      "inLanguage": "ru-RU",
      "fileFormat": "text/html"
    },
    {
      "@type": "BreadcrumbList",
      "@id": "https://prostitutkikhimki.com/privacy/#breadcrumb",
      "itemListElement": [
        {
          "@type": "ListItem",
          "position": 1,
          "name": "Индивидуалки Химки",
          "item": "https://prostitutkikhimki.com/"
        },
        {
          "@type": "ListItem",
          "position": 2,
          "name": "Политика конфиденциальности",
          "item": "https://prostitutkikhimki.com/privacy/"
        }
      ]
    }
  ]
}
```

**Переменные:**

| Переменная | Источник данных |
|------------|-----------------|
| `{{page_title}}` | SEO title страницы |
| `{{page_description}}` | Meta description страницы |
| `{{document_name}}` | Название документа, например: `"Политика конфиденциальности prostitutkikhimki.com"` |
| `{{date_published}}` | Дата первой публикации (ISO 8601) |
| `{{date_modified}}` | Дата последнего обновления (ISO 8601) |

---

### 3.13 Условия использования

**URL:** `https://prostitutkikhimki.com/terms/`
**Описание:** Юридический документ — пользовательское соглашение.

```json
{
  "@context": "https://schema.org",
  "@graph": [
    {
      "@type": "WebSite",
      "@id": "https://prostitutkikhimki.com/#website",
      "url": "https://prostitutkikhimki.com/",
      "name": "prostitutkikhimki.com",
      "inLanguage": "ru-RU",
      "publisher": {
        "@id": "https://prostitutkikhimki.com/#organization"
      }
    },
    {
      "@type": "Organization",
      "@id": "https://prostitutkikhimki.com/#organization",
      "name": "prostitutkikhimki.com",
      "url": "https://prostitutkikhimki.com/",
      "logo": {
        "@type": "ImageObject",
        "url": "https://prostitutkikhimki.com/images/logo.svg"
      }
    },
    {
      "@type": "WebPage",
      "@id": "https://prostitutkikhimki.com/terms/#webpage",
      "url": "https://prostitutkikhimki.com/terms/",
      "name": "{{page_title}}",
      "description": "{{page_description}}",
      "inLanguage": "ru-RU",
      "isPartOf": {
        "@id": "https://prostitutkikhimki.com/#website"
      },
      "about": {
        "@id": "https://prostitutkikhimki.com/#organization"
      },
      "mainEntity": {
        "@id": "https://prostitutkikhimki.com/terms/#legal-document"
      },
      "breadcrumb": {
        "@id": "https://prostitutkikhimki.com/terms/#breadcrumb"
      },
      "datePublished": "{{date_published}}",
      "dateModified": "{{date_modified}}"
    },
    {
      "@type": "DigitalDocument",
      "@id": "https://prostitutkikhimki.com/terms/#legal-document",
      "name": "{{document_name}}",
      "publisher": {
        "@id": "https://prostitutkikhimki.com/#organization"
      },
      "datePublished": "{{date_published}}",
      "dateModified": "{{date_modified}}",
      "inLanguage": "ru-RU",
      "fileFormat": "text/html"
    },
    {
      "@type": "BreadcrumbList",
      "@id": "https://prostitutkikhimki.com/terms/#breadcrumb",
      "itemListElement": [
        {
          "@type": "ListItem",
          "position": 1,
          "name": "Индивидуалки Химки",
          "item": "https://prostitutkikhimki.com/"
        },
        {
          "@type": "ListItem",
          "position": 2,
          "name": "Условия использования",
          "item": "https://prostitutkikhimki.com/terms/"
        }
      ]
    }
  ]
}
```

**Переменные:**

| Переменная | Источник данных |
|------------|-----------------|
| `{{page_title}}` | SEO title страницы |
| `{{page_description}}` | Meta description страницы |
| `{{document_name}}` | Название документа, например: `"Условия использования prostitutkikhimki.com"` |
| `{{date_published}}` | Дата первой публикации (ISO 8601) |
| `{{date_modified}}` | Дата последнего обновления (ISO 8601) |

---

### 3.14 Карта сайта

**URL:** `https://prostitutkikhimki.com/sitemap/`
**Описание:** HTML-карта сайта для навигации.

```json
{
  "@context": "https://schema.org",
  "@graph": [
    {
      "@type": "WebSite",
      "@id": "https://prostitutkikhimki.com/#website",
      "url": "https://prostitutkikhimki.com/",
      "name": "prostitutkikhimki.com",
      "inLanguage": "ru-RU",
      "publisher": {
        "@id": "https://prostitutkikhimki.com/#organization"
      }
    },
    {
      "@type": "Organization",
      "@id": "https://prostitutkikhimki.com/#organization",
      "name": "prostitutkikhimki.com",
      "url": "https://prostitutkikhimki.com/",
      "logo": {
        "@type": "ImageObject",
        "url": "https://prostitutkikhimki.com/images/logo.svg"
      }
    },
    {
      "@type": "CollectionPage",
      "@id": "https://prostitutkikhimki.com/sitemap/#webpage",
      "url": "https://prostitutkikhimki.com/sitemap/",
      "name": "{{page_title}}",
      "description": "{{page_description}}",
      "inLanguage": "ru-RU",
      "isPartOf": {
        "@id": "https://prostitutkikhimki.com/#website"
      },
      "breadcrumb": {
        "@id": "https://prostitutkikhimki.com/sitemap/#breadcrumb"
      },
      "mainEntity": {
        "@id": "https://prostitutkikhimki.com/sitemap/#navigation-list"
      }
    },
    {
      "@type": "ItemList",
      "@id": "https://prostitutkikhimki.com/sitemap/#navigation-list",
      "name": "{{list_title}}",
      "itemListOrder": "https://schema.org/ItemListOrderAscending",
      "numberOfItems": {{sections_total_count}},
      "itemListElement": [
        {
          "@type": "ListItem",
          "position": {{section_position}},
          "name": "{{section_name}}",
          "url": "{{section_url}}"
        }
        /* повторяется для каждого раздела */
      ]
    },
    {
      "@type": "BreadcrumbList",
      "@id": "https://prostitutkikhimki.com/sitemap/#breadcrumb",
      "itemListElement": [
        {
          "@type": "ListItem",
          "position": 1,
          "name": "Индивидуалки Химки",
          "item": "https://prostitutkikhimki.com/"
        },
        {
          "@type": "ListItem",
          "position": 2,
          "name": "Карта сайта",
          "item": "https://prostitutkikhimki.com/sitemap/"
        }
      ]
    }
  ]
}
```

**Переменные:**

| Переменная | Источник данных |
|------------|-----------------|
| `{{page_title}}` | SEO title страницы |
| `{{page_description}}` | Meta description страницы |
| `{{list_title}}` | Заголовок списка, например: `"Разделы сайта"` |
| `{{sections_total_count}}` | Количество разделов (число) |
| `{{section_position}}` | Порядковый номер раздела (начиная с 1) |
| `{{section_name}}` | Название раздела, например: `"Проверенные"` |
| `{{section_url}}` | URL раздела, например: `https://prostitutkikhimki.com/proverennye/` |

---

## 4. Справочник переменных

| Переменная | Тип | Шаблоны | Источник |
|------------|-----|---------|----------|
| `{{article_cover_caption}}` | string | 3.9 | Alt-текст обложки из медиатеки |
| `{{article_cover_height}}` | number | 3.9 | Высота обложки (px) из метаданных |
| `{{article_cover_image_url}}` | string (URL) | 3.8, 3.9 | URL обложки из медиатеки |
| `{{article_cover_width}}` | number | 3.9 | Ширина обложки (px) из метаданных |
| `{{article_date_modified}}` | string (ISO 8601) | 3.9 | Поле `updated_at` статьи |
| `{{article_date_published}}` | string (ISO 8601) | 3.8, 3.9 | Поле `published_at` статьи |
| `{{article_excerpt}}` | string | 3.8, 3.9 | Поле `excerpt` статьи |
| `{{article_headline}}` | string | 3.8, 3.9 | Поле `title` статьи (H1) |
| `{{article_keywords_array}}` | string[] (JSON) | 3.9 | Поле `tags`. Формат: `"ключ1", "ключ2"` |
| `{{article_position}}` | number | 3.8 | Инкремент в цикле (с 1) |
| `{{article_url}}` | string (URL) | 3.8, 3.9 | `SITE_URL + "blog/" + slug + "/"` |
| `{{articles_total_count}}` | number | 3.8 | `COUNT(*)` статей |
| `{{breadcrumb_article_name}}` | string | 3.9 | Короткое название для крошек |
| `{{breadcrumb_name}}` | string | 3.2 | Название категории моделей |
| `{{date_modified}}` | string (ISO 8601) | 3.1–3.6, 3.10–3.13 | Поле `updated_at` страницы |
| `{{date_published}}` | string (ISO 8601) | 3.2–3.6, 3.10–3.13 | Поле `created_at` страницы |
| `{{district_name}}` | string | 3.5, 3.6 | Название района из БД |
| `{{district_position}}` | number | 3.5 | Инкремент в цикле |
| `{{district_url}}` | string (URL) | 3.5 | `SITE_URL + "rajony/" + slug + "/"` |
| `{{districts_total_count}}` | number | 3.5 | `COUNT(*)` районов |
| `{{document_name}}` | string | 3.12, 3.13 | Название юридического документа |
| `{{faq_answer_html}}` | string (HTML) | 3.11 | Поле `answer` в таблице FAQ |
| `{{faq_question}}` | string | 3.11 | Поле `question` в таблице FAQ |
| `{{list_description}}` | string | 3.1–3.5 | Описание списка |
| `{{list_title}}` | string | 3.1–3.5, 3.8, 3.14 | Заголовок списка |
| `{{model_about}}` | string | 3.7 | Поле `about`/`description` модели |
| `{{model_age}}` | number | 3.1, 3.2 (caption) | Поле `age` модели |
| `{{model_district}}` | string | 3.1, 3.2 (caption), 3.7 | Поле `district` модели |
| `{{model_height}}` | number | 3.7 | Поле `height` (см) |
| `{{model_image_caption}}` | string | 3.1, 3.2, 3.4, 3.6 | Генерируется: `"{{model_name}}, {{model_age}}, {{model_district}}"` |
| `{{model_image_url}}` | string (URL) | 3.1, 3.2, 3.4, 3.6 | Поле `main_image` модели |
| `{{model_images_array}}` | string[] (JSON) | 3.7 | Все фото: `"url1", "url2"` |
| `{{model_main_image_url}}` | string (URL) | 3.7 | Первое фото из `images` |
| `{{model_name}}` | string | 3.1, 3.2, 3.4, 3.6, 3.7 | Поле `name` модели |
| `{{model_position}}` | number | 3.1, 3.2, 3.4, 3.6 | Инкремент в цикле |
| `{{model_url}}` | string (URL) | 3.1, 3.2, 3.4, 3.6 | `SITE_URL + "model/id-" + id + "/"` |
| `{{model_weight}}` | number | 3.7 | Поле `weight` (кг) |
| `{{models_total_count}}` | number | 3.1, 3.2, 3.4, 3.6 | Количество моделей в выборке |
| `{{page_description}}` | string | все | Meta description из CMS |
| `{{page_title}}` | string | все | SEO title из CMS (`<title>`) |
| `{{page_url}}` | string (URL) | 3.2, 3.4, 3.6, 3.7, 3.9 | Канонический URL страницы |
| `{{section_name}}` | string | 3.14 | Название раздела в карте сайта |
| `{{section_position}}` | number | 3.14 | Порядковый номер раздела |
| `{{section_url}}` | string (URL) | 3.14 | URL раздела |
| `{{sections_total_count}}` | number | 3.14 | Количество разделов |
| `{{service_name}}` | string | 3.3, 3.4 | Название услуги из БД |
| `{{service_position}}` | number | 3.3 | Инкремент в цикле |
| `{{service_url}}` | string (URL) | 3.3 | `SITE_URL + "uslugi/" + slug + "/"` |
| `{{services_total_count}}` | number | 3.3 | `COUNT(*)` услуг |
