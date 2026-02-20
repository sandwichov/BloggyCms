<?php

/**
 * Реестр для глобального доступа к подключению базы данных
 */
class DatabaseRegistry {
    /**
     * @var self|null Единственный экземпляр класса
     */
    private static $instance;
    
    /**
     * @var mixed Подключение к базе данных
     */
    private $db;
    
    /**
     * Приватный конструктор
     */
    private function __construct() {}
    
    /**
     * Инициализирует реестр с подключением к БД
     *
     * @param mixed $db Подключение к базе данных
     */
    public static function init($db) {
        self::getInstance()->db = $db;
    }
    
    /**
     * Получает экземпляр реестра (Singleton)
     *
     * @return self Экземпляр DatabaseRegistry
     */
    public static function getInstance() {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Получает подключение к базе данных
     *
     * @return mixed Подключение к БД
     */
    public static function getDb() {
        return self::getInstance()->db;
    }
}