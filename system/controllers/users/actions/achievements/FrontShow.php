<?php

namespace users\actions\achievements;

/**
 * Действие отображения публичной страницы конкретного достижения
 * Показывает детальную информацию об ачивке, её условиях, статистику
 * и список пользователей, которые её получили (с пагинацией)
 * 
 * @package users\actions\achievements
 * @extends AdminAchievementAction
 */
class FrontShow extends AdminAchievementAction {
    
    /**
     * Метод выполнения отображения страницы достижения
     * Получает ID ачивки из параметров, загружает данные, проверяет доступность,
     * собирает статистику и список пользователей, отображает страницу
     * 
     * @return void
     */
    public function execute() {
        try {
            $id = $this->params['id'] ?? null;
            
            if (!$id) {
                $this->redirect(BASE_URL . '/achievements');
                return;
            }
            
            $achievement = $this->userModel->getAchievementById($id);
            
            if (!$achievement || !$achievement['is_active']) {
                $this->render('front/404', [], 404);
                return;
            }
            
            $this->addBreadcrumb('Главная', BASE_URL);
            $this->addBreadcrumb('Участники', BASE_URL . '/users');
            $this->addBreadcrumb('Ачивки', BASE_URL . '/users/achievements');
            $this->addBreadcrumb($achievement['name']);
            $this->setPageTitle($achievement['name'] . ' — Ачивка');
            
            $this->enrichAchievementData($achievement, $id);
            
            $usersData = $this->getAchievementUsers($id);
            $userHasAchievement = $this->checkUserHasAchievement($id);
            
            $this->renderAchievementPage($achievement, $usersData, $userHasAchievement);
            
        } catch (\Exception $e) {
            \Notification::error('Ошибка при загрузке информации об ачивке');
            $this->redirect(BASE_URL . '/achievements');
        }
    }
    
    /**
     * Обогащает данные ачивки дополнительной информацией
     * 
     * @param array $achievement Данные ачивки
     * @param int $id ID ачивки
     * @return void
     */
    private function enrichAchievementData(&$achievement, $id) {

        $achievement['conditions'] = $this->userModel->getAchievementConditions($id);
        $achievement['formatted_conditions'] = $this->userModel->formatConditions($achievement['conditions']);
        $achievement['unlocked_count'] = $this->userModel->getAchievementUnlockedCount($id);
        
        $totalUsers = $this->userModel->getTotalUsersCount();
        if ($totalUsers > 0) {
            $achievement['percent'] = round(($achievement['unlocked_count'] / $totalUsers) * 100, 1);
        } else {
            $achievement['percent'] = 0;
        }
    }
    
    /**
     * Получает пользователей с ачивкой с пагинацией
     * 
     * @param int $id ID ачивки
     * @return array Массив с пользователями и данными пагинации
     */
    private function getAchievementUsers($id) {
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $page = max(1, $page);
        $perPage = 20;
        
        return $this->userModel->getAchievementUsers($id, $page, $perPage);
    }
    
    /**
     * Проверяет, есть ли у текущего пользователя данная ачивка
     * 
     * @param int $id ID ачивки
     * @return bool true если есть
     */
    private function checkUserHasAchievement($id) {
        if (!isset($_SESSION['user_id'])) {
            return false;
        }
        
        return $this->userModel->userHasAchievement($_SESSION['user_id'], $id);
    }
    
    /**
     * Отображает страницу с деталями ачивки
     * 
     * @param array $achievement Данные ачивки
     * @param array $usersData Данные пользователей и пагинации
     * @param bool $userHasAchievement Флаг наличия ачивки у текущего пользователя
     * @return void
     */
    private function renderAchievementPage($achievement, $usersData, $userHasAchievement) {
        $this->render('front/users/achievements/show', [
            'achievement' => $achievement,
            'users' => $usersData['users'],
            'pagination' => [
                'total' => $usersData['total'],
                'page' => $usersData['page'],
                'per_page' => $usersData['per_page'],
                'total_pages' => $usersData['total_pages']
            ],
            'userHasAchievement' => $userHasAchievement,
            'pageTitle' => $achievement['name'] . ' - Ачивки'
        ]);
    }
}