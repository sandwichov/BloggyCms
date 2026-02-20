<?php

/**
 * Поле типа "выпадающий список" для системы пользовательских полей
 * Позволяет выбрать одно значение из предопределенного списка опций
 * Поддерживает шорткоды для гибкого отображения выбранного значения
 * 
 * @package Fields
 * @extends BaseField
 */
class SelectField extends BaseField {

    /**
     * Возвращает тип поля
     * 
     * @return string 'select'
     */
    public function getType(): string {
        return 'select';
    }
    
    /**
     * Возвращает отображаемое название типа поля
     * 
     * @return string 'Список'
     */
    public function getName(): string {
        return 'Список';
    }
    
    /**
     * Генерирует HTML для редактирования поля в форме
     * Создает выпадающий список с опциями из настроек
     * 
     * @param mixed $value Текущее значение поля
     * @param string $entityType Тип сущности (post, user, category и т.д.)
     * @param int $entityId ID сущности
     * @return string HTML-код для редактирования
     */
    public function renderInput($value, $entityType, $entityId): string {
        $required = isset($this->config['required']) && $this->config['required'] ? 'required' : '';
        $options = $this->config['options'] ?? [];
        
        $html = "<select name='field_{$this->systemName}' class='form-select form-select-sm' {$required}>";
        $html .= "<option value=''>-- Выберите --</option>";
        
        foreach ($options as $optionValue => $optionLabel) {
            $selected = $value == $optionValue ? 'selected' : '';
            $html .= "<option value='" . htmlspecialchars($optionValue) . "' {$selected}>" . htmlspecialchars($optionLabel) . "</option>";
        }
        
        $html .= "</select>";
        return $html;
    }
    
    /**
     * Генерирует HTML для отображения значения поля в детальном просмотре
     * Показывает выбранную опцию (метку, а не значение)
     * 
     * @param mixed $value Значение поля
     * @param string $entityType Тип сущности
     * @param int $entityId ID сущности
     * @return string HTML-код для отображения
     */
    public function renderDisplay($value, $entityType, $entityId): string {
        $options = $this->config['options'] ?? [];
        $label = $options[$value] ?? $value;
        
        if (empty($value)) return '<span class="text-muted">Не выбрано</span>';
        
        return "<span class='field-select'>{$label}</span>";
    }
    
    /**
     * Генерирует HTML для отображения значения поля в списке
     * Показывает выбранную опцию в виде бейджа
     * 
     * @param mixed $value Значение поля
     * @param string $entityType Тип сущности
     * @param int $entityId ID сущности
     * @return string HTML-код для отображения в списке
     */
    public function renderList($value, $entityType, $entityId): string {
        $options = $this->config['options'] ?? [];
        $label = $options[$value] ?? $value;
        
        if (empty($value)) return '<span class="text-muted">-</span>';
        
        return "<span class='badge bg-secondary'>{$label}</span>";
    }

    /**
     * Возвращает шорткод для поля выбора
     * Регистрирует простой и парный варианты шорткода
     * 
     * @return string Имя шорткода
     */
    public function getShortcode(): string {
        $systemName = $this->getSystemName();
        $entityType = $this->getEntityType();
        
        $shortcodeName = $entityType . '_' . $systemName;
        
        // Регистрируем два типа шорткодов: простой и парный
        Shortcodes::add($shortcodeName, function($attrs, $content = null) {
            if ($content !== null) {
                return $this->renderPairedSelectShortcode($attrs, $content);
            }
            return $this->renderSimpleSelectShortcode($attrs);
        });
        
        return $shortcodeName;
    }

    /**
     * Рендерит простой шорткод для select
     * 
     * @param array $attrs Атрибуты шорткода
     * @return string Значение или метка выбранной опции
     */
    private function renderSimpleSelectShortcode($attrs): string {
        $entityId = $attrs['id'] ?? $this->entityId;
        $value = $this->getValue($entityId);
        
        if ($value === null || $value === '') {
            return $attrs['default'] ?? '';
        }
        
        $options = $this->config['options'] ?? [];
        $displayValue = $options[$value] ?? $value;
        
        // Возможность вернуть значение вместо метки
        if (isset($attrs['return']) && $attrs['return'] === 'value') {
            return htmlspecialchars($value);
        }
        
        return htmlspecialchars($displayValue);
    }

    /**
     * Рендерит парный шорткод для select
     * Позволяет обернуть выбранное значение в произвольный HTML
     * 
     * @param array $attrs Атрибуты шорткода
     * @param string $content Содержимое шорткода (шаблон)
     * @return string HTML результат
     */
    private function renderPairedSelectShortcode($attrs, $content): string {
        $entityId = $attrs['id'] ?? $this->entityId;
        $value = $this->getValue($entityId);
        
        if ($value === null || $value === '') {
            return $attrs['default'] ?? '';
        }
        
        $options = $this->config['options'] ?? [];
        $displayValue = $options[$value] ?? $value;
        
        // Заменяем плейсхолдеры в контенте
        $result = $content;
        $result = str_replace('{value}', htmlspecialchars($value), $result);
        $result = str_replace('{label}', htmlspecialchars($displayValue), $result);
        
        return $result;
    }
    
    /**
     * Возвращает HTML-форму для настройки поля в административной панели
     * Позволяет задать опции списка и значение по умолчанию
     * 
     * @return string HTML-код формы настроек
     */
    public function getSettingsForm(): string {
        $options = $this->config['options'] ?? [];
        $optionsText = '';
        
        foreach ($options as $value => $label) {
            $optionsText .= "{$value}|{$label}\n";
        }
        
        $defaultValue = htmlspecialchars($this->config['default_value'] ?? '');
        
        return "
            <div class='mb-3'>
                <label class='form-label'>Опции списка</label>
                <textarea class='form-control' name='config[options_text]' rows='6' placeholder='значение|Название опции'>" . trim($optionsText) . "</textarea>
                <div class='form-text'>Каждая опция с новой строки в формате: значение|Название<br>Пример:<br>red|Красный<br>green|Зеленый<br>blue|Синий</div>
            </div>
            <div class='mb-3'>
                <label class='form-label'>Значение по умолчанию</label>
                <input type='text' class='form-control' name='config[default_value]' value='{$defaultValue}' placeholder='значение из списка'>
                <div class='form-text'>Введите одно из значений из списка выше</div>
            </div>
        ";
    }
    
    /**
     * Обрабатывает конфигурацию поля после отправки формы
     * Преобразует текстовый список опций в массив
     * 
     * @param array $config Исходная конфигурация
     * @return array Обработанная конфигурация
     */
    public function processConfig($config) {
        // Преобразуем текстовый список опций в массив
        if (isset($config['options_text'])) {
            $options = [];
            $lines = explode("\n", $config['options_text']);
            foreach ($lines as $line) {
                $line = trim($line);
                if (!empty($line)) {
                    $parts = explode('|', $line, 2);
                    if (count($parts) === 2) {
                        $key = trim($parts[0]);
                        $value = trim($parts[1]);
                        if (!empty($key) && !empty($value)) {
                            $options[$key] = $value;
                        }
                    }
                }
            }
            $config['options'] = $options;
            unset($config['options_text']);
        }
        return $config;
    }
}