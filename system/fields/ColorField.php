<?php

/**
 * Поле типа "цвет" для системы пользовательских полей
 * Позволяет выбирать цвет через стандартный HTML5 color picker
 * Отображает цвет в виде квадрата с кодом цвета
 * 
 * @package Fields
 * @extends BaseField
 */
class ColorField extends BaseField {
    
    /**
     * Возвращает тип поля
     * 
     * @return string 'color'
     */
    public function getType(): string {
        return 'color';
    }
    
    /**
     * Возвращает отображаемое название типа поля
     * 
     * @return string 'Выбор цвета'
     */
    public function getName(): string {
        return 'Выбор цвета';
    }
    
    /**
     * Генерирует HTML для редактирования поля в форме
     * Создает комбинацию color picker и текстового поля для ввода HEX-кода
     * 
     * @param mixed $value Текущее значение поля
     * @param string $entityType Тип сущности (post, user, category и т.д.)
     * @param int $entityId ID сущности
     * @return string HTML-код для редактирования
     */
    public function renderInput($value, $entityType, $entityId): string {
        $required = isset($this->config['required']) && $this->config['required'] ? 'required' : '';
        
        return "
            <div class='input-group input-group-sm'>
                <input type='color' 
                       name='field_{$this->systemName}' 
                       value='" . htmlspecialchars($value) . "'
                       class='form-control form-control-color'
                       {$required}>
                <input type='text' 
                       value='" . htmlspecialchars($value) . "'
                       class='form-control'
                       placeholder='#000000'
                       onchange='this.previousElementSibling.value=this.value'>
            </div>
        ";
    }
    
    /**
     * Генерирует HTML для отображения значения поля в детальном просмотре
     * Показывает цветной квадрат и HEX-код
     * 
     * @param mixed $value Значение поля
     * @param string $entityType Тип сущности
     * @param int $entityId ID сущности
     * @return string HTML-код для отображения
     */
    public function renderDisplay($value, $entityType, $entityId): string {
        if (empty($value)) return '<span class="text-muted">Не указано</span>';
        
        return "
            <div class='d-flex align-items-center gap-2'>
                <div style='width: 20px; height: 20px; background-color: {$value}; border: 1px solid #ddd; border-radius: 3px;'></div>
                <code>{$value}</code>
            </div>
        ";
    }
    
    /**
     * Генерирует HTML для отображения значения поля в списке
     * Показывает миниатюрный цветной квадрат
     * 
     * @param mixed $value Значение поля
     * @param string $entityType Тип сущности
     * @param int $entityId ID сущности
     * @return string HTML-код для отображения в списке
     */
    public function renderList($value, $entityType, $entityId): string {
        if (empty($value)) return '<span class="text-muted">-</span>';
        
        return "<div style='width: 16px; height: 16px; background-color: {$value}; border: 1px solid #ddd; border-radius: 2px;' title='{$value}'></div>";
    }
    
    /**
     * Возвращает HTML-форму для настройки поля в административной панели
     * Позволяет задать значение по умолчанию
     * 
     * @return string HTML-код формы настроек
     */
    public function getSettingsForm(): string {
        $defaultValue = htmlspecialchars($this->config['default_value'] ?? '#000000');
        
        return "
            <div class='mb-3'>
                <label class='form-label'>Значение по умолчанию</label>
                <input type='color' class='form-control' name='config[default_value]' value='{$defaultValue}'>
            </div>
        ";
    }
}