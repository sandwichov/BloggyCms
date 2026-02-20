<?php

/**
 * Поле типа "число" для системы полей
 * Отображает HTML5 input с типом number для ввода числовых значений
 * Поддерживает ограничения минимального и максимального значения
 * 
 * @package Fields
 * @extends Field
 */
class FieldNumber extends Field {
    
    /**
     * Рендерит HTML-код поля для ввода числа
     * Создает input type="number" с поддержкой ограничений min/max
     * 
     * @param mixed $currentValue Текущее значение поля
     * @return string HTML-код поля
     */
    public function render($currentValue = null) {
        $value = $currentValue !== null ? $currentValue : $this->options['default'];
        
        // Получение стандартных атрибутов (name, id, class и т.д.)
        $attributes = $this->getAttributes();
        
        // Добавление ограничений min/max из настроек
        $min = isset($this->options['min']) ? " min=\"{$this->options['min']}\"" : '';
        $max = isset($this->options['max']) ? " max=\"{$this->options['max']}\"" : '';
        
        // Формирование HTML поля
        $fieldHtml = "<input type=\"number\" value=\"" . htmlspecialchars($value) . "\"{$attributes}{$min}{$max}>";
        
        // Оборачивание в стандартную группу поля
        return $this->renderFieldGroup($fieldHtml);
    }
}