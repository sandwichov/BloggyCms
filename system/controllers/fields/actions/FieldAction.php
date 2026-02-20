<?php

namespace fields\actions;

/**
 * Абстрактный базовый класс для действий управления полями
 * Предоставляет общую функциональность для всех действий, связанных с дополнительными полями,
 * включая доступ к модели полей, рендеринг шаблонов и вспомогательные методы
 * 
 * @package fields\actions
 * @abstract
 */
abstract class FieldAction {
    
    /**
     * @var \Database Объект подключения к базе данных
     */
    protected $db;
    
    /**
     * @var array Массив параметров, переданных действию
     */
    protected $params;
    
    /**
     * @var object|null Контроллер, управляющий действием
     */
    protected $controller;
    
    /**
     * @var \FieldModel Модель для работы с дополнительными полями
     */
    protected $fieldModel;
    
    /**
     * Конструктор базового класса действий полей
     * Инициализирует подключение к БД и создает экземпляр модели полей
     *
     * @param \Database $db Объект подключения к базе данных
     * @param array $params Дополнительные параметры действия
     */
    public function __construct($db, $params = []) {
        $this->db = $db;
        $this->params = $params;
        $this->fieldModel = new \FieldModel($db);
    }
    
    /**
     * Установка контроллера для действия
     * Связывает действие с контроллером для доступа к его методам
     *
     * @param object $controller Объект контроллера
     * @return void
     */
    public function setController($controller) {
        $this->controller = $controller;
    }
    
    /**
     * Абстрактный метод выполнения действия
     * Должен быть реализован в дочерних классах
     *
     * @return mixed Результат выполнения действия
     * @abstract
     */
    abstract public function execute();
    
    /**
     * Рендеринг шаблона с данными
     * Передает управление методу рендеринга контроллера
     *
     * @param string $template Путь к файлу шаблона
     * @param array $data Массив данных для передачи в шаблон
     * @return void
     * @throws \Exception Если контроллер не установлен
     */
    protected function render($template, $data = []) {
        if ($this->controller) {
            $this->controller->render($template, $data);
        } else {
            throw new \Exception('Controller not set for Action');
        }
    }
    
    /**
     * Перенаправление на указанный URL
     * Использует метод перенаправления контроллера или стандартный header
     *
     * @param string $url URL для перенаправления
     * @return void
     */
    protected function redirect($url) {
        if ($this->controller) {
            $this->controller->redirect($url);
        } else {
            header('Location: ' . $url);
            exit;
        }
    }
    
    /**
     * Получение читаемого имени типа поля
     * Преобразует техническое название типа поля в удобочитаемый формат
     *
     * @param string $type Техническое название типа поля
     * @return string Читаемое название типа поля
     */
    protected function getFieldTypeName($type) {
        $types = [
            'text' => 'Текст',
            'textarea' => 'Текстовая область',
            'number' => 'Число',
            'select' => 'Выпадающий список',
            'checkbox' => 'Чекбокс',
            'file' => 'Файл',
            'date' => 'Дата',
            'color' => 'Цвет',
            'email' => 'Email',
            'url' => 'URL'
        ];
        return $types[$type] ?? $type; // Возвращает исходный тип если перевод не найден
    }
    
    /**
     * Получение читаемого имени типа сущности
     * Преобразует техническое название типа сущности в удобочитаемый формат
     *
     * @param string $entityType Техническое название типа сущности
     * @return string Читаемое название типа сущности во множественном числе
     */
    protected function getEntityName($entityType) {
        $names = [
            'post' => 'Записей',
            'page' => 'Страниц',
            'category' => 'Категорий',
            'user' => 'Пользователей'
        ];
        return $names[$entityType] ?? $entityType; // Возвращает исходное название если перевод не найден
    }
}