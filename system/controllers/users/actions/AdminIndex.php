<?php

namespace users\actions;

/**
 * Действие отображения списка всех пользователей в административной панели
 * Главная страница управления пользователями, показывает всех пользователей
 * с фильтрацией по роли, статусу, группе и поиску, а также случайную подсказку
 * 
 * @package users\actions
 * @extends UserAction
 */
class AdminIndex extends UserAction {
    
    /**
     * Метод выполнения отображения списка пользователей
     * Получает параметры фильтрации из GET, загружает пользователей с группами,
     * применяет сортировку (администраторы сверху) и отображает список
     * 
     * @return void
     */
    public function execute() {
        try {
            // Массив подсказок для администратора
            $hints = [
                "Создайте группы пользователей для гибкого разграничения прав",
                "Добавьте дополнительные поля для профилей - создавайте целые анкеты для сбора важной информации о пользователях",
                "Вы можете заблокировать (забанить) пользователя прямо в общем списке ниже",
            ];
            
            // Выбор случайной подсказки
            $randomHint = $hints[array_rand($hints)];

            // Получение параметров фильтрации из GET-запроса
            $role = $_GET['role'] ?? null;
            $status = $_GET['status'] ?? null;
            $group = $_GET['group'] ?? null;
            $search = $_GET['search'] ?? null;
            
            // Загрузка пользователей с применением фильтров и информацией о группах
            $users = $this->userModel->getUsersWithGroups([
                'role' => $role,
                'status' => $status,
                'group' => $group,
                'search' => $search
            ]);
            
            // Применение сортировки (администраторы сверху), если включена настройка
            if (\SettingsHelper::get('controller_users', 'admin_top', true)) {
                $users = $this->sortUsersWithAdminsFirst($users);
            }
            
            // Загрузка всех групп для отображения в фильтре
            $allGroups = $this->userModel->getAllGroups();
            
            // Отображение страницы со списком пользователей
            $this->render('admin/users/index', [
                'users' => $users,           // Массив пользователей с группами
                'allGroups' => $allGroups,    // Все группы для фильтра
                'randomHint' => $randomHint,  // Случайная подсказка
                'pageTitle' => 'Управление пользователями'
            ]);
            
        } catch (\Exception $e) {
            // Обработка ошибок при загрузке списка
            \Notification::error('Ошибка при загрузке списка пользователей');
            $this->redirect(ADMIN_URL);
        }
    }
    
    /**
     * Сортирует пользователей: администраторы сверху, затем по алфавиту
     * 
     * @param array $users Массив пользователей для сортировки
     * @return array Отсортированный массив пользователей
     */
    private function sortUsersWithAdminsFirst($users) {
        usort($users, function($a, $b) {
            // Проверка, является ли пользователь администратором
            $aIsAdmin = $this->isUserAdmin($a);
            $bIsAdmin = $this->isUserAdmin($b);
            
            // Администраторы идут выше обычных пользователей
            if ($aIsAdmin && !$bIsAdmin) {
                return -1; // a идет перед b
            } elseif (!$aIsAdmin && $bIsAdmin) {
                return 1; // b идет перед a
            } else {
                // Оба админы или оба не админы - сортируем по имени
                return strcmp($a['username'], $b['username']);
            }
        });
        
        return $users;
    }
    
    /**
     * Проверяет, является ли пользователь администратором
     * Проверяет по группам (название "Администраторы" или system_name "administrators"),
     * а также по полю is_admin, если оно существует
     * 
     * @param array $user Данные пользователя
     * @return bool true если пользователь администратор
     */
    private function isUserAdmin($user) {
        // Проверка по группам
        if (!empty($user['groups'])) {
            foreach ($user['groups'] as $group) {
                if (isset($group['name']) && $group['name'] === 'Администраторы') {
                    return true;
                }
                // Альтернативная проверка по system_name если есть
                if (isset($group['system_name']) && $group['system_name'] === 'administrators') {
                    return true;
                }
            }
        }
        
        // Дополнительная проверка по полю is_admin если оно есть
        if (isset($user['is_admin']) && $user['is_admin']) {
            return true;
        }
        
        return false;
    }
}