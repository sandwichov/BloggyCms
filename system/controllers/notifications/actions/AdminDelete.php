<?php

namespace notifications\actions;

/**
 * Действие удаления конкретного уведомления
 * Удаляет указанное уведомление из базы данных после проверки прав доступа
 * Поддерживает как обычные HTTP-запросы, так и AJAX-вызовы
 * 
 * @package notifications\actions
 * @extends NotificationsAction
 */
class AdminDelete extends NotificationsAction {
    
    /**
     * Метод выполнения удаления уведомления
     * Проверяет наличие ID, существование уведомления и права доступа,
     * затем выполняет удаление и возвращает соответствующий ответ
     * 
     * @return void
     */
    public function execute() {
        // Получение параметров запроса
        $id = $this->params['id'] ?? null;
        $isAjax = $this->isAjaxRequest();
        
        // Проверка наличия ID уведомления
        if (!$id) {
            $this->sendError('ID уведомления не указан', $isAjax);
            return;
        }
        
        try {
            // Получение ID текущего пользователя
            $userId = $this->getCurrentUserId();
            
            // Проверка существования уведомления и прав доступа
            $this->validateNotificationAccess($id, $userId);
            
            // Выполнение удаления уведомления
            $result = $this->deleteNotification($id, $userId);
            
            // Обработка результата удаления
            $this->handleDeleteResult($result, $userId, $isAjax);
            
        } catch (\Exception $e) {
            // Обработка ошибок в процессе удаления
            $this->sendError('Ошибка: ' . $e->getMessage(), $isAjax);
            return;
        }
        
        // Перенаправление только для обычных (не AJAX) запросов
        if (!$isAjax) {
            $this->redirectToPreviousPage();
        }
    }
    
    /**
     * Проверяет существование уведомления и права доступа к нему
     * 
     * @param int $id ID уведомления
     * @param int $userId ID пользователя
     * @throws \Exception Если уведомление не найдено или доступ запрещен
     * @return void
     */
    private function validateNotificationAccess($id, $userId) {
        // Получение списка уведомлений пользователя
        $notifications = $this->notificationModel->getUserNotifications($userId, 1, 0, false);
        
        // Поиск уведомления с указанным ID
        $notificationExists = false;
        foreach ($notifications as $notification) {
            if ($notification['id'] == $id) {
                $notificationExists = true;
                break;
            }
        }
        
        if (!$notificationExists) {
            throw new \Exception('Уведомление не найдено или доступ запрещен');
        }
    }
    
    /**
     * Выполняет удаление уведомления из базы данных
     * 
     * @param int $id ID уведомления
     * @param int $userId ID пользователя
     * @return object Результат выполнения запроса
     * @throws \Exception Если не удалось удалить уведомление
     */
    private function deleteNotification($id, $userId) {
        $result = $this->notificationModel->delete($id, $userId);
        
        if (!$result || $result->rowCount() === 0) {
            throw new \Exception('Не удалось удалить уведомление');
        }
        
        return $result;
    }
    
    /**
     * Обрабатывает результат успешного удаления
     * 
     * @param object $result Результат выполнения запроса
     * @param int $userId ID пользователя
     * @param bool $isAjax Флаг AJAX-запроса
     * @return void
     */
    private function handleDeleteResult($result, $userId, $isAjax) {
        if ($isAjax) {
            // Получение обновленного количества непрочитанных уведомлений
            $unreadCount = $this->notificationModel->getUnreadCount($userId);
            
            // Отправка JSON-ответа с обновленными данными
            $this->sendJsonResponse(true, 'Уведомление успешно удалено', [
                'unread_count' => $unreadCount
            ]);
        } else {
            \Notification::success('Уведомление успешно удалено');
        }
    }
    
    /**
     * Отправляет успешный JSON-ответ с дополнительными данными
     * 
     * @param bool $success Флаг успешности операции
     * @param string $message Сообщение для пользователя
     * @param array $extra Дополнительные данные для ответа
     * @return void
     */
    private function sendJsonResponse($success, $message, $extra = []) {
        header('Content-Type: application/json');
        echo json_encode(array_merge(
            [
                'success' => $success,
                'message' => $message
            ],
            $extra
        ));
    }
    
    /**
     * Отправляет сообщение об ошибке в зависимости от типа запроса
     * 
     * @param string $message Сообщение об ошибке
     * @param bool $isAjax Флаг AJAX-запроса
     * @return void
     */
    private function sendError($message, $isAjax) {
        if ($isAjax) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => $message
            ]);
        } else {
            \Notification::error($message);
            $this->redirectToPreviousPage();
        }
    }
    
    /**
     * Перенаправляет на предыдущую страницу или на страницу уведомлений
     * 
     * @return void
     */
    private function redirectToPreviousPage() {
        $redirectUrl = $_SERVER['HTTP_REFERER'] ?? ADMIN_URL . '/notifications';
        $this->redirect($redirectUrl);
    }
}