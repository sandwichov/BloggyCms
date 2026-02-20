<?php

namespace users\actions\achievements;

/**
 * Действие назначения достижения (ачивки) пользователю в административной панели
 * Отображает форму выбора ачивки для пользователя и обрабатывает её отправку,
 * назначая выбранное достижение пользователю
 * 
 * @package users\actions\achievements
 * @extends AdminAchievementAction
 */
class AdminAchievementAssign extends AdminAchievementAction {
    
    /**
     * Метод выполнения назначения ачивки пользователю
     * При GET-запросе отображает форму с выбором ачивки,
     * при POST-запросе назначает выбранную ачивку пользователю
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

            // Получение ID пользователя из параметров
            $userId = $this->params['user_id'] ?? null;
            if (!$userId) {
                throw new \Exception('ID пользователя не указан');
            }
            
            // Загрузка данных пользователя
            $user = $this->userModel->getById($userId);
            if (!$user) {
                throw new \Exception('Пользователь не найден');
            }
            
            // Обработка POST-запроса (назначение ачивки)
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $this->handlePostRequest($userId);
                return;
            }
            
            // Отображение формы выбора ачивки
            $this->renderAssignForm($user);
            
        } catch (\Exception $e) {
            \Notification::error($e->getMessage());
            $this->redirect(ADMIN_URL . '/users');
        }
    }
    
    /**
     * Обрабатывает POST-запрос на назначение ачивки
     * 
     * @param int $userId ID пользователя
     * @return void
     * @throws \Exception При ошибках валидации
     */
    private function handlePostRequest($userId) {
        $achievementId = $_POST['achievement_id'] ?? null;
        if (!$achievementId) {
            throw new \Exception('Ачивка не выбрана');
        }
        
        // Проверка существования ачивки
        $achievement = $this->userModel->getAchievementById($achievementId);
        if (!$achievement) {
            throw new \Exception('Ачивка не найдена');
        }
        
        // Назначение ачивки пользователю
        $this->userModel->assignAchievementToUser($userId, $achievementId);
        
        \Notification::success('Ачивка успешно назначена пользователю');
        $this->redirect(ADMIN_URL . '/users/edit/' . $userId);
    }
    
    /**
     * Отображает форму назначения ачивки
     * 
     * @param array $user Данные пользователя
     * @return void
     */
    private function renderAssignForm($user) {
        // Получение списка доступных активных ачивок
        $achievements = $this->userModel->getAllAchievements(['active' => true]);
        
        $this->render('admin/users/assign-achievement', [
            'user' => $user,
            'achievements' => $achievements,
            'pageTitle' => 'Назначение ачивки пользователю'
        ]);
    }
}