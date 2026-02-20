<?php

namespace html_blocks\actions;

/**
 * Действие удаления HTML-блока в админ-панели
 * Удаляет HTML-блок из системы по его ID
 * 
 * @package html_blocks\actions
 * @extends HtmlBlockAction
 */
class AdminDelete extends HtmlBlockAction {
    
    /**
     * Метод выполнения удаления HTML-блока
     * Удаляет блок из базы данных и перенаправляет на список блоков
     * 
     * @return void
     */
    public function execute() {
        // Проверка административных прав доступа
        if (!$this->checkAdminAccess()) {
            \Notification::error('У вас нет прав доступа к этому разделу');
            $this->redirect(ADMIN_URL . '/login');
            return;
        }
        
        try {
            // Удаление блока по ID через модель
            $this->htmlBlockModel->delete($this->id);
            
            // Уведомление об успешном удалении
            \Notification::success('HTML-блок успешно удален');
            
        } catch (\Exception $e) {
            // Обработка ошибок при удалении
            \Notification::error('Ошибка при удалении HTML-блока');
        }
        
        // Перенаправление на страницу со списком блоков
        $this->redirect(ADMIN_URL . '/html-blocks');
    }
}