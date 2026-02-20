<?php

/**
 * Контроллер управления группами пользователей в административной панели
 * Обрабатывает запросы, связанные с созданием, редактированием, удалением групп,
 * управлением правами доступа и назначением групп пользователям
 * 
 * @package Controllers
 * @extends Controller
 */
class AdminUserGroupController extends Controller {
    
    /** @var UserModel Модель для работы с пользователями */
    private $userModel;
    
    /**
     * Конструктор контроллера
     * Инициализирует модель пользователей и проверяет права администратора
     * 
     * @param object $db Подключение к базе данных
     */
    public function __construct($db) {
        parent::__construct($db);
        $this->userModel = new UserModel($db);
        
        // Проверка прав доступа администратора
        if (!$this->checkAdminAccess()) {
            Notification::error('У вас нет прав доступа к этому разделу');
            $this->redirect(ADMIN_URL . '/login');
            exit;
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
     * Отображает список всех групп пользователей
     * 
     * @return void
     */
    public function indexAction() {
        $action = new \users\actions\groups\AdminGroupIndex($this->db);
        $action->setController($this);
        return $action->execute();
    }

    /**
     * Отображает форму создания новой группы
     * 
     * @return void
     */
    public function createAction() {
        $action = new \users\actions\groups\AdminGroupCreate($this->db);
        $action->setController($this);
        return $action->execute();
    }

    /**
     * Отображает форму редактирования существующей группы
     * 
     * @param int $id ID группы
     * @return void
     */
    public function editAction($id) {
        $action = new \users\actions\groups\AdminGroupEdit($this->db, ['id' => $id]);
        $action->setController($this);
        return $action->execute();
    }

    /**
     * Удаляет группу пользователей
     * 
     * @param int $id ID группы
     * @return void
     */
    public function deleteAction($id) {
        $action = new \users\actions\groups\AdminGroupDelete($this->db, ['id' => $id]);
        $action->setController($this);
        return $action->execute();
    }

    /**
     * Управление правами доступа для группы
     * 
     * @param int $id ID группы
     * @return void
     */
    public function permissionsAction($id) {
        $action = new \users\actions\groups\AdminGroupPermissions($this->db, ['id' => $id]);
        $action->setController($this);
        return $action->execute();
    }

    /**
     * Управление группами конкретного пользователя
     * 
     * @param int $userId ID пользователя
     * @return void
     */
    public function manageUserGroupsAction($userId) {
        $action = new \users\actions\groups\AdminManageUserGroups($this->db, ['id' => $userId]);
        $action->setController($this);
        return $action->execute();
    }
}