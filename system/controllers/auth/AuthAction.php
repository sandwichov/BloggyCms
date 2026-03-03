<?php

namespace auth\actions;

/**
 * Абстрактный базовый класс для всех действий аутентификации
 * 
 * Предоставляет общую инфраструктуру для обработки запросов,
 * связанных с авторизацией и управлением пользователями.
 * Реализует паттерн "Template Method", где наследники определяют
 * конкретную логику в методе execute().
 * 
 * @package auth\actions
 * @version 1.0.0
 * @author BloggyCMS Team
 */
abstract class AuthAction {
    /**
     * @var \Database Объект подключения к базе данных
     * @access protected
     */
    protected $db;
    
    /**
     * @var array Параметры действия, переданные из маршрутизатора
     * @access protected
     */
    protected $params;
    
    /**
     * @var \Controller|null Родительский контроллер для делегирования операций
     * @access protected
     */
    protected $controller;
    
    /**
     * @var \UserModel Модель для работы с пользователями
     * @access protected
     */
    protected $userModel;
    
    /**
     * @var \BreadcrumbsManager Менеджер для работы с хлебными крошками
     * @access protected
     */
    protected $breadcrumbs;
    
    /**
     * @var string Заголовок страницы
     * @access protected
     */
    protected $pageTitle;
    
    /**
     * Конструктор абстрактного действия аутентификации
     * Инициализирует зависимости и создает экземпляр модели пользователя
     * 
     * @param \Database $db Объект подключения к базе данных
     * @param array $params Дополнительные параметры действия (опционально)
     */
    public function __construct($db, $params = []) {
        $this->db = $db;
        $this->params = $params;
        $this->userModel = new \UserModel($db);
        $this->breadcrumbs = new \BreadcrumbsManager($db);
        $this->pageTitle = '';
    }
    
    /**
     * Устанавливает родительский контроллер для действия
     * Позволяет делегировать операции рендеринга и редиректов
     * 
     * @param \Controller $controller Контроллер, которому принадлежит действие
     * @return void
     */
    public function setController($controller) {
        $this->controller = $controller;
    }
    
    /**
     * Абстрактный метод выполнения действия
     * Должен быть реализован в дочерних классах для определения
     * конкретной бизнес-логики действия (логин, регистрация и т.д.)
     * 
     * @return void
     * @throws \Exception При ошибках выполнения действия
     */
    abstract public function execute();
    
    /**
     * Добавляет элемент в хлебные крошки
     * 
     * @param string $title Название элемента
     * @param string|null $url URL элемента (null для текущего элемента)
     * @return self
     */
    protected function addBreadcrumb($title, $url = null) {
        $this->breadcrumbs->add($title, $url);
        return $this;
    }
    
    /**
     * Устанавливает заголовок страницы
     * 
     * @param string $title Заголовок
     * @return self
     */
    protected function setPageTitle($title) {
        $this->pageTitle = $title;
        return $this;
    }
    
    /**
     * Делегирует рендеринг шаблона родительскому контроллеру
     * Если контроллер не установлен, выбрасывает исключение
     */
    protected function render($template, $data = []) {
        if (!$this->controller) {
            throw new \Exception('Controller not set for Action');
        }
        
        if (!isset($data['breadcrumbs'])) {
            $data['breadcrumbs'] = $this->breadcrumbs;
        }
        
        if (!isset($data['title']) && $this->pageTitle) {
            $data['title'] = $this->pageTitle;
        }
        
        $this->controller->render($template, $data);
    }
    
    /**
     * Выполняет перенаправление на указанный URL
     * Использует метод контроллера если он доступен,
     * в противном случае отправляет заголовок Location напрямую
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
     * Генерирует и возвращает CSRF-токен для защиты форм
     * Создает криптографически безопасный токен и сохраняет его в сессии
     * Если токен уже существует, возвращает существующий
     */
    protected function generateCsrfToken() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Валидирует CSRF-токен из POST-запроса
     * Проверяет соответствие токена из формы токену в сессии
     * Защищает от межсайтовой подделки запросов (CSRF атак)
     */
    protected function validateCsrfToken() {
        return !empty($_POST['csrf_token']) && 
               !empty($_SESSION['csrf_token']) && 
               $_POST['csrf_token'] === $_SESSION['csrf_token'];
    }
}