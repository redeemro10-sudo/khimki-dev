<?php

namespace App\Support;

use WP_Term;

class GoneUrls
{
    private const PAGE_PATHS = [
        'bust-size',
        'individualki',
        'bdsm',
        'kontakty',
    ];

    private const TAXONOMY_BASES = [
        'feature' => 'features',
        'hair_color' => 'hair-color',
        'aye_color' => 'aye-color',
        'nationality' => 'nationalities',
        'bust_size' => 'bust-size',
        'physique' => 'physique',
        'intimate_haircut' => 'intimate-haircut',
        'extreme_services' => 'extreme-services',
        'sado_maso' => 'bdsm',
    ];

    private const TAXONOMY_TERMS = [
        'feature' => ['vip', 'proverennyye'],
        'hair_color' => ['blondinka', 'bryunetka', 'rusyye', 'ryzhaya', 'shatenka'],
        'aye_color' => ['goluboy', 'zelonyy', 'kariy', 'seryy'],
        'nationality' => ['aziatka', 'kazashka', 'russkaya', 'tatarka', 'uzbechka'],
        'bust_size' => ['1', '2', '3', '4'],
        'physique' => ['polnaya', 'sportivnaya', 'stroynaya', 'khudaya'],
        'intimate_haircut' => ['akkuratnaya-strizhka', 'naturalnaya-strizhka', 'polnaya-depilyatsiya'],
        'extreme_services' => [
            'anilingus-delayu',
            'anilingus-prinimayu',
            'zolotoy-dozhd-vydacha',
            'zolotoy-dozhd-priyem',
            'strapon',
            'fisting-analnyy',
            'fisting-klassicheskiy',
        ],
        'sado_maso' => ['bondazh', 'gospozha', 'legkaya-dominatsiya', 'porka', 'rabynya', 'trampling', 'fetish'],
    ];

    public static function isGoneRequest(): bool
    {
        $requestUri = isset($_SERVER['REQUEST_URI']) ? (string) $_SERVER['REQUEST_URI'] : '/';
        $path = (string) parse_url($requestUri, PHP_URL_PATH);

        return in_array(self::normalizePath($path), self::exactPaths(), true);
    }

    public static function isGonePageId(int $pageId): bool
    {
        if ($pageId <= 0) {
            return false;
        }

        foreach (self::pagePaths() as $path) {
            $page = get_page_by_path($path);

            if ($page && (int) $page->ID === $pageId) {
                return true;
            }
        }

        return false;
    }

    public static function isGoneTerm(WP_Term $term): bool
    {
        return in_array($term->slug, self::termSlugs($term->taxonomy), true);
    }

    public static function excludedPageIds(): array
    {
        $ids = [];

        foreach (self::pagePaths() as $path) {
            $page = get_page_by_path($path);
            if ($page) {
                $ids[] = (int) $page->ID;
            }
        }

        return array_values(array_unique(array_filter($ids)));
    }

    public static function excludedTermIds(string $taxonomy): array
    {
        $ids = [];

        foreach (self::termSlugs($taxonomy) as $slug) {
            $term = get_term_by('slug', $slug, $taxonomy);
            if ($term instanceof WP_Term) {
                $ids[] = (int) $term->term_id;
            }
        }

        return array_values(array_unique(array_filter($ids)));
    }

    public static function pagePaths(): array
    {
        return self::PAGE_PATHS;
    }

    public static function exactPaths(): array
    {
        $paths = array_map([self::class, 'normalizePath'], self::pagePaths());

        foreach (self::TAXONOMY_TERMS as $taxonomy => $slugs) {
            $base = self::TAXONOMY_BASES[$taxonomy] ?? null;
            if (!$base) {
                continue;
            }

            foreach ($slugs as $slug) {
                $paths[] = self::normalizePath($base . '/' . $slug);
            }
        }

        return array_values(array_unique($paths));
    }

    private static function termSlugs(string $taxonomy): array
    {
        return self::TAXONOMY_TERMS[$taxonomy] ?? [];
    }

    private static function normalizePath(string $path): string
    {
        $normalized = '/' . trim($path, '/');

        return $normalized === '/' ? $normalized : rtrim($normalized, '/');
    }
}
