<?php

namespace notifications\actions;

/**
 * Действие очистки всех прочитанных уведомлений
 * Удаляет из базы данных все уведомления текущего пользователя, отмеченные как прочитанные
 * Поддерживает как обычные HTTP-запросы, так и AJAX-вызовы
 * 
 * @package notifications\actions
 * @extends NotificationsAction
 */
class AdminClear extends NotificationsAction {
    
    /**
     * Метод выполнения очистки прочитанных уведомлений
     * Проверяет наличие прочитанных уведомлений и удаляет их из базы данных
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
            
            // Проверка наличия прочитанных уведомлений перед очисткой
            $this->checkReadNotificationsExist($userId, $isAjax);
            
            // Выполнение очистки прочитанных уведомлений
            $result = $this->notificationModel->clearRead($userId);
            
            // Обработка результата операции
            $this->handleClearResult($result, $isAjax);
            
        } catch (\Exception $e) {
            // Обработка ошибок в процессе очистки
            $this->handleClearError($e, $isAjax);
        }
        
        // Перенаправление только для обычных (не AJAX) запросов
        if (!$isAjax) {
            $this->redirect(ADMIN_URL . '/notifications');
        }
    }
    
    /**
     * Проверяет наличие прочитанных уведомлений перед очисткой
     * Если прочитанных уведомлений нет, отправляет соответствующее сообщение
     * 
     * @param int $userId ID пользователя
     * @param bool $isAjax Флаг AJAX-запроса
     * @throws \Exception Если нет прочитанных уведомлений
     * @return void
     */
    private function checkReadNotificationsExist($userId, $isAjax) {
        $stats = $this->notificationModel->getStats($userId);
        
        if ($stats['read_count'] == 0) {
            if ($isAjax) {
                $this->sendJsonResponse(false, 'Нет прочитанных уведомлений для очистки');
                exit; // Завершаем выполнение для AJAX
            } else {
                \Notification::warning('Нет прочитанных уведомлений для очистки');
                $this->redirect(ADMIN_URL . '/notifications');
                exit; // Завершаем выполнение для обычного запроса
            }
        }
    }
    
    /**
     * Обрабатывает результат операции очистки
     * 
     * @param bool $result Результат операции очистки
     * @param bool $isAjax Флаг AJAX-запроса
     * @throws \Exception Если операция не удалась
     * @return void
     */
    private function handleClearResult($result, $isAjax) {
        if ($result) {
            if ($isAjax) {
                $this->sendJsonResponse(true, 'Прочитанные уведомления успешно очищены');
            } else {
                \Notification::success('Прочитанные уведомления успешно очищены');
            }
        } else {
            throw new \Exception('Не удалось очистить уведомления');
        }
    }
    
    /**
     * Обрабатывает ошибки, возникшие при очистке уведомлений
     * 
     * @param \Exception $e Исключение
     * @param bool $isAjax Флаг AJAX-запроса
     * @return void
     */
    private function handleClearError($e, $isAjax) {
        $errorMessage = 'Ошибка при очистке уведомлений: ' . $e->getMessage();
        
        if ($isAjax) {
            $this->sendJsonResponse(false, $errorMessage, 500);
        } else {
            \Notification::error($errorMessage);
        }
    }
    
    /**
     * Отправляет JSON-ответ для AJAX-запросов
     * 
     * @param bool $success Флаг успешности операции
     * @param string $message Сообщение для пользователя
     * @param int $httpCode HTTP-код ответа (по умолчанию 200)
     * @return void
     */
    private function sendJsonResponse($success, $message, $httpCode = 200) {
        if (!$success && $httpCode === 200) {
            $httpCode = 400;
        }
        
        http_response_code($httpCode);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => $success,
            'message' => $message
        ]);
    }
}