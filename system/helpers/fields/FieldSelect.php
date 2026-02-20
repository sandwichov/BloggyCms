<?php

/**
 * Поле типа "выпадающий список" для системы полей
 * Отображает HTML select с опциями из конфигурации
 * Поддерживает выбор одного значения из предопределенного списка
 * 
 * @package Fields
 * @extends Field
 */
class FieldSelect extends Field {
    
    /**
     * Рендерит HTML-код выпадающего списка
     * Создает select элемент с опциями из конфигурации
     * 
     * @param mixed $currentValue Текущее значение поля
     * @return string HTML-код поля
     */
    public function render($currentValue = null) {
        $value = $currentValue !== null ? $currentValue : $this->options['default'];
        
        // Получение стандартных атрибутов (name, id, class и т.д.)
        $attributes = $this->getAttributes();
        
        // Генерация HTML для опций
        $optionsHtml = '';
        foreach ($this->options['options'] as $optionValue => $optionLabel) {
            $selected = $value == $optionValue ? ' selected' : '';
            $optionsHtml .= "<option value=\"" . htmlspecialchars($optionValue) . "\"{$selected}>" . htmlspecialchars($optionLabel) . "</option>";
        }
        
        // Формирование HTML поля
        $fieldHtml = "<select{$attributes}>{$optionsHtml}</select>";
        
        // Оборачивание в стандартную группу поля
        return $this->renderFieldGroup($fieldHtml);
    }
}