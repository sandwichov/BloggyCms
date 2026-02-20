<?php

namespace users\actions\achievements;

/**
 * Действие отображения списка всех достижений (ачивок) в административной панели
 * Главная страница управления ачивками, показывает все ачивки с возможностью
 * фильтрации по типу и поиска по названию/описанию
 * 
 * @package users\actions\achievements
 * @extends AdminAchievementAction
 */
class AdminAchievementIndex extends AdminAchievementAction {
    
    /**
     * Метод выполнения отображения списка ачивок
     * Получает параметры фильтрации из GET-запроса, загружает ачивки
     * и передает их в шаблон для отображения
     * 
     * @return void
     */
    public function execute() {
        try {
            // Получение параметров фильтрации из GET-запроса
            $type = $_GET['type'] ?? null;
            $search = $_GET['search'] ?? null;
            
            // Формирование массива фильтров
            $filters = [];
            if ($type) {
                $filters['type'] = $type;
            }
            if ($search) {
                $filters['search'] = $search;
            }
            
            // Получение списка ачивок с применением фильтров
            $achievements = $this->userModel->getAllAchievements($filters);
            
            // Отображение страницы со списком ачивок
            $this->render('admin/user-achievements/index', [
                'achievements' => $achievements,
                'pageTitle' => 'Управление ачивками'
            ]);
            
        } catch (\Exception $e) {
            // Обработка ошибок при загрузке списка
            \Notification::error('Ошибка при загрузке ачивок: ' . $e->getMessage());
            $this->redirect(ADMIN_URL);
        }
    }
}