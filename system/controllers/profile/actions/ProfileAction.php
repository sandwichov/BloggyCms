<?php

namespace profile\actions;

/**
 * Абстрактный базовый класс для всех действий модуля профиля пользователя
 * Предоставляет общую функциональность, доступ к моделям и вспомогательные методы
 * для работы с профилями, аутентификацией и безопасностью
 * 
 * @package profile\actions
 */
abstract class ProfileAction {
    
    /** @var object Подключение к базе данных */
    protected $db;
    
    /** @var array Параметры запроса (GET, POST, маршрутные параметры) */
    protected $params;
    
    /** @var object Контроллер, вызывающий действие */
    protected $controller;
    
    /** @var \UserModel Модель для работы с пользователями */
    protected $userModel;
    
    /** @var \PostModel Модель для работы с постами */
    protected $postModel;
    
    /** @var \FieldModel Модель для работы с пользовательскими полями */
    protected $fieldModel;
    
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
        $this->userModel = new \UserModel($this->db);
        $this->postModel = new \PostModel($this->db);
        $this->fieldModel = new \FieldModel($this->db);
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
     * Проверяет, авторизован ли пользователь
     * Если нет, сохраняет текущий URL для редиректа после входа
     * и перенаправляет на страницу логина
     * 
     * @return bool true если пользователь авторизован
     */
    protected function checkAuthentication() {
        if (!isset($_SESSION['user_id'])) {
            $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
            $this->redirect('/login');
            exit;
        }
        return true;
    }
    
    /**
     * Генерирует или возвращает существующий CSRF-токен из сессии
     * Используется для защиты форм от межсайтовой подделки запросов
     * 
     * @return string CSRF-токен
     */
    protected function generateCsrfToken() {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Проверяет валидность CSRF-токена из POST-запроса
     * Сравнивает токен из формы с токеном в сессии
     * 
     * @return bool true если токен валидный
     */
    protected function validateCsrfToken() {
        return !empty($_POST['csrf_token']) && 
               !empty($_SESSION['csrf_token']) && 
               $_POST['csrf_token'] === $_SESSION['csrf_token'];
    }
    
    /**
     * Перенаправляет с сообщением об ошибке
     * Сохраняет сообщение в сессии и выполняет редирект
     * 
     * @param string $message Текст сообщения об ошибке
     * @param string $path URL для перенаправления
     * @return void
     */
    protected function redirectWithError($message, $path) {
        $_SESSION['error_message'] = $message;
        $this->redirect($path);
        exit;
    }
    
    /**
     * Перенаправляет с сообщением об успехе
     * Сохраняет стандартное сообщение об успехе в сессии и выполняет редирект
     * 
     * @param string $path URL для перенаправления
     * @return void
     */
    protected function redirectWithSuccess($path) {
        $_SESSION['success_message'] = 'Профиль успешно обновлен';
        $this->redirect($path);
        exit;
    }
}