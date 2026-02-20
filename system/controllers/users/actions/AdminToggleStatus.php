<?php

namespace users\actions;

/**
 * Действие переключения статуса пользователя в административной панели
 * Меняет статус пользователя между 'active' (активен) и 'banned' (заблокирован)
 * 
 * @package users\actions
 * @extends UserAction
 */
class AdminToggleStatus extends UserAction {
    
    /**
     * Метод выполнения переключения статуса пользователя
     * Проверяет ID, защиту от самоизменения, существование пользователя,
     * определяет новый статус (противоположный текущему) и обновляет его
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
            // Защита: нельзя изменить статус самому себе
            if ($id == $this->getCurrentUserId()) {
                \Notification::error('Нельзя изменить статус собственного аккаунта');
                $this->redirect(ADMIN_URL . '/users');
                return;
            }

            // Загрузка пользователя для проверки существования и получения текущего статуса
            $user = $this->userModel->getById($id);
            if (!$user) {
                throw new \Exception('Пользователь не найден');
            }
            
            // Определение нового статуса (противоположный текущему)
            $newStatus = $user['status'] === 'active' ? 'banned' : 'active';
            
            // Обновление статуса пользователя
            $this->userModel->update($id, ['status' => $newStatus]);
            
            // Формирование текста уведомления
            $statusText = $newStatus === 'active' ? 'активирован' : 'заблокирован';
            \Notification::success("Пользователь {$statusText}");
            
        } catch (\Exception $e) {
            // Уведомление об ошибке
            \Notification::error('Ошибка при изменении статуса пользователя: ' . $e->getMessage());
        }
        
        // Перенаправление на страницу со списком пользователей
        $this->redirect(ADMIN_URL . '/users');
    }
}