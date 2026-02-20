<?php

namespace users\actions\groups;

/**
 * Действие удаления группы пользователей в административной панели
 * Удаляет указанную группу из базы данных вместе со связями с пользователями и правами
 * 
 * @package users\actions\groups
 * @extends AdminGroupAction
 */
class AdminGroupDelete extends AdminGroupAction {
    
    /**
     * Метод выполнения удаления группы
     * Проверяет права доступа, наличие ID, удаляет группу через модель
     * и перенаправляет на страницу со списком групп
     * 
     * @return void
     */
    public function execute() {
        try {
            // Проверка прав доступа администратора
            if (!$this->checkAdminAccess()) {
                \Notification::error('У вас нет прав доступа');
                $this->redirect(ADMIN_URL);
                return;
            }

            // Получение ID группы из параметров
            $id = $this->params['id'] ?? null;
            if (!$id) {
                throw new \Exception('ID группы не указан');
            }

            // Удаление группы (каскадно удаляет связи и права)
            $this->userModel->deleteGroup($id);
            
            // Уведомление об успешном удалении
            \Notification::success('Группа успешно удалена');
            
        } catch (\Exception $e) {
            // Уведомление об ошибке (без деталей для пользователя)
            \Notification::error('Ошибка при удалении группы');
        }
        
        // Перенаправление на страницу со списком групп
        $this->redirect(ADMIN_URL . '/user-groups');
    }
}