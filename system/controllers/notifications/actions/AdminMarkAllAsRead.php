<?php

namespace notifications\actions;

/**
 * Действие отметки всех уведомлений как прочитанных
 * Помечает все непрочитанные уведомления текущего пользователя как прочитанные
 * Поддерживает как обычные HTTP-запросы, так и AJAX-вызовы
 * 
 * @package notifications\actions
 * @extends NotificationsAction
 */
class AdminMarkAllAsRead extends NotificationsAction {
    
    /**
     * Метод выполнения отметки всех уведомлений как прочитанных
     * Проверяет наличие непрочитанных уведомлений и обновляет их статус
     * Возвращает соответствующий ответ в зависимости от типа запроса
     * 
     * @return void
     */
    public function execute() {
        // Определение типа запроса (AJAX или обычный)
        $isAjax = $this->isAjaxRequest();
        
        try {
            // Получение ID текущего пользователя
            $userId = $this->getCurrentUserId();
            
            // Проверка наличия непрочитанных уведомлений
            $this->checkUnreadNotificationsExist($userId, $isAjax);
            
            // Отметка всех уведомлений как прочитанных
            $result = $this->notificationModel->markAllAsRead($userId);
            
            // Обработка результата операции
            $this->handleMarkAllResult($result, $isAjax);
            
        } catch (\Exception $e) {
            // Обработка ошибок в процессе отметки
            $this->handleMarkAllError($e, $isAjax);
        }
        
        // Перенаправление только для обычных (не AJAX) запросов
        if (!$isAjax) {
            $this->redirectToPreviousPage();
        }
    }
    
    /**
     * Проверяет наличие непрочитанных уведомлений перед отметкой
     * Если непрочитанных уведомлений нет, отправляет соответствующее сообщение
     * 
     * @param int $userId ID пользователя
     * @param bool $isAjax Флаг AJAX-запроса
     * @throws \Exception Если нет непрочитанных уведомлений (через отправку ответа)
     * @return void
     */
    private function checkUnreadNotificationsExist($userId, $isAjax) {
        $unreadCount = $this->notificationModel->getUnreadCount($userId);
        
        if ($unreadCount == 0) {
            if ($isAjax) {
                $this->sendJsonResponse(false, 'Нет непрочитанных уведомлений');
                exit; // Завершаем выполнение для AJAX
            } else {
                \Notification::warning('Нет непрочитанных уведомлений');
                $this->redirect(ADMIN_URL . '/notifications');
                exit; // Завершаем выполнение для обычного запроса
            }
        }
    }
    
    /**
     * Обрабатывает результат операции отметки всех уведомлений
     * 
     * @param bool $result Результат операции
     * @param bool $isAjax Флаг AJAX-запроса
     * @throws \Exception Если операция не удалась
     * @return void
     */
    private function handleMarkAllResult($result, $isAjax) {
        if ($result) {
            if ($isAjax) {
                $this->sendJsonResponse(true, 'Все уведомления отмечены как прочитанные', [
                    'unread_count' => 0
                ]);
            } else {
                \Notification::success('Все уведомления отмечены как прочитанные');
            }
        } else {
            throw new \Exception('Не удалось обновить уведомления');
        }
    }
    
    /**
     * Обрабатывает ошибки, возникшие при отметке уведомлений
     * 
     * @param \Exception $e Исключение
     * @param bool $isAjax Флаг AJAX-запроса
     * @return void
     */
    private function handleMarkAllError($e, $isAjax) {
        $errorMessage = 'Ошибка при обновлении уведомлений: ' . $e->getMessage();
        
        if ($isAjax) {
            $this->sendJsonResponse(false, $errorMessage, [], 500);
        } else {
            \Notification::error($errorMessage);
        }
    }
    
    /**
     * Отправляет JSON-ответ для AJAX-запросов
     * 
     * @param bool $success Флаг успешности операции
     * @param string $message Сообщение для пользователя
     * @param array $extra Дополнительные данные для ответа
     * @param int $httpCode HTTP-код ответа (по умолчанию 200)
     * @return void
     */
    private function sendJsonResponse($success, $message, $extra = [], $httpCode = 200) {
        if (!$success && $httpCode === 200) {
            $httpCode = 400;
        }
        
        http_response_code($httpCode);
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
     * Перенаправляет на предыдущую страницу или на страницу уведомлений
     * 
     * @return void
     */
    private function redirectToPreviousPage() {
        $redirectUrl = $_SERVER['HTTP_REFERER'] ?? ADMIN_URL . '/notifications';
        $this->redirect($redirectUrl);
    }
}