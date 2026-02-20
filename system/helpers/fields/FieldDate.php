<?php

/**
 * Поле типа "дата" для системы полей
 * Отображает стандартный HTML5 date picker для выбора даты
 * Сохраняет значение в формате YYYY-MM-DD
 * 
 * @package Fields
 * @extends Field
 */
class FieldDate extends Field {
    
    /**
     * Рендерит HTML-код поля для выбора даты
     * Создает input type="date" с привязкой к настройкам
     * 
     * @param mixed $currentValue Текущее значение поля (дата в формате YYYY-MM-DD)
     * @return string HTML-код поля
     */
    public function render($currentValue = null) {
        // Определение значения (текущее или по умолчанию)
        $value = $currentValue !== null ? $currentValue : $this->options['default'];
        
        // HTML поле для выбора даты
        $fieldHtml = "<input type=\"date\" 
                   value=\"" . htmlspecialchars($value) . "\" 
                   name=\"settings[{$this->name}]\" 
                   class=\"form-control\">";
        
        // Оборачиваем в стандартную группу поля
        return $this->renderFieldGroup($fieldHtml);
    }
}