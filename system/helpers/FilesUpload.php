<?php

/**
 * Класс для множественной загрузки и управления файлами
 * Поддерживает загрузку нескольких файлов одновременно, удаление,
 * валидацию типов и размеров, а также работу с изображениями
 * 
 * @package Core
 */
class FilesUpload {
    
    /**
     * Загружает несколько файлов в указанную директорию
     * Обрабатывает как единичные файлы, так и множественные загрузки
     * 
     * @param array $files Массив файлов из $_FILES
     * @param string $uploadDir Директория для загрузки
     * @param array $allowedTypes Разрешенные расширения (например ['jpg', 'png'])
     * @param int $maxSize Максимальный размер в КБ (по умолчанию 5120 = 5MB)
     * @return array Массив результатов для каждого файла
     */
    public static function uploadMultiple($files, $uploadDir, $allowedTypes = [], $maxSize = 5120) {
        $results = [];
        
        // Если передан один файл, преобразуем в массив для единообразия
        if (!isset($files['name']) || !is_array($files['name'])) {
            $files = self::normalizeFilesArray($files);
        }
        
        // Обработка каждого файла
        for ($i = 0; $i < count($files['name']); $i++) {
            if ($files['error'][$i] !== UPLOAD_ERR_OK) {
                $results[] = [
                    'success' => false,
                    'error' => 'Ошибка загрузки файла: ' . self::getUploadError($files['error'][$i]),
                    'file_name' => $files['name'][$i]
                ];
                continue;
            }
            
            // Проверка размера файла
            if ($files['size'][$i] > $maxSize * 1024) {
                $results[] = [
                    'success' => false,
                    'error' => "Файл слишком большой. Максимальный размер: {$maxSize}КБ",
                    'file_name' => $files['name'][$i]
                ];
                continue;
            }
            
            // Проверка типа файла по расширению
            $fileExtension = strtolower(pathinfo($files['name'][$i], PATHINFO_EXTENSION));
            if (!empty($allowedTypes) && !in_array($fileExtension, $allowedTypes)) {
                $results[] = [
                    'success' => false,
                    'error' => "Недопустимый тип файла. Разрешенные: " . implode(', ', $allowedTypes),
                    'file_name' => $files['name'][$i]
                ];
                continue;
            }
            
            // Создание директории если не существует
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            // Генерация уникального имени файла
            $fileName = uniqid() . '_' . self::sanitizeFileName($files['name'][$i]);
            $targetPath = $uploadDir . '/' . $fileName;
            
            // Перемещение файла
            if (!move_uploaded_file($files['tmp_name'][$i], $targetPath)) {
                $results[] = [
                    'success' => false,
                    'error' => 'Не удалось сохранить файл',
                    'file_name' => $files['name'][$i]
                ];
                continue;
            }
            
            $results[] = [
                'success' => true,
                'file_name' => $fileName,
                'original_name' => $files['name'][$i],
                'file_path' => $targetPath,
                'file_size' => $files['size'][$i],
                'file_type' => $files['type'][$i]
            ];
        }
        
        return $results;
    }
    
    /**
     * Загружает несколько изображений для блока галереи
     * Специализированный метод с предустановленными параметрами
     * 
     * @param array $files Массив файлов из $_FILES
     * @param string $subfolder Подпапка внутри images/
     * @return array Массив результатов с добавленными URL
     */
    public static function uploadGalleryImages($files, $subfolder = 'gallery') {
        $uploadDir = UPLOADS_PATH . '/images/' . $subfolder;
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $maxSize = 5120; // 5MB
        
        $results = self::uploadMultiple($files, $uploadDir, $allowedTypes, $maxSize);
        
        // Добавление URL к успешным загрузкам
        foreach ($results as &$result) {
            if ($result['success']) {
                $result['url'] = $subfolder . '/' . $result['file_name'];
            }
        }
        
        return $results;
    }
    
