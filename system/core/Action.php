<?php

/**
 * Абстрактный базовый класс для реализации паттерна "Команда"
 */
abstract class Action {
    /**
     * @var mixed Подключение к базе данных
     */
    protected $db;
    
    /**
     * @var array Параметры для выполнения действия
     */
    protected $params;
    
    /**
     * Конструктор класса Action
     *
     * @param mixed $db Подключение к базе данных
     * @param array $params Параметры для выполнения действия
     */
    public function __construct($db, $params = []) {
        $this->db = $db;
        $this->params = $params;
    }
    
    /**
     * Абстрактный метод выполнения действия
     * Должен быть реализован в дочерних классах
     *
     * @return mixed Результат выполнения действия
     */
    abstract public function execute();
}