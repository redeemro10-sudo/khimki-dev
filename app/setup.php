<?php

/**
 * Theme setup.
 */

namespace App;

use Illuminate\Support\Facades\Vite;

function get_faq_items(int $postId): array
{
    $items = get_post_meta($postId, '_faq_items', true);

    if (! is_array($items)) {
        return [];
    }

    return array_values(array_filter(array_map(static function ($item) {
        if (! is_array($item)) {
            return null;
        }

        $question = isset($item['question']) ? sanitize_text_field($item['question']) : '';
        $answer = isset($item['answer']) ? wp_kses_post($item['answer']) : '';

        if ($question === '' || trim(wp_strip_all_tags($answer)) === '') {
            return null;
        }

        return [
            'question' => $question,
            'answer' => $answer,
        ];
    }, $items)));
}

/**
 * Inject styles into the block editor.
 *
 * @return array
 */
add_filter('block_editor_settings_all', function ($settings) {
    $style = Vite::asset('resources/css/editor.css');

    $settings['styles'][] = [
        'css' => "@import url('{$style}')",
    ];

    return $settings;
});

/**
 * Inject scripts into the block editor.
 *
 * @return void
 */
add_filter('admin_head', function () {
    if (! get_current_screen()?->is_block_editor()) {
        return;
    }

    $dependencies = json_decode(Vite::content('editor.deps.json'));

    foreach ($dependencies as $dependency) {
        if (! wp_script_is($dependency)) {
            wp_enqueue_script($dependency);
        }
    }

    echo Vite::withEntryPoints([
        'resources/js/editor.js',
    ])->toHtml();
});

/**
 * Use the generated theme.json file.
 *
 * @return string
 */
add_filter('theme_file_path', function ($path, $file) {
    return $file === 'theme.json'
        ? public_path('build/assets/theme.json')
        : $path;
}, 10, 2);

/**
 * Register the initial theme setup.
 *
 * @return void
 */
add_action('after_setup_theme', function () {
    /**
     * Disable full-site editing support.
     *
     * @link https://wptavern.com/gutenberg-10-5-embeds-pdfs-adds-verse-block-color-options-and-introduces-new-patterns
     */
    remove_theme_support('block-templates');

    /**
     * Register the navigation menus.
     *
     * @link https://developer.wordpress.org/reference/functions/register_nav_menus/
     */
    register_nav_menus([
        'primary_navigation' => __('Primary Navigation', 'sage'),
        'footer_navigation'  => __('Footer: Sections', 'sage'),
        'footer_services'    => __('Footer: Escorts / Services', 'sage'),
        'footer_community'   => __('Footer: Information', 'sage'),
        'footer_legal'       => __('Footer: Legal Information', 'sage'),
        'footer_locations'   => __('Footer: Cities / Popular', 'sage'),
    ]);

    /**
     * Disable the default block patterns.
     *
     * @link https://developer.wordpress.org/block-editor/developers/themes/theme-support/#disabling-the-default-block-patterns
     */
    remove_theme_support('core-block-patterns');

    /**
     * Enable plugins to manage the document title.
     *
     * @link https://developer.wordpress.org/reference/functions/add_theme_support/#title-tag
     */
    add_theme_support('title-tag');

    /**
     * Enable post thumbnail support.
     *
     * @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
     */
    add_theme_support('post-thumbnails');

    /**
     * Enable responsive embed support.
     *
     * @link https://developer.wordpress.org/block-editor/how-to-guides/themes/theme-support/#responsive-embedded-content
     */
    add_theme_support('responsive-embeds');

    /**
     * Enable HTML5 markup support.
     *
     * @link https://developer.wordpress.org/reference/functions/add_theme_support/#html5
     */
    add_theme_support('html5', [
        'caption',
        'comment-form',
        'comment-list',
        'gallery',
        'search-form',
        'script',
        'style',
    ]);

    /**
     * Enable selective refresh for widgets in customizer.
     *
     * @link https://developer.wordpress.org/reference/functions/add_theme_support/#customize-selective-refresh-widgets
     */
    add_theme_support('customize-selective-refresh-widgets');
}, 20);

/**
 * Register the theme sidebars.
 *
 * @return void
 */
