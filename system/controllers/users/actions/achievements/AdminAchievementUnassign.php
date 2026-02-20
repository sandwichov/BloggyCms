<?php

namespace users\actions\achievements;

/**
 * Действие отзыва (удаления) достижения у пользователя в административной панели
 * Удаляет назначенную ранее ачивку у конкретного пользователя
 * 
 * @package users\actions\achievements
 * @extends AdminAchievementAction
 */
class AdminAchievementUnassign extends AdminAchievementAction {
    
    /**
     * Метод выполнения отзыва ачивки у пользователя
     * Проверяет права доступа, наличие ID пользователя и ачивки,
     * существование пользователя и ачивки, затем отзывает ачивку
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

            // Получение ID пользователя и ачивки из параметров
            $userId = $this->params['user_id'] ?? null;
            $achievementId = $this->params['achievement_id'] ?? null;
            
            // Проверка наличия обязательных параметров
            if (!$userId || !$achievementId) {
                throw new \Exception('Не указаны ID пользователя или ачивки');
            }
            
            // Проверка существования пользователя
            $user = $this->userModel->getById($userId);
            if (!$user) {
                throw new \Exception('Пользователь не найден');
            }
            
            // Проверка существования ачивки
            $achievement = $this->userModel->getAchievementById($achievementId);
            if (!$achievement) {
                throw new \Exception('Ачивка не найдена');
            }
            
            // Отзыв ачивки у пользователя
            $this->userModel->removeAchievementFromUser($userId, $achievementId);
            
            // Уведомление об успешном отзыве
            \Notification::success('Ачивка успешно отозвана у пользователя');
            
        } catch (\Exception $e) {
            // Уведомление об ошибке
            \Notification::error($e->getMessage());
        }
        
        // Перенаправление на страницу редактирования пользователя
        $this->redirect(ADMIN_URL . '/users/edit/' . $userId);
    }
}