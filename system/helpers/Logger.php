<?php

/**
 * Класс для логирования событий в файл
 * Предоставляет удобные методы для записи логов с разными уровнями
 * 
 * @package Core
 */
class Logger {
    
    /** @var string Путь к файлу лога */
    private static $logFile = __DIR__ . '/../../logs/app.log'; // Используем абсолютный путь
    
    /**
     * Инициализирует систему логирования
     * Создает директорию для логов, если она не существует
     * 
     * @return void
     */
    public static function init() {
        $logDir = dirname(self::$logFile);
        if (!file_exists($logDir)) {
            mkdir($logDir, 0777, true);
        }
    }
    
    /**
     * Записывает сообщение в лог
     * Автоматически вызывает init() при необходимости
     * 
     * @param mixed $message Сообщение для логирования (может быть массивом/объектом)
     * @param string $type Тип сообщения (INFO, ERROR, DEBUG и т.д.)
     * @return void
     */
    public static function log($message, $type = 'INFO') {
        self::init();
        
        // Преобразование массива/объекта в строку
        $message = is_array($message) || is_object($message) 
            ? print_r($message, true) 
            : (string)$message;
        
        // Форматирование сообщения
        $logMessage = sprintf("[%s][%s] %s\n", 
            date('Y-m-d H:i:s'), 
            $type, 
            $message
        );
        
        // Запись в файл
        error_log($logMessage, 3, self::$logFile);
    }
    
    /**
     * Записывает сообщение об ошибке
     * 
     * @param mixed $message Сообщение об ошибке
     * @return void
     */
    public static function error($message) {
        self::log($message, 'ERROR');
    }
    
    /**
     * Записывает отладочное сообщение
     * Запись происходит только если определена константа DEBUG_MODE и она равна true
     * 
     * @param mixed $message Отладочное сообщение
     * @return void
     */
    public static function debug($message) {
        if (defined('DEBUG_MODE') && DEBUG_MODE) {
            self::log($message, 'DEBUG');
        }
    }
}