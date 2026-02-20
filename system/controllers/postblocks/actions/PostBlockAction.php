<?php

namespace postblocks\actions;

/**
 * Абстрактный базовый класс для всех действий модуля постблоков
 * Предоставляет общую функциональность, доступ к моделям и вспомогательные методы
 * 
 * @package postblocks\actions
 */
abstract class PostBlockAction {
    
    /** @var object Подключение к базе данных */
    protected $db;
    
    /** @var array Параметры запроса (GET, POST, маршрутные параметры) */
    protected $params;
    
    /** @var object Контроллер, вызывающий действие */
    protected $controller;
    
    /** @var \PostBlockManager Менеджер для работы с постблоками */
    protected $postBlockManager;
    
    /** @var \PostBlockModel Модель для работы с данными постблоков */
    protected $postBlockModel;
    
    /** @var string|null Системное имя постблока (для действий, где нужно) */
    protected $systemName;
    
    /**
     * Конструктор класса действия
     * Инициализирует подключение к БД, параметры и необходимые компоненты
     * 
     * @param object $db Подключение к базе данных
     * @param array $params Параметры запроса (по умолчанию [])
     */
    public function __construct($db, $params = []) {
        $this->db = $db;
        $this->params = $params;
        
        // Инициализация компонентов для работы с постблоками
        $this->postBlockManager = new \PostBlockManager($db);
        $this->postBlockModel = new \PostBlockModel($db);
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
     * Устанавливает системное имя постблока
     * Используется в действиях, где требуется идентификация конкретного блока
     * 
     * @param string $systemName Системное имя постблока
     * @return void
     */
    public function setSystemName($systemName) {
        $this->systemName = $systemName;
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
     * Основана на проверке сессионных переменных
     * 
     * @return bool true если пользователь администратор, false в противном случае
     */
    protected function checkAdminAccess() {
        return isset($_SESSION['user_id']) && isset($_SESSION['is_admin']) && $_SESSION['is_admin'];
    }
    
    /**
     * Отправляет JSON-ответ и завершает выполнение
     * Устанавливает правильный заголовок и кодирует данные в JSON с поддержкой Unicode
     * 
     * @param array $data Данные для JSON-ответа
     * @return void
     */
    protected function jsonResponse($data) {
        header('Content-Type: application/json');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
}