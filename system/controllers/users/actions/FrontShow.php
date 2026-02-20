<?php

namespace users\actions;

/**
 * Действие отображения публичного профиля пользователя
 * Показывает страницу с детальной информацией о пользователе: посты, комментарии,
 * группы, достижения, статистика и последняя активность
 * 
 * @package users\actions
 * @extends UserAction
 */
class FrontShow extends UserAction {
    
    /**
     * Метод выполнения отображения публичного профиля пользователя
     * Получает ID пользователя из параметров, загружает данные,
     * проверяет статус, собирает всю информацию и отображает профиль
     * 
     * @return void
     */
    public function execute() {
        // Получение ID пользователя из параметров
        $id = $this->params['id'] ?? null;
        
        // Если ID не указан - 404
        if (!$id) {
            $this->render('404', [], 404);
            return;
        }
        
        try {
            // Загрузка данных пользователя
            $user = $this->userModel->getById($id);
            
            // Если пользователь не найден или не активен - 404
            if (!$user || $user['status'] !== 'active') {
                $this->render('404', [], 404);
                return;
            }
            
            // Получение групп пользователя (с пробросом разных методов для совместимости)
            $groups = $this->getUserGroups($user['id']);
            
            // Получение постов пользователя
            $userPosts = $this->postModel->getPublishedByUserId($user['id']);
            
            // Получение статистики
            $commentsCount = $this->userModel->getUserStatValue($user['id'], 'comments_count');
            $daysSinceRegistration = $this->userModel->getUserStatValue($user['id'], 'registration_days');
            $postsCount = count($userPosts);
            
            // Получение пользовательских полей
            $customFields = $this->fieldModel->getActiveByEntityType('user');
            
            // Получение и подсчет достижений
            $achievementsData = $this->getUserAchievementsData($user['id']);
            
            // Информация о последнем входе
            $lastLoginInfo = $this->getLastLoginInfo($user);
            
            // Отображаемая роль
            $roleDisplay = $this->getRoleDisplay($user['role'] ?? 'user');
            
            // Отображение страницы профиля
            $this->render('front/profile/user', [
                'user' => $user,
                'posts' => $userPosts,
                'customFields' => $customFields,
                'groups' => $groups,
                'achievements' => $achievementsData['achievements'],
                'unlockedCount' => $achievementsData['unlockedCount'],
                'commentsCount' => $commentsCount,
                'postsCount' => $postsCount,
                'daysSinceRegistration' => $daysSinceRegistration,
                'lastLoginDaysAgo' => $lastLoginInfo['daysAgo'],
                'lastLoginHuman' => $lastLoginInfo['human'],
                'roleDisplay' => $roleDisplay,
                'title' => 'Профиль пользователя: ' . ($user['display_name'] ?: $user['username'])
            ]);
            
        } catch (\Exception $e) {
            // Логирование и обработка ошибок
            \Notification::error('Ошибка при загрузке профиля пользователя');
            $this->redirect(BASE_URL . '/users');
        }
    }
    
    /**
     * Получает группы пользователя, пробуя разные методы для совместимости
     * 
     * @param int $userId ID пользователя
     * @return array Массив групп
     */
    private function getUserGroups($userId) {
        // Пробуем разные методы получения групп
        $groupsMethod1 = $this->userModel->getUserGroups($userId);
        $groupsMethod2 = $this->userModel->getUserGroupsWithDetails($userId);
        
        // Прямой запрос к БД
        $directGroups = $this->db->fetchAll(
            "SELECT g.* 
             FROM user_groups g 
             INNER JOIN users_groups ug ON g.id = ug.group_id 
             WHERE ug.user_id = ?",
            [$userId]
        );
        
        // Используем тот, что работает (приоритет: прямой запрос > method2 > method1)
        $groups = !empty($directGroups) ? $directGroups : (!empty($groupsMethod2) ? $groupsMethod2 : $groupsMethod1);
        
        // Если получили массив ID, преобразуем в объекты
        if (!empty($groups) && isset($groups[0]) && is_numeric($groups[0])) {
            $groupIds = $groups;
            $placeholders = implode(',', array_fill(0, count($groupIds), '?'));
            $groups = $this->db->fetchAll(
                "SELECT * FROM user_groups WHERE id IN ($placeholders) ORDER BY name",
                $groupIds
            );
        }
        
        return $groups;
    }
    
    /**
     * Получает достижения пользователя и подсчитывает разблокированные
     * 
     * @param int $userId ID пользователя
     * @return array Массив с достижениями и количеством разблокированных
     */
    private function getUserAchievementsData($userId) {
        $achievements = $this->userModel->getUserAchievements($userId);
        
        $unlockedCount = 0;
        foreach ($achievements as $achievement) {
            if ($achievement['is_unlocked']) {
                $unlockedCount++;
            }
        }
        
        return [
            'achievements' => $achievements,
            'unlockedCount' => $unlockedCount
        ];
    }
    
    /**
     * Получает информацию о последнем входе пользователя
     * 
     * @param array $user Данные пользователя
     * @return array Массив с информацией о последнем входе
     */
    private function getLastLoginInfo($user) {
        $daysAgo = 0;
        $human = 'никогда';
        
        if (!empty($user['last_login'])) {
            $lastLoginTimestamp = strtotime($user['last_login']);
            $currentTimestamp = time();
            $daysAgo = floor(($currentTimestamp - $lastLoginTimestamp) / (60 * 60 * 24));
            
            $minutesAgo = floor(($currentTimestamp - $lastLoginTimestamp) / 60);
            $hoursAgo = floor($minutesAgo / 60);
            
            if ($minutesAgo < 5) {
                $human = 'только что';
            } elseif ($minutesAgo < 60) {
                $human = $minutesAgo . ' мин назад';
            } elseif ($hoursAgo < 24) {
                $human = $hoursAgo . ' ч назад';
            } else {
                $human = $daysAgo . ' д назад';
            }
        }
        
        return [
            'daysAgo' => $daysAgo,
            'human' => $human
        ];
    }
    
    /**
     * Получает отображаемое название роли пользователя
     * 
     * @param string $userRole Код роли
     * @return string Отображаемое название
     */
    private function getRoleDisplay($userRole) {
        if ($userRole === 'user') {
            return '';
        }
        
        $roles = [
            'admin' => 'Администратор', 
            'author' => 'Автор',
            'editor' => 'Редактор',
            'moderator' => 'Модератор'
        ];
        
        return $roles[$userRole] ?? 'Участник';
    }
}