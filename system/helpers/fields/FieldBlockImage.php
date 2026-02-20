<?php

/**
 * Поле для загрузки изображений в блоках
 * Позволяет загружать, просматривать и удалять изображения
 * Поддерживает работу внутри repeater-полей (повторяющихся групп)
 * 
 * @package Fields
 * @extends Field
 */
class FieldBlockImage extends Field {
    
    /**
     * Рендерит HTML-код поля для загрузки изображения
     * Отображает текущее изображение (если есть), чекбокс для удаления
     * и поле для загрузки нового файла
     * 
     * @param mixed $currentValue Текущее значение поля (путь к файлу)
     * @return string HTML-код поля
     */
    public function render($currentValue = null) {
        $value = $currentValue !== null ? $currentValue : ($this->options['default'] ?? '');
        $uploadPath = $this->options['upload_path'] ?? 'uploads/';
        
        // Формирование URL для превью
        $previewUrl = '';
        if (!empty($value)) {
            $cleanValue = str_replace(BASE_URL . '/', '', $value);
            $cleanValue = ltrim($cleanValue, '/');
            
            if (strpos($cleanValue, 'uploads/') === 0 || strpos($cleanValue, '/') === 0) {
                $previewUrl = BASE_URL . '/' . ltrim($cleanValue, '/');
            } else {
                $previewUrl = BASE_URL . '/' . ltrim($uploadPath, '/') . ltrim($value, '/');
            }
        }
        
        // Имена полей для формы
        $fileFieldName = $this->name . '_file';
        $hiddenFieldName = "settings[{$this->name}]";
        $removeFieldName = "remove_{$this->name}";
        
        // Настройки отображения
        $previewSize = $this->options['preview_size'] ?? '64px';
        $previewClass = $this->options['preview_class'] ?? 'img-fluid rounded';
        
        // Текстовые метки
        $uploadLabel = $this->options['upload_label'] ?? 'Загрузить изображение';
        $replaceLabel = $this->options['replace_label'] ?? 'Заменить изображение';
        $currentLabel = $this->options['current_label'] ?? 'Текущее изображение';
        $deleteLabel = $this->options['delete_label'] ?? 'Удалить изображение';
        
        ob_start();
        ?>
        <div class="image-field">
            <?php if ($previewUrl) { ?>
                <div class="mb-3">
                    <label class="form-label"><?php echo html($currentLabel) ?></label>
                    <div class="border rounded p-3 text-center">
                        <img src="<?php echo html($previewUrl) ?>" alt="Preview" class="<?php echo html($previewClass) ?>" style="max-width: <?php echo html($previewSize) ?>; max-height: <?php echo html($previewSize) ?>;">
                        <div class="mt-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="<?php echo html($removeFieldName) ?>" name="<?php echo html($removeFieldName) ?>" value="1">
                                <label class="form-check-label text-danger" for="<?php echo html($removeFieldName) ?>">
                                    <?php echo html($deleteLabel) ?>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            <?php } ?>
            <div class="mb-3">
                <label class="form-label">
                    <?php echo html($previewUrl ? $replaceLabel : $uploadLabel) ?>
                </label>
                <input type="file" class="form-control" name="<?php echo html($fileFieldName) ?>" accept="image/*" <?= $this->options['multiple'] ?? false ? 'multiple' : '' ?>>
                <input type="hidden" name="<?php echo html($hiddenFieldName) ?>" value="<?php echo html($value) ?>">
                <div class="form-text text-muted">
                    <?php echo html($this->options['hint'] ?? '') ?>
                </div>
            </div>
        </div>
        <?php
        return $this->renderFieldGroup(ob_get_clean());
    }

    /**
     * Обрабатывает загрузку изображений для repeater поля
     * Анализирует POST и FILES данные, обрабатывает удаление старых файлов
     * и загрузку новых для каждого элемента repeater
     * 
     * @param string $repeaterName Имя repeater поля
     * @param string $blockSystemName Системное имя блока
     * @param array $currentValues Текущие значения repeater
     * @return array Массив обновлений для каждого индекса
     */
    public static function handleRepeaterUploads($repeaterName, $blockSystemName, $currentValues = []) {
        $updates = [];

        // Обработка удаления файлов
        foreach ($_POST as $field => $value) {
            if (strpos($field, $repeaterName . '[') === 0 && strpos($field, 'remove_') !== false) {
                preg_match('/' . preg_quote($repeaterName, '/') . '\[(\d+)\]\[remove_(.+?)\]/', $field, $matches);
                
                if (count($matches) === 3 && $value == '1') {
                    $index = $matches[1];
                    $fieldName = $matches[2];

                    if (isset($currentValues[$index][$fieldName]) && !empty($currentValues[$index][$fieldName])) {
                        $filePath = $currentValues[$index][$fieldName];
                        if (file_exists($filePath)) {
                            unlink($filePath);
                        }
                        
                        if (!isset($updates[$index])) {
                            $updates[$index] = [];
                        }
                        $updates[$index][$fieldName] = '';
                    }
                }
            }
        }
        
        // Обработка загрузки новых файлов
        foreach ($_FILES as $field => $fileData) {
            if (strpos($field, $repeaterName . '[') === 0 && strpos($field, '_file]') !== false) {
                preg_match('/' . preg_quote($repeaterName, '/') . '\[(\d+)\]\[(.+?)_file\]/', $field, $matches);
                
                if (count($matches) === 3 && $fileData['error'] === UPLOAD_ERR_OK) {
                    $index = $matches[1];
                    $fieldName = $matches[2];
                    
                    // Создание директории для загрузки
                    $uploadDir = 'uploads/images/html_blocks/' . $blockSystemName . '/repeater/' . $repeaterName . '/';
                    if (!file_exists($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }

                    // Удаление старого файла если есть
                    if (isset($currentValues[$index][$fieldName]) && !empty($currentValues[$index][$fieldName])) {
                        $oldPath = $currentValues[$index][$fieldName];
                        if (file_exists($oldPath)) {
                            unlink($oldPath);
                        }
                    }
                    
                    // Сохранение нового файла
                    $fileExtension = pathinfo($fileData['name'], PATHINFO_EXTENSION);
                    $fileName = $repeaterName . '_' . $index . '_' . $fieldName . '_' . uniqid() . '.' . $fileExtension;
                    $filePath = $uploadDir . $fileName;
                    
                    if (move_uploaded_file($fileData['tmp_name'], $filePath)) {
                        if (!isset($updates[$index])) {
                            $updates[$index] = [];
                        }
                        $updates[$index][$fieldName] = $filePath;
                    }
                }
            }
        }
        
        return $updates;
    }
    
    /**
     * Применяет обновления к данным repeater
     * Объединяет текущие данные repeater с обновлениями из загруженных файлов
     * 
     * @param array $repeaterData Текущие данные repeater
     * @param array $updates Обновления для применения
     * @return array Обновленные данные repeater
     */
    public static function applyRepeaterUpdates($repeaterData, $updates) {
        foreach ($updates as $index => $fieldUpdates) {
            if (isset($repeaterData[$index])) {
                foreach ($fieldUpdates as $fieldName => $value) {
                    $repeaterData[$index][$fieldName] = $value;
                }
            }
        }
        
        return $repeaterData;
    }
    
}