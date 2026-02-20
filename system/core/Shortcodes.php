<?php

/**
 * Класс для работы со шорткодами
 */
class Shortcodes {
    /**
     * @var array Зарегистрированные шорткоды
     */
    private static $shortcodes = [];
    
    /**
     * Добавляет шорткод
     *
     * @param string $name Имя шорткода
     * @param callable $callback Функция обработки
     */
    public static function add(string $name, callable $callback): void {
        self::$shortcodes[strtolower($name)] = $callback;
    }
    
    /**
     * Удаляет шорткод
     *
     * @param string $name Имя шорткода
     */
    public static function remove(string $name): void {
        unset(self::$shortcodes[strtolower($name)]);
    }
    
    /**
     * Обрабатывает шорткоды в контенте
     *
     * @param string $content Контент с шорткодами
     * @return string Обработанный контент
     */
    public static function process(string $content): string {
        return preg_replace_callback(
            '/\{([a-z0-9_-]+)(?:\s+([^}]+))?\}/i',
            function($matches) {
                $name = strtolower($matches[1]);
                $attrs = [];
                
                if (isset($matches[2])) {
                    preg_match_all('/(\w+)\s*=\s*["\']([^"\']+)["\']/', $matches[2], $attrMatches, PREG_SET_ORDER);
                    foreach ($attrMatches as $attr) {
                        $attrs[$attr[1]] = $attr[2];
                    }
                }
                
                if (isset(self::$shortcodes[$name])) {
                    return call_user_func(self::$shortcodes[$name], $attrs);
                }
                
                return $matches[0];
            },
            $content
        );
    }
    
    /**
     * Получает все зарегистрированные шорткоды
     *
     * @return array Массив шорткодов
     */
    public static function getAll(): array {
        return self::$shortcodes;
    }
}