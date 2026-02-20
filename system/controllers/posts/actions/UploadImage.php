<?php

namespace posts\actions;

/**
 * Действие загрузки изображений через редактор контента
 * Используется для AJAX-запросов из текстового редактора (CKEditor и подобных)
 * Загружает изображение в общую директорию для изображений,
 * проверяет тип файла, возвращает URL загруженного изображения в формате CKEditor
 * 
 * @package posts\actions
 * @extends PostAction
 */
class UploadImage extends PostAction {
    
    /**
     * Метод выполнения загрузки изображения через редактор
     * Проверяет наличие файла, его тип, сохраняет в директорию images,
     * возвращает JSON с URL в формате, совместимом с CKEditor
     * 
     * @return void
     */
    public function execute() {
        // Проверка наличия файла и отсутствия ошибок загрузки
        if (!isset($_FILES['upload']) || $_FILES['upload']['error'] !== UPLOAD_ERR_OK) {
            echo json_encode(['error' => ['message' => 'Ошибка при загрузке файла']]);
            return;
        }
    
        $file = $_FILES['upload'];
        
        // Генерация уникального имени файла
        $fileName = uniqid() . '_' . $file['name'];
        $uploadPath = 'uploads/images/' . $fileName;
    
        // Разрешенные типы файлов
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        
        // Проверка типа файла
        if (!in_array($file['type'], $allowedTypes)) {
            echo json_encode(['error' => ['message' => 'Недопустимый тип файла']]);
            return;
        }
    
        // Сохранение файла на сервер
        if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
            // Возврат успешного ответа в формате CKEditor
            echo json_encode([
                'url' => BASE_URL . '/' . $uploadPath
            ]);
        } else {
            echo json_encode(['error' => ['message' => 'Не удалось сохранить загруженный файл']]);
        }
    }
}