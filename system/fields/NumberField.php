<?php

/**
 * Поле типа "число" для системы пользовательских полей
 * Позволяет вводить числовые значения с поддержкой ограничений
 * (минимум, максимум, шаг) и настраиваемым плейсхолдером
 * 
 * @package Fields
 * @extends BaseField
 */
class NumberField extends BaseField {
    
    /**
     * Возвращает тип поля
     * 
     * @return string 'number'
     */
    public function getType(): string {
        return 'number';
    }
    
    /**
     * Возвращает отображаемое название типа поля
     * 
     * @return string 'Число'
     */
    public function getName(): string {
        return 'Число';
    }
    
    /**
     * Генерирует HTML для редактирования поля в форме
     * Создает input type="number" с поддержкой ограничений
     * 
     * @param mixed $value Текущее значение поля
     * @param string $entityType Тип сущности (post, user, category и т.д.)
     * @param int $entityId ID сущности
     * @return string HTML-код для редактирования
     */
    public function renderInput($value, $entityType, $entityId): string {
        $min = $this->config['min'] ?? '';
        $max = $this->config['max'] ?? '';
        $step = $this->config['step'] ?? '1';
        $placeholder = $this->config['placeholder'] ?? '';
        $required = isset($this->config['required']) && $this->config['required'] ? 'required' : '';
        
        return "
            <input type='number' 
                   name='field_{$this->systemName}' 
                   value='" . htmlspecialchars($value) . "'
                   class='form-control'
                   placeholder='" . htmlspecialchars($placeholder) . "'
                   min='{$min}'
                   max='{$max}'
                   step='{$step}'
                   {$required}>
        ";
    }
    
    /**
     * Генерирует HTML для отображения значения поля в детальном просмотре
     * 
     * @param mixed $value Значение поля
     * @param string $entityType Тип сущности
     * @param int $entityId ID сущности
     * @return string HTML-код для отображения
     */
    public function renderDisplay($value, $entityType, $entityId): string {
        return "<span class='field-number'>" . htmlspecialchars($value) . "</span>";
    }
    
    /**
     * Генерирует HTML для отображения значения поля в списке
     * 
     * @param mixed $value Значение поля
     * @param string $entityType Тип сущности
     * @param int $entityId ID сущности
     * @return string HTML-код для отображения в списке
     */
    public function renderList($value, $entityType, $entityId): string {
        return "<span class='field-number'>" . htmlspecialchars($value) . "</span>";
    }
    
    /**
     * Возвращает HTML-форму для настройки поля в административной панели
     * Позволяет задать минимальное/максимальное значение, шаг, плейсхолдер
     * и значение по умолчанию
     * 
     * @return string HTML-код формы настроек
     */
    public function getSettingsForm(): string {
        $min = htmlspecialchars($this->config['min'] ?? '');
        $max = htmlspecialchars($this->config['max'] ?? '');
        $step = htmlspecialchars($this->config['step'] ?? '1');
        $placeholder = htmlspecialchars($this->config['placeholder'] ?? '');
        $defaultValue = htmlspecialchars($this->config['default_value'] ?? '');
        
        return "
            <div class='row'>
                <div class='col-md-4'>
                    <div class='mb-3'>
                        <label class='form-label'>Минимальное значение</label>
                        <input type='number' class='form-control' name='config[min]' value='{$min}'>
                    </div>
                </div>
                <div class='col-md-4'>
                    <div class='mb-3'>
                        <label class='form-label'>Максимальное значение</label>
                        <input type='number' class='form-control' name='config[max]' value='{$max}'>
                    </div>
                </div>
                <div class='col-md-4'>
                    <div class='mb-3'>
                        <label class='form-label'>Шаг</label>
                        <input type='number' class='form-control' name='config[step]' value='{$step}' step='0.01'>
                    </div>
                </div>
            </div>
            <div class='row'>
                <div class='col-md-6'>
                    <div class='mb-3'>
                        <label class='form-label'>Плейсхолдер</label>
                        <input type='text' class='form-control' name='config[placeholder]' value='{$placeholder}'>
                    </div>
                </div>
                <div class='col-md-6'>
                    <div class='mb-3'>
                        <label class='form-label'>Значение по умолчанию</label>
                        <input type='number' class='form-control' name='config[default_value]' value='{$defaultValue}'>
                    </div>
                </div>
            </div>
        ";
    }
}