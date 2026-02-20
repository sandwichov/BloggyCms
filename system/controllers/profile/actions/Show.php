<?php

namespace profile\actions;

/**
 * Действие отображения публичного профиля пользователя
 * Показывает профиль пользователя по его имени пользователя (username)
 * Содержит информацию о пользователе, его постах, достижениях, активности и группах
 * 
 * @package profile\actions
 * @extends ProfileAction
 */
class Show extends ProfileAction {
    
    /** @var string|null Имя пользователя для отображения профиля */
    protected $username;
    
    /**
     * Устанавливает имя пользователя для отображения профиля
     * 
     * @param string|null $username Имя пользователя
     * @return void
     */
    public function setUsername($username) {
        $this->username = $username;
    }
    
    /**
     * Метод выполнения отображения публичного профиля
     * Получает имя пользователя, загружает данные пользователя,
     * его посты, достижения, активность и отображает страницу профиля
     * 
     * @return void
     */
    public function execute() {
        // Получение имени пользователя (из установленного свойства или параметров)
        $username = $this->username ?: ($this->params['username'] ?? '');
        
        // Проверка наличия имени пользователя
        if (empty($username)) {
            $this->render('front/404', [], 404);
            return;
        }
        
        // Загрузка данных пользователя по имени
        $user = $this->userModel->getByUsername($username);
        
        // Проверка существования пользователя
        if (!$user) {
            $this->render('front/404', [], 404);
            return;
        }
        
        // Работа с активностью пользователя
        $activityManager = \UserActivityManager::getInstance($this->db);
        
        if ($activityManager) {
            // Обновление активности пользователя
            $result = $activityManager->touch($user['id']);
            
            // Дополнительные проверки для отладки/логирования
            $checkSql = "SELECT last_activity FROM user_activity WHERE user_id = ?";
            $checkResult = $this->db->fetch($checkSql, [$user['id']]);
            
            $isOnline = $activityManager->isOnline($user['id']);
            
            if ($checkResult && $checkResult['last_activity']) {
                $diffSql = "SELECT TIMESTAMPDIFF(SECOND, last_activity, NOW()) as diff FROM user_activity WHERE user_id = ?";
                $diffResult = $this->db->fetch($diffSql, [$user['id']]);
            }
        }
        
        // Получение статуса онлайн и информации о последней активности
        $isOnline = $activityManager ? $activityManager->isOnline($user['id']) : false;
        $lastActivityInfo = $activityManager ? $activityManager->getLastActivityInfo($user['id']) : ['human' => 'неизвестно', 'days' => 0];
        
        // Получение опубликованных постов пользователя
        $userPosts = $this->postModel->getPublishedByUserId($user['id']);
        
        // Проверка, является ли текущий пользователь владельцем профиля
        $isOwnProfile = isset($_SESSION['user_id']) && $_SESSION['user_id'] == $user['id'];
        
        // Получение групп пользователя
        $groups = $this->getUserGroups($user['id']);
        
        // Получение пользовательских полей
        $customFields = $this->fieldModel->getActiveByEntityType('user');
        
        // Получение всех ачивок пользователя
        $allAchievements = $this->userModel->getUserAchievements($user['id']);
        
        // Фильтрация только разблокированных ачивок для показа
        $achievements = array_filter($allAchievements, function($achievement) {
            return $achievement['is_unlocked'];
        });
        
        // Получение общего количества активных ачивок в системе
        $allActiveAchievements = $this->userModel->getAllAchievements(['active' => true]);
        $totalAchievementsInSystem = count($allActiveAchievements);
        
        // Подсчет статистики
        $unlockedCount = count($achievements);
        $allAchievementsCount = count($allAchievements);
        $commentsCount = $this->userModel->getUserStatValue($user['id'], 'comments_count');
        $postsCount = count($userPosts);
        $daysSinceRegistration = $this->userModel->getUserStatValue($user['id'], 'registration_days');
        $roleDisplay = $this->getRoleDisplay($user['role'] ?? 'user');
        
        // Отображение страницы профиля
        $this->render('front/profile/show', [
            'user' => $user,                          // Данные пользователя
            'posts' => $userPosts,                     // Посты пользователя
            'is_own_profile' => $isOwnProfile,         // Флаг своего профиля
            'customFields' => $customFields,           // Пользовательские поля
            'achievements' => $achievements,           // Только разблокированные ачивки
            'allAchievementsCount' => $allAchievementsCount, // Все ачивки пользователя
            'totalAchievementsInSystem' => $totalAchievementsInSystem, // Все ачивки в системе
            'unlockedCount' => $unlockedCount,         // Количество разблокированных
            'groups' => $groups,                        // Группы пользователя
            'commentsCount' => $commentsCount,          // Количество комментариев
            'postsCount' => $postsCount,                // Количество постов
            'daysSinceRegistration' => $daysSinceRegistration, // Дней с регистрации
            'is_online' => $isOnline,                   // Статус онлайн
            'last_activity_human' => $lastActivityInfo['human'], // Последняя активность (текст)
            'last_activity_days' => $lastActivityInfo['days'],   // Последняя активность (дни)
            'roleDisplay' => $roleDisplay                // Отображаемое название роли
        ]);
    }
    
    /**
     * Получает группы пользователя с деталями
     * 
     * @param int $userId ID пользователя
     * @return array Массив групп пользователя
     */
    private function getUserGroups($userId) {
        try {
            // Использование специализированного метода, если он существует
            if (method_exists($this->userModel, 'getUserGroupsWithDetails')) {
                return $this->userModel->getUserGroupsWithDetails($userId);
            }
            
            // Ручной запрос, если метод отсутствует
            return $this->db->fetchAll(
                "SELECT g.* 
                 FROM user_groups g 
                 INNER JOIN users_groups ug ON g.id = ug.group_id 
                 WHERE ug.user_id = ?
                 ORDER BY g.name",
                [$userId]
            );
        } catch (\Exception $e) {
            // Возврат пустого массива при ошибке
            return [];
        }
    }
    
    /**
     * Получает отображаемое название роли пользователя
     * 
     * @param string $userRole Код роли пользователя
     * @return string Отображаемое название роли
     */
    private function getRoleDisplay($userRole) {
        // Для обычного пользователя ничего не показываем
        if ($userRole === 'user') {
            return '';
        }
        
        // Маппинг кодов ролей на отображаемые названия
        $roles = [
            'admin' => 'Администратор', 
            'author' => 'Автор',
            'editor' => 'Редактор',
            'moderator' => 'Модератор'
        ];
        
        return $roles[$userRole] ?? 'Участник';
    }
}