add_action('widgets_init', function () {
    $config = [
        'before_widget' => '<section class="widget %1$s %2$s">',
        'after_widget' => '</section>',
        'before_title' => '<h3>',
        'after_title' => '</h3>',
    ];

    register_sidebar([
        'name' => __('Primary', 'sage'),
        'id' => 'sidebar-primary',
    ] + $config);

    register_sidebar([
        'name' => __('Footer', 'sage'),
        'id' => 'sidebar-footer',
    ] + $config);
});

add_action('add_meta_boxes_page', function ($post) {
    if (! $post instanceof \WP_Post) {
        return;
    }

    if (get_page_template_slug($post->ID) !== 'template-faq.blade.php') {
        return;
    }

    add_meta_box(
        'faq_items_metabox',
        __('FAQ Questions', 'sage'),
        function (\WP_Post $post) {
            $faqItems = get_faq_items($post->ID);

            if ($faqItems === []) {
                $faqItems = [['question' => '', 'answer' => '']];
            }

            wp_nonce_field('save_faq_items', 'faq_items_nonce');
            ?>
            <div id="faq-items-metabox" class="faq-items-metabox">
                <p style="margin-bottom:12px;">
                    <?php esc_html_e('Add and sort questions for the FAQ page template. Empty rows are not saved.', 'sage'); ?>
                </p>

                <div class="faq-items-list" style="display:grid;gap:12px;">
                    <?php foreach ($faqItems as $index => $item) : ?>
                        <div class="faq-item" style="border:1px solid #dcdcde;border-radius:10px;padding:12px;background:#fff;">
                            <p style="margin:0 0 8px;">
                                <label style="display:block;font-weight:600;margin-bottom:6px;">
                                    <?php esc_html_e('Question', 'sage'); ?>
                                </label>
                                <input type="text" name="faq_items[<?php echo esc_attr((string) $index); ?>][question]" value="<?php echo esc_attr($item['question']); ?>" style="width:100%;">
                            </p>
                            <p style="margin:0 0 10px;">
                                <label style="display:block;font-weight:600;margin-bottom:6px;">
                                    <?php esc_html_e('Answer', 'sage'); ?>
                                </label>
                                <textarea name="faq_items[<?php echo esc_attr((string) $index); ?>][answer]" rows="5" style="width:100%;"><?php echo esc_textarea($item['answer']); ?></textarea>
                            </p>
                            <div style="display:flex;gap:8px;">
                                <button type="button" class="button faq-move-up"><?php esc_html_e('Up', 'sage'); ?></button>
                                <button type="button" class="button faq-move-down"><?php esc_html_e('Down', 'sage'); ?></button>
                                <button type="button" class="button button-link-delete faq-remove"><?php esc_html_e('Remove', 'sage'); ?></button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <p style="margin-top:12px;">
                    <button type="button" class="button button-secondary faq-add-item">
                        <?php esc_html_e('Add question', 'sage'); ?>
                    </button>
                </p>
            </div>

            <template id="faq-item-template">
                <div class="faq-item" style="border:1px solid #dcdcde;border-radius:10px;padding:12px;background:#fff;">
                    <p style="margin:0 0 8px;">
                        <label style="display:block;font-weight:600;margin-bottom:6px;">
                            <?php esc_html_e('Question', 'sage'); ?>
                        </label>
                        <input type="text" data-name="question" style="width:100%;">
                    </p>
                    <p style="margin:0 0 10px;">
                        <label style="display:block;font-weight:600;margin-bottom:6px;">
                            <?php esc_html_e('Answer', 'sage'); ?>
                        </label>
                        <textarea data-name="answer" rows="5" style="width:100%;"></textarea>
                    </p>
                    <div style="display:flex;gap:8px;">
                        <button type="button" class="button faq-move-up"><?php esc_html_e('Up', 'sage'); ?></button>
                        <button type="button" class="button faq-move-down"><?php esc_html_e('Down', 'sage'); ?></button>
                        <button type="button" class="button button-link-delete faq-remove"><?php esc_html_e('Remove', 'sage'); ?></button>
                    </div>
                </div>
            </template>

            <script>
                document.addEventListener('DOMContentLoaded', function () {
                    const metabox = document.getElementById('faq-items-metabox');
                    const template = document.getElementById('faq-item-template');

                    if (!metabox || !template || metabox.dataset.ready === 'true') {
                        return;
                    }

                    metabox.dataset.ready = 'true';

                    const list = metabox.querySelector('.faq-items-list');
                    const addButton = metabox.querySelector('.faq-add-item');

                    const reindex = () => {
                        [...list.querySelectorAll('.faq-item')].forEach((item, index) => {
                            item.querySelectorAll('[name]').forEach((field) => {
                                const match = field.name.match(/\[(question|answer)\]$/);
                                if (!match) {
                                    return;
                                }

                                field.name = `faq_items[${index}][${match[1]}]`;
                            });
                        });
                    };

                    const buildItem = () => {
                        const fragment = template.content.cloneNode(true);
                        const item = fragment.querySelector('.faq-item');

                        item.querySelectorAll('[data-name]').forEach((field) => {
                            const name = field.getAttribute('data-name');
                            field.setAttribute('name', `faq_items[0][${name}]`);
                            field.removeAttribute('data-name');
                        });

                        return item;
                    };

                    addButton.addEventListener('click', () => {
                        list.appendChild(buildItem());
                        reindex();
                    });

                    list.addEventListener('click', (event) => {
                        const item = event.target.closest('.faq-item');

                        if (!item) {
                            return;
                        }

                        if (event.target.classList.contains('faq-remove')) {
                            item.remove();

                            if (!list.children.length) {
                                list.appendChild(buildItem());
                            }

                            reindex();
                        }

                        if (event.target.classList.contains('faq-move-up')) {
                            const previous = item.previousElementSibling;
                            if (previous) {
                                list.insertBefore(item, previous);
                                reindex();
                            }
                        }

                        if (event.target.classList.contains('faq-move-down')) {
                            const next = item.nextElementSibling;
                            if (next) {
                                list.insertBefore(next, item);
                                reindex();
                            }
                        }
                    });
                });
            </script>
            <?php
        },
        'page',
        'normal',
        'default'
    );
});

