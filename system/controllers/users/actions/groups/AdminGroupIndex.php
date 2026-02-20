<?php

namespace users\actions\groups;

/**
 * Действие отображения списка всех групп пользователей в административной панели
 * Главная страница управления группами, показывает все созданные группы
 * 
 * @package users\actions\groups
 * @extends AdminGroupAction
 */
class AdminGroupIndex extends AdminGroupAction {
    
    /**
     * Метод выполнения отображения списка групп
     * Проверяет права доступа, получает все группы из базы данных
     * и передает их в шаблон для отображения
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

            // Получение списка всех групп пользователей
            $groups = $this->userModel->getAllGroups();
            
            // Отображение страницы со списком групп
            $this->render('admin/user-groups/index', [
                'groups' => $groups,
                'pageTitle' => 'Управление группами пользователей'
            ]);
            
        } catch (\Exception $e) {
            // Обработка ошибок при загрузке списка
            \Notification::error('Ошибка при загрузке групп');
            $this->redirect(ADMIN_URL);
        }
    }
}