<?php

namespace auth\actions;

/**
 * Действие для авторизации администратора с расширенной безопасностью
 * 
 * Реализует многоуровневую защиту входа в административную панель:
 * - Ограничение попыток входа с блокировкой по IP
 * - Контрольные вопросы для дополнительной верификации
 * - Проверка IP  адреса при нестандартном входе
 * - CSRF защита через родительский класс
 * 
 * @package auth\actions
 * @extends AuthAction
 * @version 1.2.0
 * @author BloggyCMS Security Team
 * @since 2023.11.0
 */
class AdminLogin extends AuthAction {

    /**
     * @var \LoginAttemptModel Модель для отслеживания попыток входа
     * @access private
     */
    private $loginAttemptModel;

    /**
     * Конструктор действия входа администратора
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
     * Основной метод выполнения действия входа администратора
     * 
     * Реализует следующий алгоритм:
     * 1. Проверка блокировки по IP
     * 2. Загрузка настроек безопасности
     * 3. Обработка POST запроса (если есть)
     * 4. Отображение формы входа с учетом текущего состояния
     * 
     * @return void
     * @throws \Exception При критических ошибках безопасности
     * 
     * @see getAuthSettings() Для получения настроек безопасности
     * @see processAdminLogin() Для обработки данных авторизации
     */
    public function execute() {
        // Проверка глобальной блокировки входа
        if ($this->loginAttemptModel->isBlocked()) {
            $this->showBlockedPage();
            return;
        }

        // Загрузка настроек безопасности из конфигурации
        $authSettings = $this->getAuthSettings();
        $showQA = $authSettings['show_qa'] ?? false;
        $qaParam = $authSettings['qa_param'] ?? 'opt2';
        $wordsArray = $authSettings['words_array'] ?? [];
        $maxAttempts = $authSettings['count_auth'] ?? 3;
        $blockTime = $authSettings['count_time'] ?? 20;

        // Нормализация булевых значений
        if ($showQA === '1' || $showQA === 1) {
            $showQA = true;
        }

        // Получение текущей статистики попыток
        $attemptsInfo = $this->loginAttemptModel->getAttemptsInfo();

        // Обработка POST запроса (попытка входа)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'] ?? '';
            $password = $_POST['password'] ?? '';
            
            // Увеличение счетчика попыток с проверкой блокировки
            $newAttempts = $this->loginAttemptModel->incrementAttempt(null, $maxAttempts, $blockTime);
            
            if ($newAttempts['is_blocked']) {
                $this->showBlockedPage();
                return;
            }
            
            // Определение необходимости показа контрольного вопроса
            $shouldShowQA = $this->shouldShowQuestions($showQA, $qaParam, $username);
            
            if ($shouldShowQA) {
                // Валидация контрольного вопроса
                if (empty($_POST['qa_answer'])) {
                    $randomQA = $this->getRandomQuestion($wordsArray);
                    $this->render('admin/login', [
                        'username' => $username,
                        'password' => $password,
                        'showQuestion' => true,
                        'question' => $randomQA['question'],
                        'expectedAnswer' => $randomQA['answer'],
                        'authSettings' => $authSettings,
                        'currentAttempts' => $newAttempts['attempts'],
                        'maxAttempts' => $maxAttempts,
                        'error' => 'Пожалуйста, ответьте на контрольный вопрос'
                    ]);
                    return;
                } else {
                    $userAnswer = $_POST['qa_answer'] ?? '';
                    $expectedAnswer = $_POST['expected_answer'] ?? '';
                    
                    // Сравнение ответов без учета регистра и пробелов
                    if (strtolower(trim($userAnswer)) !== strtolower(trim($expectedAnswer))) {
                        $randomQA = $this->getRandomQuestion($wordsArray);
                        $this->render('admin/login', [
                            'username' => $username,
                            'password' => $password,
                            'showQuestion' => true,
                            'question' => $randomQA['question'],
                            'expectedAnswer' => $randomQA['answer'],
                            'authSettings' => $authSettings,
                            'currentAttempts' => $newAttempts['attempts'],
                            'maxAttempts' => $maxAttempts,
                            'error' => 'Неверный ответ на контрольный вопрос'
                        ]);
                        return;
                    }
                }
            }
            
            // Обработка основных учетных данных
            $this->processAdminLogin($username, $password, $authSettings, $newAttempts['attempts']);
            return;
        }

        // Отображение формы входа для GET запроса
        $shouldShowQA = $this->shouldShowQuestions($showQA, $qaParam, '');
        