add_action('save_post_page', function ($postId) {
    if (! isset($_POST['faq_items_nonce']) || ! wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['faq_items_nonce'])), 'save_faq_items')) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (! current_user_can('edit_post', $postId)) {
        return;
    }

    if (get_page_template_slug($postId) !== 'template-faq.blade.php') {
        delete_post_meta($postId, '_faq_items');
        return;
    }

    $rawItems = isset($_POST['faq_items']) ? wp_unslash($_POST['faq_items']) : [];

    if (! is_array($rawItems)) {
        delete_post_meta($postId, '_faq_items');
        return;
    }

    $items = array_values(array_filter(array_map(static function ($item) {
        if (! is_array($item)) {
            return null;
        }

        $question = isset($item['question']) ? sanitize_text_field($item['question']) : '';
        $answer = isset($item['answer']) ? wp_kses_post($item['answer']) : '';

        if ($question === '' || trim(wp_strip_all_tags($answer)) === '') {
            return null;
        }

        return [
            'question' => $question,
            'answer' => $answer,
        ];
    }, $rawItems)));

    if ($items === []) {
        delete_post_meta($postId, '_faq_items');
        return;
    }

    update_post_meta($postId, '_faq_items', $items);
});

add_action('after_setup_theme', function () {
    add_theme_support('post-thumbnails');
    add_image_size('post-card', 640, 480, true); // 4:3 под карточки
});

add_filter('excerpt_length', function ($length) {
    return 18; // короткая превьюшка
}, 999);

add_filter('excerpt_more', function () {
    return '…';
});

add_filter('document_title_parts', function ($parts) {
    if (is_post_type_archive('blog')) {
        if ($p = get_page_by_path('blog-seo')) {
            $t = (string) get_post_meta($p->ID, '_seo_title', true);
            if ($t !== '') {
                $parts['title'] = $t;
            }
        }
    }
    return $parts;
});
add_action('wp_head', function () {
    if (is_post_type_archive('blog')) {
        if ($p = get_page_by_path('blog-seo')) {
            $d = (string) get_post_meta($p->ID, '_seo_description', true);
            if ($d !== '') {
                echo '<meta name="description" content="' . esc_attr($d) . "\" />\n";
            }
        }
    }
}, 1);

