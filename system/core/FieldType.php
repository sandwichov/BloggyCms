<?php

/**
 * Абстрактный класс для определения типов полей (устаревшая реализация)
 */
abstract class FieldType {
    /**
     * @var string Отображаемое имя поля
     */
    protected $name;
    
    /**
     * @var string Системное имя поля
     */
    protected $systemName;
    
    /**
     * @var array Конфигурация поля
     */
    protected $config = [];
    
    /**
     * Рендерит поле ввода
     *
     * @param mixed $value Значение поля
     * @param array $config Конфигурация поля
     * @return string HTML код поля ввода
     */
    abstract public function renderInput($value, $config);
    
    /**
     * Обрабатывает значение поля
     *
     * @param mixed $value Значение поля
     * @return mixed Обработанное значение
     */
    abstract public function processValue($value);
    
    /**
     * Валидирует значение поля
     *
     * @param mixed $value Значение для валидации
     * @return bool Результат валидации
     */
    abstract public function validate($value);
    
    /**
     * Конструктор FieldType
     *
     * @param string $name Отображаемое имя
     * @param string $systemName Системное имя
     * @param array $config Конфигурация
     */
    public function __construct($name, $systemName, $config = []) {
        $this->name = $name;
        $this->systemName = $systemName;
        $this->config = $config;
    }
    
    /**
     * Получает отображаемое имя поля
     *
     * @return string Имя поля
     */
    public function getName() { 
        return $this->name; 
    }
    
    /**
     * Получает системное имя поля
     *
     * @return string Системное имя
     */
    public function getSystemName() { 
        return $this->systemName; 
    }
    
    /**
     * Получает конфигурацию поля
     *
     * @return array Конфигурация
     */
    public function getConfig() { 
        return $this->config; 
    }
}