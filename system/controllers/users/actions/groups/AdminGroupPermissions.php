<?php

namespace users\actions\groups;

/**
 * Действие управления правами доступа для группы пользователей в административной панели
 * Отображает форму со всеми доступными правами из контроллеров и позволяет
 * назначать/отзывать права для выбранной группы
 * 
 * @package users\actions\groups
 * @extends AdminGroupAction
 */
class AdminGroupPermissions extends AdminGroupAction {
    
    /**
     * Метод выполнения управления правами группы
     * Проверяет права доступа, ID, существование группы,
     * при POST-запросе сохраняет выбранные права, при GET-запросе отображает форму
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

            // Получение ID группы из параметров
            $id = $this->params['id'] ?? null;
            if (!$id) {
                throw new \Exception('ID группы не указан');
            }

            // Загрузка данных группы
            $group = $this->userModel->getGroupById($id);
            if (!$group) {
                throw new \Exception('Группа не найдена');
            }

            // Обработка POST-запроса (сохранение прав)
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $this->handlePostRequest($id);
                return;
            }

            // Отображение формы управления правами
            $this->renderPermissionsForm($group);

        } catch (\Exception $e) {
            \Notification::error($e->getMessage());
            $this->redirect(ADMIN_URL . '/user-groups');
        }
    }
    
    /**
     * Обрабатывает POST-запрос на сохранение прав группы
     * 
     * @param int $id ID группы
     * @return void
     */
    private function handlePostRequest($id) {
        // Получение выбранных прав из POST-данных
        $permissions = $_POST['permissions'] ?? [];
        
        // Обновление прав группы через модель
        $this->userModel->updateGroupPermissions($id, $permissions);
        
        \Notification::success('Права группы обновлены');
        $this->redirect(ADMIN_URL . '/user-groups');
    }
    
    /**
     * Отображает форму управления правами группы
     * 
     * @param array $group Данные группы
     * @return void
     */
    private function renderPermissionsForm($group) {
        // Загрузка всех доступных прав из файлов контроллеров
        $allPermissions = $this->loadAllPermissions();
        
        // Получение текущих прав группы
        $groupPermissions = $this->userModel->getGroupPermissions($id);
        
        $this->render('admin/user-groups/permissions', [
            'group' => $group,
            'allPermissions' => $allPermissions,
            'groupPermissions' => $groupPermissions,
            'pageTitle' => 'Управление правами группы'
        ]);
    }

    /**
     * Загружает все доступные права из файлов permissions.php контроллеров
     * Сканирует директорию system/controllers и подключает файлы permissions.php
     * 
     * @return array Массив прав, сгруппированных по контроллерам
     */
    private function loadAllPermissions() {
        $permissions = [];
        
        $controllersPath = ROOT_PATH . '/system/controllers';
        
        // Поиск всех файлов permissions.php в подпапках контроллеров
        $permissionFiles = glob($controllersPath . '/*/permissions.php');
        
        foreach ($permissionFiles as $file) {
            if (file_exists($file) && is_readable($file)) {
                // Загрузка прав из файла
                $controllerPermissions = include $file;
                
                // Получение имени контроллера из пути
                $controllerName = basename(dirname($file));
                
                // Группировка прав по имени контроллера
                $permissions[$controllerName] = $controllerPermissions;
            }
        }
        
        return $permissions;
    }
}