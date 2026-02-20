<?php

/**
 * Вспомогательный класс для обработки текста
 * Предоставляет методы для сокращения текста, подсветки поисковых терминов
 * и удаления HTML-тегов
 * 
 * @package Helpers
 */
class TextHelper {
    
    /**
     * Сокращает текст до указанной длины, обрезая по последнему слову
     * Удаляет HTML-теги перед обработкой
     *
     * @param string $text Исходный текст
     * @param int $length Максимальная длина (по умолчанию 100)
     * @param string $append Строка, добавляемая в конец сокращенного текста (по умолчанию '...')
     * @return string Сокращенный текст
     */
    public static function truncate($text, $length = 100, $append = '...') {
        $text = strip_tags($text);
        
        if (mb_strlen($text) <= $length) {
            return $text;
        }
        
        $text = mb_substr($text, 0, $length);
        $text = mb_substr($text, 0, mb_strrpos($text, ' '));
        
        return $text . $append;
    }
    
    /**
     * Подсвечивает ключевые слова в тексте
     * Игнорирует слова короче 3 символов
     * 
     * @param string $text Исходный текст
     * @param string $query Поисковый запрос (может содержать несколько слов)
     * @param string $highlightClass CSS-класс для подсветки (по умолчанию: 'search-highlight')
     * @return string Текст с подсвеченными ключевыми словами
     */
    public static function highlightSearchTerms($text, $query, $highlightClass = 'search-highlight') {
        if (empty($query) || empty($text)) {
            return $text;
        }
        
        $text = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
        
        $query = preg_quote($query, '/');
        
        $words = explode(' ', $query);
        
        foreach ($words as $word) {
            if (strlen($word) > 2) {
                $pattern = '/(' . $word . ')/iu';
                $replacement = '<span class="' . $highlightClass . '">$1</span>';
                $text = preg_replace($pattern, $replacement, $text);
            }
        }
        
        return $text;
    }
    
    /**
     * Удаляет HTML-теги и сокращает текст
     * Комбинация strip_tags + truncate
     * 
     * @param string $html HTML-текст
     * @param int $length Максимальная длина (по умолчанию 100)
     * @param string $append Строка, добавляемая в конец сокращенного текста (по умолчанию '...')
     * @return string Очищенный и сокращенный текст
     */
    public static function stripHtmlAndTruncate($html, $length = 100, $append = '...') {
        $text = strip_tags($html);
        return self::truncate($text, $length, $append);
    }
}

/**
 * Функция-обертка для сокращения текста
 * 
 * @param string $text Исходный текст
 * @param int $length Максимальная длина
 * @param string $append Строка, добавляемая в конец
 * @return string Сокращенный текст
 */
function truncate_text($text, $length = 100, $append = '...') {
    return TextHelper::truncate($text, $length, $append);
}

/**
 * Функция-обертка для подсветки поисковых терминов
 * 
 * @param string $text Исходный текст
 * @param string $query Поисковый запрос
 * @param string $highlightClass CSS-класс для подсветки
 * @return string Текст с подсвеченными ключевыми словами
 */
function highlight_search_terms($text, $query, $highlightClass = 'search-highlight') {
    return TextHelper::highlightSearchTerms($text, $query, $highlightClass);
}

/**
 * Функция-обертка для удаления HTML и сокращения текста
 * 
 * @param string $html HTML-текст
 * @param int $length Максимальная длина
 * @param string $append Строка, добавляемая в конец
 * @return string Очищенный и сокращенный текст
 */
function strip_html_and_truncate($html, $length = 100, $append = '...') {
    return TextHelper::stripHtmlAndTruncate($html, $length, $append);
}

/**
 * Выводит SVG иконку из спрайта
 * Поддерживает размеры, цвета и дополнительные CSS классы
 * 
 * @param string $set Название набора иконок (файла)
 * @param string $icon ID иконки
 * @param string|null $size Размер иконки (например "24 24" или "24")
 * @param string|null $color Цвет иконки (например "#ff0000")
 * @param string|null $class Дополнительные CSS классы
 * @return string HTML-код иконки
 */
function bloggy_icon($set, $icon, $size = null, $color = null, $class = null) {
    $attrs = [];

    $baseClass = 'icon icon-' . $icon;
    $attrs['class'] = $class ? $baseClass . ' ' . $class : $baseClass;
    
    if ($size) {
        list($width, $height) = explode(' ', $size . ' ' . $size);
        $attrs['width'] = $width;
        $attrs['height'] = $height ?: $width;
    }
    
    if ($color) {
        $attrs['style'] = 'fill: ' . $color;
    } else {
        $attrs['style'] = 'fill: currentColor';
    }
    
    $attrsString = '';
    foreach ($attrs as $name => $value) {
        $attrsString .= ' ' . $name . '="' . htmlspecialchars($value) . '"';
    }
    
    return sprintf(
        '<svg%s><use href="%s#%s"/></svg>',
        $attrsString,
        BASE_URL . '/templates/default/admin/icons/' . $set . '.svg',
        htmlspecialchars($icon)
    );
}

/**
 * Экранирование HTML специальных символов
 * Безопасно обрабатывает строки, числа и объекты с __toString()
 * 
 * @param mixed $string Входные данные
 * @return string Экранированная строка
 */
function html($string) {
    if ($string === null) {
        return '';
    }
    
    if (!is_string($string) && !is_numeric($string)) {
        if (is_object($string) && method_exists($string, '__toString')) {
            $string = (string)$string;
        } else {
            return '';
        }
    }
    
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Функция для склонения слов в зависимости от числа
 * 
 * @param int $number Число, для которого нужно склонение
 * @param array $forms Массив форм слова (например, ["просмотр", "просмотра", "просмотров"])
 * @return string Правильная форма слова
 */
function plural_form(int $number, array $forms): string
{
    $cases = [2, 0, 1, 1, 1, 2];
    $index = ($number % 100 > 4 && $number % 100 < 20) ? 2 : $cases[min($number % 10, 5)];
    return $forms[$index];
}

/**
 * Альтернативная функция для склонения числительных
 * 
 * @param int $number Число
 * @param array $endings Массив окончаний [для 1, для 2-4, для остальных]
 * @return string Правильная форма слова
 * 
 * @example get_numeric_ending(5, ['комментарий', 'комментария', 'комментариев'])
 */
function get_numeric_ending($number, $endings) {
    $number = $number % 100;
    if ($number >= 11 && $number <= 19) {
        return $endings[2];
    }
    $i = $number % 10;
    switch ($i) {
        case 1: return $endings[0];
        case 2:
        case 3:
        case 4: return $endings[1];
        default: return $endings[2];
    }
}