    /**
     * Нормализует массив файлов для единообразной обработки
     * Преобразует структуру $_FILES в удобный для итерации формат
     * 
     * @param array $files Исходный массив файлов
     * @return array Нормализованный массив
     */
    private static function normalizeFilesArray($files) {
        $normalized = [];
        
        if (is_array($files['name'])) {
            foreach ($files as $key => $values) {
                foreach ($values as $index => $value) {
                    $normalized[$index][$key] = $value;
                }
            }
        } else {
            $normalized[] = $files;
        }
        
        // Преобразование обратно в формат с раздельными массивами
        $result = ['name' => [], 'type' => [], 'tmp_name' => [], 'error' => [], 'size' => []];
        
        foreach ($normalized as $file) {
            $result['name'][] = $file['name'];
            $result['type'][] = $file['type'];
            $result['tmp_name'][] = $file['tmp_name'];
            $result['error'][] = $file['error'];
            $result['size'][] = $file['size'];
        }
        
        return $result;
    }
    
    /**
     * Удаляет несколько файлов по списку путей
     * 
     * @param array $filePaths Массив путей к файлам
     * @return array Массив результатов удаления
     */
    public static function deleteMultiple($filePaths) {
        $results = [];
        
        foreach ($filePaths as $filePath) {
            if (file_exists($filePath) && is_file($filePath)) {
                $results[] = [
                    'success' => unlink($filePath),
                    'file_path' => $filePath
                ];
            } else {
                $results[] = [
                    'success' => false,
                    'error' => 'Файл не существует',
                    'file_path' => $filePath
                ];
            }
        }
        
        return $results;
    }
    
    /**
     * Удаляет изображения галереи по именам файлов
     * 
     * @param array $fileNames Массив имен файлов в папке gallery
     * @return array Массив результатов удаления
     */
    public static function deleteGalleryImages($fileNames) {
        $results = [];
        
        foreach ($fileNames as $fileName) {
            $filePath = UPLOADS_PATH . '/images/gallery/' . $fileName;
            $results[] = self::delete($filePath);
        }
        
        return $results;
    }
    
    /**
     * Удаляет один файл (совместимость с FileUpload)
     * 
     * @param string $filePath Путь к файлу
     * @return bool true при успешном удалении
     */
    public static function delete($filePath) {
        if (file_exists($filePath) && is_file($filePath)) {
            return unlink($filePath);
        }
        return false;
    }
    
    /**
     * Очищает имя файла от небезопасных символов
     * Заменяет все кроме букв, цифр, точек, дефисов и подчеркиваний на '_'
     * 
     * @param string $fileName Исходное имя файла
     * @return string Очищенное имя файла
     */
    private static function sanitizeFileName($fileName) {
        $fileName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $fileName);
        return preg_replace('/_{2,}/', '_', $fileName);
    }
    
    /**
     * Возвращает текстовое описание ошибки загрузки по коду
     * 
     * @param int $errorCode Код ошибки из $_FILES
     * @return string Описание ошибки
     */
    private static function getUploadError($errorCode) {
        $errors = [
            UPLOAD_ERR_INI_SIZE => 'Файл превышает максимальный размер',
            UPLOAD_ERR_FORM_SIZE => 'Файл превышает максимальный размер формы',
            UPLOAD_ERR_PARTIAL => 'Файл был загружен только частично',
            UPLOAD_ERR_NO_FILE => 'Файл не был загружен',
            UPLOAD_ERR_NO_TMP_DIR => 'Отсутствует временная директория',
            UPLOAD_ERR_CANT_WRITE => 'Не удалось записать файл на диск',
            UPLOAD_ERR_EXTENSION => 'Расширение PHP остановило загрузку файла'
        ];
        
        return $errors[$errorCode] ?? 'Неизвестная ошибка';
    }
    
    /**
     * Проверяет, является ли файл изображением по MIME-типу
     * 
     * @param string $filePath Путь к файлу
     * @return bool true если файл является изображением
     */
    public static function isImage($filePath) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $filePath);
        finfo_close($finfo);
        
        return in_array($mimeType, $allowedTypes);
    }
    
    /**
     * Получает размеры изображения
     * 
     * @param string $filePath Путь к файлу
     * @return array|null Массив с шириной, высотой и MIME-типом или null
     */
    public static function getImageDimensions($filePath) {
        if (!self::isImage($filePath)) {
            return null;
        }
        
        $dimensions = getimagesize($filePath);
        return [
            'width' => $dimensions[0],
            'height' => $dimensions[1],
            'mime' => $dimensions['mime']
        ];
    }
}