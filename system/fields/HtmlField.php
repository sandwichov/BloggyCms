<?php

/**
 * Поле типа "HTML-блок" для системы пользовательских полей
 * Позволяет вводить и сохранять произвольный HTML-код
 * Предназначен для хранения форматированного контента
 * 
 * @package Fields
 * @extends BaseField
 */
class HtmlField extends BaseField {
    
    /**
     * Возвращает тип поля
     * 
     * @return string 'html'
     */
    public function getType(): string {
        return 'html';
    }
    
    /**
     * Возвращает отображаемое название типа поля
     * 
     * @return string 'HTML-блок'
     */
    public function getName(): string {
        return 'HTML-блок';
    }
    
    /**
     * Генерирует HTML для редактирования поля в форме
     * Создает текстовую область (textarea) для ввода HTML-кода
     * 
     * @param mixed $value Текущее значение поля
     * @param string $entityType Тип сущности (post, user, category и т.д.)
     * @param int $entityId ID сущности
     * @return string HTML-код для редактирования
     */
    public function renderInput($value, $entityType, $entityId): string {
        $required = isset($this->config['required']) && $this->config['required'] ? 'required' : '';
        $rows = $this->config['rows'] ?? 6;
        
        return "
            <textarea name='field_{$this->systemName}' 
                      class='form-control form-control-sm'
                      rows='{$rows}'
                      placeholder='Введите HTML код...'
                      {$required}>" . htmlspecialchars($value) . "</textarea>
            <div class='form-text'>Поддерживается HTML разметка</div>
        ";
    }
    
    /**
     * Генерирует HTML для отображения значения поля в детальном просмотре
     * Возвращает значение напрямую (без экранирования) для корректного отображения HTML
     * 
     * @param mixed $value Значение поля
     * @param string $entityType Тип сущности
     * @param int $entityId ID сущности
     * @return string Исходный HTML-код
     */
    public function renderDisplay($value, $entityType, $entityId): string {
        return $value;
    }
    
    /**
     * Генерирует HTML для отображения значения поля в списке
     * Удаляет HTML-теги, обрезает длинный текст, добавляет всплывающую подсказку
     * 
     * @param mixed $value Значение поля
     * @param string $entityType Тип сущности
     * @param int $entityId ID сущности
     * @return string Обрезанный текст без HTML-тегов
     */
    public function renderList($value, $entityType, $entityId): string {
        $stripped = strip_tags($value);
        $truncated = mb_strlen($stripped) > 50 ? mb_substr($stripped, 0, 50) . '...' : $stripped;
        return "<span title='" . htmlspecialchars($stripped) . "'>" . htmlspecialchars($truncated) . "</span>";
    }
    
    /**
     * Возвращает HTML-форму для настройки поля в административной панели
     * Позволяет задать количество строк textarea и значение по умолчанию
     * 
     * @return string HTML-код формы настроек
     */
    public function getSettingsForm(): string {
        $defaultValue = htmlspecialchars($this->config['default_value'] ?? '');
        $rows = htmlspecialchars($this->config['rows'] ?? '6');
        
        return "
            <div class='row'>
                <div class='col-md-6'>
                    <div class='mb-3'>
                        <label class='form-label'>Количество строк</label>
                        <input type='number' class='form-control' name='config[rows]' value='{$rows}' min='3' max='20'>
                    </div>
                </div>
            </div>
            <div class='mb-3'>
                <label class='form-label'>Значение по умолчанию</label>
                <textarea class='form-control' name='config[default_value]' rows='4'>{$defaultValue}</textarea>
            </div>
        ";
    }
}