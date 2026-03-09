<?php

namespace App\Navigation;

use Walker_Nav_Menu;

class BurgerWalker extends Walker_Nav_Menu
{
    private $current_parent_id = 0;

    public function display_element($element, &$children_elements, $max_depth, $depth = 0, $args = [], &$output = '')
    {
        $element->has_children = !empty($children_elements[$element->ID]);
        parent::display_element($element, $children_elements, $max_depth, $depth, $args, $output);
    }

    public function start_lvl(&$output, $depth = 0, $args = null)
    {
        $indent = str_repeat("\t", $depth + 1);
        $parent_id = $this->current_parent_id;
        $submenu_id = "submenu-{$parent_id}";

        // Возвращаем класс hidden - CSS будет управлять отображением
        $output .= "\n{$indent}<ul id=\"{$submenu_id}\" class=\"submenu-mobile hidden\" data-depth=\"{$depth}\">\n";
    }

    public function end_lvl(&$output, $depth = 0, $args = null)
    {
        $indent = str_repeat("\t", $depth + 1);
        $output .= "{$indent}</ul>\n";
    }

    public function start_el(&$output, $item, $depth = 0, $args = null, $id = 0)
    {
        $indent = str_repeat("\t", $depth + 1);
        $has_children = !empty($item->has_children);

        // Классы для li элемента
        $li_classes = ['menu-item'];

        if ($has_children) {
            $li_classes[] = 'relative';
            $li_classes[] = 'group';
        }

        // WordPress классы
        if (!empty($item->classes)) {
            $li_classes = array_merge($li_classes, array_filter((array) $item->classes));
        }

        // Активные состояния
        if (in_array('current-menu-item', (array) $item->classes)) {
            $li_classes[] = 'current-menu-item';
        }
        if (in_array('current-menu-ancestor', (array) $item->classes)) {
            $li_classes[] = 'current-menu-ancestor';
        }

        $li_class_string = implode(' ', array_unique($li_classes));
        $output .= "{$indent}<li class=\"{$li_class_string}\">\n";

        // Контейнер только для мобильных элементов с подменю
        if ($has_children) {
            $output .= "{$indent}\t<div class=\"flex items-center justify-between lg:block\">\n";
        }

        // Ссылка
        $link_classes = $this->getLinkClasses($depth, $has_children);
        $link_attributes = $this->getLinkAttributes($item, $link_classes, $has_children);
        $link_content = esc_html($item->title);

        // Стрелка для десктопа - встроенная в ссылку
        if ($has_children && $depth === 0) {
            $link_content .= ' <svg class="hidden lg:inline ml-2 h-4 w-4 opacity-60" viewBox="0 0 20 20" fill="currentColor"><path d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.24a.75.75 0 01-1.06 0L5.21 8.27a.75.75 0 01.02-1.06z"/></svg>';
        }

        $output .= "{$indent}\t\t<a{$link_attributes}>{$link_content}</a>\n";

        // Toggle кнопка только для мобильных
        if ($has_children) {
            $submenu_id = "submenu-{$item->ID}";
            $this->current_parent_id = $item->ID;

            $output .= "{$indent}\t\t<button class=\"submenu-toggle lg:hidden flex-shrink-0 p-2 text-gray-400 hover:text-gray-600 rounded-lg transition-colors\" ";
            $output .= "type=\"button\" aria-expanded=\"false\" aria-controls=\"{$submenu_id}\" data-target=\"{$submenu_id}\">\n";
            $output .= "{$indent}\t\t\t<span class=\"sr-only\">Открыть подменю</span>\n";
            $output .= "{$indent}\t\t\t<svg class=\"h-5 w-5 transition-transform duration-200\" viewBox=\"0 0 20 20\" fill=\"currentColor\">\n";
            $output .= "{$indent}\t\t\t\t<path d=\"M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.24a.75.75 0 01-1.06 0L5.21 8.27a.75.75 0 01.02-1.06z\"/>\n";
            $output .= "{$indent}\t\t\t</svg>\n";
            $output .= "{$indent}\t\t</button>\n";
            $output .= "{$indent}\t</div>\n";
        }
    }

    public function end_el(&$output, $item, $depth = 0, $args = null)
    {
        $indent = str_repeat("\t", $depth + 1);
        $output .= "{$indent}</li>\n";
    }

    private function getLinkClasses($depth, $has_children)
    {
        $classes = ['nav-link', 'block', 'transition-all', 'duration-200'];

        if ($depth === 0) {
            // Основные пункты меню
            $classes = array_merge($classes, [
                'px-4',
                'py-3',
                'rounded-lg',
                'text-gray-700',
                'hover:text-blue-600',
                'font-medium',
                'text-sm',
                'lg:px-4',
                'lg:py-3',
            ]);

            if ($has_children) {
                $classes[] = 'lg:flex';
                $classes[] = 'lg:items-center';
                $classes[] = 'flex-1'; // Для мобильной версии
            }
        } else {
            // Подменю
            $classes = array_merge($classes, [
                'px-4',
                'py-2',
                'text-sm',
                'text-gray-600',
                'hover:text-blue-600',
                'rounded-lg',
                'lg:px-5',
                'lg:py-2',
            ]);
        }

        return $classes;
    }

    private function getLinkAttributes($item, $link_classes, $has_children)
    {
        $attributes = '';
        $attributes .= ' class="' . esc_attr(implode(' ', $link_classes)) . '"';

        // На десктопе все ссылки кликабельны, на мобильных с подменю - нет
        if ($has_children) {
            $attributes .= ' href="' . esc_url($item->url) . '"'; // Кликабельно на десктопе
            $attributes .= ' onclick="if(window.innerWidth < 1024) { event.preventDefault(); return false; }"'; // Блокируем на мобильных
        } else {
            $attributes .= !empty($item->url) ? ' href="' . esc_url($item->url) . '"' : ' href="#"';
        }

        if (!empty($item->title)) {
            $attributes .= ' title="' . esc_attr($item->title) . '"';
        }
        if (!empty($item->target)) {
            $attributes .= ' target="' . esc_attr($item->target) . '"';
        }
        if (!empty($item->xfn)) {
            $attributes .= ' rel="' . esc_attr($item->xfn) . '"';
        }

        return $attributes;
    }
}
