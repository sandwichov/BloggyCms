<?php

namespace notifications\actions;

/**
 * Действие отметки конкретного уведомления как прочитанного
 * Помечает указанное уведомление текущего пользователя как прочитанное
 * Поддерживает как обычные HTTP-запросы, так и AJAX-вызовы
 * 
 * @package notifications\actions
 * @extends NotificationsAction
 */
class AdminMarkAsRead extends NotificationsAction {
    
    /**
     * Метод выполнения отметки уведомления как прочитанного
     * Проверяет наличие ID, обновляет статус уведомления и возвращает обновленный счетчик
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
            
            // Отметка уведомления как прочитанного
            $result = $this->markNotificationAsRead($id, $userId);
            
            // Получение обновленного количества непрочитанных уведомлений
            $unreadCount = $this->notificationModel->getUnreadCount($userId);
            
            // Обработка результата в зависимости от типа запроса
            $this->handleSuccessResult($result, $unreadCount, $isAjax);
            
        } catch (\Exception $e) {
            // Обработка ошибок
            $this->sendError('Ошибка: ' . $e->getMessage(), $isAjax);
        }
    }
    
    /**
     * Отмечает уведомление как прочитанное в базе данных
     * 
     * @param int $id ID уведомления
     * @param int $userId ID пользователя
     * @return object Результат выполнения запроса
     * @throws \Exception Если уведомление не найдено
     */
    private function markNotificationAsRead($id, $userId) {
        $result = $this->notificationModel->markAsRead($id, $userId);
        
        // Проверка, было ли найдено и обновлено уведомление
        if (!$result || $result->rowCount() === 0) {
            throw new \Exception('Уведомление не найдено');
        }
        
        return $result;
    }
    
    /**
     * Обрабатывает успешный результат операции
     * 
     * @param object $result Результат выполнения запроса
     * @param int $unreadCount Обновленное количество непрочитанных уведомлений
     * @param bool $isAjax Флаг AJAX-запроса
     * @return void
     */
    private function handleSuccessResult($result, $unreadCount, $isAjax) {
        if ($isAjax) {
            // Для AJAX-запросов возвращаем JSON с обновленными данными
            $this->sendJsonResponse(true, 'Уведомление отмечено как прочитанное', [
                'unread_count' => $unreadCount
            ]);
        } else {
            // Для обычных запросов показываем уведомление и перенаправляем
            \Notification::success('Уведомление отмечено как прочитанное');
            $this->redirectToPreviousPage();
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