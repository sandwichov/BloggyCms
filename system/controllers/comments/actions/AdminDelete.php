<?php

namespace comments\actions;

/**
 * Действие удаления комментария в админ-панели
 * Позволяет администраторам и модераторам удалять комментарии из системы
 * Простое действие без дополнительных опций или подтверждений
 * 
 * @package comments\actions
 * @extends CommentAction
 */
class AdminDelete extends CommentAction {
    
    /**
     * Метод выполнения удаления комментария
     * Удаляет комментарий по ID и перенаправляет обратно в админ-панель
     * 
     * @return void
     */
    public function execute() {
        // Получение ID комментария из параметров
        $id = $this->params['id'] ?? null;
        
        // Проверка наличия ID комментария
        if (!$id) {
            \Notification::error('ID комментария не указан');
            $this->redirect(ADMIN_URL . '/comments');
            return;
        }
        
        try {
            // Выполнение удаления комментария через модель
            $this->commentModel->deleteComment($id);
            
            // Уведомление об успешном удалении
            \Notification::success('Комментарий успешно удален');
            
        } catch (\Exception $e) {
            // Обработка исключений при удалении
            \Notification::error('Ошибка при удалении комментария');
        }
        
        // Перенаправление обратно на страницу управления комментариями
        $this->redirect(ADMIN_URL . '/comments');
    }
}