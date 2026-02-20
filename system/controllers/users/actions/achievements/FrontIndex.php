<?php

namespace users\actions\achievements;

/**
 * Действие отображения публичного списка всех достижений системы
 * Показывает все активные ачивки с дополнительной информацией: условия,
 * количество получивших пользователей, процент выполнения, превью пользователей
 * 
 * @package users\actions\achievements
 * @extends AdminAchievementAction
 */
class FrontIndex extends AdminAchievementAction {
    
    /**
     * Метод выполнения отображения публичного списка достижений
     * Получает все активные ачивки, обогащает их дополнительной информацией,
     * сортирует по редкости (от самых редких) и передает в шаблон
     * 
     * @return void
     */
    public function execute() {
        try {
            // Получение всех активных ачивок системы
            $achievements = $this->userModel->getAllAchievements(['active' => true]);
            
            // Обогащение каждой ачивки дополнительной информацией
            foreach ($achievements as &$achievement) {
                $this->enrichAchievementData($achievement);
            }
            
            // Сортировка ачивок: сначала самые редкие (малый процент)
            usort($achievements, function($a, $b) {
                return $a['percent'] <=> $b['percent'];
            });
            
            // Получение общей статистики
            $totalAchievements = count($achievements);
            $totalUsers = $this->userModel->getTotalUsersCount();
            $totalUnlockedAchievements = $this->userModel->getTotalUnlockedAchievements();
            
            // Получение ачивок текущего пользователя (если авторизован)
            $userAchievements = $this->getUserAchievements();
            
            // Отображение страницы со списком достижений
            $this->render('front/users/achievements/index', [
                'achievements' => $achievements,
                'userAchievements' => $userAchievements,
                'totalAchievements' => $totalAchievements,
                'totalUsers' => $totalUsers,
                'totalUnlockedAchievements' => $totalUnlockedAchievements,
                'pageTitle' => 'Все достижения системы',
                'title' => 'Достижения',
                'breadcrumbs' => [
                    ['url' => BASE_URL . '/', 'title' => 'Главная'],
                    ['url' => BASE_URL . '/users', 'title' => 'Участники'],
                    ['title' => 'Достижения']
                ]
            ]);
            
        } catch (\Exception $e) {
            \Notification::error('Ошибка при загрузке списка достижений');
            $this->redirect(BASE_URL . '/users');
        }
    }
    
    /**
     * Обогащает данные одной ачивки дополнительной информацией
     * 
     * @param array &$achievement Ссылка на массив с данными ачивки
     * @return void
     */
    private function enrichAchievementData(&$achievement) {
        // Получение условий ачивки
        $achievement['conditions'] = $this->userModel->getAchievementConditions($achievement['id']);
        
        // Получение количества пользователей, получивших ачивку
        $achievement['unlocked_count'] = $this->userModel->getAchievementUnlockedCount($achievement['id']);
        
        // Получение первых 5 пользователей для предпросмотра
        $achievement['preview_users'] = $this->userModel->getAchievementUsersPreview($achievement['id'], 5);
        
        // Форматирование условий для отображения
        $achievement['formatted_conditions'] = $this->userModel->formatConditions($achievement['conditions']);
        
        // Расчет процента пользователей с ачивкой
        $totalUsers = $this->userModel->getTotalUsersCount();
        if ($totalUsers > 0) {
            $achievement['percent'] = round(($achievement['unlocked_count'] / $totalUsers) * 100, 1);
        } else {
            $achievement['percent'] = 0;
        }
    }
    
    /**
     * Получает массив ачивок текущего пользователя (если авторизован)
     * 
     * @return array Массив с ключами ID ачивок и значениями is_unlocked
     */
    private function getUserAchievements() {
        if (!isset($_SESSION['user_id'])) {
            return [];
        }
        
        $userAchievementsData = $this->userModel->getUserAchievements($_SESSION['user_id']);
        return array_column($userAchievementsData, 'is_unlocked', 'id');
    }
}