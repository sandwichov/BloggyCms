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
            $this->addBreadcrumb('Главная', BASE_URL);
            $this->addBreadcrumb('Участники', BASE_URL . '/users');
            $this->addBreadcrumb('Ачивки');
            $this->setPageTitle('Все ачивки блога');
            
            $achievements = $this->userModel->getAllAchievements(['active' => true]);
            
            foreach ($achievements as &$achievement) {
                $this->enrichAchievementData($achievement);
            }
            
            usort($achievements, function($a, $b) {
                return $a['percent'] <=> $b['percent'];
            });
            
            $totalAchievements = count($achievements);
            $totalUsers = $this->userModel->getTotalUsersCount();
            $totalUnlockedAchievements = $this->userModel->getTotalUnlockedAchievements();
            $userAchievements = $this->getUserAchievements();
            
            $this->render('front/users/achievements/index', [
                'achievements' => $achievements,
                'userAchievements' => $userAchievements,
                'totalAchievements' => $totalAchievements,
                'totalUsers' => $totalUsers,
                'totalUnlockedAchievements' => $totalUnlockedAchievements,
                'pageTitle' => 'Все достижения системы'
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
        $achievement['conditions'] = $this->userModel->getAchievementConditions($achievement['id']);
        $achievement['unlocked_count'] = $this->userModel->getAchievementUnlockedCount($achievement['id']);
        $achievement['preview_users'] = $this->userModel->getAchievementUsersPreview($achievement['id'], 5);
        $achievement['formatted_conditions'] = $this->userModel->formatConditions($achievement['conditions']);
        
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