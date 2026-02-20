<?php

namespace posts\actions;

/**
 * Действие множественной загрузки изображений для галереи поста
 * Используется для AJAX-запросов в административной панели
 * Загружает несколько изображений одновременно в специальную директорию для галерей,
 * проверяет тип каждого файла, возвращает массив загруженных изображений с URL
 * 
 * @package posts\actions
 * @extends PostAction
 */
class UploadGalleryImages extends PostAction {
    
    /**
     * Метод выполнения множественной загрузки изображений
     * Принимает массив файлов из поля 'gallery_images', обрабатывает каждый,
     * фильтрует по типу, сохраняет в директорию gallery и возвращает список загруженных
     * 
     * @return void
     */
    public function execute() {
        // Установка заголовка для JSON-ответа
        header('Content-Type: application/json');
        
        try {
            // Проверка наличия файлов
            if (!isset($_FILES['gallery_images']) || empty($_FILES['gallery_images']['name'][0])) {
                throw new \Exception('Файлы не загружены');
            }

            $uploadedFiles = [];
            
            // Создание директории для галереи, если не существует
            $uploadDir = UPLOADS_PATH . '/images/gallery/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            // Разрешенные типы файлов
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

            // Обработка каждого загруженного файла
            foreach ($_FILES['gallery_images']['name'] as $index => $name) {
                // Проверка, что файл загружен без ошибок
                if ($_FILES['gallery_images']['error'][$index] === UPLOAD_ERR_OK) {
                    // Определение MIME-типа файла
                    $fileType = mime_content_type($_FILES['gallery_images']['tmp_name'][$index]);
                    
                    // Пропуск файлов с недопустимым типом
                    if (!in_array($fileType, $allowedTypes)) {
                        continue;
                    }

                    // Генерация уникального имени файла
                    $fileName = uniqid() . '_' . basename($name);
                    $targetPath = $uploadDir . $fileName;

                    // Сохранение файла на сервер
                    if (move_uploaded_file($_FILES['gallery_images']['tmp_name'][$index], $targetPath)) {
                        $uploadedFiles[] = [
                            'url' => BASE_URL . '/uploads/images/gallery/' . $fileName,
                            'path' => $fileName,
                            'name' => $name
                        ];
                    }
                }
            }

            // Проверка, что хотя бы один файл был успешно загружен
            if (empty($uploadedFiles)) {
                throw new \Exception('Не удалось загрузить файлы');
            }

            // Возврат успешного ответа со списком загруженных файлов
            echo json_encode([
                'success' => true,
                'files' => $uploadedFiles,
                'message' => 'Изображения успешно загружены'
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