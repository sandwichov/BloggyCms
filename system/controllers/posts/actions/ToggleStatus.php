<?php

namespace posts\actions;

/**
 * Действие переключения статуса поста в административной панели
 * Меняет статус поста между 'published' (опубликовано) и 'draft' (черновик)
 * 
 * @package posts\actions
 * @extends PostAction
 */
class ToggleStatus extends PostAction {
    
    /**
     * Метод выполнения переключения статуса поста
     * Получает ID поста из параметров, проверяет существование,
     * определяет новый статус (противоположный текущему), обновляет пост
     * и показывает уведомление о результате
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
            // Получение текущего поста
            $post = $this->postModel->getById($id);
            if (!$post) {
                throw new \Exception('Пост не найден');
            }
            
            // Определение нового статуса (противоположный текущему)
            $newStatus = $post['status'] === 'published' ? 'draft' : 'published';
            
            // Обновление статуса поста
            $this->postModel->update($id, ['status' => $newStatus]);
            
            // Формирование текста уведомления
            $statusText = $newStatus === 'published' ? 'опубликован' : 'перемещен в черновики';
            \Notification::success("Пост {$statusText}");
            
        } catch (\Exception $e) {
            // Уведомление об ошибке
            \Notification::error('Ошибка при изменении статуса поста: ' . $e->getMessage());
        }
        
        // Перенаправление на страницу со списком постов
        $this->redirect(ADMIN_URL . '/posts');
    }
}