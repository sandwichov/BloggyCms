<?php

/**
 * Контроллер управления контроллерами системы
 * Предоставляет административный интерфейс для просмотра всех контроллеров, зарегистрированных в системе
 * Используется для мониторинга и управления расширениями функциональности CMS
 * 
 * @package controllers
 * @extends Controller
 */
class ControllersController extends Controller {
    
    /**
     * @var array Информация о контроллере для админ-панели
     */
    protected $controllerInfo = [
        'name' => 'Управление контроллерами',
        'author' => 'BloggyCMS', 
        'version' => '1.0.0',
        'has_settings' => false,
        'description' => 'Просмотр всех контроллеров системы'
    ];
    
    /**
     * Конструктор контроллера управления контроллерами
     * Инициализирует контроллер и проверяет права доступа для административных действий
     *
     * @param Database $db Объект подключения к базе данных
     */
    public function __construct($db) {
        parent::__construct($db);
        
        // Проверка прав доступа для админ-методов
        $currentAction = $_GET['action'] ?? '';
        if (strpos($currentAction, 'admin') === 0 || $currentAction === '') {
            if (!$this->checkAdminAccess()) {
                if ($this->isAjaxRequest()) {
                    http_response_code(403);
                    header('Content-Type: application/json');
                    die(json_encode([
                        'success' => false,
                        'message' => 'Доступ запрещен'
                    ]));
                } else {
                    \Notification::error('У вас нет прав доступа к этому разделу');
                    $this->redirect(\ADMIN_URL . '/login');
                    exit;
                }
            }
        }
    }

    /**
     * Проверка прав администратора
     * Проверяет наличие административных прав в сессии пользователя
     *
     * @return bool true если пользователь имеет административные права
     */
    private function checkAdminAccess() {
        return isset($_SESSION['is_admin']) && $_SESSION['is_admin'];
    }

    /**
     * Проверка типа запроса
     * Определяет, является ли текущий запрос AJAX-запросом
     *
     * @return bool true если запрос является AJAX-запросом
     */
    private function isAjaxRequest() {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) 
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
    
    /**
     * Действие: Отображение списка контроллеров в админ-панели
     * Основная страница управления, показывает все доступные контроллеры системы
     * 
     * @return mixed
     */
    public function adminIndexAction() {
        $this->pageTitle = 'Управление контроллерами';
        
        // Создание и выполнение соответствующего действия
        $action = new \controllers\actions\AdminIndex($this->db);
        $action->setController($this);
        return $action->execute();
    }
}