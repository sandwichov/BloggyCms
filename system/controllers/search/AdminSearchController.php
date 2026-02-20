<?php

/**
 * Контроллер управления историей поиска в административной панели
 * Обрабатывает запросы, связанные с просмотром и управлением историей поиска пользователей
 * 
 * @package Controllers
 * @extends Controller
 */
class AdminSearchController extends Controller {
    
    /** @var SearchModel Модель для работы с поиском и историей */
    private $searchModel;
    
    /**
     * Конструктор контроллера
     * Инициализирует модель поиска и проверяет авторизацию пользователя
     * 
     * @param object $db Подключение к базе данных
     */
    public function __construct($db) {
        parent::__construct($db);
        $this->searchModel = new SearchModel($db);
        
        // Проверка авторизации пользователя
        if (!isset($_SESSION['user_id'])) {
            Notification::error('Пожалуйста, авторизуйтесь для доступа к истории поиска');
            $this->redirect(ADMIN_URL . '/login');
            exit;
        }
    }
    
    /**
     * Отображает список истории поиска в административной панели
     * 
     * @return void
     */
    public function adminIndexAction() {
        $action = new \search\actions\AdminIndex($this->db);
        $action->setController($this);
        return $action->execute();
    }
    
    /**
     * Удаляет конкретную запись из истории поиска по ID
     * 
     * @param int $id ID записи в истории поиска
     * @return void
     */
    public function deleteAction($id) {
        $action = new \search\actions\AdminDelete($this->db, ['id' => $id]);
        $action->setController($this);
        return $action->execute();
    }
    
    /**
     * Очищает всю историю поиска текущего пользователя
     * 
     * @return void
     */
    public function clearAction() {
        $action = new \search\actions\AdminClear($this->db);
        $action->setController($this);
        return $action->execute();
    }
}