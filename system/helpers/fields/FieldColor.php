<?php

/**
 * Поле типа "цвет" для системы полей
 * Отображает комбинацию HTML5 color picker и текстового поля для ввода HEX-кода цвета
 * Синхронизирует значения между двумя полями через JavaScript
 * 
 * @package Fields
 * @extends Field
 */
class FieldColor extends Field {
    
    /**
     * Рендерит HTML-код поля для выбора цвета
     * Создает два связанных поля: color picker и текстовое поле для HEX-кода
     * Добавляет JavaScript для синхронизации между ними
     * 
     * @param mixed $currentValue Текущее значение поля (HEX-код цвета)
     * @return string HTML-код поля
     */
    public function render($currentValue = null) {
        $value = $currentValue !== null ? $currentValue : $this->options['default'];
        
        // HTML структура: color picker и текстовое поле
        $fieldHtml = "
        <div class=\"input-group color-picker-group\">
            <input type=\"color\" 
                   value=\"" . htmlspecialchars($value) . "\" 
                   class=\"form-control color-picker-input\"
                   style=\"max-width: 80px;\">
            <input type=\"text\" 
                   value=\"" . htmlspecialchars($value) . "\" 
                   name=\"settings[{$this->name}]\" 
                   class=\"form-control color-text-input\"
                   placeholder=\"#000000\"
                   maxlength=\"7\">
        </div>";
        
        // JavaScript для двусторонней синхронизации полей
        $fieldHtml .= "
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const colorInput = document.querySelector('input[name=\"settings[{$this->name}]\"]');
            const pickerInput = colorInput.previousElementSibling;
            
            // Синхронизация: color picker -> текстовое поле
            pickerInput.addEventListener('input', function() {
                colorInput.value = this.value;
            });
            
            // Синхронизация: текстовое поле -> color picker (только если введен валидный HEX)
            colorInput.addEventListener('input', function() {
                if (this.value.match(/^#[0-9A-F]{6}$/i)) {
                    pickerInput.value = this.value;
                }
            });
        });
        </script>";
        
        return $this->renderFieldGroup($fieldHtml);
    }
}