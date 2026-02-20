<?php

/**
 * Поле типа "повторитель" (repeater) для системы полей
 * Позволяет создавать повторяющиеся группы полей с поддержкой различных типов
 * Включает JavaScript для динамического добавления/удаления элементов
 * 
 * @package Fields
 * @extends Field
 */
class FieldRepeater extends Field {
    
    /**
     * Рендерит HTML-код поля-повторителя
     * Создает структуру с существующими элементами, шаблоном для новых
     * и JavaScript для управления добавлением/удалением
     * 
     * @param mixed $currentValue Текущее значение поля (массив элементов)
     * @return string HTML-код поля
     */
    public function render($currentValue = null) {
        $values = $currentValue !== null ? $currentValue : [];
        $subFieldsConfig = $this->options['fields'] ?? [];
        
        ob_start();
        ?>
        <div class="repeater-field" data-field-name="<?= $this->name ?>">
            <!-- Контейнер для существующих элементов -->
            <div class="repeater-items">
                <?php foreach ($values as $index => $item): ?>
                <div class="repeater-item card mb-3">
                    <div class="card-body">
                        <div class="row">
                            <?php foreach ($subFieldsConfig as $fieldConfig): ?>
                            <div class="<?= $this->getColumnClass($fieldConfig) ?>">
                                <div class="mb-3">
                                    <?php
                                    $fieldName = $fieldConfig['name'] ?? '';
                                    $fieldTitle = $fieldConfig['title'] ?? '';
                                    $fieldType = $fieldConfig['type'] ?? 'string';
                                    $fieldValue = $item[$fieldName] ?? '';
                                    
                                    // ВАЖНО: Для полей в repeater используем другую структуру имен
                                    $hiddenFieldName = "settings[{$this->name}][{$index}][{$fieldName}]";
                                    $fileFieldName = "{$this->name}[{$index}][{$fieldName}_file]"; // Без settings[]!
                                    $removeFieldName = "{$this->name}[{$index}][remove_{$fieldName}]"; // Без settings[]!
                                    
                                    echo '<label class="form-label">' . $fieldTitle . '</label>';
                                    
                                    // Рендеринг поля в зависимости от типа
                                    $this->renderFieldByType($fieldConfig, $fieldValue, $hiddenFieldName, $fileFieldName, $removeFieldName, $index, $fieldType);
                                    
                                    if (!empty($fieldConfig['hint'])) {
                                        echo '<div class="form-text">' . $fieldConfig['hint'] . '</div>';
                                    }
                                    ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-danger repeater-remove-btn mt-2">
                            <i class="bi bi-trash"></i> Удалить
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Кнопка добавления нового элемента -->
            <button type="button" class="btn btn-outline-primary repeater-add-btn">
                <i class="bi bi-plus"></i> Добавить элемент
            </button>
            
            <!-- Шаблон для нового элемента (скрыт) -->
            <template class="repeater-template">
                <div class="repeater-item card mb-3">
                    <div class="card-body">
                        <div class="row">
                            <?php foreach ($subFieldsConfig as $fieldConfig): ?>
                            <div class="<?= $this->getColumnClass($fieldConfig) ?>">
                                <div class="mb-3">
                                    <?php
                                    $fieldName = $fieldConfig['name'] ?? '';
                                    $fieldTitle = $fieldConfig['title'] ?? '';
                                    $fieldType = $fieldConfig['type'] ?? 'string';
                                    
                                    $hiddenFieldName = "settings[{$this->name}][__INDEX__][{$fieldName}]";
                                    $fileFieldName = "{$this->name}[__INDEX__][{$fieldName}_file]"; // Без settings[]!
                                    $removeFieldName = "{$this->name}[__INDEX__][remove_{$fieldName}]"; // Без settings[]!
                                    
                                    echo '<label class="form-label">' . $fieldTitle . '</label>';
                                    
                                    $this->renderFieldByType($fieldConfig, '', $hiddenFieldName, $fileFieldName, $removeFieldName, '__INDEX__', $fieldType, true);
                                    
                                    if (!empty($fieldConfig['hint'])) {
                                        echo '<div class="form-text">' . $fieldConfig['hint'] . '</div>';
                                    }
                                    ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-danger repeater-remove-btn mt-2">
                            <i class="bi bi-trash"></i> Удалить
                        </button>
                    </div>
                </div>
            </template>
        </div>
        
        <!-- JavaScript для управления повторителем -->
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const repeater = document.querySelector('.repeater-field[data-field-name="<?= $this->name ?>"]');
            const addBtn = repeater.querySelector('.repeater-add-btn');
            const template = repeater.querySelector('.repeater-template');
            const itemsContainer = repeater.querySelector('.repeater-items');
            
            let itemIndex = <?= count($values) ?>;
            
            addBtn.addEventListener('click', function() {
                const newItem = template.content.cloneNode(true);
                const itemElement = newItem.querySelector('.repeater-item');
                
                // Замена плейсхолдера __INDEX__ на текущий индекс
                const htmlContent = itemElement.outerHTML.replace(/__INDEX__/g, itemIndex);
                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = htmlContent;
                
                itemsContainer.appendChild(tempDiv.firstElementChild);
                itemIndex++;
            });
            
            // Обработка удаления элементов
            repeater.addEventListener('click', function(e) {
                if (e.target.classList.contains('repeater-remove-btn')) {
                    e.target.closest('.repeater-item').remove();
                }
            });
            
            // Обработка предпросмотра изображений
            repeater.addEventListener('change', function(e) {
                if (e.target.type === 'file' && e.target.accept === 'image/*') {
                    this.handleImagePreview(e);
                }
            }.bind(this));
        });
        
        // Функция для предпросмотра изображений
        function handleImagePreview(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                const parentDiv = e.target.closest('.mb-3');
                const hiddenInput = parentDiv.querySelector('input[type="hidden"]');
                
                reader.onload = function(e) {
                    let previewContainer = parentDiv.querySelector('.image-preview');
                    if (!previewContainer) {
                        previewContainer = document.createElement('div');
                        previewContainer.className = 'image-preview mb-2';
                        parentDiv.insertBefore(previewContainer, e.target.parentNode);
                    }
                    
                    previewContainer.innerHTML = `
                        <div class="border rounded p-2 text-center">
                            <img src="${e.target.result}" 
                                 alt="Preview" 
                                 class="img-fluid rounded" 
                                 style="max-width: 48px; max-height: 48px;">
                            <div class="mt-1">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" 
                                           name="${e.target.name.replace('_file', 'remove')}" 
                                           value="1">
                                    <label class="form-check-label text-danger small">
                                        Удалить
                                    </label>
                                </div>
                            </div>
                        </div>
                    `;
                };
                reader.readAsDataURL(file);
            }
        }
        </script>
        <?php
        return $this->renderFieldGroup(ob_get_clean());
    }
    
    /**
     * Определяет класс колонки для поля (сетка Bootstrap)
     * Изображения и текстовые области занимают всю ширину, остальные - половину
     * 
     * @param array $fieldConfig Конфигурация поля
     * @return string CSS класс для колонки
     */
    private function getColumnClass($fieldConfig) {
        $fieldType = $fieldConfig['type'] ?? 'string';
        if ($fieldType === 'image' || $fieldType === 'blockimage' || $fieldType === 'textarea') {
            return 'col-md-12';
        }
        return 'col-md-6';
    }
    
    /**
     * Рендерит поле в зависимости от его типа
     * 
     * @param array $config Конфигурация поля
     * @param mixed $value Текущее значение
     * @param string $hiddenFieldName Имя скрытого поля
     * @param string $fileFieldName Имя поля для файла
     * @param string $removeFieldName Имя поля для удаления
     * @param string|int $index Индекс элемента
     * @param string $fieldType Тип поля
     * @param bool $isTemplate Флаг шаблона
     */
    private function renderFieldByType($config, $value, $hiddenFieldName, $fileFieldName, $removeFieldName, $index, $fieldType, $isTemplate = false) {
        switch ($fieldType) {
            case 'string':
                echo '<input type="text" name="' . $hiddenFieldName . '" class="form-control" value="' . htmlspecialchars($value) . '" placeholder="' . ($config['placeholder'] ?? '') . '">';
                break;
            case 'select':
                echo '<select name="' . $hiddenFieldName . '" class="form-control">';
                foreach ($config['options'] as $optValue => $optLabel) {
                    $selected = $value == $optValue ? ' selected' : '';
                    echo '<option value="' . $optValue . '"' . $selected . '>' . $optLabel . '</option>';
                }
                echo '</select>';
                break;
            case 'textarea':
                echo '<textarea name="' . $hiddenFieldName . '" class="form-control" rows="3">' . htmlspecialchars($value) . '</textarea>';
                break;
            case 'number':
                echo '<input type="number" name="' . $hiddenFieldName . '" class="form-control" value="' . htmlspecialchars($value) . '" min="' . ($config['min'] ?? '') . '" max="' . ($config['max'] ?? '') . '">';
                break;
            case 'image':
            case 'blockimage':
                $this->renderBlockImageField($config, $value, $hiddenFieldName, $fileFieldName, $removeFieldName, $index, $isTemplate);
                break;
        }
    }
    
    /**
     * Рендерит поле blockimage для repeater
     * Отображает превью изображения, чекбокс для удаления и поле загрузки
     * 
     * @param array $config Конфигурация поля
     * @param string $value Текущее значение (путь к файлу)
     * @param string $hiddenFieldName Имя скрытого поля
     * @param string $fileFieldName Имя поля для файла
     * @param string $removeFieldName Имя поля для удаления
     * @param string|int $index Индекс элемента
     * @param bool $isTemplate Флаг шаблона
     */
    private function renderBlockImageField($config, $value, $hiddenFieldName, $fileFieldName, $removeFieldName, $index, $isTemplate = false) {
        $uploadPath = $config['upload_path'] ?? 'uploads/';
        $previewUrl = '';
        
        // Формирование URL для превью (только не в шаблоне)
        if (!empty($value) && !$isTemplate) {
            $cleanValue = str_replace(BASE_URL . '/', '', $value);
            $cleanValue = ltrim($cleanValue, '/');
            
            if (strpos($cleanValue, 'uploads/') === 0 || strpos($cleanValue, '/') === 0) {
                $previewUrl = BASE_URL . '/' . ltrim($cleanValue, '/');
            } else {
                $previewUrl = BASE_URL . '/' . ltrim($uploadPath, '/') . ltrim($value, '/');
            }
        }
        
        $previewSize = $config['preview_size'] ?? '48px';
        $previewClass = $config['preview_class'] ?? 'img-fluid rounded';
        
        // Превью текущего изображения
        if ($previewUrl && !$isTemplate) {
            ?>
            <div class="image-preview mb-2">
                <div class="border rounded p-2 text-center">
                    <img src="<?= html($previewUrl) ?>" 
                         alt="Preview" 
                         class="<?= html($previewClass) ?>" 
                         style="max-width: <?= html($previewSize) ?>; max-height: <?= html($previewSize) ?>;">
                    <div class="mt-1">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="checkbox" 
                                   name="<?= $removeFieldName ?>" 
                                   value="1" 
                                   id="remove_<?= $index ?>_<?= $config['name'] ?>">
                            <label class="form-check-label text-danger small" 
                                   for="remove_<?= $index ?>_<?= $config['name'] ?>">
                                Удалить
                            </label>
                        </div>
                    </div>
                </div>
            </div>
            <?php
        }
        ?>
        <div>
            <!-- Скрытое поле для хранения значения -->
            <input type="hidden" 
                   name="<?= $hiddenFieldName ?>" 
                   value="<?= $isTemplate ? '' : html($value) ?>">
            <!-- Поле для загрузки файла -->
            <input type="file" 
                   class="form-control form-control-sm" 
                   name="<?= $fileFieldName ?>" 
                   accept="image/*">
            <div class="form-text text-muted small">
                <?= $config['hint'] ?? 'Разрешенные форматы: JPG, PNG, GIF, WebP' ?>
            </div>
        </div>
        <?php
    }
}