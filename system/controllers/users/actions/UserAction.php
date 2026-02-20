<?php

namespace users\actions;

/**
 * Абстрактный базовый класс для всех действий модуля пользователей
 * Предоставляет общую функциональность, доступ к моделям пользователей,
 * полей и постов, а также вспомогательные методы для работы с представлениями,
 * перенаправлениями и проверкой прав доступа
 * 
 * @package users\actions
 */
abstract class UserAction {
    
    /** @var object Подключение к базе данных */
    protected $db;
    
    /** @var array Параметры запроса (GET, POST, маршрутные параметры) */
    protected $params;
    
    /** @var object Контроллер, вызывающий действие */
    protected $controller;
    
    /** @var \UserModel Модель для работы с пользователями, группами и достижениями */
    protected $userModel;
    
    /** @var \FieldModel Модель для работы с пользовательскими полями */
    protected $fieldModel;
    
    /** @var \PostModel Модель для работы с постами */
    protected $postModel;
    
    /**
     * Конструктор класса действия
     * Инициализирует подключение к БД, параметры и все необходимые модели
     * 
     * @param object $db Подключение к базе данных
     * @param array $params Параметры запроса (по умолчанию [])
     */
    public function __construct($db, $params = []) {
        $this->db = $db;
        $this->params = $params;
        
        // Инициализация моделей для работы с данными
        $this->userModel = new \UserModel($db);
        $this->fieldModel = new \FieldModel($db);
        $this->postModel = new \PostModel($db);
    }
    
    /**
     * Устанавливает контроллер, вызывающий действие
     * Необходимо для делегирования операций рендеринга и перенаправления
     * 
     * @param object $controller Контроллер
     * @return void
     */
    public function setController($controller) {
        $this->controller = $controller;
    }
    
    /**
     * Абстрактный метод выполнения действия
     * Должен быть реализован в классах-наследниках
     * Содержит основную логику конкретного действия
     * 
     * @return void
     */
    abstract public function execute();
    
    /**
     * Рендерит шаблон с переданными данными
     * Использует контроллер для рендеринга, если он установлен
     * 
     * @param string $template Путь к шаблону относительно папки views
     * @param array $data Данные для передачи в шаблон
     * @throws \Exception Если контроллер не установлен
     * @return void
     */
    protected function render($template, $data = []) {
        if ($this->controller) {
            $this->controller->render($template, $data);
        } else {
            throw new \Exception('Controller not set for Action');
        }
    }
    
    /**
     * Выполняет перенаправление на указанный URL
     * Использует контроллер для перенаправления, если он установлен,
     * иначе выполняет перенаправление через стандартный PHP-заголовок
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
     * Проверяет, имеет ли текущий пользователь права администратора
     * Основана на проверке сессионной переменной is_admin
     * 
     * @return bool true если пользователь администратор, false в противном случае
     */
    protected function checkAdminAccess() {
        return isset($_SESSION['is_admin']) && $_SESSION['is_admin'];
    }
    
    /**
     * Получает ID текущего авторизованного пользователя
     * 
     * @return int|null ID пользователя или null, если пользователь не авторизован
     */
    protected function getCurrentUserId() {
        return $_SESSION['user_id'] ?? null;
    }
}