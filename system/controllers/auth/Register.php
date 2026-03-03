<?php

namespace auth\actions;

/**
* Класс действия "Регистрация"
* Обрабатывает процесс создания новых учетных записей пользователей
* Включает валидацию данных, проверку уникальности и интеграцию с системой достижений
*/
class Register extends AuthAction {
    
    /**
    * Выполнение действия регистрации пользователя
    * Управляет полным процессом регистрации от отображения формы до создания учетной записи
    */
    public function execute() {
        try {
            $this->addBreadcrumb('Главная', BASE_URL);
            $this->addBreadcrumb('Регистрация');
            $this->pageTitle = 'Регистрация';
            
            $authSettings = $this->getFrontAuthSettings();
            
            $enableRegisterSetting = $authSettings['enable_register'] ?? '0';
            
            if ($enableRegisterSetting === '1' || $enableRegisterSetting === 1 || $enableRegisterSetting === true) {
                $disableRegisterReason = $authSettings['disable_register_reason'] ?? 'Регистрация новых пользователей временно остановлена';
                
                $this->render('front/auth/register', [
                    'csrf_token' => $this->generateCsrfToken(),
                    'error' => $disableRegisterReason,
                    'registration_disabled' => true
                ]);
                return;
            }

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {

                if (!$this->validateCsrfToken()) {
                    throw new \Exception('Неверный CSRF токен');
                }

                if (empty($_POST['username'])) {
                    throw new \Exception('Имя пользователя обязательно');
                }

                if (empty($_POST['email'])) {
                    throw new \Exception('Email обязателен');
                }

                if (empty($_POST['password'])) {
                    throw new \Exception('Пароль обязателен');
                }

                if ($_POST['password'] !== $_POST['password_confirm']) {
                    throw new \Exception('Пароли не совпадают');
                }

                if ($this->userModel->getByUsername($_POST['username'])) {
                    throw new \Exception('Пользователь с таким именем уже существует');
                }

                if ($this->userModel->getByEmail($_POST['email'])) {
                    throw new \Exception('Пользователь с таким email уже существует');
                }

                $userData = [
                    'username' => $_POST['username'],
                    'email' => $_POST['email'],
                    'password' => password_hash($_POST['password'], PASSWORD_DEFAULT),
                    'display_name' => $_POST['display_name'] ?? $_POST['username'],
                    'role' => 'user',
                    'status' => 'active'
                ];

                $userId = $this->userModel->create($userData);
                $defaultGroup = $this->userModel->getDefaultGroup();
                $groupName = $defaultGroup ? $defaultGroup['name'] : '';

                try {
                    $achievementTriggers = new \AchievementTriggers($this->db);
                    $achievementTriggers->onUserRegistered($userId);
                } catch (\Exception $e) {}

                $user = $this->userModel->getById($userId);
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['display_name'] = $user['display_name'];
                $_SESSION['avatar'] = $user['avatar'];
                $_SESSION['is_admin'] = false;

                $message = 'Регистрация прошла успешно! Добро пожаловать!';
                if ($groupName) {
                    $message .= ' Вы были добавлены в группу "' . $groupName . '".';
                }
                
                \Notification::success($message);
                $this->redirect(BASE_URL);
                return;
            }

            $this->render('front/auth/register', [
                'csrf_token' => $this->generateCsrfToken(),
                'registration_disabled' => false
            ]);

        } catch (\Exception $e) {
            \Notification::error($e->getMessage());
            
            $this->render('front/auth/register', [
                'username' => $_POST['username'] ?? '',
                'email' => $_POST['email'] ?? '',
                'display_name' => $_POST['display_name'] ?? '',
                'csrf_token' => $this->generateCsrfToken(),
                'registration_disabled' => false
            ]);
        }
    }

    /**
    * Получение настроек авторизации для фронтенда
    * Извлекает параметры регистрации из системы настроек
    */
    private function getFrontAuthSettings() {
        return [
            'enable_register' => \SettingsHelper::get('controller_auth', 'enable_register', '0'),
            'disable_register_reason' => \SettingsHelper::get('controller_auth', 'disable_register_reason', 'Регистрация новых пользователей временно остановлена')
        ];
    }
}