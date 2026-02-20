<?php

/**
 * Контроллер для управления тегами
 * Обрабатывает запросы, связанные с отображением, созданием, редактированием,
 * удалением тегов как в публичной части, так и в административной панели
 * 
 * @package Controllers
 * @extends Controller
 */
class TagController extends Controller {
    
    /** @var TagModel Модель для работы с тегами */
    private $tagModel;
    
    /** @var PostModel Модель для работы с постами */
    private $postModel;

    /** @var array Информация о контроллере для административной панели */
    protected $controllerInfo = [
        'name' => 'Теги',
        'author' => 'BloggyCMS', 
        'version' => '1.0.0',
        'has_settings' => true,
        'description' => 'Управление тегами и облаком тегов'
    ];

    /**
     * Возвращает настройки контроллера по умолчанию
     * 
     * @return array Пустой массив настроек
     */
    public function getDefaultSettings() {
        return [];
    }
    
    /**
     * Конструктор контроллера
     * Инициализирует модели и проверяет права доступа для административных действий
     * 
     * @param object $db Подключение к базе данных
     */
    public function __construct($db) {
        parent::__construct($db);
        
        $this->tagModel = new TagModel($db);
        $this->postModel = new PostModel($db);
        
        $currentAction = $_GET['action'] ?? '';
        if (strpos($currentAction, 'admin') === 0 || $currentAction === 'search') {
            if (!$this->checkAdminAccess()) {
                if ($this->isAjaxRequest()) {
                    http_response_code(403);
                    header('Content-Type: application/json');
                    die(json_encode([
                        'success' => false,
                        'message' => 'Доступ запрещен'
                    ]));
                } else {
                    Notification::error('У вас нет прав доступа к этому разделу');
                    $this->redirect(ADMIN_URL . '/login');
                    exit;
                }
            }
        }
    }

    /**
     * Проверяет, имеет ли текущий пользователь права администратора
     * 
     * @return bool true если пользователь администратор
     */
    private function checkAdminAccess() {
        return isset($_SESSION['is_admin']) && $_SESSION['is_admin'];
    }

    /**
     * Проверяет, является ли текущий запрос AJAX-запросом
     * 
     * @return bool true если запрос AJAX
     */
    private function isAjaxRequest() {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) 
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
    
    /**
     * Отображает список тегов в публичной части
     * 
     * @return void
     */
    public function indexAction() {
        $action = new \tags\actions\Index($this->db);
        $action->setController($this);
        return $action->execute();
    }
    
    /**
     * Отображает список тегов в административной панели
     * 
     * @return void
     */
    public function adminIndexAction() {
        $action = new \tags\actions\AdminIndex($this->db);
        $action->setController($this);
        return $action->execute();
    }
    
    /**
     * Отображает форму создания нового тега
     * 
     * @return void
     */
    public function createAction() {
        $action = new \tags\actions\Create($this->db);
        $action->setController($this);
        return $action->execute();
    }
    
    /**
     * Отображает форму редактирования существующего тега
     * 
     * @param int $id ID тега
     * @return void
     */
    public function editAction($id) {
        $action = new \tags\actions\Edit($this->db, ['id' => $id]);
        $action->setController($this);
        return $action->execute();
    }
    
    /**
     * Удаляет тег
     * 
     * @param int $id ID тега
     * @return void
     */
    public function deleteAction($id) {
        $action = new \tags\actions\Delete($this->db, ['id' => $id]);
        $action->setController($this);
        return $action->execute();
    }
    
    /**
     * Отображает страницу с постами по тегу
     * 
     * @param string $slug URL-адрес тега
     * @return void
     */
    public function showAction($slug) {
        $action = new \tags\actions\Show($this->db, ['slug' => $slug]);
        $action->setController($this);
        return $action->execute();
    }
    
    /**
     * Поиск тегов (административная часть)
     * 
     * @return void
     */
    public function searchAction() {
        $action = new \tags\actions\Search($this->db);
        $action->setController($this);
        return $action->execute();
    }
    
    /**
     * Создание тега через AJAX (для автодополнения)
     * 
     * @return void
     */
    public function createAjaxAction() {
        $action = new \tags\actions\CreateAjax($this->db);
        $action->setController($this);
        return $action->execute();
    }

}