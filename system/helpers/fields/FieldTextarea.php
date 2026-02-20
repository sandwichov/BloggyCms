<?php

/**
 * Поле типа "текстовая область" для системы полей
 * Отображает многострочное текстовое поле (textarea)
 * Поддерживает настройку количества строк
 * 
 * @package Fields
 * @extends Field
 */
class FieldTextarea extends Field {
    
    /**
     * Рендерит HTML-код текстовой области
     * Создает textarea с текущим значением и настраиваемым количеством строк
     * 
     * @param mixed $currentValue Текущее значение поля
     * @return string HTML-код поля
     */
    public function render($currentValue = null) {
        $value = $currentValue !== null ? $currentValue : $this->options['default'];
        
        // Получение стандартных атрибутов (name, id, class, placeholder, required и т.д.)
        $attributes = $this->getAttributes();
        
        // Определение количества строк (по умолчанию 3)
        $rows = isset($this->options['rows']) ? " rows=\"{$this->options['rows']}\"" : ' rows="3"';
        
        // Формирование HTML поля
        $fieldHtml = "<textarea{$attributes}{$rows}>" . htmlspecialchars($value) . "</textarea>";
        
        // Оборачивание в стандартную группу поля
        return $this->renderFieldGroup($fieldHtml);
    }
}