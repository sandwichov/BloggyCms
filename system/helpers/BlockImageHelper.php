<?php

/**
 * Вспомогательный класс для работы с изображениями в HTML-блоках
 * Предоставляет методы для загрузки, удаления и управления изображениями
 * в контент-блоках и repeater-полях
 * 
 * @package Helpers
 */
class BlockImageHelper {
    
    /**
     * Обрабатывает загрузку изображения для контент-блока
     * Проверяет тип файла, размер, создает директорию и сохраняет файл
     * 
     * @param string $fieldName Имя поля
     * @param string $blockSystemName Системное имя блока
     * @param string $currentValue Текущее значение (путь к файлу)
     * @return array Результат операции с ключами:
     *               - success: bool
     *               - value: новое значение
     *               - error: сообщение об ошибке
     *               - file_path: путь к сохраненному файлу
     */
    public static function handleUpload($fieldName, $blockSystemName, $currentValue = '') {
        $result = [
            'success' => false,
            'value' => $currentValue,
            'error' => '',
            'file_path' => ''
        ];
        
        $fileField = $fieldName . '_file';
        
        if (!isset($_FILES[$fileField]) || $_FILES[$fileField]['error'] !== UPLOAD_ERR_OK) {
            $result['error'] = 'Файл не загружен';
            return $result;
        }
        
        $file = $_FILES[$fileField];
        
        // Валидация типа файла
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml'];
        $fileType = mime_content_type($file['tmp_name']);
        
        if (!in_array($fileType, $allowedTypes)) {
            $result['error'] = 'Недопустимый тип файла. Разрешены: JPG, PNG, GIF, WebP, SVG.';
            return $result;
        }
        
        // Валидация размера (макс. 5MB)
        $maxSize = 5 * 1024 * 1024;
        if ($file['size'] > $maxSize) {
            $result['error'] = 'Файл слишком большой. Максимальный размер: 5MB.';
            return $result;
        }
        
        // Создание директории для блока
        $uploadDir = 'uploads/images/html_blocks/' . $blockSystemName . '/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        // Удаление старого файла
        if (!empty($currentValue) && file_exists($currentValue)) {
            unlink($currentValue);
        }
        
        // Сохранение нового файла
        $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $fileName = $fieldName . '_' . uniqid() . '.' . $fileExtension;
        $filePath = $uploadDir . $fileName;
        
        if (move_uploaded_file($file['tmp_name'], $filePath)) {
            $result['success'] = true;
            $result['value'] = $filePath;
            $result['file_path'] = $filePath;
        } else {
            $result['error'] = 'Ошибка при сохранении файла.';
        }
        
        return $result;
    }
    
    /**
     * Обрабатывает удаление изображения по чекбоксу
     * 
     * @param string $fieldName Имя поля
     * @param string $currentValue Текущее значение (путь к файлу)
     * @return string Новое значение (пустое при удалении, иначе текущее)
     */
    public static function handleDelete($fieldName, $currentValue) {
        $removeField = 'remove_' . $fieldName;
        
        if (isset($_POST[$removeField]) && $_POST[$removeField] == '1' && !empty($currentValue)) {
            if (file_exists($currentValue)) {
                unlink($currentValue);
            }
            return '';
        }
        
        return $currentValue;
    }
    
    /**
     * Обрабатывает загрузку изображений для repeater поля
     * Анализирует сложную структуру $_FILES для множественных загрузок
     * 
     * @param string $repeaterName Имя repeater поля
     * @param string $blockSystemName Системное имя блока
     * @param array $currentValues Текущие значения repeater
     * @return array Обновления для применения
     */
    public static function handleRepeaterUploads($repeaterName, $blockSystemName, $currentValues = []) {
        $updates = [];
        
        // Обработка загрузки новых файлов
        foreach ($_FILES as $field => $fileData) {
            if (is_array($fileData['name'])) {
                if (isset($fileData['name'][0]) && is_array($fileData['name'][0])) {
                    foreach ($fileData['name'] as $index => $innerArray) {
                        if (is_array($innerArray)) {
                            foreach ($innerArray as $fieldKey => $fileName) {
                                if (!empty($fileName) && $fileData['error'][$index][$fieldKey] === UPLOAD_ERR_OK) {
                                    $fieldName = str_replace('_file', '', $fieldKey);
                                    
                                    $singleFileData = [
                                        'name' => $fileName,
                                        'type' => $fileData['type'][$index][$fieldKey],
                                        'tmp_name' => $fileData['tmp_name'][$index][$fieldKey],
                                        'error' => $fileData['error'][$index][$fieldKey],
                                        'size' => $fileData['size'][$index][$fieldKey]
                                    ];
                                    
                                    $uploadResult = self::uploadRepeaterFile($singleFileData, $blockSystemName, $repeaterName, $index, $fieldName);
                                    
                                    if ($uploadResult['success']) {
                                        if (!isset($updates[$index])) {
                                            $updates[$index] = [];
                                        }
                                        $updates[$index][$fieldName] = $uploadResult['file_path'];
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        
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
        
        return $updates;
    }
    
    /**
     * Загружает один файл для repeater
     * Внутренний вспомогательный метод
     * 
     * @param array $fileData Данные файла из $_FILES
     * @param string $blockSystemName Системное имя блока
     * @param string $repeaterName Имя repeater поля
     * @param int $index Индекс элемента
     * @param string $fieldName Имя поля
     * @return array Результат загрузки
     */
    private static function uploadRepeaterFile($fileData, $blockSystemName, $repeaterName, $index, $fieldName) {
        $result = ['success' => false, 'file_path' => '', 'error' => ''];
        
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml'];
        $fileType = mime_content_type($fileData['tmp_name']);
        
        if (!in_array($fileType, $allowedTypes)) {
            $result['error'] = 'Недопустимый тип файла. Разрешены: JPG, PNG, GIF, WebP, SVG.';
            return $result;
        }
        
        $maxSize = 5 * 1024 * 1024;
        if ($fileData['size'] > $maxSize) {
            $result['error'] = 'Файл слишком большой. Максимальный размер: 5MB.';
            return $result;
        }
        
        $uploadDir = 'uploads/images/html_blocks/' . $blockSystemName . '/repeater/' . $repeaterName . '/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $fileExtension = pathinfo($fileData['name'], PATHINFO_EXTENSION);
        $fileName = $repeaterName . '_' . $index . '_' . $fieldName . '_' . uniqid() . '.' . $fileExtension;
        $filePath = $uploadDir . $fileName;
        
        if (move_uploaded_file($fileData['tmp_name'], $filePath)) {
            $result['success'] = true;
            $result['file_path'] = $filePath;
        } else {
            $result['error'] = 'Ошибка при сохранении файла.';
        }
        
        return $result;
    }
    
    /**
     * Применяет обновления к данным repeater
     * Объединяет текущие данные с загруженными/удаленными файлами
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
            } else {
                $repeaterData[$index] = $fieldUpdates;
            }
        }
        
        return $repeaterData;
    }
    
    /**
     * Получает URL для отображения изображения
     * Очищает путь от возможного дублирования BASE_URL
     * 
     * @param string $imagePath Путь к изображению
     * @return string Полный URL изображения
     */
    public static function getImageUrl($imagePath) {
        if (empty($imagePath)) {
            return '';
        }
        
        $cleanPath = str_replace(BASE_URL . '/', '', $imagePath);
        $cleanPath = ltrim($cleanPath, '/');
        
        return BASE_URL . '/' . $cleanPath;
    }
}