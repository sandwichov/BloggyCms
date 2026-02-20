<?php

namespace search\actions;

/**
 * Действие удаления конкретного поискового запроса из истории
 * Удаляет запись из таблицы search_queries по указанному ID
 * 
 * @package search\actions
 * @extends SearchAction
 */
class AdminDelete extends SearchAction {
    
    /**
     * Метод выполнения удаления поискового запроса
     * Получает ID из параметров, проверяет его наличие,
     * удаляет запись через модель и перенаправляет обратно
     * 
     * @return void
     */
    public function execute() {
        // Получение ID запроса из параметров
        $id = $this->params['id'] ?? null;
        
        // Проверка наличия ID
        if (!$id) {
            \Notification::error('ID запроса не указан');
            $this->redirect(ADMIN_URL . '/search-history');
            return;
        }
        
        try {
            // Удаление конкретного поискового запроса по ID
            $this->searchModel->deleteSearchQuery($id);
            
            // Уведомление об успешном удалении
            \Notification::success('Поисковый запрос успешно удален');
            
        } catch (\Exception $e) {
            // Уведомление об ошибке при удалении
            \Notification::error('Ошибка при удалении поискового запроса');
        }
        
        // Перенаправление на страницу со списком истории поиска
        $this->redirect(ADMIN_URL . '/search-history');
    }
}