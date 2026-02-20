<?php

/**
 * Контроллер для управления пользователями в публичной части сайта
 * Отображает профили пользователей и страницы достижений
 * 
 * @package Controllers
 * @extends Controller
 */
class UserController extends Controller {
    
    /** @var UserModel Модель для работы с пользователями */
    private $userModel;
    
    /** @var FieldModel Модель для работы с пользовательскими полями */
    private $fieldModel;

    /** @var array Информация о контроллере для административной панели */
    protected $controllerInfo = [
        'name' => 'Пользователи',
        'author' => 'BloggyCMS', 
        'version' => '1.0.0',
        'has_settings' => true,
        'description' => 'Управление пользователями блога'
    ];

    /**
     * Конструктор контроллера
     * Инициализирует модели пользователей и пользовательских полей
     * 
     * @param object $db Подключение к базе данных
     */
    public function __construct($db) {
        parent::__construct($db);
        $this->userModel = new UserModel($db);
        $this->fieldModel = new FieldModel($db);
    }

    /**
     * Отображает список пользователей (публичная часть)
     * 
     * @return void
     */
    public function indexAction() {
        $action = new \users\actions\FrontIndex($this->db);
        $action->setController($this);
        return $action->execute();
    }

    /**
     * Отображает публичный профиль пользователя по имени пользователя
     * 
     * @param string $username Имя пользователя
     * @return void
     */
    public function showAction($username) {
        $action = new \users\actions\FrontShow($this->db, ['username' => $username]);
        $action->setController($this);
        return $action->execute();
    }
    
    /**
     * Отображает страницу со списком всех достижений системы
     * 
     * @return void
     */
    public function achievementsAction() {
        $action = new \users\actions\achievements\FrontIndex($this->db);
        $action->setController($this);
        return $action->execute();
    }
    
    /**
     * Отображает страницу конкретного достижения с информацией о нем
     * и пользователях, которые его получили
     * 
     * @param int $id ID достижения
     * @return void
     */
    public function achievementAction($id) {
        $action = new \users\actions\achievements\FrontShow($this->db, ['id' => $id]);
        $action->setController($this);
        return $action->execute();
    }
}