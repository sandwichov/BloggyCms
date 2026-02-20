<?php

namespace users\actions\achievements;

/**
 * Действие быстрого назначения ручного достижения пользователю в административной панели
 * Позволяет выбрать ручную ачивку из списка доступных, указать причину назначения
 * и отправить уведомление пользователю
 * 
 * @package users\actions\achievements
 * @extends AdminAchievementAction
 */
class AdminQuickAssignAchievement extends AdminAchievementAction {
    
    /**
     * Метод выполнения быстрого назначения ачивки
     * При GET-запросе отображает форму с доступными ручными ачивками,
     * при POST-запросе обрабатывает назначение, сохраняет историю и отправляет уведомление
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

            // Обработка POST-запроса (назначение ачивки)
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $this->handlePostRequest();
                return;
            }

            // Обработка GET-запроса (отображение формы)
            $this->handleGetRequest();

        } catch (\Exception $e) {
            \Notification::error($e->getMessage());
            $this->redirect(ADMIN_URL . '/users');
        }
    }
    
    /**
     * Обрабатывает POST-запрос на назначение ачивки
     * 
     * @return void
     * @throws \Exception При ошибках валидации
     */
    private function handlePostRequest() {
        $userId = $_POST['user_id'] ?? null;
        $achievementId = $_POST['achievement_id'] ?? null;
        $reason = $_POST['reason'] ?? '';
        $sendNotification = isset($_POST['send_notification']) ? true : false;
        
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
        
        // Проверка типа ачивки (только ручные)
        if ($achievement['type'] !== 'manual') {
            throw new \Exception('Можно назначать только ручные ачивки');
        }
        
        // Сохранение причины в историю
        $this->saveAchievementAssignmentHistory($userId, $achievementId, $reason);
        
        // Назначение ачивки пользователю
        $this->userModel->assignAchievementToUser($userId, $achievementId);
        
        // Отправка уведомления (если требуется)
        if ($sendNotification) {
            $this->sendAchievementNotification($user, $achievement, $reason);
        }
        
        \Notification::success('Ачивка успешно назначена пользователю');
        $this->redirect(ADMIN_URL . '/users/edit/' . $userId);
    }
    
    /**
     * Обрабатывает GET-запрос для отображения формы назначения
     * 
     * @return void
     * @throws \Exception При ошибках валидации
     */
    private function handleGetRequest() {
        $userId = $this->params['user_id'] ?? null;
        if (!$userId) {
            throw new \Exception('ID пользователя не указан');
        }
        
        // Проверка существования пользователя
        $user = $this->userModel->getById($userId);
        if (!$user) {
            throw new \Exception('Пользователь не найден');
        }
        
        // Получение всех активных ачивок
        $allAchievements = $this->userModel->getAllAchievements(['active' => true]);
        
        // Получение уже назначенных пользователю ачивок
        $userAchievements = $this->userModel->getUserUnlockedAchievements($userId);
        $userAchievementIds = array_column($userAchievements, 'id');
        
        // Фильтрация: только ручные ачивки, которые еще не назначены
        $availableAchievements = array_filter($allAchievements, function($achievement) use ($userAchievementIds) {
            return $achievement['type'] == 'manual' && !in_array($achievement['id'], $userAchievementIds);
        });
        
        // Отображение формы
        $this->render('admin/users/quick-assign-achievement', [
            'user' => $user,
            'availableAchievements' => $availableAchievements,
            'pageTitle' => 'Назначение ачивки'
        ]);
    }
    
    /**
     * Сохраняет историю назначения ачивки
     * Создает таблицу для истории, если она не существует
     * 
     * @param int $userId ID пользователя
     * @param int $achievementId ID ачивки
     * @param string $reason Причина назначения
     * @return bool Результат операции
     */
    private function saveAchievementAssignmentHistory($userId, $achievementId, $reason) {
        // Сохранение записи в историю
        $this->db->query(
            "INSERT INTO achievement_assignment_history (user_id, achievement_id, admin_id, reason) 
             VALUES (?, ?, ?, ?)",
            [$userId, $achievementId, $_SESSION['user_id'] ?? null, $reason]
        );
        
        return true;
    }
    
    /**
     * Отправляет уведомление пользователю о назначении ачивки
     * Создает запись в таблице notifications
     * 
     * @param array $user Данные пользователя
     * @param array $achievement Данные ачивки
     * @param string $reason Причина назначения
     * @return bool Результат операции
     */
    private function sendAchievementNotification($user, $achievement, $reason) {
        // Формирование сообщения
        $message = "Поздравляем! Вам была назначена ачивка \"{$achievement['name']}\"";
        
        if (!empty($reason)) {
            $message .= " за: " . $reason;
        }
        
        // Сохранение уведомления в базу данных
        $this->db->query(
            "INSERT INTO notifications (user_id, type, title, message, is_read) 
             VALUES (?, 'achievement', ?, ?, 0)",
            [$user['id'], 'Новая ачивка!', $message]
        );
        
        return true;
    }
}