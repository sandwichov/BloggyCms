<?php

namespace comments\actions;

/**
 * Абстрактный базовый класс для действий с комментариями
 * Предоставляет общую функциональность для всех действий, связанных с комментариями,
 * включая доступ к моделям, проверку прав и базовые операции с HTTP-запросами
 * 
 * @package comments\actions
 * @abstract
 */
abstract class CommentAction {
    
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
     * @var \CommentModel Модель для работы с комментариями
     */
    protected $commentModel;
    
    /**
     * @var \PostModel Модель для работы с постами
     */
    protected $postModel;
    
    /**
     * @var \UserModel Модель для работы с пользователями
     */
    protected $userModel;
    
    /**
     * @var \CategoryModel Модель для работы с категориями
     */
    protected $categoryModel;
    
    /**
     * Конструктор базового класса действий комментариев
     * Инициализирует подключение к БД и создает экземпляры всех необходимых моделей
     *
     * @param \Database $db Объект подключения к базе данных
     * @param array $params Дополнительные параметры действия
     */
    public function __construct($db, $params = []) {
        $this->db = $db;
        $this->params = $params;
        
        // Инициализация помощника аутентификации
        \AuthHelper::init();
        
        // Инициализация моделей
        $this->commentModel = new \CommentModel($db);
        $this->postModel = new \PostModel($db);
        $this->userModel = new \UserModel($db);
        $this->categoryModel = new \CategoryModel($db);
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
     * Проверка доступа администратора
     * Проверяет наличие административных прав через систему аутентификации
     *
     * @return bool true если пользователь имеет административные права
     */
    protected function checkAdminAccess() {
        return \Auth::isAdmin();
    }
    
    /**
     * Проверка типа запроса
     * Определяет, является ли текущий запрос AJAX-запросом
     *
     * @return bool true если запрос является AJAX-запросом
     */
    protected function isAjaxRequest() {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) 
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
    
    /**
     * Получение ID текущего пользователя
     * Возвращает идентификатор авторизованного пользователя через систему аутентификации
     *
     * @return int|null ID пользователя или null если пользователь не авторизован
     */
    protected function getCurrentUserId() {
        return \Auth::getUserId();
    }
    
    /**
     * Проверка статуса администратора
     * Быстрый метод для проверки, является ли текущий пользователь администратором
     *
     * @return bool true если пользователь является администратором
     */
    protected function isAdmin() {
        return \Auth::isAdmin();
    }
}