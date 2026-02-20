<?php

/**
 * Контроллер управления достижениями пользователей в административной панели
 * Обрабатывает запросы, связанные с созданием, редактированием, удалением
 * и назначением достижений (ачивок) пользователям
 * 
 * @package Controllers
 * @extends Controller
 */
class AdminUserAchievementController extends Controller {
    
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
     * Отображает список всех достижений в административной панели
     * 
     * @return void
     */
    public function indexAction() {
        $action = new \users\actions\achievements\AdminAchievementIndex($this->db);
        $action->setController($this);
        return $action->execute();
    }
    
    /**
     * Отображает форму создания нового достижения
     * 
     * @return void
     */
    public function createAction() {
        $action = new \users\actions\achievements\AdminAchievementCreate($this->db);
        $action->setController($this);
        return $action->execute();
    }
    
    /**
     * Отображает форму редактирования существующего достижения
     * 
     * @param int $id ID достижения
     * @return void
     */
    public function editAction($id) {
        $action = new \users\actions\achievements\AdminAchievementEdit($this->db, ['id' => $id]);
        $action->setController($this);
        return $action->execute();
    }
    
    /**
     * Удаляет достижение
     * 
     * @param int $id ID достижения
     * @return void
     */
    public function deleteAction($id) {
        $action = new \users\actions\achievements\AdminAchievementDelete($this->db, ['id' => $id]);
        $action->setController($this);
        return $action->execute();
    }
    
    /**
     * Переключает статус активности достижения (вкл/выкл)
     * 
     * @param int $id ID достижения
     * @return void
     */
    public function toggleAction($id) {
        $action = new \users\actions\achievements\AdminAchievementToggle($this->db, ['id' => $id]);
        $action->setController($this);
        return $action->execute();
    }
    
    /**
     * Отображает форму назначения достижений пользователю
     * 
     * @param int $userId ID пользователя
     * @return void
     */
    public function assignAction($userId) {
        $action = new \users\actions\achievements\AdminAchievementAssign($this->db, ['user_id' => $userId]);
        $action->setController($this);
        return $action->execute();
    }
    
    /**
     * Отменяет назначение достижения пользователю
     * 
     * @param int $userId ID пользователя
     * @param int $achievementId ID достижения
     * @return void
     */
    public function unassignAction($userId, $achievementId) {
        $action = new \users\actions\achievements\AdminAchievementUnassign($this->db, [
            'user_id' => $userId,
            'achievement_id' => $achievementId
        ]);
        $action->setController($this);
        return $action->execute();
    }

    /**
     * Быстрое назначение достижения пользователю (AJAX)
     * 
     * @param int $userId ID пользователя
     * @return void
     */
    public function quickAssignAction($userId) {
        $action = new \users\actions\achievements\AdminQuickAssignAchievement($this->db, ['user_id' => $userId]);
        $action->setController($this);
        return $action->execute();
    }

}