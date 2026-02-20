<?php

namespace tags\actions;

/**
 * Действие удаления тега в административной панели
 * Удаляет указанный тег из базы данных вместе со связями с постами
 * 
 * @package tags\actions
 * @extends TagAction
 */
class Delete extends TagAction {
    
    /**
     * Метод выполнения удаления тега
     * Получает ID из параметров, проверяет его наличие,
     * удаляет тег через модель и перенаправляет обратно
     * 
     * @return void
     */
    public function execute() {
        // Получение ID тега из параметров
        $id = $this->params['id'] ?? null;
        
        // Проверка наличия ID
        if (!$id) {
            \Notification::error('ID тега не указан');
            $this->redirect(ADMIN_URL . '/tags');
            return;
        }
        
        try {
            // Удаление тега (автоматически удаляет связи в post_tags)
            $this->tagModel->delete($id);
            
            // Уведомление об успешном удалении
            \Notification::success('Тег успешно удален');
            
        } catch (\Exception $e) {
            // Уведомление об ошибке при удалении
            \Notification::error('Ошибка при удалении тега');
        }
        
        // Перенаправление на страницу со списком тегов
        $this->redirect(ADMIN_URL . '/tags');
    }
}