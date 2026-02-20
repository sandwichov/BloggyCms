<?php

/**
* Контроллер аутентификации и регистрации
* Управляет всеми действиями, связанными с пользовательскими сессиями:
* - Вход в систему
* - Регистрация новых пользователей
* - Выход из системы
* - Восстановление пароля
* - Административный вход
*/
class AuthController extends Controller {
    /**
    * @var UserModel Модель для работы с пользователями
    */
    private $userModel;

    /**
    * @var array Метаинформация о контроллере
    * Содержит описание функциональности контроллера
    */
    protected $controllerInfo = [
        'name' => 'Авторизация / Регистрация',
        'author' => 'BloggyCMS', 
        'version' => '1.0.0',
        'has_settings' => true,
        'description' => 'Настройка авторизации и регистрации'
    ];
    
    /**
    * Конструктор контроллера аутентификации
    * Инициализирует модель пользователя для работы с данными аккаунтов
    *
    * @param Database $db Объект подключения к базе данных
    */
    public function __construct($db) {
        parent::__construct($db);
        $this->userModel = new UserModel($db);
    }
    
    /**
    * Действие входа пользователя в систему
    * Обрабатывает аутентификацию по логину и паролю
    *
    * @return mixed Результат выполнения действия авторизации
    */
    public function loginAction() {
        $action = new \auth\actions\Login($this->db);
        $action->setController($this);
        return $action->execute();
    }
    
    /**
    * Действие регистрации нового пользователя
    * Обрабатывает создание нового аккаунта с валидацией данных
    *
    * @return mixed Результат выполнения действия регистрации
    */
    public function registerAction() {
        $action = new \auth\actions\Register($this->db);
        $action->setController($this);
        return $action->execute();
    }
    
    /**
    * Действие выхода пользователя из системы
    * Завершает текущую сессию и очищает данные аутентификации
    *
    * @return mixed Результат выполнения действия выхода
    */
    public function logoutAction() {
        $action = new \auth\actions\Logout($this->db);
        $action->setController($this);
        return $action->execute();
    }

    /**
    * Действие входа в административную панель
    * Специализированный вход с проверкой прав администратора
    *
    * @return mixed Результат выполнения действия административного входа
    */
    public function adminLoginAction() {
        $action = new \auth\actions\AdminLogin($this->db);
        $action->setController($this);
        return $action->execute();
    }

    /**
    * Действие восстановления пароля
    * Инициирует процесс сброса пароля через email
    *
    * @return mixed Результат выполнения действия восстановления пароля
    */
    public function forgotPasswordAction() {
        $action = new \auth\actions\ForgotPassword($this->db);
        $action->setController($this);
        return $action->execute();
    }
    
    /**
    * Действие сброса пароля
    * Обрабатывает установку нового пароля по токену сброса
    *
    * @return mixed Результат выполнения действия сброса пароля
    */
    public function resetPasswordAction() {
        $action = new \auth\actions\ResetPassword($this->db);
        $action->setController($this);
        return $action->execute();
    }
}