<?php

namespace controllers\actions;

/**
 * Абстрактный базовый класс для действий управления контроллерами
 * Предоставляет общую функциональность для всех действий, связанных с управлением контроллерами,
 * включая доступ к базе данных, рендеринг шаблонов и проверку прав доступа
 * 
 * @package controllers\actions
 * @abstract
 */
abstract class ControllersAction {
    
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
     * Конструктор базового класса действий управления контроллерами
     * Инициализирует подключение к БД и параметры действия
     *
     * @param \Database $db Объект подключения к базе данных
     * @param array $params Дополнительные параметры действия
     */
    public function __construct($db, $params = []) {
        $this->db = $db;
        $this->params = $params;
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
}