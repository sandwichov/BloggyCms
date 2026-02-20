<?php

/**
 * Поле типа "мультивыбор" для системы пользовательских полей
 * Позволяет выбирать несколько значений из выпадающего списка
 * Сохраняет данные в формате JSON, поддерживает шорткоды и гибкое отображение
 * 
 * @package Fields
 * @extends BaseField
 */
class MultiSelectField extends BaseField {
    
    /**
     * Возвращает тип поля
     * 
     * @return string 'multiselect'
     */
    public function getType(): string {
        return 'multiselect';
    }
    
    /**
     * Возвращает отображаемое название типа поля
     * 
     * @return string 'Список: мультивыбор'
     */
    public function getName(): string {
        return 'Список: мультивыбор';
    }
    
    /**
     * Генерирует HTML для редактирования поля в форме
     * Создает select multiple с опциями из настроек
     * 
     * @param mixed $value Текущее значение поля (JSON строка)
     * @param string $entityType Тип сущности
     * @param int $entityId ID сущности
     * @return string HTML-код для редактирования
     */
    public function renderInput($value, $entityType, $entityId): string {
        $required = isset($this->config['required']) && $this->config['required'] ? 'required' : '';
        $options = $this->config['options'] ?? [];
        
        // Преобразование значения в массив
        $selectedValues = [];
        if (!empty($value)) {
            if (is_string($value)) {
                $selectedValues = json_decode($value, true) ?: [];
            } else {
                $selectedValues = (array)$value;
            }
        }
        
        $html = "<select name='field_{$this->systemName}[]' class='form-select form-select-sm' multiple {$required} style='height: 120px;'>";
        
        foreach ($options as $optionValue => $optionLabel) {
            $selected = in_array($optionValue, $selectedValues) ? 'selected' : '';
            $html .= "<option value='" . htmlspecialchars($optionValue) . "' {$selected}>" . htmlspecialchars($optionLabel) . "</option>";
        }
        
        $html .= "</select>";
        $html .= "<div class='form-text'>Зажмите Ctrl (Cmd на Mac) для выбора нескольких опций</div>";
        
        return $html;
    }
    
    /**
     * Генерирует HTML для отображения значения поля в детальном просмотре
     * Показывает выбранные опции в виде бейджей
     * 
     * @param mixed $value Значение поля (JSON строка)
     * @param string $entityType Тип сущности
     * @param int $entityId ID сущности
     * @return string HTML-код для отображения
     */
    public function renderDisplay($value, $entityType, $entityId): string {
        $items = $this->getIterableData($value, $entityType, $entityId);
        
        if (empty($items)) {
            return '<span class="text-muted">Не выбрано</span>';
        }
        
        // По умолчанию рендерим как баджи
        $html = '<div class="field-multiselect d-flex flex-wrap gap-1">';
        foreach ($items as $item) {
            $html .= '<span class="badge bg-secondary">' . 
                    htmlspecialchars($item['label']) . 
                    '</span>';
        }
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Генерирует HTML для отображения значения поля в списке
     * Показывает первую выбранную опцию с индикатором количества
     * 
     * @param mixed $value Значение поля (JSON строка)
     * @param string $entityType Тип сущности
     * @param int $entityId ID сущности
     * @return string HTML-код для отображения в списке
     */
    public function renderList($value, $entityType, $entityId): string {
        $items = $this->getIterableData($value, $entityType, $entityId);
        $count = count($items);
        
        if ($count === 0) {
            return '<span class="text-muted">-</span>';
        }
        
        $firstItem = $items[0];
        $display = htmlspecialchars($firstItem['label']);
        if ($count > 1) {
            $display .= " +" . ($count - 1);
        }
        
        $allValues = array_column($items, 'value');
        return "<span class='badge bg-secondary' title='" . 
               htmlspecialchars(implode(', ', $allValues)) . 
               "'>{$display}</span>";
    }
    
    /**
     * Обрабатывает значение перед сохранением
     * Преобразует массив в JSON строку
     * 
     * @param mixed $value Исходное значение
     * @return string JSON строка
     */
    public function processValue($value) {
        // Сохраняем как JSON
        if (is_array($value)) {
            return json_encode(array_values($value));
        }
        
        // Если пришла строка JSON, проверяем валидность
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $value; // Уже валидный JSON
            }
        }
        
