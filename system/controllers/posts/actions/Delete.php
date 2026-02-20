<?php

namespace posts\actions;

/**
 * Действие удаления поста в административной панели
 * Удаляет указанный пост из базы данных вместе со связанными файлами
 * 
 * @package posts\actions
 * @extends PostAction
 */
class Delete extends PostAction {
    
    /**
     * Метод выполнения удаления поста
     * Проверяет наличие ID, существование поста, удаляет связанное изображение,
     * затем удаляет пост из базы данных и перенаправляет на список постов
     * 
     * @return void
     * @throws \Exception Если ID не указан
     */
    public function execute() {
        // Получение ID поста из параметров
        $id = $this->params['id'] ?? null;
        
        // Проверка наличия ID поста
        if (!$id) {
            throw new \Exception('ID поста не указан');
        }

        try {
            // Получение данных поста для проверки существования и получения имени файла
            $post = $this->postModel->getById($id);
            
            // Проверка существования поста
            if (!$post) {
                \Notification::error('Запись не найдена');
                $this->redirect(ADMIN_URL . '/posts');
                return;
            }
            
            // Удаление файла главного изображения, если он существует
            if ($post['featured_image']) {
                $filePath = UPLOADS_PATH . '/images/' . $post['featured_image'];
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }
            
            // Удаление поста из базы данных (каскадно удаляются теги, комментарии и т.д.)
            $this->postModel->delete($id);
            
            // Уведомление об успешном удалении
            \Notification::success('Запись успешно удалена');
            
        } catch (\Exception $e) {
            // Уведомление об ошибке при удалении
            \Notification::error('Ошибка при удалении записи');
        }
        
        // Перенаправление на страницу со списком постов
        $this->redirect(ADMIN_URL . '/posts');
    }
}