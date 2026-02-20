<?php

/**
 * Класс для группировки полей в логические блоки (fieldset)
 * Управляет отображением, зависимостями и компоновкой полей в сетке Bootstrap
 * 
 * @package Fields
 */
class Fieldset {
    
    /** @var string Заголовок группы */
    private $title;
    
    /** @var string Иконка для заголовка (класс Bootstrap Icons) */
    private $icon;
    
    /** @var string Количество колонок для полей (по умолчанию '6' - половина ширины) */
    private $columns;
    
    /** @var array Массив полей в группе */
    private $fields;
    
    /**
     * Конструктор fieldset
     * 
     * @param string $title Заголовок группы
     * @param array $options Опции группы:
     *                       - icon: класс иконки (например 'bi bi-gear')
     *                       - columns: количество колонок ('6', '4', '3' и т.д.)
     *                       - fields: массив начальных полей
     */
    public function __construct($title, $options = []) {
        $this->title = $title;
        $this->icon = $options['icon'] ?? '';
        $this->columns = $options['columns'] ?? '6';
        $this->fields = $options['fields'] ?? [];
    }
    
    /**
     * Добавляет поле в группу
     * 
     * @param Field $field Объект поля
     * @return self Для цепочки вызовов
     */
    public function addField($field) {
        $this->fields[] = $field;
        return $this;
    }
    
    /**
     * Рендерит всю группу полей
     * Учитывает зависимости между полями и компоновку в сетке
     * 
     * @param array $currentSettings Текущие значения настроек
     * @return string HTML-код группы
     */
    public function render($currentSettings) {
        $formData = ['settings' => $currentSettings];
        
        ob_start();
        ?>
        <div class="settings-group mb-4" data-fieldset-name="<?= htmlspecialchars($this->title) ?>">
            <h6 class="settings-group-title bg-light p-3 rounded">
                <?php if ($this->icon): ?>
                    <i class="<?= $this->icon ?> me-2"></i>
                <?php endif; ?>
                <?= $this->title ?>
            </h6>
            <div class="p-3">
                <div class="row">
                    <?php 
                    // Группируем поля по родительским зависимостям
                    $groupedFields = $this->groupFieldsByDependency($formData);
                    
                    foreach ($groupedFields as $fieldOrGroup):
                        if (is_array($fieldOrGroup)) {
                            // Это группа зависимых полей
                            echo $this->renderDependentGroup($fieldOrGroup, $formData);
                        } else {
                            // Одиночное поле
                            $field = $fieldOrGroup;
                            echo $this->renderSingleField($field, $formData);
                        }
                    endforeach;
                    ?>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Группирует поля по зависимостям
     * Зависимые поля помещаются в отдельную группу после их родительского поля
     * 
     * @param array $formData Данные формы
     * @return array Массив, где элементы могут быть либо полями, либо массивами зависимых полей
     */
    private function groupFieldsByDependency($formData) {
        $independentFields = [];
        $dependentFields = [];
        $dependencyMap = [];
        
        // Разделение полей на независимые и зависимые
        foreach ($this->fields as $field) {
            if (method_exists($field, 'isConditional') && $field->isConditional()) {
                $parentField = $this->getParentFieldName($field);
                if ($parentField) {
                    $dependentFields[$parentField][] = $field;
                    $dependencyMap[$parentField] = true;
                } else {
                    $independentFields[] = $field;
                }
            } else {
                $independentFields[] = $field;
            }
        }
        
        // Построение результата с учетом порядка
        $result = [];
        foreach ($independentFields as $field) {
            $result[] = $field;
            $fieldName = $field->getName();
            
            // Добавление зависимых полей после родителя
            if (isset($dependentFields[$fieldName])) {
                $result[] = $dependentFields[$fieldName];
            }
        }
        
        return $result;
    }

    /**
     * Получает имя родительского поля из условия
     * 
     * @param Field $field Поле с условием
     * @return string|null Имя родительского поля или null
     */
    private function getParentFieldName($field) {
        if (!method_exists($field, 'getShowCondition')) {
            return null;
        }
        
        $condition = $field->getShowCondition();
        if (preg_match('/^field:(\w+)/', $condition, $matches)) {
            return $matches[1];
        }
        
        return null;
    }

    /**
     * Рендерит группу зависимых полей
     * Зависимые поля отображаются вместе в отдельном контейнере
     * 
     * @param array $dependentFields Массив зависимых полей
     * @param array $formData Данные формы
     * @return string HTML-код группы зависимых полей
     */
    private function renderDependentGroup($dependentFields, $formData) {
        ob_start();
        ?>
        <div class="col-12 dependent-group-container mb-3">
            <div class="dependent-group">
                <?php foreach ($dependentFields as $field): ?>
                    <?php 
                    $conditionalAttrs = '';
                    if (method_exists($field, 'isConditional') && $field->isConditional()) {
                        $shouldShow = $field->shouldShow($formData);
                        $hiddenClass = $shouldShow ? '' : 'd-none';
                        $conditionalAttrs = " data-conditional=\"true\" data-condition=\"" . htmlspecialchars($field->getShowCondition()) . "\" class=\"field-conditional {$hiddenClass}\"";
                    }
                    
                    $colClass = $this->shouldFieldTakeFullWidth($field) ? 'col-12' : "col-md-{$this->columns}";
                    ?>
                    
                    <div class="<?= $colClass ?> mt-2"<?= $conditionalAttrs ?>>
                        <?= $field->render($formData['settings'][$field->getName()] ?? null) ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Рендерит одиночное поле
     * 
     * @param Field $field Объект поля
     * @param array $formData Данные формы
     * @return string HTML-код поля в колонке
     */
    private function renderSingleField($field, $formData) {
        $conditionalAttrs = '';
        if (method_exists($field, 'isConditional') && $field->isConditional()) {
            $shouldShow = $field->shouldShow($formData);
            $hiddenClass = $shouldShow ? '' : 'd-none';
            $conditionalAttrs = " data-conditional=\"true\" data-condition=\"" . htmlspecialchars($field->getShowCondition()) . "\" class=\"field-conditional {$hiddenClass}\"";
        }
        
        $colClass = $this->shouldFieldTakeFullWidth($field) ? 'col-12' : "col-md-{$this->columns}";
        
        ob_start();
        ?>
        <div class="<?= $colClass ?>"<?= $conditionalAttrs ?>>
            <?= $field->render($formData['settings'][$field->getName()] ?? null) ?>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Проверяет, должно ли поле занимать всю ширину (col-12)
     * 
     * @param Field $field Объект поля
     * @return bool true если поле должно быть на всю ширину
     */
    private function shouldFieldTakeFullWidth($field) {
        // Проверка через опции поля
        if (method_exists($field, 'getOptions')) {
            $options = $field->getOptions();
            if (isset($options['full_width']) && $options['full_width'] === true) {
                return true;
            }
        }
        
        // Поле-уведомление всегда на всю ширину
        if ($field instanceof FieldAlert) {
            return true;
        }
        
        // Проверка опций для условных полей
        if (method_exists($field, 'isConditional') && $field->isConditional()) {
            $options = $field->getOptions();
            if (isset($options['full_width']) && $options['full_width'] === true) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Получает список всех условных полей в fieldset
     * 
     * @return array Массив полей, у которых есть условие показа
     */
    public function getConditionalFields() {
        $conditionalFields = [];
        foreach ($this->fields as $field) {
            if (method_exists($field, 'isConditional') && $field->isConditional()) {
                $conditionalFields[] = $field;
            }
        }
        return $conditionalFields;
    }
}