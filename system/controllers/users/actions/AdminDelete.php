<?php

namespace users\actions;

/**
 * Действие удаления пользователя в административной панели
 * Удаляет указанного пользователя из базы данных с проверками безопасности:
 * нельзя удалить самого себя, нельзя удалить последнего администратора
 * 
 * @package users\actions
 * @extends UserAction
 */
class AdminDelete extends UserAction {
    
    /**
     * Метод выполнения удаления пользователя
     * Проверяет ID, права, выполняет валидацию безопасности,
     * удаляет аватар и пользователя из БД
     * 
     * @return void
     */
    public function execute() {
        // Получение ID пользователя из параметров
        $id = $this->params['id'] ?? null;
        
        // Проверка наличия ID
        if (!$id) {
            \Notification::error('ID пользователя не указан');
            $this->redirect(ADMIN_URL . '/users');
            return;
        }
        
        try {
            // Проверка: нельзя удалить самого себя
            if ($id == $this->getCurrentUserId()) {
                \Notification::error('Нельзя удалить собственный аккаунт');
                $this->redirect(ADMIN_URL . '/users');
                return;
            }

            // Загрузка данных пользователя
            $user = $this->userModel->getById($id);
            
            if (!$user) {
                \Notification::error('Пользователь не найден');
                $this->redirect(ADMIN_URL . '/users');
                return;
            }
            
            // Проверка: нельзя удалить последнего администратора
            if ($user['role'] === 'admin') {
                $adminsCount = $this->userModel->db->fetch("SELECT COUNT(*) as count FROM users WHERE role = 'admin'");
                if ($adminsCount['count'] <= 1) {
                    \Notification::error('Нельзя удалить последнего администратора');
                    $this->redirect(ADMIN_URL . '/users');
                    return;
                }
            }
            
            // Удаление аватара (если есть и не стандартный)
            $this->deleteUserAvatar($user);
            
            // Удаление пользователя из базы данных (каскадно)
            $this->userModel->delete($id);
            
            \Notification::success('Пользователь успешно удален');
            
        } catch (\Exception $e) {
            \Notification::error('Ошибка при удалении пользователя: ' . $e->getMessage());
        }
        
        // Перенаправление на страницу со списком пользователей
        $this->redirect(ADMIN_URL . '/users');
    }
    
    /**
     * Удаляет аватар пользователя с сервера
     * Не удаляет стандартный аватар (default.jpg)
     * 
     * @param array $user Данные пользователя
     * @return void
     */
    protected function deleteUserAvatar($user) {
        if (!empty($user['avatar']) && $user['avatar'] !== 'default.jpg') {
            $filePath = UPLOADS_PATH . '/avatars/' . $user['avatar'];
            if (file_exists($filePath)) {
                @unlink($filePath);
            }
        }
    }
}