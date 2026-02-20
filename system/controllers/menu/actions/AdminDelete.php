<?php

namespace menu\actions;

/**
 * Действие удаления меню в админ-панели
 * Удаляет меню из системы по указанному ID
 * 
 * @package menu\actions
 * @extends MenuAction
 */
class AdminDelete extends MenuAction {
    
    /**
     * Метод выполнения удаления меню
     * Проверяет существование меню и удаляет его из базы данных
     * 
     * @return void
     */
    public function execute() {
        // Получение ID меню из параметров
        $id = $this->params['id'] ?? null;
        
        // Проверка наличия ID меню
        if (!$id) {
            \Notification::error('ID меню не указан');
            $this->redirect(ADMIN_URL . '/menu');
            return;
        }
        
        try {
            // Получение данных меню для подтверждения существования
            $menu = $this->menuModel->getById($id);
            
            // Проверка существования меню
            if (!$menu) {
                throw new \Exception('Меню не найдено');
            }
            
            // Удаление меню из базы данных
            $this->menuModel->delete($id);
            
            // Уведомление об успешном удалении
            \Notification::success('Меню успешно удалено');
            
        } catch (\Exception $e) {
            // Обработка ошибок удаления
            \Notification::error('Ошибка при удалении меню: ' . $e->getMessage());
        }
        
        // Перенаправление на страницу управления меню
        $this->redirect(ADMIN_URL . '/menu');
    }
}