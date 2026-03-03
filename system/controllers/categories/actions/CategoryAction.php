<?php

namespace categories\actions;

/**
 * Абстрактный базовый класс для действий с категориями
 * Предоставляет общую функциональность для всех действий, связанных с категориями,
 * включая доступ к базе данных, рендеринг шаблонов и проверку прав доступа.
 * 
 * @package categories\actions
 * @abstract
 */
abstract class CategoryAction {
    
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
     * @var \CategoryModel Модель для работы с категориями
     */
    protected $categoryModel;
    
    /**
     * @var \BreadcrumbsManager Менеджер для работы с хлебными крошками
     */
    protected $breadcrumbs;
    
    /**
     * @var string Заголовок страницы
     */
    protected $pageTitle;
    
    /**
     * Конструктор базового класса действий
     * Инициализирует подключение к БД и создает экземпляр модели категорий
     *
     * @param \Database $db Объект подключения к базе данных
     * @param array $params Дополнительные параметры действия
     */
    public function __construct($db, $params = []) {
        $this->db = $db;
        $this->params = $params;
        $this->categoryModel = new \CategoryModel($db);
        $this->breadcrumbs = new \BreadcrumbsManager($db);
        $this->pageTitle = '';
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
     * Добавляет элемент в начало хлебных крошек
     * 
     * @param string $title Название элемента
     * @param string|null $url URL элемента
     * @return self
     */
    protected function prependBreadcrumb($title, $url = null) {
        $this->breadcrumbs->prepend($title, $url);
        return $this;
    }
    
    /**
     * Очищает все хлебные крошки
     * 
     * @return self
     */
    protected function clearBreadcrumbs() {
        $this->breadcrumbs->clear();
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
     * Рендеринг шаблона с данными
     * Передает управление методу рендеринга контроллера
     *
     * @param string $template Путь к файлу шаблона
     * @param array $data Массив данных для передачи в шаблон
     * @return void
     * @throws \Exception Если контроллер не установлен
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
     * Проверяет наличие административных прав в сессии пользователя
     *
     * @return bool true если пользователь имеет административные права
     */
    protected function checkAdminAccess() {
        return isset($_SESSION['is_admin']) && $_SESSION['is_admin'];
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
     * Возвращает менеджер хлебных крошек
     * 
     * @return \BreadcrumbsManager
     */
    protected function getBreadcrumbs() {
        return $this->breadcrumbs;
    }
}