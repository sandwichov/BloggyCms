<?php

namespace posts\actions;

/**
 * Действие загрузки главного изображения (обложки) для поста
 * Используется для AJAX-запросов в административной панели
 * Загружает изображение в директорию для изображений,
 * проверяет тип и размер файла, возвращает URL загруженного изображения
 * 
 * @package posts\actions
 * @extends PostAction
 */
class UploadFeaturedImage extends PostAction {
    
    /**
     * Метод выполнения загрузки главного изображения
     * Проверяет наличие файла, его тип и размер, сохраняет в директорию images,
     * возвращает JSON с URL загруженного изображения или сообщением об ошибке
     * 
     * @return void
     */
    public function execute() {
        // Установка заголовка для JSON-ответа
        header('Content-Type: application/json');
        
        try {
            // Проверка наличия файла и отсутствия ошибок загрузки
            if (!isset($_FILES['featured_image']) || $_FILES['featured_image']['error'] !== UPLOAD_ERR_OK) {
                throw new \Exception('Ошибка при загрузке файла');
            }

            $file = $_FILES['featured_image'];
            
            // Валидация типа файла
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $fileType = mime_content_type($file['tmp_name']);
            if (!in_array($fileType, $allowedTypes)) {
                throw new \Exception('Недопустимый тип файла. Разрешены: JPEG, PNG, GIF, WebP');
            }
            
            // Валидация размера файла (макс. 5MB)
            if ($file['size'] > 5 * 1024 * 1024) {
                throw new \Exception('Файл слишком большой. Максимальный размер: 5MB');
            }

            // Создание директории для изображений, если не существует
            $uploadDir = UPLOADS_PATH . '/images/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            // Генерация уникального имени файла
            $fileName = uniqid() . '_' . basename($file['name']);
            $targetPath = $uploadDir . $fileName;

            // Сохранение файла на сервер
            if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
                throw new \Exception('Не удалось сохранить файл');
            }

            // Возврат успешного ответа с URL и путем к файлу
            echo json_encode([
                'success' => true,
                'url' => BASE_URL . '/uploads/images/' . $fileName,
                'path' => $fileName,
                'message' => 'Изображение успешно загружено'
            ]);

        } catch (\Exception $e) {
            // Возврат ответа с ошибкой
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
        exit;
    }
}