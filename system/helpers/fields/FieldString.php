<?php

/**
 * Поле типа "строка" для системы полей
 * Отображает стандартное текстовое поле ввода (input type="text")
 * Базовый тип для ввода однострочного текста
 * 
 * @package Fields
 * @extends Field
 */
class FieldString extends Field {
    
    /**
     * Рендерит HTML-код текстового поля
     * Создает input type="text" с текущим значением
     * 
     * @param mixed $currentValue Текущее значение поля
     * @return string HTML-код поля
     */
    public function render($currentValue = null) {
        $value = $currentValue !== null ? $currentValue : $this->options['default'];
        
        // Получение стандартных атрибутов (name, id, class, placeholder, required и т.д.)
        $attributes = $this->getAttributes();
        
        // Формирование HTML поля
        $fieldHtml = "<input type=\"text\" value=\"" . htmlspecialchars($value) . "\"{$attributes}>";
        
        // Оборачивание в стандартную группу поля
        return $this->renderFieldGroup($fieldHtml);
    }
}