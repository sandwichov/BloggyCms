<?php

// ==================== ФРОНТЕНД ====================

/**
 * Добавляет CSS файл для фронтенда
 * 
 * @param string $path Путь к CSS файлу
 */
if (!function_exists('front_css')) {
    function front_css(string $path): void {
        AssetManager::getInstance()->addCss($path, 'frontend');
    }
}

/**
 * Добавляет JS файл для фронтенда
 * 
 * @param string $path Путь к JS файлу
 */
if (!function_exists('front_js')) {
    function front_js(string $path): void {
        AssetManager::getInstance()->addJs($path, 'frontend');
    }
}

/**
 * Добавляет инлайн CSS код для фронтенда
 * 
 * @param string $css CSS код
 */
if (!function_exists('front_inline_css')) {
    function front_inline_css(string $css): void {
        AssetManager::getInstance()->addInlineCss($css, 'frontend');
    }
}

/**
 * Добавляет инлайн JS код для фронтенда
 * 
 * @param string $js JS код
 */
if (!function_exists('front_inline_js')) {
    function front_inline_js(string $js): void {
        AssetManager::getInstance()->addInlineJs($js, 'frontend');
    }
}

/**
 * Добавляет JS код в конец страницы (перед </body>) для фронтенда
 * 
 * @param string $js JS код
 */
if (!function_exists('front_bottom_js')) {
    function front_bottom_js(string $js): void {
        AssetManager::getInstance()->addBottomJs($js, 'frontend');
    }
}

/**
 * Выводит JS код для конца страницы для фронтенда
 * 
 * @return string HTML с JS кодом
 */
if (!function_exists('render_front_bottom_js')) {
    function render_front_bottom_js(): string {
        return AssetManager::getInstance()->renderBottomJs('frontend');
    }
}

/**
 * Выводит все CSS и JS для фронтенда
 * 
 * @return string HTML со всеми ассетами
 */
if (!function_exists('render_front_assets')) {
    function render_front_assets(): string {
        return AssetManager::getInstance()->renderCss('frontend') . 
               AssetManager::getInstance()->renderJs('frontend');
    }
}

/**
 * Выводит только CSS для фронтенда
 * 
 * @return string HTML с CSS
 */
if (!function_exists('render_front_css')) {
    function render_front_css(): string {
        return AssetManager::getInstance()->renderCss('frontend');
    }
}

/**
 * Выводит только JS для фронтенда
 * 
 * @return string HTML с JS
 */
if (!function_exists('render_front_js')) {
    function render_front_js(): string {
        return AssetManager::getInstance()->renderJs('frontend');
    }
}

// ==================== АДМИНКА ====================

/**
 * Добавляет CSS файл для админки
 * 
 * @param string $path Путь к CSS файлу
 */
if (!function_exists('admin_css')) {
    function admin_css(string $path): void {
        AssetManager::getInstance()->addCss($path, 'admin');
    }
}

/**
 * Добавляет JS файл для админки
 * 
 * @param string $path Путь к JS файлу
 */
if (!function_exists('admin_js')) {
    function admin_js(string $path): void {
        AssetManager::getInstance()->addJs($path, 'admin');
    }
}

/**
 * Добавляет инлайн CSS код для админки
 * 
 * @param string $css CSS код
 */
if (!function_exists('admin_inline_css')) {
    function admin_inline_css(string $css): void {
        AssetManager::getInstance()->addInlineCss($css, 'admin');
    }
}

/**
 * Добавляет инлайн JS код для админки
 * 
 * @param string $js JS код
 */
if (!function_exists('admin_inline_js')) {
    function admin_inline_js(string $js): void {
        AssetManager::getInstance()->addInlineJs($js, 'admin');
    }
}

/**
 * Добавляет JS код в конец страницы (перед </body>) для админки
 * 
 * @param string $js JS код
 */
if (!function_exists('admin_bottom_js')) {
    function admin_bottom_js(string $js): void {
        AssetManager::getInstance()->addBottomJs($js, 'admin');
    }
}

/**
 * Выводит JS код для конца страницы для админки
 * 
 * @return string HTML с JS кодом
 */
if (!function_exists('render_admin_bottom_js')) {
    function render_admin_bottom_js(): string {
        return AssetManager::getInstance()->renderBottomJs('admin');
    }
}

/**
 * Выводит все CSS и JS для админки
 * 
 * @return string HTML со всеми ассетами
 */
if (!function_exists('render_admin_assets')) {
    function render_admin_assets(): string {
        return AssetManager::getInstance()->renderCss('admin') . 
               AssetManager::getInstance()->renderJs('admin');
    }
}

