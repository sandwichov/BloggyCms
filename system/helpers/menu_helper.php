<?php

/**
 * Проверка существования функции render_menu
 * Рендерит меню по ID, названию или шаблону
 * 
 * @param mixed $identifier ID меню, название меню или 'template:название_шаблона'
 * @param string|null $theme Название темы (опционально)
 * @return string HTML-код меню или пустой комментарий при ошибке
 */
if (!function_exists('render_menu')) {
    function render_menu($identifier, $theme = null) {
        try {
            if (is_numeric($identifier)) {
                // Рендер по ID
                return MenuRenderer::renderById($identifier, $theme);
            } elseif (strpos($identifier, 'template:') === 0) {
                // Рендер по шаблону
                $template = substr($identifier, 9);
                return MenuRenderer::renderByTemplate($template, $theme);
            } else {
                // Рендер по названию
                return MenuRenderer::render($identifier, $theme);
            }
        } catch (Exception $e) {
            return '<!-- Menu render error -->';
        }
    }
}

/**
 * Проверка существования функции get_menu_info
 * Получает информацию о меню по ID
 * 
 * @param int $menuId ID меню
 * @return array|null Данные меню или null при ошибке
 */
if (!function_exists('get_menu_info')) {
    function get_menu_info($menuId) {
        try {
            return MenuRenderer::getMenuInfo($menuId);
        } catch (Exception $e) {
            return null;
        }
    }
}

/**
 * Проверка существования функции get_all_menus
 * Получает список всех активных меню
 * 
 * @return array Массив активных меню
 */
if (!function_exists('get_all_menus')) {
    function get_all_menus() {
        try {
            return MenuRenderer::getAllActiveMenus();
        } catch (Exception $e) {
            return [];
        }
    }
}

/**
 * Проверка существования функции get_menus_for_select
 * Получает список всех меню для выпадающего списка (для форм)
 * 
 * @return array Массив меню в формате [id => name]
 */
if (!function_exists('get_menus_for_select')) {
    function get_menus_for_select() {
        try {
            return MenuRenderer::getAllMenusForSelect();
        } catch (Exception $e) {
            return [];
        }
    }
}