        return json_encode([]); // По умолчанию пустой массив
    }
    
    /**
     * Возвращает HTML-форму для настройки поля в административной панели
     * Позволяет задать опции списка в формате "значение|Название"
     * 
     * @return string HTML-код формы настроек
     */
    public function getSettingsForm(): string {
        $options = $this->config['options'] ?? [];
        $optionsText = '';
        
        // Преобразование массива опций в текстовый формат
        foreach ($options as $value => $label) {
            $optionsText .= htmlspecialchars($value) . "|" . htmlspecialchars($label) . "\n";
        }
        
        return "
            <div class='mb-3'>
                <label class='form-label'>Опции списка</label>
                <textarea class='form-control' name='config[options_text]' rows='6' placeholder='значение|Название опции'>" . trim($optionsText) . "</textarea>
                <div class='form-text'>Каждая опция с новой строки в формате: значение|Название<br>Пример:<br>php|PHP<br>js|JavaScript<br>python|Python</div>
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
        // Преобразование текстового списка опций в массив
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

    /**
     * Валидирует значение поля
     * Проверяет обязательность и наличие выбранных опций
     * 
     * @param mixed $value Значение для проверки
     * @return bool true если значение корректно
     */
    public function validate($value): bool {
        if (isset($this->config['required']) && $this->config['required']) {
            if (empty($value)) return false;
            if (is_string($value)) {
                $decoded = json_decode($value, true);
                return !empty($decoded);
            }
            return !empty($value);
        }
        return true;
    }
    
    /**
     * Получает значения как массив
     * 
     * @param mixed $value Значение поля
     * @return array Массив выбранных значений
     */
    public function getValuesArray($value): array {
        if (empty($value)) {
            return [];
        }
        
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            return is_array($decoded) ? $decoded : [];
        }
        
        return (array)$value;
    }
    
    /**
     * Возвращает шорткод для MultiSelectField
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
                return $this->renderPairedShortcode($attrs, $content);
            }
            return $this->renderSimpleShortcode($attrs);
        });
        
        return $shortcodeName;
    }
    
    /**
     * Рендерит простой шорткод (без содержимого)
     * Поддерживает различные форматы вывода
     * 
     * @param array $attrs Атрибуты шорткода
     * @return string HTML результат
     */
    private function renderSimpleShortcode($attrs): string {
        $entityId = $attrs['id'] ?? $this->entityId;
        $value = $this->getValue($entityId);
        
        if (empty($value)) {
            return $attrs['default'] ?? '';
        }
        
        $selectedValues = json_decode($value, true) ?? [];
        $options = $this->config['options'] ?? [];
        
        $format = $attrs['format'] ?? 'list'; // list, badges, comma, count
        $separator = $attrs['separator'] ?? ', ';
        $badgeClass = $attrs['badge_class'] ?? 'badge bg-secondary';
        
        if (empty($selectedValues)) {
            return $attrs['empty'] ?? 'Не выбрано';
        }
        
        switch ($format) {
            case 'badges':
                $labels = [];
                foreach ($selectedValues as $val) {
                    $label = $options[$val] ?? $val;
                    $labels[] = '<span class="' . htmlspecialchars($badgeClass) . ' me-1">' . 
                               htmlspecialchars($label) . '</span>';
                }
                return implode('', $labels);
                
            case 'comma':
                $labels = [];
                foreach ($selectedValues as $val) {
                    $labels[] = $options[$val] ?? $val;
                }
                return implode($separator, $labels);
                
            case 'count':
                $count = count($selectedValues);
                if (isset($attrs['show_labels']) && $attrs['show_labels'] === 'true' && $count > 0) {
                    $firstValue = $selectedValues[0];
                    $firstLabel = $options[$firstValue] ?? $firstValue;
                    $result = htmlspecialchars($firstLabel);
                    if ($count > 1) {
                        $result .= " +" . ($count - 1);
                    }
                    return $result;
                }
                return (string)$count;
                
            case 'list':
            default:
                $labels = [];
                foreach ($selectedValues as $val) {
                    $labels[] = $options[$val] ?? $val;
                }
                return '<ul class="list-unstyled mb-0"><li>' . 
                       implode('</li><li>', array_map('htmlspecialchars', $labels)) . 
                       '</li></ul>';
        }
    }
    
    /**
     * Рендерит парный шорткод для итерации по выбранным значениям
     * Поддерживает условные блоки {if_first}, {if_last}, {if_even}, {if_odd}
     * 
     * @param array $attrs Атрибуты шорткода
     * @param string $content Содержимое шорткода (шаблон для каждого элемента)
     * @return string HTML результат
     */
    private function renderPairedShortcode($attrs, $content): string {
        $entityId = $attrs['id'] ?? $this->entityId;
        $value = $this->getValue($entityId);
        
        if (empty($value)) {
            return $attrs['empty'] ?? '';
        }
        
        $selectedValues = json_decode($value, true) ?? [];
        $options = $this->config['options'] ?? [];
        
        $result = '';
        $counter = 0;
        
        foreach ($selectedValues as $index => $val) {
            $counter++;
            $itemContent = $content;
            
            // Замена простых плейсхолдеров
            $itemContent = str_replace('{index}', $counter, $itemContent);
            $itemContent = str_replace('{value}', htmlspecialchars($val), $itemContent);
            $itemContent = str_replace('{label}', htmlspecialchars($options[$val] ?? $val), $itemContent);
            
            // Обработка условных блоков
            $itemContent = $this->processConditionalBlocks($itemContent, $counter, count($selectedValues));
            
            $result .= $itemContent;
        }
        
        return $result;
    }

    /**
     * Обрабатывает условные блоки в парном шорткоде
     * 
     * @param string $content Содержимое для обработки
     * @param int $counter Текущая позиция (1-индексированная)
     * @param int $total Общее количество элементов
     * @return string Обработанное содержимое
     */
    private function processConditionalBlocks($content, $counter, $total) {
        // {if_first}
        if ($counter === 1) {
            $content = preg_replace('/\{if_first\}(.*?)\{\/if_first\}/s', '$1', $content);
        } else {
            $content = preg_replace('/\{if_first\}(.*?)\{\/if_first\}/s', '', $content);
        }
        
        // {if_last}
        if ($counter === $total) {
            $content = preg_replace('/\{if_last\}(.*?)\{\/if_last\}/s', '$1', $content);
        } else {
            $content = preg_replace('/\{if_last\}(.*?)\{\/if_last\}/s', '', $content);
        }
        
        // {if_even} и {if_odd}
        if ($counter % 2 === 0) {
            $content = preg_replace('/\{if_even\}(.*?)\{\/if_even\}/s', '$1', $content);
            $content = preg_replace('/\{if_odd\}(.*?)\{\/if_odd\}/s', '', $content);
        } else {
            $content = preg_replace('/\{if_even\}(.*?)\{\/if_even\}/s', '', $content);
            $content = preg_replace('/\{if_odd\}(.*?)\{\/if_odd\}/s', '$1', $content);
        }
        
        return $content;
    }

    /**
     * Подготавливает конфигурацию для отображения в форме
     * 
     * @param array $config Конфигурация поля
     * @return array Подготовленная конфигурация
     */
    public function prepareConfigForForm(array $config): array {
        // Если есть опции в виде массива, преобразуем их в текстовый формат
        if (isset($config['options']) && is_array($config['options'])) {
            $optionsText = '';
            foreach ($config['options'] as $value => $label) {
                $optionsText .= htmlspecialchars($value) . "|" . htmlspecialchars($label) . "\n";
            }
            $config['options_text'] = trim($optionsText);
        }
        return $config;
    }
    
    /**
     * Форматирует значение для шорткода
     * 
     * @param mixed $value Значение поля
     * @param array $attrs Атрибуты шорткода
     * @return string Отформатированное значение
     */
    protected function formatShortcodeValue($value, $attrs): string {
        return $this->renderSimpleShortcode(array_merge($attrs, ['id' => $this->entityId]));
    }
}