/**
 * Выводит только CSS для админки
 * 
 * @return string HTML с CSS
 */
if (!function_exists('render_admin_css')) {
    function render_admin_css(): string {
        return AssetManager::getInstance()->renderCss('admin');
    }
}

/**
 * Выводит только JS для админки
 * 
 * @return string HTML с JS
 */
if (!function_exists('render_admin_js')) {
    function render_admin_js(): string {
        return AssetManager::getInstance()->renderJs('admin');
    }
}

// ==================== УНИВЕРСАЛЬНЫЕ ====================

/**
 * Добавляет CSS файл (автоопределение контекста)
 * 
 * @param string $path Путь к CSS файлу
 */
if (!function_exists('asset_css')) {
    function asset_css(string $path): void {
        AssetManager::getInstance()->addCss($path);
    }
}

/**
 * Добавляет JS файл (автоопределение контекста)
 * 
 * @param string $path Путь к JS файлу
 */
if (!function_exists('asset_js')) {
    function asset_js(string $path): void {
        AssetManager::getInstance()->addJs($path);
    }
}

/**
 * Добавляет инлайн CSS код (автоопределение контекста)
 * 
 * @param string $css CSS код
 */
if (!function_exists('asset_inline_css')) {
    function asset_inline_css(string $css): void {
        AssetManager::getInstance()->addInlineCss($css);
    }
}

/**
 * Добавляет инлайн JS код (автоопределение контекста)
 * 
 * @param string $js JS код
 */
if (!function_exists('asset_inline_js')) {
    function asset_inline_js(string $js): void {
        AssetManager::getInstance()->addInlineJs($js);
    }
}

/**
 * Выводит все ассеты для текущего контекста
 * 
 * @return string HTML со всеми ассетами
 */
if (!function_exists('render_assets')) {
    function render_assets(): string {
        return AssetManager::getInstance()->renderAll();
    }
}

/**
 * Добавляет JS код в конец страницы (автоопределение контекста)
 * 
 * @param string $js JS код
 */
if (!function_exists('asset_bottom_js')) {
    function asset_bottom_js(string $js): void {
        AssetManager::getInstance()->addBottomJs($js);
    }
}

/**
 * Выводит JS код для конца страницы (автоопределение контекста)
 * 
 * @return string HTML с JS кодом
 */
if (!function_exists('render_bottom_js')) {
    function render_bottom_js(): string {
        return AssetManager::getInstance()->renderBottomJs();
    }
}

// ==================== ДЛЯ ОБРАТНОЙ СОВМЕСТИМОСТИ ====================

/**
 * @deprecated Используйте front_bottom_js() или admin_bottom_js()
 */
if (!function_exists('add_bottom_js')) {
    function add_bottom_js(string $js): void {
        if (defined('ADMIN_URL') && strpos($_SERVER['REQUEST_URI'], ADMIN_URL) === 0) {
            admin_bottom_js($js);
        } else {
            front_bottom_js($js);
        }
    }
}

// ==================== ВСПОМОГАТЕЛЬНЫЕ ====================

/**
 * Добавляет базовый CSS для админки (шаблонные файлы)
 * 
 * @param array $files Массив имен файлов (без расширения)
 */
if (!function_exists('base_admin_css')) {
    function base_admin_css(array $files): void {
        foreach ($files as $file) {
            AssetManager::getInstance()->addBaseCss(
                'templates/default/admin/assets/css/' . $file . '.css', 
                'admin'
            );
        }
    }
}

/**
 * Добавляет базовый JS для админки (шаблонные файлы)
 * 
 * @param array $files Массив имен файлов (без расширения)
 */
if (!function_exists('base_admin_js')) {
    function base_admin_js(array $files): void {
        foreach ($files as $file) {
            AssetManager::getInstance()->addBaseJs(
                'templates/default/admin/assets/js/' . $file . '.js', 
                'admin'
            );
        }
    }
}

/**
 * Добавляет базовый CSS для фронта (шаблонные файлы)
 * 
 * @param array $files Массив имен файлов (без расширения)
 */
if (!function_exists('base_front_css')) {
    function base_front_css(array $files): void {
        $template = SettingsHelper::getCurrentTemplate();
        foreach ($files as $file) {
            AssetManager::getInstance()->addBaseCss(
                'templates/' . $template . '/front/assets/css/' . $file . '.css', 
                'frontend'
            );
        }
    }
}

/**
 * Добавляет базовый JS для фронта (шаблонные файлы)
 * 
 * @param array $files Массив имен файлов (без расширения)
 */
