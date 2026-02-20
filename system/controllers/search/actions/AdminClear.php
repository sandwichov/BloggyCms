<?php

namespace search\actions;

/**
 * Действие очистки всей истории поисковых запросов в административной панели
 * Удаляет все записи из таблицы search_queries
 * 
 * @package search\actions
 * @extends SearchAction
 */
class AdminClear extends SearchAction {
    
    /**
     * Метод выполнения очистки истории поиска
     * Вызывает метод модели для очистки всей истории,
     * показывает уведомление о результате и перенаправляет обратно
     * 
     * @return void
     */
    public function execute() {
        try {
            // Очистка всей истории поисковых запросов
            $this->searchModel->clearSearchHistory();
            
            // Уведомление об успешной очистке
            \Notification::success('История поисковых запросов успешно очищена');
            
        } catch (\Exception $e) {
            // Уведомление об ошибке при очистке
            \Notification::error('Ошибка при очистке истории поисковых запросов');
        }
        
        // Перенаправление на страницу со списком истории поиска
        $this->redirect(ADMIN_URL . '/search-history');
    }
}