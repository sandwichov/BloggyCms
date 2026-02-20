<?php

/**
 * Поле типа "флаг" (булево значение) для системы пользовательских полей
 * Реализует переключатель (checkbox/switch) для хранения логических значений
 * С различными вариантами отображения в списках и детальном просмотре
 * 
 * @package Fields
 * @extends BaseField
 */
class FlagField extends BaseField {
    
    /**
     * Возвращает тип поля
     * 
     * @return string 'flag'
     */
    public function getType(): string {
        return 'flag';
    }
    
    /**
     * Возвращает отображаемое название типа поля
     * 
     * @return string 'Флаг'
     */
    public function getName(): string {
        return 'Флаг';
    }
    
    /**
     * Генерирует HTML для редактирования поля в форме
     * Создает скрытое поле (для отправки 0 при выключенном чекбоксе)
     * и видимый переключатель
     * 
     * @param mixed $value Текущее значение поля
     * @param string $entityType Тип сущности (post, user, category и т.д.)
     * @param int $entityId ID сущности
     * @return string HTML-код для редактирования
     */
    public function renderInput($value, $entityType, $entityId): string {
        $required = isset($this->config['required']) && $this->config['required'] ? 'required' : '';
        $label = $this->config['label'] ?? 'Включено';
        
        $checked = $value ? 'checked' : '';
        
        return "
            <div class='form-check form-switch'>
                <input type='hidden' name='field_{$this->systemName}' value='0'>
                <input type='checkbox' 
                       name='field_{$this->systemName}' 
                       value='1'
                       class='form-check-input'
                       {$checked}
                       {$required}>
                <label class='form-check-label'>{$label}</label>
            </div>
        ";
    }
    
    /**
     * Генерирует HTML для отображения значения поля в детальном просмотре
     * Показывает бейдж с текстом для true/false
     * 
     * @param mixed $value Значение поля
     * @param string $entityType Тип сущности
     * @param int $entityId ID сущности
     * @return string HTML-код для отображения
     */
    public function renderDisplay($value, $entityType, $entityId): string {
        $trueText = $this->config['true_text'] ?? 'Да';
        $falseText = $this->config['false_text'] ?? 'Нет';
        
        if ($value) {
            return "<span class='badge bg-success'><i class='bi bi-check-lg'></i> {$trueText}</span>";
        } else {
            return "<span class='badge bg-secondary'><i class='bi bi-x'></i> {$falseText}</span>";
        }
    }
    
    /**
     * Генерирует HTML для отображения значения поля в списке
     * Показывает иконку с цветовой индикацией
     * 
     * @param mixed $value Значение поля
     * @param string $entityType Тип сущности
     * @param int $entityId ID сущности
     * @return string HTML-код для отображения в списке
     */
    public function renderList($value, $entityType, $entityId): string {
        if ($value) {
            return "<i class='bi bi-check-circle-fill text-success' title='Да'></i>";
        } else {
            return "<i class='bi bi-x-circle-fill text-secondary' title='Нет'></i>";
        }
    }
    
    /**
     * Обрабатывает значение перед сохранением в базу данных
     * Преобразует различные представления булевых значений в 0 или 1
     * 
     * @param mixed $value Исходное значение
     * @return int 1 для true, 0 для false
     */
    public function processValue($value) {
        if ($value === '1' || $value === 1 || $value === true) {
            return 1;
        } else {
            return 0;
        }
    }
    
    /**
     * Возвращает HTML-форму для настройки поля в административной панели
     * Позволяет настроить текст переключателя, тексты для true/false и значение по умолчанию
     * 
     * @return string HTML-код формы настроек
     */
    public function getSettingsForm(): string {
        $label = htmlspecialchars($this->config['label'] ?? 'Включено');
        $trueText = htmlspecialchars($this->config['true_text'] ?? 'Да');
        $falseText = htmlspecialchars($this->config['false_text'] ?? 'Нет');
        $defaultValue = isset($this->config['default_value']) && $this->config['default_value'] ? 'checked' : '';
        
        return "
            <div class='row'>
                <div class='col-md-6'>
                    <div class='mb-3'>
                        <label class='form-label'>Текст переключателя</label>
                        <input type='text' class='form-control' name='config[label]' value='{$label}'>
                    </div>
                </div>
                <div class='col-md-6'>
                    <div class='mb-3'>
                        <label class='form-label'>Значение по умолчанию</label>
                        <div class='form-check pt-2'>
                            <input class='form-check-input' type='checkbox' name='config[default_value]' id='default_value' {$defaultValue}>
                            <label class='form-check-label' for='default_value'>
                                Включено по умолчанию
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            <div class='row'>
                <div class='col-md-6'>
                    <div class='mb-3'>
                        <label class='form-label'>Текст для 'Да'</label>
                        <input type='text' class='form-control' name='config[true_text]' value='{$trueText}'>
                    </div>
                </div>
                <div class='col-md-6'>
                    <div class='mb-3'>
                        <label class='form-label'>Текст для 'Нет'</label>
                        <input type='text' class='form-control' name='config[false_text]' value='{$falseText}'>
                    </div>
                </div>
            </div>
        ";
    }
}