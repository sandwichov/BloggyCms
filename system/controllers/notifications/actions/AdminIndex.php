<?php

namespace notifications\actions;

/**
 * Действие отображения главной страницы уведомлений в админ-панели
 * Загружает статистику уведомлений и передает необходимые данные в шаблон
 * 
 * @package notifications\actions
 * @extends NotificationsAction
 */
class AdminIndex extends NotificationsAction {
    
    /**
     * Метод выполнения отображения страницы уведомлений
     * Получает статистику уведомлений текущего пользователя и передает данные в шаблон
     * При возникновении ошибки показывает уведомление и перенаправляет на главную
     * 
     * @return void
     */
    public function execute() {
        try {
            // Получение ID текущего пользователя
            $userId = $this->getCurrentUserId();
            
            // Получение статистики уведомлений пользователя
            $stats = $this->notificationModel->getStats($userId);
            
            // Подготовка данных для шаблона
            $viewData = $this->prepareViewData($userId, $stats);
            
            // Отображение страницы с передачей всех необходимых данных
            $this->render('admin/notifications/index', $viewData);
            
        } catch (\Exception $e) {
            // Обработка ошибок при загрузке страницы
            $this->handleError($e);
        }
    }
    
    /**
     * Подготавливает данные для передачи в шаблон
     * 
     * @param int $userId ID текущего пользователя
     * @param array $stats Статистика уведомлений
     * @return array Массив данных для шаблона
     */
    private function prepareViewData($userId, $stats) {
        return [
            'stats' => $stats,                                  // Статистика уведомлений
            'notificationModel' => $this->notificationModel,    // Модель уведомлений для использования в шаблоне
            'userModel' => $this->userModel,                    // Модель пользователей для использования в шаблоне
            'currentUserId' => $userId,                         // ID текущего пользователя
            'pageTitle' => 'Уведомления'                        // Заголовок страницы
        ];
    }
    
    /**
     * Обрабатывает ошибки при загрузке страницы уведомлений
     * 
     * @param \Exception $e Исключение
     * @return void
     */
    private function handleError($e) {
        // Отображение сообщения об ошибке пользователю
        \Notification::error('Ошибка при загрузке уведомлений: ' . $e->getMessage());
        
        // Перенаправление на главную страницу административной панели
        $this->redirect(ADMIN_URL);
    }
}