        if ($shouldShowQA) {
            $randomQA = $this->getRandomQuestion($wordsArray);
            $this->render('admin/login', [
                'showQuestion' => true,
                'question' => $randomQA['question'],
                'expectedAnswer' => $randomQA['answer'],
                'authSettings' => $authSettings,
                'currentAttempts' => $attemptsInfo['attempts'],
                'maxAttempts' => $maxAttempts
            ]);
        } else {
            $this->render('admin/login', [
                'showQuestion' => false,
                'authSettings' => $authSettings,
                'currentAttempts' => $attemptsInfo['attempts'],
                'maxAttempts' => $maxAttempts
            ]);
        }
    }

    /**
     * Получение настроек безопасности авторизации из базы данных
     * 
     * Использует SettingsHelper для доступа к конфигурации системы.
     * Возвращает массив с ключами:
     * - show_qa: Показывать ли контрольные вопросы (bool)
     * - qa_param: Стратегия показа вопросов (string)
     * - words_array: Массив вопросов и ответов (array)
     * - count_auth: Максимальное количество попыток (int)
     * - count_time: Время блокировки в минутах (int)
     * 
     * @return array Настройки безопасности авторизации
     * 
     * @see \SettingsHelper::get() Для получения значений из конфигурации
     */
    private function getAuthSettings() {
        return [
            'show_qa' => \SettingsHelper::get('controller_auth', 'show_qa', false),
            'qa_param' => \SettingsHelper::get('controller_auth', 'qa_param', 'opt2'),
            'words_array' => \SettingsHelper::get('controller_auth', 'words_array', []),
            'count_auth' => \SettingsHelper::get('controller_auth', 'count_auth', 3),
            'count_time' => \SettingsHelper::get('controller_auth', 'count_time', 20)
        ];
    }

    /**
     * Проверка наличия активной блокировки страницы входа
     * 
     * Использует временную метку в сессии для определения
     * временного окна блокировки после превышения лимита попыток
     * 
     * @return bool true если вход заблокирован, false если доступен
     * @deprecated Используйте $this->loginAttemptModel->isBlocked()
     */
    private function isLoginBlocked() {
        $blockedUntil = $_SESSION['login_blocked_until'] ?? 0;
        return time() < $blockedUntil;
    }

    /**
     * Получение времени разблокировки страницы входа
     * 
     * @return int Unix timestamp времени разблокировки
     * @deprecated Используйте $this->loginAttemptModel->getUnlockTime()
     */
    private function getUnlockTime() {
        $blockedUntil = $_SESSION['login_blocked_until'] ?? 0;
        return $blockedUntil;
    }

    /**
     * Установка блокировки страницы входа
     * 
     * @param int $blockTimeMinutes Время блокировки в минутах
     * @deprecated Используйте $this->loginAttemptModel->block()
     */
    private function blockLoginPage($blockTimeMinutes) {
        $blockTimeSeconds = $blockTimeMinutes * 60;
        $_SESSION['login_blocked_until'] = time() + $blockTimeSeconds;
        $_SESSION['login_attempts'] = 0;
    }

    /**
     * Увеличение счетчика неудачных попыток входа
     * 
     * @deprecated Используйте $this->loginAttemptModel->incrementAttempt()
     */
    private function incrementLoginAttempts() {
        $_SESSION['login_attempts'] = ($_SESSION['login_attempts'] ?? 0) + 1;
    }

    /**
     * Получение текущего количества неудачных попыток
     * 
     * @return int Количество неудачных попыток входа
     * @deprecated Используйте $this->loginAttemptModel->getAttemptsCount()
     */
    private function getLoginAttemptsCount() {
        return $_SESSION['login_attempts'] ?? 0;
    }

    /**
     * Сброс счетчика неудачных попыток входа
     * 
     * @deprecated Используйте $this->loginAttemptModel->resetAttempts()
     */
    private function resetLoginAttempts() {
        unset($_SESSION['login_attempts']);
    }

    /**
     * Отображение страницы с информацией о блокировке входа
     * 
     * Показывает пользователю время оставшейся блокировки
     * и рекомендации по дальнейшим действиям
     * 
     * @return void
     */
    private function showBlockedPage() {
        $unlockTime = $this->loginAttemptModel->getUnlockTime();
        $remainingTime = $unlockTime - time();
        $remainingMinutes = ceil($remainingTime / 60);
        
        $this->render('admin/auth/login_blocked', [
            'unlockTime' => $unlockTime,
            'remainingMinutes' => $remainingMinutes
        ]);
    }

    /**
     * Безопасное получение случайного вопроса из массива
     * 
     * Проверяет валидность массива вопросов и корректность
     * структуры каждого элемента перед выбором случайного
     * 
     * @param array $wordsArray Массив вопросов из настроек
     * @return array Ассоциативный массив с ключами 'question' и 'answer'
     * 
     * @example
     * // Входной массив
     * $wordsArray = [
     *     ['question' => 'Столица России?', 'answer' => 'Москва'],
     *     ['question' => '2+2?', 'answer' => '4']
     * ];
     * 
     * // Выходной массив
     * ['question' => '2+2?', 'answer' => '4']
     */
    private function getRandomQuestion($wordsArray) {
        if (empty($wordsArray) || !is_array($wordsArray)) {
            return ['question' => 'Нет доступных вопросов', 'answer' => 'none'];
        }
        
        // Фильтрация некорректных вопросов
        $validQuestions = array_filter($wordsArray, function($qa) {
            return !empty($qa['question']) && !empty($qa['answer']);
        });
        
        if (empty($validQuestions)) {
            return ['question' => 'Нет доступных вопросов', 'answer' => 'none'];
        }
        
        $randomIndex = array_rand($validQuestions);
        return $validQuestions[$randomIndex];
    }

    /**
     * Определение необходимости показа контрольных вопросов
     * 
     * Анализирует текущий контекст входа и настройки безопасности
     * для принятия решения о необходимости дополнительной проверки
     * 
     * @param bool $showQA Глобальная настройка показа вопросов
     * @param string $qaParam Стратегия показа:
     *                       - 'opt1': После неудачной попытки
     *                       - 'opt2': При смене IP адреса
     *                       - 'opt3': Всегда
     * @param string $username Имя пользователя для проверки IP истории
     * @return bool true если нужно показать контрольный вопрос
     */
    private function shouldShowQuestions($showQA, $qaParam, $username) {
        if (!$showQA) return false;
        
        switch ($qaParam) {
            case 'opt1': // После неудачной попытки
                return isset($_SESSION['admin_login_attempt_failed']) && $_SESSION['admin_login_attempt_failed'];
                
            case 'opt2': // При смене IP адреса
                $lastIP = $this->getLastAdminIP($username);
                $currentIP = $_SERVER['REMOTE_ADDR'];
                return $lastIP && $lastIP !== $currentIP;
                
            case 'opt3': // Всегда показывать вопросы
                return true;
                
            default:
                return false;
        }
    }

    /**
     * Получение последнего известного IP адреса администратора
     * 
     * Извлекает из базы данных IP адрес, с которого пользователь
     * последний раз успешно заходил в административную панель
     * 
     * @param string $username Имя пользователя для поиска
     * @return string|null IP адрес или null если не найден
     */
    private function getLastAdminIP($username) {
        if (empty($username)) return null;
        
        $user = $this->userModel->getByUsername($username);
        return $user['last_admin_ip'] ?? null;
    }

    /**
     * Обновление последнего IP адреса администратора
     * 
     * Сохраняет текущий IP адрес пользователя в базе данных
     * при успешном входе в административную панель
     * 
     * @param int $userId Идентификатор пользователя
     * @param string $ip IP адрес для сохранения
     * @return void
     */
    private function updateLastAdminIP($userId, $ip) {
        $this->db->query(
            "UPDATE users SET last_admin_ip = ? WHERE id = ?",
            [$ip, $userId]
        );
    }

    /**
     * Обработка основных учетных данных администратора
     * 
     * Выполняет аутентификацию, проверяет права администратора,
     * устанавливает сессию и выполняет перенаправление
     * 
     * @param string $username Имя пользователя
     * @param string $password Пароль
     * @param array $authSettings Настройки безопасности
     * @param int $currentAttempts Текущее количество попыток
     * @return void
     */
    private function processAdminLogin($username, $password, $authSettings, $currentAttempts) {
        $user = $this->userModel->authenticate($username, $password);
        
        // Проверка успешности аутентификации и прав администратора
        if ($user && ($user['is_admin'] || $user['role'] === 'admin')) {
            // Установка сессии администратора
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['is_admin'] = true;
            
            // Обновление IP истории
            $this->updateLastAdminIP($user['id'], $_SERVER['REMOTE_ADDR']);
            
            // Сброс флагов неудачных попыток
            unset($_SESSION['admin_login_attempt_failed']);
            $this->loginAttemptModel->resetAttempts();
            
            // Перенаправление на главную админ-панели
            $this->redirect(ADMIN_URL . '/');
        } else {
            // Неудачная попытка входа
            $_SESSION['admin_login_attempt_failed'] = true;
            
            $showQA = $authSettings['show_qa'] ?? false;
            $qaParam = $authSettings['qa_param'] ?? 'opt2';
            $wordsArray = $authSettings['words_array'] ?? [];
            
            $shouldShowQA = $this->shouldShowQuestions($showQA, $qaParam, $username);
            $randomQA = $this->getRandomQuestion($wordsArray);
            
            // Повторный показ формы с ошибкой
            $this->render('admin/login', [
                'error' => 'Неверный логин, пароль или недостаточно прав для доступа к админ-панели',
                'showQuestion' => $shouldShowQA,
                'question' => $shouldShowQA ? $randomQA['question'] : '',
                'expectedAnswer' => $shouldShowQA ? $randomQA['answer'] : '',
                'authSettings' => $authSettings,
                'currentAttempts' => $currentAttempts,
                'maxAttempts' => $authSettings['count_auth'] ?? 3
            ]);
        }
    }
}