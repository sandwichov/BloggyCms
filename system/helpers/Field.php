<?php

/**
 * Абстрактный базовый класс для всех типов полей в системе управления формами
 * Предоставляет общую функциональность для рендеринга, валидации и условного отображения
 * 
 * @package Fields
 */
abstract class Field {
    
    /** @var string Имя поля */
    protected $name;
    
    /** @var array Опции и конфигурация поля */
    protected $options;
    
    /**
     * Конструктор поля
     * 
     * @param string $name Имя поля
     * @param array $options Опции поля:
     *                       - title: заголовок поля
     *                       - hint: подсказка под полем
     *                       - default: значение по умолчанию
     *                       - required: обязательность
     *                       - placeholder: placeholder
     *                       - class: дополнительные CSS классы
     *                       - attributes: дополнительные HTML атрибуты
     *                       - show: условие показа
     *                       - show_class: класс для условного отображения
     */
    public function __construct($name, $options = []) {
        $this->name = $name;
        $this->options = array_merge([
            'title' => '',
            'hint' => '',
            'default' => '',
            'required' => false,
            'placeholder' => '',
            'class' => '',
            'attributes' => [],
            'show' => null,
            'show_class' => 'field-conditional'
        ], $options);
    }
    
    /**
     * Получает имя поля
     * 
     * @return string
     */
    public function getName() {
        return $this->name;
    }
    
    /**
     * Абстрактный метод для рендеринга поля
     * Должен быть реализован в классах-наследниках
     * 
     * @param mixed $currentValue Текущее значение поля
     * @return string HTML-код поля
     */
    abstract public function render($currentValue = null);
    
