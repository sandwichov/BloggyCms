<?php

/**
* Пространство имен для действий аутентификации
* Содержит классы для обработки операций регистрации, входа и управления пользователями
*/
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
    *
    * Основной алгоритм:
    * 1. Проверка доступности регистрации через системные настройки
    * 2. Валидация POST-данных формы
    * 3. Проверка уникальности логина и email
    * 4. Создание учетной записи в базе данных
    * 5. Начисление достижений за регистрацию
    * 6. Автоматическая аутентификация после успешной регистрации
    *
    * @throws \Exception При ошибках валидации или сохранения данных
    */
    public function execute() {
        try {
            // Установка заголовка страницы
            $this->pageTitle = 'Регистрация';
            
            // Получение настроек авторизации из системы
            $authSettings = $this->getFrontAuthSettings();
            
            // Проверка доступности регистрации
            $enableRegisterSetting = $authSettings['enable_register'] ?? '0';
            
            // Если регистрация отключена - показываем сообщение
            if ($enableRegisterSetting === '1' || $enableRegisterSetting === 1 || $enableRegisterSetting === true) {
                $disableRegisterReason = $authSettings['disable_register_reason'] ?? 'Регистрация новых пользователей временно остановлена';
                
                $this->render('front/auth/register', [
                    'csrf_token' => $this->generateCsrfToken(),
                    'error' => $disableRegisterReason,
                    'registration_disabled' => true
                ]);
                return;
            }

            // Обработка POST-запроса (отправка формы регистрации)
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                // Проверка CSRF-токена для защиты от межсайтовой подделки запросов
                if (!$this->validateCsrfToken()) {
                    throw new \Exception('Неверный CSRF токен');
                }

                // Валидация обязательных полей
                if (empty($_POST['username'])) {
                    throw new \Exception('Имя пользователя обязательно');
                }

                if (empty($_POST['email'])) {
                    throw new \Exception('Email обязателен');
                }

                if (empty($_POST['password'])) {
                    throw new \Exception('Пароль обязателен');
                }

                // Проверка совпадения паролей
                if ($_POST['password'] !== $_POST['password_confirm']) {
                    throw new \Exception('Пароли не совпадают');
                }

                // Проверка уникальности имени пользователя
                if ($this->userModel->getByUsername($_POST['username'])) {
                    throw new \Exception('Пользователь с таким именем уже существует');
                }

                // Проверка уникальности email
                if ($this->userModel->getByEmail($_POST['email'])) {
                    throw new \Exception('Пользователь с таким email уже существует');
                }

                // Подготовка данных пользователя для сохранения
                $userData = [
                    'username' => $_POST['username'],
                    'email' => $_POST['email'],
                    'password' => password_hash($_POST['password'], PASSWORD_DEFAULT), // Хеширование пароля
                    'display_name' => $_POST['display_name'] ?? $_POST['username'], // Отображаемое имя
                    'role' => 'user', // Роль по умолчанию
                    'status' => 'active' // Статус аккаунта
                ];

                // Создание пользователя в базе данных
                $userId = $this->userModel->create($userData);

                // Получение группы пользователей по умолчанию
                $defaultGroup = $this->userModel->getDefaultGroup();
                $groupName = $defaultGroup ? $defaultGroup['name'] : '';

                // Начисление достижений за регистрацию (если система достижений доступна)
                try {
                    $achievementTriggers = new \AchievementTriggers($this->db);
                    $achievementTriggers->onUserRegistered($userId);
                } catch (\Exception $e) {
                    // Игнорируем ошибки системы достижений - они не критичны для регистрации
                }

                // Автоматическая аутентификация после успешной регистрации
                $user = $this->userModel->getById($userId);
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['display_name'] = $user['display_name'];
                $_SESSION['avatar'] = $user['avatar'];
                $_SESSION['is_admin'] = false;

                // Формирование сообщения об успехе
                $message = 'Регистрация прошла успешно! Добро пожаловать!';
                if ($groupName) {
                    $message .= ' Вы были добавлены в группу "' . $groupName . '".';
                }
                
                // Показ уведомления и перенаправление на главную
                \Notification::success($message);
                $this->redirect(BASE_URL);
                return;
            }

            // Отображение формы регистрации при GET-запросе
            $this->render('front/auth/register', [
                'csrf_token' => $this->generateCsrfToken(),
                'registration_disabled' => false
            ]);

        } catch (\Exception $e) {
            // Обработка ошибок регистрации
            \Notification::error($e->getMessage());
            
            // Повторное отображение формы с сохранением введенных данных
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
    *
    * @return array Массив с настройками:
    *               - enable_register: флаг доступности регистрации
    *               - disable_register_reason: причина отключения регистрации
    */
    private function getFrontAuthSettings() {
        return [
            'enable_register' => \SettingsHelper::get('controller_auth', 'enable_register', '0'),
            'disable_register_reason' => \SettingsHelper::get('controller_auth', 'disable_register_reason', 'Регистрация новых пользователей временно остановлена')
        ];
    }
}