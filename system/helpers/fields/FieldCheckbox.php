<?php

/**
 * Поле типа "чекбокс" для системы полей
 * Отображает переключатель (checkbox) с поддержкой режима "переключатель" (switch)
 * Автоматически добавляет скрытое поле для корректной отправки значения 0
 * 
 * @package Fields
 * @extends Field
 */
class FieldCheckbox extends Field {
    
    /**
     * Рендерит HTML-код поля чекбокса
     * 
     * @param mixed $currentValue Текущее значение поля
     * @return string HTML-код поля
     */
    public function render($currentValue = null) {
        // Определение состояния чекбокса
        $isChecked = $currentValue !== null ? (bool)$currentValue : (bool)($this->options['default'] ?? false);
        $checked = $isChecked ? ' checked' : '';
        
        // Определение класса для отображения (обычный чекбокс или switch)
        $switchClass = $this->options['switch'] ?? true ? 'form-switch' : '';
        
        // Получение атрибутов чекбокса
        $attributes = $this->getCheckboxAttributes();
        
        // Скрытое поле для отправки значения 0 при неотмеченном чекбоксе
        $hiddenField = "<input type=\"hidden\" name=\"{$this->getFieldName()}\" value=\"0\">";
        
        // Основной HTML чекбокса
        $fieldHtml = "
        {$hiddenField}
        <div class=\"form-check {$switchClass}\">
            <input type=\"checkbox\" value=\"1\"{$checked}{$attributes}>
            <label class=\"form-check-label\" for=\"{$this->name}\">
                {$this->options['title']}
            </label>
        </div>";
        
        // Подсказка (если есть)
        $hint = $this->options['hint'] ? '<div class="form-text mt-2">' . htmlspecialchars($this->options['hint']) . '</div>' : '';
        
        return "
        <div class=\"mb-3\">
            {$fieldHtml}
            {$hint}
        </div>";
    }
    
    /**
     * Получает имя поля с учетом admin_mode
     * В режиме администратора имя используется напрямую,
     * иначе оборачивается в массив settings[]
     * 
     * @return string Имя поля для использования в форме
     */
    private function getFieldName() {
        if ($this->options['admin_mode'] ?? false) {
            return $this->name;
        } else {
            return "settings[{$this->name}]";
        }
    }
    
    /**
     * Получает атрибуты для чекбокса
     * Формирует строку атрибутов из массива параметров
     * 
     * @return string Строка с HTML-атрибутами
     */
    private function getCheckboxAttributes() {
        $attrs = [];
        
        // Базовые атрибуты
        $attrs['name'] = $this->getFieldName();
        $attrs['id'] = $this->name;
        $attrs['class'] = "form-check-input {$this->options['class']}";
        
        // Обязательность
        if ($this->options['required']) {
            $attrs['required'] = 'required';
        }
        
        // Пользовательские атрибуты
        foreach ($this->options['attributes'] as $key => $value) {
            $attrs[$key] = $value;
        }
        
        // Сборка строки атрибутов
        $attributesString = '';
        foreach ($attrs as $key => $value) {
            if (!empty($value)) {
                $attributesString .= " {$key}=\"" . htmlspecialchars($value) . "\"";
            }
        }
        
        return $attributesString;
    }
}