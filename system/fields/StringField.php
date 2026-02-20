<?php

/**
 * Поле типа "текстовая строка" для системы пользовательских полей
 * Позволяет вводить однострочный текст с поддержкой валидации,
 * ограничений по длине и регулярных выражений
 * 
 * @package Fields
 * @extends BaseField
 */
class StringField extends BaseField {

    /**
     * Возвращает тип поля
     * 
     * @return string 'string'
     */
    public function getType(): string {
        return 'string';
    }
    
    /**
     * Возвращает отображаемое название типа поля
     * 
     * @return string 'Текстовая строка'
     */
    public function getName(): string {
        return 'Текстовая строка';
    }
    
    /**
     * Генерирует HTML для редактирования поля в форме
     * Создает input type="text" с поддержкой плейсхолдера и максимальной длины
     * 
     * @param mixed $value Текущее значение поля
     * @param string $entityType Тип сущности (post, user, category и т.д.)
     * @param int $entityId ID сущности
     * @return string HTML-код для редактирования
     */
    public function renderInput($value, $entityType, $entityId): string {
        $placeholder = $this->config['placeholder'] ?? '';
        $maxlength = $this->config['maxlength'] ?? '';
        $required = isset($this->config['required']) && $this->config['required'] ? 'required' : '';
        
        return "
            <input type='text' 
                   name='field_{$this->systemName}' 
                   value='" . htmlspecialchars($value) . "'
                   class='form-control form-control-sm'
                   placeholder='" . htmlspecialchars($placeholder) . "'
                   " . ($maxlength ? "maxlength='{$maxlength}'" : "") . "
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
        return "<span class='field-string'>" . htmlspecialchars($value) . "</span>";
    }
    
    /**
     * Генерирует HTML для отображения значения поля в списке
     * Обрезает длинный текст до 50 символов с многоточием
     * 
     * @param mixed $value Значение поля
     * @param string $entityType Тип сущности
     * @param int $entityId ID сущности
     * @return string HTML-код для отображения в списке
     */
    public function renderList($value, $entityType, $entityId): string {
        $truncated = mb_strlen($value) > 50 ? mb_substr($value, 0, 50) . '...' : $value;
        return "<span title='" . htmlspecialchars($value) . "'>" . htmlspecialchars($truncated) . "</span>";
    }
    
    /**
     * Валидирует значение поля
     * Проверяет обязательность, максимальную длину и соответствие регулярному выражению
     * 
     * @param mixed $value Значение для проверки
     * @return bool true если значение корректно
     */
    public function validate($value): bool {
        if (isset($this->config['required']) && $this->config['required'] && empty($value)) {
            return false;
        }
        
        if (!empty($this->config['maxlength']) && mb_strlen($value) > $this->config['maxlength']) {
            return false;
        }
        
        if (!empty($this->config['pattern']) && !preg_match($this->config['pattern'], $value)) {
            return false;
        }
        
        return true;
    }

    /**
     * Возвращает шорткод для строкового поля
     * Регистрирует шорткод с поддержкой форматирования
     * 
     * @return string Имя шорткода
     */
    public function getShortcode(): string {
        $systemName = $this->getSystemName();
        $entityType = $this->getEntityType();
        
        $shortcodeName = $entityType . '_' . $systemName;
        
        Shortcodes::add($shortcodeName, function($attrs) {
            return $this->renderStringShortcode($attrs);
        });
        
        return $shortcodeName;
    }

    /**
     * Рендерит шорткод для строкового поля
     * Поддерживает преобразование регистра и обрезку длины
     * 
     * @param array $attrs Атрибуты шорткода
     * @return string Отформатированное значение
     */
    private function renderStringShortcode($attrs): string {
        $entityId = $attrs['id'] ?? $this->entityId;
        $value = $this->getValue($entityId);
        
        if ($value === null || $value === '') {
            return $attrs['default'] ?? '';
        }
        
        $formattedValue = $value;
        
        // Преобразование регистра
        if (isset($attrs['uppercase']) && $attrs['uppercase'] === 'true') {
            $formattedValue = mb_strtoupper($formattedValue);
        }
        
        if (isset($attrs['lowercase']) && $attrs['lowercase'] === 'true') {
            $formattedValue = mb_strtolower($formattedValue);
        }
        
        if (isset($attrs['capitalize']) && $attrs['capitalize'] === 'true') {
            $formattedValue = mb_convert_case($formattedValue, MB_CASE_TITLE);
        }
        
        // Обрезка длины
        if (isset($attrs['maxlength'])) {
            $maxLength = (int)$attrs['maxlength'];
            if (mb_strlen($formattedValue) > $maxLength) {
                $formattedValue = mb_substr($formattedValue, 0, $maxLength) . '...';
            }
        }
        
        return htmlspecialchars($formattedValue);
    }
    
    /**
     * Возвращает HTML-форму для настройки поля в административной панели
     * Позволяет задать плейсхолдер, максимальную длину, регулярное выражение
     * и значение по умолчанию
     * 
     * @return string HTML-код формы настроек
     */
    public function getSettingsForm(): string {
        $placeholder = htmlspecialchars($this->config['placeholder'] ?? '');
        $maxlength = htmlspecialchars($this->config['maxlength'] ?? '');
        $pattern = htmlspecialchars($this->config['pattern'] ?? '');
        $defaultValue = htmlspecialchars($this->config['default_value'] ?? '');
        
        return "
            <div class='row'>
                <div class='col-md-6'>
                    <div class='mb-3'>
                        <label class='form-label'>Плейсхолдер</label>
                        <input type='text' class='form-control' name='config[placeholder]' value='{$placeholder}'>
                    </div>
                </div>
                <div class='col-md-6'>
                    <div class='mb-3'>
                        <label class='form-label'>Максимальная длина</label>
                        <input type='number' class='form-control' name='config[maxlength]' value='{$maxlength}' min='1'>
                    </div>
                </div>
            </div>
            <div class='row'>
                <div class='col-md-6'>
                    <div class='mb-3'>
                        <label class='form-label'>Регулярное выражение</label>
                        <input type='text' class='form-control' name='config[pattern]' value='{$pattern}' placeholder='Например: ^[a-zA-Z0-9]+$'>
                    </div>
                </div>
                <div class='col-md-6'>
                    <div class='mb-3'>
                        <label class='form-label'>Значение по умолчанию</label>
                        <input type='text' class='form-control' name='config[default_value]' value='{$defaultValue}'>
                    </div>
                </div>
            </div>
        ";
    }
}