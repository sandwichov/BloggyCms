<?php

/**
 * Поле типа "ссылка" для системы пользовательских полей
 * Позволяет вводить и отображать URL-адреса с возможностью настройки
 * текста ссылки и поведения (открытие в новой вкладке)
 * 
 * @package Fields
 * @extends BaseField
 */
class LinkField extends BaseField {
    
    /**
     * Возвращает тип поля
     * 
     * @return string 'link'
     */
    public function getType(): string {
        return 'link';
    }
    
    /**
     * Возвращает отображаемое название типа поля
     * 
     * @return string 'Ссылка'
     */
    public function getName(): string {
        return 'Ссылка';
    }
    
    /**
     * Генерирует HTML для редактирования поля в форме
     * Создает input type="url" для ввода веб-адреса
     * 
     * @param mixed $value Текущее значение поля
     * @param string $entityType Тип сущности (post, user, category и т.д.)
     * @param int $entityId ID сущности
     * @return string HTML-код для редактирования
     */
    public function renderInput($value, $entityType, $entityId): string {
        $required = isset($this->config['required']) && $this->config['required'] ? 'required' : '';
        $placeholder = $this->config['placeholder'] ?? 'https://example.com';
        
        return "
            <input type='url' 
                   name='field_{$this->systemName}' 
                   value='" . htmlspecialchars($value) . "'
                   class='form-control form-control-sm'
                   placeholder='{$placeholder}'
                   {$required}>
        ";
    }
    
    /**
     * Генерирует HTML для отображения значения поля в детальном просмотре
     * Создает кликабельную ссылку с настраиваемым текстом и поведением
     * 
     * @param mixed $value Значение поля (URL)
     * @param string $entityType Тип сущности
     * @param int $entityId ID сущности
     * @return string HTML-код для отображения
     */
    public function renderDisplay($value, $entityType, $entityId): string {
        if (empty($value)) return '<span class="text-muted">Не указана</span>';
        
        $text = $this->config['link_text'] ?? $value;
        $target = $this->config['new_tab'] ? 'target="_blank"' : '';
        
        return "<a href='{$value}' {$target} class='field-link'>{$text}</a>";
    }
    
    /**
     * Генерирует HTML для отображения значения поля в списке
     * Показывает иконку-ссылку
     * 
     * @param mixed $value Значение поля (URL)
     * @param string $entityType Тип сущности
     * @param int $entityId ID сущности
     * @return string HTML-код для отображения в списке
     */
    public function renderList($value, $entityType, $entityId): string {
        if (empty($value)) return '<span class="text-muted">-</span>';
        
        return "<a href='{$value}' target='_blank' class='text-decoration-none'>🔗</a>";
    }
    
    /**
     * Возвращает HTML-форму для настройки поля в административной панели
     * Позволяет настроить плейсхолдер, текст ссылки, значение по умолчанию
     * и поведение (открытие в новой вкладке)
     * 
     * @return string HTML-код формы настроек
     */
    public function getSettingsForm(): string {
        $placeholder = htmlspecialchars($this->config['placeholder'] ?? 'https://example.com');
        $linkText = htmlspecialchars($this->config['link_text'] ?? '');
        $newTab = isset($this->config['new_tab']) && $this->config['new_tab'] ? 'checked' : '';
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
                        <label class='form-label'>Текст ссылки</label>
                        <input type='text' class='form-control' name='config[link_text]' value='{$linkText}' placeholder='Оставить пустым для отображения URL'>
                    </div>
                </div>
            </div>
            <div class='row'>
                <div class='col-md-6'>
                    <div class='mb-3'>
                        <label class='form-label'>Значение по умолчанию</label>
                        <input type='url' class='form-control' name='config[default_value]' value='{$defaultValue}'>
                    </div>
                </div>
                <div class='col-md-6'>
                    <div class='mb-3 pt-4'>
                        <div class='form-check'>
                            <input class='form-check-input' type='checkbox' name='config[new_tab]' id='new_tab' {$newTab}>
                            <label class='form-check-label' for='new_tab'>
                                Открывать в новой вкладке
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        ";
    }
}