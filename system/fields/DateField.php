<?php

/**
 * Поле типа "дата" для системы пользовательских полей
 * Позволяет выбирать дату через стандартный HTML5 date picker
 * Поддерживает различные форматы отображения
 * 
 * @package Fields
 * @extends BaseField
 */
class DateField extends BaseField {
    
    /**
     * Возвращает тип поля
     * 
     * @return string 'date'
     */
    public function getType(): string {
        return 'date';
    }
    
    /**
     * Возвращает отображаемое название типа поля
     * 
     * @return string 'Дата'
     */
    public function getName(): string {
        return 'Дата';
    }
    
    /**
     * Генерирует HTML для редактирования поля в форме
     * Создает input type="date" для выбора даты
     * 
     * @param mixed $value Текущее значение поля
     * @param string $entityType Тип сущности (post, user, category и т.д.)
     * @param int $entityId ID сущности
     * @return string HTML-код для редактирования
     */
    public function renderInput($value, $entityType, $entityId): string {
        $required = isset($this->config['required']) && $this->config['required'] ? 'required' : '';
        $format = $this->config['format'] ?? 'Y-m-d';
        
        // Преобразование значения в формат Y-m-d для input type="date"
        $dateValue = '';
        if (!empty($value)) {
            $dateValue = date('Y-m-d', strtotime($value));
        }
        
        return "
            <input type='date' 
                   name='field_{$this->systemName}' 
                   value='{$dateValue}'
                   class='form-control form-control-sm'
                   {$required}>
        ";
    }
    
    /**
     * Генерирует HTML для отображения значения поля в детальном просмотре
     * Форматирует дату согласно настройкам поля
     * 
     * @param mixed $value Значение поля
     * @param string $entityType Тип сущности
     * @param int $entityId ID сущности
     * @return string HTML-код для отображения
     */
    public function renderDisplay($value, $entityType, $entityId): string {
        if (empty($value)) return '<span class="text-muted">Не указана</span>';
        
        $format = $this->config['format'] ?? 'd.m.Y';
        $formattedDate = date($format, strtotime($value));
        
        return "<span class='field-date'>{$formattedDate}</span>";
    }
    
    /**
     * Генерирует HTML для отображения значения поля в списке
     * Использует тот же формат, что и для детального просмотра
     * 
     * @param mixed $value Значение поля
     * @param string $entityType Тип сущности
     * @param int $entityId ID сущности
     * @return string HTML-код для отображения в списке
     */
    public function renderList($value, $entityType, $entityId): string {
        if (empty($value)) return '<span class="text-muted">-</span>';
        
        $format = $this->config['format'] ?? 'd.m.Y';
        $formattedDate = date($format, strtotime($value));
        
        return "<span class='field-date'>{$formattedDate}</span>";
    }
    
    /**
     * Возвращает HTML-форму для настройки поля в административной панели
     * Позволяет задать формат даты и значение по умолчанию
     * 
     * @return string HTML-код формы настроек
     */
    public function getSettingsForm(): string {
        $format = htmlspecialchars($this->config['format'] ?? 'd.m.Y');
        $defaultValue = htmlspecialchars($this->config['default_value'] ?? '');
        
        return "
            <div class='row'>
                <div class='col-md-6'>
                    <div class='mb-3'>
                        <label class='form-label'>Формат даты</label>
                        <input type='text' class='form-control' name='config[format]' value='{$format}' placeholder='d.m.Y'>
                        <div class='form-text'>d - день, m - месяц, Y - год</div>
                    </div>
                </div>
                <div class='col-md-6'>
                    <div class='mb-3'>
                        <label class='form-label'>Значение по умолчанию</label>
                        <input type='date' class='form-control' name='config[default_value]' value='{$defaultValue}'>
                    </div>
                </div>
            </div>
        ";
    }
}