if (!function_exists('base_front_js')) {
    function base_front_js(array $files): void {
        $template = SettingsHelper::getCurrentTemplate();
        foreach ($files as $file) {
            AssetManager::getInstance()->addBaseJs(
                'templates/' . $template . '/front/assets/js/' . $file . '.js', 
                'frontend'
            );
        }
    }
}

// ==================== ДЛЯ ОБРАТНОЙ СОВМЕСТИМОСТИ ====================

/**
 * @deprecated Используйте front_css()
 */
if (!function_exists('add_html_block_css')) {
    function add_html_block_css(string $path): void {
        front_css($path);
    }
}

/**
 * @deprecated Используйте front_js()
 */
if (!function_exists('add_html_block_js')) {
    function add_html_block_js(string $path): void {
        front_js($path);
    }
}

/**
 * @deprecated Используйте front_css() или admin_css()
 */
if (!function_exists('add_plugin_css')) {
    function add_plugin_css(string $path): void {
        if (defined('ADMIN_URL') && strpos($_SERVER['REQUEST_URI'], ADMIN_URL) === 0) {
            admin_css($path);
        } else {
            front_css($path);
        }
    }
}

/**
 * @deprecated Используйте front_js() или admin_js()
 */
if (!function_exists('add_plugin_js')) {
    function add_plugin_js(string $path): void {
        if (defined('ADMIN_URL') && strpos($_SERVER['REQUEST_URI'], ADMIN_URL) === 0) {
            admin_js($path);
        } else {
            front_js($path);
        }
    }
}

/**
 * @deprecated Используйте front_inline_css() или admin_inline_css()
 */
if (!function_exists('add_inline_css')) {
    function add_inline_css(string $css): void {
        if (defined('ADMIN_URL') && strpos($_SERVER['REQUEST_URI'], ADMIN_URL) === 0) {
            admin_inline_css($css);
        } else {
            front_inline_css($css);
        }
    }
}

/**
 * @deprecated Используйте front_inline_js() или admin_inline_js()
 */
if (!function_exists('add_inline_js')) {
    function add_inline_js(string $js): void {
        if (defined('ADMIN_URL') && strpos($_SERVER['REQUEST_URI'], ADMIN_URL) === 0) {
            admin_inline_js($js);
        } else {
            front_inline_js($js);
        }
    }
}

// Старые функции для поддержки существующего кода

/**
 * @deprecated Используйте admin_css()
 */
if (!function_exists('add_admin_css')) {
    function add_admin_css(string $path): void {
        admin_css($path);
    }
}

/**
 * @deprecated Используйте admin_js()
 */
if (!function_exists('add_admin_js')) {
    function add_admin_js(string $path): void {
        admin_js($path);
    }
}

/**
 * @deprecated Используйте front_css()
 */
if (!function_exists('add_frontend_css')) {
    function add_frontend_css(string $path): void {
        front_css($path);
    }
}

/**
 * @deprecated Используйте front_js()
 */
if (!function_exists('add_frontend_js')) {
    function add_frontend_js(string $path): void {
        front_js($path);
    }
}

// ==================== ОБЕРТКИ ====================

/**
 * Универсальная функция для добавления JS кода
 * 
 * @param string $js JS код
 * @param bool $bottom Добавить в конец страницы
 * @param string|null $context Контекст (frontend/admin)
 */
if (!function_exists('js')) {
    function js(string $js, bool $bottom = false, ?string $context = null): void {
        if ($bottom) {
            if ($context === 'admin') {
                admin_bottom_js($js);
            } elseif ($context === 'frontend') {
                front_bottom_js($js);
            } else {
                asset_bottom_js($js);
            }
        } else {
            if ($context === 'admin') {
                admin_inline_js($js);
            } elseif ($context === 'frontend') {
                front_inline_js($js);
            } else {
                asset_inline_js($js);
            }
        }
    }
}

/**
 * Передает переменную из PHP в JavaScript
 * 
 * @param string $name Имя переменной в JS (window.имя)
 * @param mixed $value Значение
 * @param bool $bottom Добавить в конец страницы
 * @param string|null $context Контекст (frontend/admin)
 */
if (!function_exists('js_var')) {
    function js_var(string $name, $value, bool $bottom = false, ?string $context = null): void {
        $js = sprintf('window.%s = %s;', $name, json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        js($js, $bottom, $context);
    }
}

/**
 * Передает конфигурацию из PHP в JavaScript
 * 
 * @param array $config Массив [имя => значение]
 * @param bool $bottom Добавить в конец страницы
 * @param string|null $context Контекст (frontend/admin)
 */
if (!function_exists('js_config')) {
    function js_config(array $config, bool $bottom = false, ?string $context = null): void {
        foreach ($config as $name => $value) {
            js_var($name, $value, $bottom, $context);
        }
    }
}