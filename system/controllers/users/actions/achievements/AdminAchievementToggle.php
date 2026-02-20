<?php

namespace users\actions\achievements;

/**
 * Действие переключения статуса активности достижения (ачивки) в административной панели
 * Меняет статус ачивки между активной (is_active = 1) и неактивной (is_active = 0)
 * 
 * @package users\actions\achievements
 * @extends AdminAchievementAction
 */
class AdminAchievementToggle extends AdminAchievementAction {
    
    /**
     * Метод выполнения переключения статуса ачивки
     * Проверяет права доступа, наличие ID, существование ачивки,
     * определяет новый статус (противоположный текущему), обновляет его в БД
     * и показывает уведомление о результате
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

            // Получение ID ачивки из параметров
            $id = $this->params['id'] ?? null;
            if (!$id) {
                throw new \Exception('ID ачивки не указан');
            }
            
            // Проверка существования ачивки и получение текущего статуса
            $achievement = $this->userModel->getAchievementById($id);
            if (!$achievement) {
                throw new \Exception('Ачивка не найдена');
            }
            
            // Определение нового статуса (противоположный текущему)
            $newStatus = $achievement['is_active'] ? 0 : 1;
            
            // Прямое обновление статуса через запрос к БД
            $this->db->query(
                "UPDATE user_achievements SET is_active = ? WHERE id = ?",
                [$newStatus, $id]
            );
            
            // Формирование текста уведомления
            $statusText = $newStatus ? 'активирована' : 'деактивирована';
            \Notification::success("Ачивка успешно {$statusText}");
            
        } catch (\Exception $e) {
            // Уведомление об ошибке
            \Notification::error('Ошибка при изменении статуса ачивки: ' . $e->getMessage());
        }
        
        // Перенаправление на страницу со списком ачивок
        $this->redirect(ADMIN_URL . '/user-achievements');
    }
}