StructuredData::boot();

// <title> Имя + цвет волос + возраст + национальность
add_filter('document_title_parts', function ($parts) {
    if (!is_singular('model')) {
        return $parts;
    }
    $name = get_the_title() ?: '';
    $hair = (wp_get_post_terms(get_the_ID(), 'hair_color', ['fields' => 'names']))[0] ?? null;
    $age  = (int) get_post_meta(get_the_ID(), 'age', true);
    $nat  = (wp_get_post_terms(get_the_ID(), 'nationality', ['fields' => 'names']))[0] ?? null;
    $bits = array_filter([$name, $hair, $age ? $age . ' лет' : null, $nat]);
    $parts['title'] = implode(' · ', $bits);
    return $parts;
});

// <meta name="description"> первые 150 символов без обрыва слова
add_action('wp_head', function () {
    if (!is_singular('model')) {
        return;
    }
    $raw = wp_strip_all_tags(get_the_content());
    $raw = preg_replace('~\s+~u', ' ', $raw);
    $limit = 150;
    if (mb_strlen($raw) > $limit) {
        $cut = mb_substr($raw, 0, $limit);
        $rest = mb_substr($raw, $limit);
        if (preg_match('~^\S+\b~u', $rest, $m)) {
            $cut .= $m[0];
        }
        $raw = rtrim($cut) . '…';
    }
    echo '<meta name="description" content="' . esc_attr($raw) . '">' . "\n";
}, 5);

add_action('wp_enqueue_scripts', function () {
    // Только фронт (не админка, не редактор)
    if (is_admin()) {
        return;
    }

    // Основные стили блоков
    wp_dequeue_style('wp-block-library');
    wp_deregister_style('wp-block-library');

    // Тема блоков и классическая тема
    wp_dequeue_style('wp-block-library-theme');
    wp_deregister_style('wp-block-library-theme');
    wp_dequeue_style('classic-theme-styles');
    wp_deregister_style('classic-theme-styles');

    // Глобальные стили (theme.json) + SVG-фильтры дуотона
    wp_dequeue_style('global-styles');
    wp_deregister_style('global-styles');
}, 100);

// На всякий случай убираем инлайновые global styles и SVG-фильтры дуотона
remove_action('wp_enqueue_scripts', 'wp_enqueue_global_styles');
remove_action('wp_body_open', 'wp_global_styles_render_svg_filters');

add_filter('the_generator', '__return_empty_string');
add_filter('wp_speculation_rules_configuration', '__return_null');

remove_action('do_feed_rdf', 'do_feed_rdf', 10, 1);
remove_action('do_feed_rss', 'do_feed_rss', 10, 1);
remove_action('do_feed_rss2', 'do_feed_rss2', 10, 1);
remove_action('do_feed_atom', 'do_feed_atom', 10, 1);
remove_action('wp_head', 'rest_output_link_wp_head', 10);

remove_action('wp_head', 'wp_oembed_add_discovery_links');
remove_action('wp_head', 'wp_oembed_add_host_js');
remove_action('wp_head', 'wlwmanifest_link');

remove_action('wp_head', 'feed_links_extra', 3);
remove_action('wp_head', 'feed_links', 2);
remove_action('wp_head', 'adjacent_posts_rel_link_wp_head', 10);
remove_action('wp_head', 'wp_shortlink_wp_head', 10);
remove_action('wp_head', 'wp_resource_hints', 2);

remove_action('wp_head', 'rsd_link');
remove_action('wp_head', 'print_emoji_detection_script', 7);
remove_action('wp_head', 'wp_generator');
remove_action('wp_head', 'wp_site_icon', 99);

add_action('wp_enqueue_scripts', function () {
    wp_dequeue_style('classic-theme-styles');
}, 20);

