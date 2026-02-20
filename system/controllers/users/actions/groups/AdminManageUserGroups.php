<?php

namespace users\actions\groups;

/**
 * Действие управления членством пользователя в группах в административной панели
 * Отображает форму со списком всех групп и позволяет назначать/отзывать
 * членство пользователя в группах
 * 
 * @package users\actions\groups
 * @extends AdminGroupAction
 */
class AdminManageUserGroups extends AdminGroupAction {
    
    /**
     * Метод выполнения управления группами пользователя
     * Проверяет права доступа, ID пользователя, существование пользователя,
     * при POST-запросе сохраняет выбранные группы, при GET-запросе отображает форму
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
            $userId = $this->params['id'] ?? null;
            if (!$userId) {
                throw new \Exception('ID пользователя не указан');
            }

            // Загрузка данных пользователя
            $user = $this->userModel->getById($userId);
            if (!$user) {
                throw new \Exception('Пользователь не найден');
            }

            // Обработка POST-запроса (сохранение групп)
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $this->handlePostRequest($userId);
                return;
            }

            // Отображение формы управления группами
            $this->renderGroupsForm($user);

        } catch (\Exception $e) {
            \Notification::error($e->getMessage());
            $this->redirect(ADMIN_URL . '/users');
        }
    }
    
    /**
     * Обрабатывает POST-запрос на обновление групп пользователя
     * 
     * @param int $userId ID пользователя
     * @return void
     */
    private function handlePostRequest($userId) {
        // Получение выбранных групп из POST-данных
        $groupIds = $_POST['groups'] ?? [];
        
        // Обновление групп пользователя через модель
        $this->userModel->updateUserGroups($userId, $groupIds);
        
        \Notification::success('Группы пользователя обновлены');
        $this->redirect(ADMIN_URL . '/users');
    }
    
    /**
     * Отображает форму управления группами пользователя
     * 
     * @param array $user Данные пользователя
     * @return void
     */
    private function renderGroupsForm($user) {
        // Получение всех существующих групп
        $allGroups = $this->userModel->getAllGroups();
        
        // Получение ID групп, в которых состоит пользователь
        $userGroups = $this->userModel->getUserGroups($userId);
        
        $this->render('admin/users/manage-groups', [
            'user' => $user,              // Данные пользователя (для отображения имени)
            'allGroups' => $allGroups,     // Все группы для отображения в чекбоксах
            'userGroups' => $userGroups,   // ID групп, в которых состоит пользователь
            'pageTitle' => 'Управление группами пользователя'
        ]);
    }
}