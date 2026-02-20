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
     * Конструктор абстрактного действия аутентификации
     * Инициализирует зависимости и создает экземпляр модели пользователя
     * 
     * @param \Database $db Объект подключения к базе данных
     * @param array $params Дополнительные параметры действия (опционально)
     *                      Может содержать данные из URL, GET-параметры и т.д.
     */
    public function __construct($db, $params = []) {
        $this->db = $db;
        $this->params = $params;
        $this->userModel = new \UserModel($db);
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
     * Делегирует рендеринг шаблона родительскому контроллеру
     * Если контроллер не установлен, выбрасывает исключение
     * 
     * @param string $template Имя файла шаблона (относительно директории views)
     * @param array $data Ассоциативный массив данных для передачи в шаблон
     * @return void
     * @throws \Exception Если контроллер не был установлен
     * 
     * @example $this->render('auth/login', ['error' => 'Invalid credentials']);
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
     * Использует метод контроллера если он доступен,
     * в противном случае отправляет заголовок Location напрямую
     * 
     * @param string $url Абсолютный или относительный URL для перенаправления
     * @return void
     * @throws \Exception Если редирект не может быть выполнен
     * 
     * @example $this->redirect('/dashboard'); // Перенаправление на дашборд
     * @example $this->redirect('https://example.com'); // Внешний редирект
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
     * 
     * @return string CSRF-токен в шестнадцатеричном формате (64 символа)
     * @throws \Exception Если невозможно получить криптографически безопасные случайные байты
     * 
     * @see validateCsrfToken() Для проверки токена
     * @see https://cheatsheetseries.owasp.org/cheatsheets/Cross-Site_Request_Forgery_Prevention_Cheat_Sheet.html
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
     * 
     * @return bool true если токен валиден, false в противном случае
     * 
     * @example
     * if (!$this->validateCsrfToken()) {
     *     throw new \Exception('CSRF token validation failed');
     * }
     * 
     * @warning Не используйте этот метод для GET запросов - CSRF атаки работают только
     *          с изменяющими состояние запросами (POST, PUT, DELETE)
     */
    protected function validateCsrfToken() {
        return !empty($_POST['csrf_token']) && 
               !empty($_SESSION['csrf_token']) && 
               $_POST['csrf_token'] === $_SESSION['csrf_token'];
    }
}