function disable_emojis()
{
    remove_action('wp_head', 'print_emoji_detection_script', 7);
    remove_action('admin_print_scripts', 'print_emoji_detection_script');
    remove_action('wp_print_styles', 'print_emoji_styles');
    remove_action('admin_print_styles', 'print_emoji_styles');
    remove_filter('the_content_feed', 'wp_staticize_emoji');
    remove_filter('comment_text_rss', 'wp_staticize_emoji');
    remove_filter('wp_mail', 'wp_staticize_emoji_for_email');
    add_filter('tiny_mce_plugins', __NAMESPACE__ . '\\disable_emojis_tinymce');
    add_filter('wp_resource_hints', __NAMESPACE__ . '\\disable_emojis_remove_dns_prefetch', 10, 2);
}
add_action('init', __NAMESPACE__ . '\\disable_emojis');

/**
 * Filter function used to remove the tinymce emoji plugin.
 *
 * @param    array  $plugins
 * @return   array             Difference betwen the two arrays
 */
function disable_emojis_tinymce($plugins)
{
    if (is_array($plugins)) {
        return array_diff($plugins, ['wpemoji']);
    }

    return [];
}

/**
 * Remove emoji CDN hostname from DNS prefetching hints.
 *
 * @param  array  $urls          URLs to print for resource hints.
 * @param  string $relation_type The relation type the URLs are printed for.
 * @return array                 Difference betwen the two arrays.
 */
function disable_emojis_remove_dns_prefetch($urls, $relation_type)
{

    if ('dns-prefetch' == $relation_type) {

        // Strip out any URLs referencing the WordPress.org emoji location
        $emoji_svg_url_bit = 'https://s.w.org/images/core/emoji/';
        foreach ($urls as $key => $url) {
            if (strpos($url, $emoji_svg_url_bit) !== false) {
                unset($urls[$key]);
            }
        }
    }

    return $urls;
}

/**
 * Disable Speculation Rules <script type="speculationrules"> and
 * inline Block Supports CSS <style id="core-block-supports-inline-css">.
 */
add_action('init', function () {
    // 1) Отключаем inline CSS «block supports», если ядро их подключило.
    if (function_exists('wp_enqueue_block_support_styles')) {
        remove_action('wp_enqueue_scripts', 'wp_enqueue_block_support_styles');
        remove_action('wp_print_styles', 'wp_enqueue_block_support_styles');
        remove_action('wp_footer', 'wp_enqueue_block_support_styles');
    }

    // 2) Отключаем вывод Speculation Rules в head (ядро 6.6+ / perf-lab).
    if (function_exists('wp_print_speculation_rules')) {
        remove_action('wp_head', 'wp_print_speculation_rules', 1);
    }
    // Performance Lab / другие плагины могли вешать свой коллбек:
    if (function_exists('\\Performance_Lab\\Speculative_Loading\\print_speculation_rules')) {
        remove_action('wp_head', '\\Performance_Lab\\Speculative_Loading\\print_speculation_rules');
    }
}, 20);


// Фолбэк: если кто-то напечатал — вырежем из <head> (только фронт)
add_action('template_redirect', function () {
    if (is_admin() || wp_doing_ajax() || defined('REST_REQUEST')) {
        return;
    }

    ob_start(function ($html) {
        // speculationrules уже отрубил фильтром — на всякий пожарный тоже чистим
        $html = preg_replace('#<script[^>]+type=["\']speculationrules["\'][^>]*>.*?</script>#is', '', $html, 1);
        // инлайн block supports
        $html = preg_replace('#<style[^>]+id=["\']core-block-supports-inline-css["\'][^>]*>.*?</style>#is', '', $html, 1);
        return $html;
    });
});
add_action('shutdown', function () {
    if (ob_get_level() > 0) {
        @ob_end_flush();
    }
});

add_action('shutdown', function () {
    if (!empty($GLOBALS['__strip_inline_head_bits']) && ob_get_level() > 0) {
        @ob_end_flush();
    }
});

/* add_filter('redis_cache_expiration', function (int $exp, string $key, string $group, $orig) {
  // Твой репозиторий: 600 сек (и так задано в коде, но пусть будет гарантировано)
  if ($group === 'models_repo') return 600;

  // WP запросы по постам — можно уменьшить, например, до 300 сек
  if ($group === 'post-queries') return 300;

  // Критичное, что можно реже обновлять — 3600 сек
  if ($group === 'options' || $group === 'terms') return 3600;

  return $exp; // по умолчанию — как есть
}, 10, 4); */