    /**
     * Генерирует строку с HTML-атрибутами для поля ввода
     * Включает name, class, placeholder, required и пользовательские атрибуты
     * 
     * @return string Строка с HTML-атрибутами
     */
    protected function getAttributes() {
        $attrs = [];
        
        // Для админских форм используем простое имя, для настроек - settings[name]
        if ($this->options['admin_mode'] ?? false) {
            $attrs['name'] = $this->name; // Простое имя для админки
        } else {
            $attrs['name'] = "settings[{$this->name}]"; // Для настроек
        }
        
        // Определение класса в зависимости от типа поля
        if ($this instanceof FieldSelect) {
            $attrs['class'] = "form-select {$this->options['class']}";
        } else {
            $attrs['class'] = "form-control {$this->options['class']}";
        }
        
        $attrs['placeholder'] = $this->options['placeholder'];
        
        if ($this->options['required']) {
            $attrs['required'] = 'required';
        }
        
        // Добавление пользовательских атрибутов
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
    
    /**
     * Оборачивает HTML поля в стандартную группу с заголовком и подсказкой
     * 
     * @param string $fieldHtml HTML-код поля
     * @param array $formData Данные формы для условного отображения
     * @return string Полный HTML группы поля
     */
    protected function renderFieldGroup($fieldHtml, $formData = []) {
        $requiredBadge = $this->options['required'] ? ' <span class="text-danger">*</span>' : '';
        $hint = $this->options['hint'] ? '<div class="form-text">' . htmlspecialchars($this->options['hint']) . '</div>' : '';
        
        $conditionalAttrs = $this->getConditionalAttributes($formData);
        
        // Индикатор зависимости
        $dependencyIndicator = '';
        if ($this->isConditional()) {
            $dependencyIndicator = $this->renderDependencyIndicator($formData);
        }
        
        // Дополнительный CSS класс для условных полей
        $conditionalClass = $this->isConditional() ? 'field-dependent' : '';
        
        return "
        <div class=\"mb-3 {$conditionalClass}\" data-field-name=\"{$this->name}\" {$conditionalAttrs}>
            {$dependencyIndicator}
            <label class=\"form-label\">{$this->options['title']}{$requiredBadge}</label>
            {$fieldHtml}
            {$hint}
        </div>";
    }

    /**
     * Рендерит индикатор зависимости для условного поля
     * 
     * @param array $formData Данные формы
     * @return string HTML индикатора
     */
    protected function renderDependencyIndicator($formData) {
        $showCondition = $this->options['show'] ?? null;
        if (empty($showCondition)) {
            return '';
        }
        
        $parentField = $this->extractParentFieldFromCondition($showCondition);
        if (!$parentField) {
            return '';
        }
        
        $isVisible = $this->shouldShow($formData);
        $visibilityClass = $isVisible ? '' : 'dependency-hidden';
        
        // Возвращаем пустую строку (метод не завершен в оригинале)
        return '';
    }

    /**
     * Извлекает имя родительского поля из условия
     * 
     * @param string $condition Условие показа
     * @return string|null Имя поля или null
     */
    protected function extractParentFieldFromCondition($condition) {
        // Формат: "field:field_name = value"
        if (preg_match('/^field:(\w+)/', $condition, $matches)) {
            return $matches[1];
        }
        return null;
    }

    /**
     * Получает заголовок родительского поля
     * 
     * @param string $parentFieldName Имя родительского поля
     * @return string Заголовок поля
     */
    protected function getParentFieldTitle($parentFieldName) {
        // В реальной реализации нужно получить заголовок поля из контекста
        // Пока возвращаем просто имя поля
        return $parentFieldName;
    }

    /**
     * Получает заголовок поля
     * 
     * @return string
     */
    public function getTitle() {
        return $this->options['title'] ?? '';
    }
    
    /**
     * Получает подсказку поля
     * 
     * @return string
     */
    public function getHint() {
        return $this->options['hint'] ?? '';
    }
    
    /**
     * Получает placeholder поля
     * 
     * @return string
     */
    public function getPlaceholder() {
        return $this->options['placeholder'] ?? '';
    }
    
    /**
     * Получает минимальное значение (для числовых полей)
     * 
     * @return mixed
     */
    public function getMin() {
        return $this->options['min'] ?? null;
    }
    
    /**
     * Получает максимальное значение (для числовых полей)
     * 
     * @return mixed
     */
    public function getMax() {
        return $this->options['max'] ?? null;
    }
    
    /**
     * Получает список опций (для select и multiselect)
     * 
     * @return array
     */
    public function getOptions() {
        return $this->options['options'] ?? [];
    }

    /**
     * Проверяет, должно ли поле отображаться на основе условия
     * 
     * @param array $formData Данные формы
     * @return bool true если поле должно быть видимо
     */
    public function shouldShow($formData) {
        $showCondition = $this->options['show'] ?? null;
        
        if (empty($showCondition)) {
            return true;
        }
        
        return $this->evaluateShowCondition($showCondition, $formData);
    }

    /**
     * Парсит и вычисляет условие показа поля
     * Поддерживает форматы:
     * - field:field_name = value
     * - field:field_name (проверка на истинность)
     * - field:field_name in value1,value2,value3
     * 
     * @param string $condition Условие
     * @param array $formData Данные формы
     * @return bool Результат проверки
     */
    protected function evaluateShowCondition($condition, $formData) {
        // Формат: "field:field_name = value"
        if (preg_match('/^field:(\w+)\s*([!=<>]+)\s*(.+)$/', $condition, $matches)) {
            $targetField = $matches[1];
            $operator = $matches[2];
            $expectedValue = $matches[3];
            $actualValue = $this->getFieldValue($targetField, $formData);
            return $this->compareValues($actualValue, $operator, $expectedValue);
        }
        
        // Формат: "field:field_name" (просто проверка на истинность)
        if (preg_match('/^field:(\w+)$/', $condition, $matches)) {
            $targetField = $matches[1];
            $actualValue = $this->getFieldValue($targetField, $formData);
            return (bool)$actualValue;
        }
        
        // Формат: "field:field_name in value1,value2,value3"
        if (preg_match('/^field:(\w+)\s+in\s+(.+)$/', $condition, $matches)) {
            $targetField = $matches[1];
            $expectedValues = array_map('trim', explode(',', $matches[2]));
            $actualValue = $this->getFieldValue($targetField, $formData);
            return in_array($actualValue, $expectedValues);
        }
        
        return true;
    }

    /**
     * Получает значение поля из данных формы
     * 
     * @param string $fieldName Имя поля
     * @param array $formData Данные формы
     * @return mixed Значение поля или null
     */
    protected function getFieldValue($fieldName, $formData) {
        if (isset($formData['settings'][$fieldName])) {
            return $formData['settings'][$fieldName];
        }
        if (isset($formData[$fieldName])) {
            return $formData[$fieldName];
        }
        return null;
    }

    /**
     * Сравнивает значения по оператору
     * 
     * @param mixed $actual Фактическое значение
     * @param string $operator Оператор сравнения
     * @param mixed $expected Ожидаемое значение
     * @return bool Результат сравнения
     */
    protected function compareValues($actual, $operator, $expected) {
        if ($expected === 'true') $expected = true;
        if ($expected === 'false') $expected = false;
        if (is_numeric($expected)) $expected = (float)$expected;
        if (is_numeric($actual)) $actual = (float)$actual;
        
        switch ($operator) {
            case '=': case '==': return $actual == $expected;
            case '!=': return $actual != $expected;
            case '>': return $actual > $expected;
            case '<': return $actual < $expected;
            case '>=': return $actual >= $expected;
            case '<=': return $actual <= $expected;
            default: return false;
        }
    }

    /**
     * Получает HTML-атрибуты для условного отображения через JavaScript
     * 
     * @param array $formData Данные формы
     * @return string Строка с data-атрибутами
     */
    public function getConditionalAttributes($formData) {
        $showCondition = $this->options['show'] ?? null;
        
        if (empty($showCondition)) {
            return '';
        }
        
        $isVisible = $this->shouldShow($formData);
        $hiddenClass = $isVisible ? '' : 'd-none';
        
        return " data-conditional=\"true\" data-condition=\"" . htmlspecialchars($showCondition) . "\" class=\"field-conditional {$hiddenClass}\"";
    }

    /**
     * Получает условие показа поля
     * 
     * @return string|null Условие или null
     */
    public function getShowCondition() {
        return $this->options['show'] ?? null;
    }

    /**
     * Проверяет, является ли поле условным (зависит от другого поля)
     * 
     * @return bool true если поле условное
     */
    public function isConditional() {
        return !empty($this->options['show']);
    }

}