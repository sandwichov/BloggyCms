<?php
namespace auth\actions;

/**
 * Действие для входа пользователя в систему (фронтенд)
 * 
 * Реализует полный цикл аутентификации с расширенными функциями безопасности:
 * - Ограничение попыток входа с блокировкой по IP
 * - Валидация учетных данных по email и паролю
 * - Обновление статистики активности пользователя
 * - Интеграция с системой достижений (ачивок)
 * - Гибкая система редиректов после успешного входа
 *
 */
class Login extends AuthAction {
    /**
     * @var \LoginAttemptModel Модель для отслеживания попыток входа и блокировок
     * @access private
     */
    private $loginAttemptModel;

    /**
     * Конструктор действия входа пользователя
     * Инициализирует модель отслеживания попыток входа
     * 
     * @param \Database $db Объект подключения к базе данных
     * @param array $params Дополнительные параметры маршрутизации
     */
    public function __construct($db, $params = []) {
        parent::__construct($db, $params);
        $this->loginAttemptModel = new \LoginAttemptModel($db);
    }

    /**
     * Основной метод выполнения процесса входа пользователя
     * 
     */
    public function execute() {
        try {
            $this->pageTitle = 'Вход в систему';

            // Проверка блокировки входа по IP адресу
            if ($this->loginAttemptModel->isBlocked()) {
                $this->showBlockedPage();
                return;
            }

            // Загрузка конфигурационных параметров безопасности
            $authSettings = $this->getFrontAuthSettings();
            $maxAttempts = $authSettings['count_auth'] ?? 5;
            $blockTime = $authSettings['count_time'] ?? 30;
            $disableRestore = $authSettings['disable_restore'] ?? false;
            $authRedirect = $authSettings['auth_redirect'] ?? 'show_profile';

            // Получение текущей статистики попыток входа
            $attemptsInfo = $this->loginAttemptModel->getAttemptsInfo();

            // Обработка POST запроса (попытка входа пользователя)
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                // Валидация CSRF токена для защиты от межсайтовой подделки
                if (!$this->validateCsrfToken()) {
                    throw new \Exception('Неверный CSRF токен');
                }

                // Проверка обязательных полей формы
                if (empty($_POST['email'])) {
                    throw new \Exception('Email обязателен');
                }

                if (empty($_POST['password'])) {
                    throw new \Exception('Пароль обязателен');
                }

                // Увеличение счетчика попыток с проверкой блокировки
                $newAttempts = $this->loginAttemptModel->incrementAttempt(null, $maxAttempts, $blockTime);
                
                if ($newAttempts['is_blocked']) {
                    $this->showBlockedPage();
                    return;
                }

                // Аутентификация пользователя по email и паролю
                $user = $this->userModel->authenticateByEmail($_POST['email'], $_POST['password']);
                
                if (!$user) {
                    throw new \Exception('Неверный email или пароль. Попытка ' . $newAttempts['attempts'] . ' из ' . $maxAttempts);
                }

                // Проверка статуса аккаунта пользователя
                if ($user['status'] !== 'active') {
                    throw new \Exception('Ваш аккаунт заблокирован. Обратитесь к администратору.');
                }

                // Сброс счетчика попыток при успешном входе
                $this->loginAttemptModel->resetAttempts();

                // Обновление времени последнего входа пользователя
                $this->updateUserLastLogin($user['id']);

                // Установка сессионных данных пользователя
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['display_name'] = $user['display_name'];
                $_SESSION['avatar'] = $user['avatar'];
                $_SESSION['is_admin'] = $user['role'] === 'admin';

                // Активация достижений, связанных с входом в систему
                // Ошибки логируются, но не прерывают процесс входа
                try {
                    $achievementTriggers = new \AchievementTriggers($this->db);
                    $achievementTriggers->onUserLogin($user['id']);
                } catch (\Exception $e) {
                    
                }

                // Определение URL для перенаправления после успешного входа
                $redirectUrl = $this->getRedirectUrl($user, $authRedirect);
                unset($_SESSION['redirect_url']);

                // Пользовательское уведомление об успешном входе
                \Notification::success('Добро пожаловать, ' . ($user['display_name'] ?: $user['username']) . '!');
                $this->redirect($redirectUrl);
                return;
            }

            // Отображение формы входа для GET запроса
            $this->render('front/auth/login', [
                'csrf_token' => $this->generateCsrfToken(),
                'email' => $_POST['email'] ?? '',
                'currentAttempts' => $attemptsInfo['attempts'],
                'maxAttempts' => $maxAttempts,
                'disable_restore' => $disableRestore,
                'enable_register' => $authSettings['enable_register'] ?? true,
                'disable_register_reason' => $authSettings['disable_register_reason'] ?? 'Регистрация новых пользователей временно остановлена'
            ]);

        } catch (\Exception $e) {
            // Обработка исключений с сохранением UX
            \Notification::error($e->getMessage());
            
            $this->render('front/auth/login', [
                'email' => $_POST['email'] ?? '',
                'csrf_token' => $this->generateCsrfToken(),
                'currentAttempts' => $this->loginAttemptModel->getAttemptsInfo()['attempts'],
                'maxAttempts' => $maxAttempts,
                'disable_restore' => $disableRestore
            ]);
        }
    }
    
    /**
     * Обновление времени последнего входа пользователя в системе
     * 
     * Записывает текущую дату и время в поле last_login таблицы users.
     * Используется для аналитики активности пользователей и определения
     * неактивных аккаунтов.
     * 
     * @param int $userId Идентификатор пользователя
     * @return void
     * 
     * @database_field users.last_login (DATETIME)
     * @analytics Используется для отчетов по активности пользователей
     * @cleanup Может использоваться для очистки неактивных сессий
     *
     */
    private function updateUserLastLogin($userId) {
        try {
            $this->userModel->update($userId, [
                'last_login' => date('Y-m-d H:i:s')
            ]);
        } catch (\Exception $e) {
           
        }
    }
    
    /**
     * Получение настроек авторизации для фронтенда из конфигурации
     * 
     * Использует SettingsHelper для доступа к системным настройкам.
     * 
     * @return array Настройки авторизации для фронтенда
     * 
     * @see \SettingsHelper::get() Для получения значений из конфигурации
     * 
     */
    private function getFrontAuthSettings() {
        return [
            'count_auth' => \SettingsHelper::get('controller_auth', 'count_auth', 5),
            'count_time' => \SettingsHelper::get('controller_auth', 'count_time', 30),
            'disable_restore' => \SettingsHelper::get('controller_auth', 'disable_restore', false),
            'auth_redirect' => \SettingsHelper::get('controller_auth', 'auth_redirect', 'show_profile'),
            'enable_register' => \SettingsHelper::get('controller_auth', 'enable_register', true),
            'disable_register_reason' => \SettingsHelper::get('controller_auth', 'disable_register_reason', 'Регистрация новых пользователей временно остановлена')
        ];
    }
    
    /**
     * Определение URL для редиректа после успешного входа пользователя
     * 
     * Приоритет редиректов:
     * 1. Сессионный redirect_url (например, при попытке доступа к защищенной странице)
     * 2. Настройка auth_redirect из конфигурации
     * 
     * @param array $user Данные аутентифицированного пользователя
     * @param string $redirectOption Настройка редиректа из конфигурации
     * @return string URL для перенаправления
     *
     */
    private function getRedirectUrl($user, $redirectOption) {
        // Приоритет: сессионный редирект (сохраненный при попытке доступа)
        if (isset($_SESSION['redirect_url'])) {
            return $_SESSION['redirect_url'];
        }
        
        // Конфигурационный редирект по умолчанию
        switch ($redirectOption) {
            case 'show_profile':
                return BASE_URL . '/profile/' . $user['username'];
                
            case 'show_index':
            default:
                return BASE_URL;
        }
    }

    /**
     * Отображение страницы с информацией о временной блокировке входа
     * 
     * Показывает пользователю:
     * - Время окончания блокировки
     * - Оставшееся время в минутах
     * - Рекомендации по дальнейшим действиям
     */
    private function showBlockedPage() {
        $unlockTime = $this->loginAttemptModel->getUnlockTime();
        $remainingTime = $unlockTime - time();
        $remainingMinutes = ceil($remainingTime / 60);
        
        $this->render('front/auth/login_blocked', [
            'unlockTime' => $unlockTime,
            'remainingMinutes' => $remainingMinutes
        ]);
    }
}