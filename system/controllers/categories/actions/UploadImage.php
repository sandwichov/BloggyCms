<?php

namespace categories\actions;

/**
 * Действие загрузки изображения для категории
 * Обрабатывает загрузку изображений через CKEditor или другие WYSIWYG-редакторы
 * Предназначено для встраивания изображений в контент категорий
 * 
 * @package categories\actions
 * @extends CategoryAction
 */
class UploadImage extends CategoryAction {
    
    /**
     * Метод выполнения загрузки изображения
     * Обрабатывает файловую загрузку, валидирует тип файла и возвращает JSON-ответ с URL
     * Используется для загрузки изображений через редакторы контента
     * 
     * @return void
     */
    public function execute() {
        // Проверка наличия и успешности загрузки файла
        if (!isset($_FILES['upload']) || $_FILES['upload']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode([
                'error' => [
                    'message' => 'Ошибка при загрузке файла'
                ]
            ]);
            return;
        }
    
        // Получение информации о загружаемом файле
        $file = $_FILES['upload'];
        
        // Генерация уникального имени файла для предотвращения конфликтов
        $fileName = uniqid() . '_' . $file['name'];
        
        // Формирование пути для сохранения файла
        $uploadPath = 'uploads/images/categories/' . $fileName;
        
        // Создание директории для загрузок если она не существует
        $uploadDir = dirname($uploadPath);
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
    
        // Валидация типа MIME загружаемого файла
        $allowedTypes = [
            'image/jpeg', 
            'image/png', 
            'image/gif', 
            'image/webp'
        ];
        
        if (!in_array($file['type'], $allowedTypes)) {
            echo json_encode([
                'error' => [
                    'message' => 'Недопустимый тип файла'
                ]
            ]);
            return;
        }
    
        // Перемещение загруженного файла в целевую директорию
        if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
            // Успешный ответ с URL загруженного изображения
            // Формат ответа соответствует требованиям CKEditor
            echo json_encode([
                'url' => BASE_URL . '/' . $uploadPath
            ]);
        } else {
            // Ошибка при сохранении файла на сервере
            echo json_encode([
                'error' => [
                    'message' => 'Не удалось сохранить загруженный файл'
                ]
            ]);
        }
    }
}