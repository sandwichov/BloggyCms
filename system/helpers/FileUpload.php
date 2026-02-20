<?php

/**
 * Класс для загрузки и управления отдельными файлами
 * Предоставляет методы для загрузки, валидации и удаления файлов
 * 
 * @package Core
 */
class FileUpload {
    
    /**
     * Загружает файл в указанную директорию и возвращает имя файла
     * Выбрасывает исключения при ошибках
     * 
     * @param array $file Данные файла из $_FILES
     * @param string $uploadDir Директория для загрузки
     * @param array $allowedTypes Разрешенные расширения (например ['jpg', 'png'])
     * @param int $maxSize Максимальный размер в КБ (по умолчанию 2048 = 2MB)
     * @return string Имя загруженного файла
     * @throws Exception При ошибках загрузки или валидации
     */
    public static function upload($file, $uploadDir, $allowedTypes = [], $maxSize = 2048) {
        // Проверка ошибок загрузки
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('Ошибка загрузки файла: ' . self::getUploadError($file['error']));
        }
        
        // Проверка размера файла
        if ($file['size'] > $maxSize * 1024) {
            throw new Exception("Файл слишком большой. Максимальный размер: {$maxSize}КБ");
        }
        
        // Проверка типа файла по расширению
        $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!empty($allowedTypes) && !in_array($fileExtension, $allowedTypes)) {
            throw new Exception("Недопустимый тип файла. Разрешенные: " . implode(', ', $allowedTypes));
        }
        
        // Создание директории, если не существует
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        // Генерация уникального имени файла
        $fileName = uniqid() . '_' . self::sanitizeFileName($file['name']);
        $targetPath = $uploadDir . '/' . $fileName;
        
        // Перемещение файла
        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            throw new Exception('Не удалось сохранить файл');
        }
        
        return $fileName;
    }
    
    /**
     * Загружает изображение для блока поста
     * Специализированный метод с предустановленными параметрами
     * 
     * @param array $file Данные файла из $_FILES
     * @param string $subfolder Подпапка внутри images/
     * @return string Путь к файлу относительно корня (например 'blocks/имя.jpg')
     * @throws Exception При ошибках загрузки
     */
    public static function uploadBlockImage($file, $subfolder = 'blocks') {
        $uploadDir = UPLOADS_PATH . '/images/' . $subfolder;
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $maxSize = 5120; // 5MB
        
        $fileName = self::upload($file, $uploadDir, $allowedTypes, $maxSize);
        
        return $subfolder . '/' . $fileName;
    }
    
    /**
     * Удаляет файл
     * 
     * @param string $filePath Полный путь к файлу
     * @return bool true при успешном удалении, false если файл не существует
     */
    public static function delete($filePath) {
        if (file_exists($filePath) && is_file($filePath)) {
            return unlink($filePath);
        }
        return false;
    }
    
    /**
     * Удаляет изображение блока
     * 
     * @param string $fileName Имя файла (может включать подпапку)
     * @return bool Результат удаления
     */
    public static function deleteBlockImage($fileName) {
        $filePath = UPLOADS_PATH . '/images/' . $fileName;
        return self::delete($filePath);
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
}