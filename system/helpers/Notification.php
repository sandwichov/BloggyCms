<?php

/**
 * Класс для управления уведомлениями (тостами) через сессию
 * Позволяет устанавливать сообщения разных типов, которые затем
 * могут быть отображены на следующей странице
 * 
 * @package Core
 */
class Notification {
    
    /**
     * Устанавливает уведомление об успешной операции
     * Сохраняет в сессию toast типа 'success'
     * 
     * @param string $message Текст уведомления
     * @return void
     */
    public static function success($message) {
        $_SESSION['toast'] = [
            'type' => 'success',
            'message' => $message
        ];
    }

    /**
     * Устанавливает уведомление об ошибке
     * Сохраняет в сессию toast типа 'danger'
     * 
     * @param string $message Текст уведомления
     * @return void
     */
    public static function error($message) {
        $_SESSION['toast'] = [
            'type' => 'danger',
            'message' => $message
        ];
    }

    /**
     * Устанавливает предупреждающее уведомление
     * Сохраняет в сессию toast типа 'warning'
     * 
     * @param string $message Текст уведомления
     * @return void
     */
    public static function warning($message) {
        $_SESSION['toast'] = [
            'type' => 'warning',
            'message' => $message
        ];
    }

    /**
     * Устанавливает информационное уведомление
     * Сохраняет в сессию toast типа 'info'
     * 
     * @param string $message Текст уведомления
     * @return void
     */
    public static function info($message) {
        $_SESSION['toast'] = [
            'type' => 'info',
            'message' => $message
        ];
    }
}