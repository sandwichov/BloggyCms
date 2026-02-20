<?php

namespace auth\actions;

/**
 * Действие для восстановления пароля пользователя
 * 
 * Реализует безопасный процесс восстановления пароля по email:
 * 1. Валидация email пользователя
 * 2. Генерация криптографически безопасного токена
 * 3. Сохранение токена с ограниченным временем жизни
 * 4. Отправка email с уникальной ссылкой для сброса
 * 5. CSRF защита формы
 * 
 * @package auth\actions
 * @extends AuthAction
 * @version 1.1.0
 * @author BloggyCMS Security Team
 * @since 2023.11.0
 * @see ResetPassword Действие для установки нового пароля
 */
class ForgotPassword extends AuthAction {
    
    /**
     * Основной метод выполнения процесса восстановления пароля
     * 
     * Алгоритм работы:
     * 1. Проверяет доступность функции восстановления
     * 2. Обрабатывает POST запрос с email пользователя
     * 3. Генерирует и сохраняет токен восстановления
     * 4. Отправляет email с инструкциями
     * 5. Отображает форму восстановления для GET запроса
     * 
     * @return void
     * @throws \Exception При ошибках валидации или отправки email
     * 
     * @flow GET → Показать форму → POST → Обработать → Редирект
     * @security CSRF защита, временные токены, валидация email
     */
    public function execute() {
        try {
            $this->pageTitle = 'Восстановление пароля';

            // Получаем настройки системы для восстановления пароля
            $authSettings = $this->getFrontAuthSettings();
            $disableRestore = $authSettings['disable_restore'] ?? false;
            
            // Проверяем, разрешено ли восстановление пароля администратором
            if ($disableRestore) {
                \Notification::error('Восстановление пароля временно отключено администратором');
                $this->redirect(BASE_URL . '/login');
                return;
            }

            // Обработка отправки формы (POST запрос)
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                // Валидация CSRF токена для предотвращения подделки запросов
                if (!$this->validateCsrfToken()) {
                    throw new \Exception('Неверный CSRF токен');
                }

                // Проверка обязательного поля email
                if (empty($_POST['email'])) {
                    throw new \Exception('Email обязателен');
                }

                $email = $_POST['email'];
                
                // Проверяем существование пользователя с указанным email
                $user = $this->userModel->getByEmail($email);
                if (!$user) {
                    // Не раскрываем информацию о существовании пользователя
                    // для предотвращения перебора email адресов
                    throw new \Exception('Пользователь с таким email не найден');
                }

                // Генерируем криптографически безопасный токен восстановления
                $resetToken = bin2hex(random_bytes(32)); // 64 шестнадцатеричных символа
                $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour')); // Токен действителен 1 час

                // Сохраняем токен в базе данных для последующей верификации
                $this->saveResetToken($user['id'], $resetToken, $expiresAt);

                // Отправляем email с безопасной ссылкой для сброса пароля
                $this->sendResetEmail($user['email'], $resetToken, $user['username']);

                // Уведомляем пользователя об успешной отправке инструкций
                \Notification::success('На вашу почту отправлена ссылка для восстановления пароля. Ссылка действительна 1 час.');
                $this->redirect(BASE_URL . '/login');
                return;
            }

            // Отображение формы восстановления пароля (GET запрос)
            $this->render('front/auth/forgot_password', [
                'csrf_token' => $this->generateCsrfToken()
            ]);

        } catch (\Exception $e) {
            // Обработка исключений с сохранением введенного email для UX
            \Notification::error($e->getMessage());
            
            $this->render('front/auth/forgot_password', [
                'csrf_token' => $this->generateCsrfToken(),
                'email' => $_POST['email'] ?? ''
            ]);
        }
    }

    /**
     * Получение настроек авторизации для фронтенда из базы данных
     * 
     * Использует SettingsHelper для доступа к системным настройкам.
     * Возвращает массив с ключами:
     * - disable_restore: Флаг отключения восстановления пароля (bool)
     * 
     * @return array Настройки авторизации для фронтенда
     * 
     * @see \SettingsHelper::get() Для получения значений из конфигурации
     * 
     * @example
     * // Возвращаемый массив
     * [
     *     'disable_restore' => false // Восстановление пароля разрешено
     * ]
     */
    private function getFrontAuthSettings() {
        return [
            'disable_restore' => \SettingsHelper::get('controller_auth', 'disable_restore', false)
        ];
    }

    /**
     * Сохранение токена восстановления пароля в базе данных
     * 
     * Создает запись в таблице password_resets со следующей структурой:
     * - user_id: ID пользователя (внешний ключ)
     * - token: Криптографически безопасный токен (64 hex символа)
     * - expires_at: Время истечения срока действия токена
     * - created_at: Автоматически устанавливается в текущее время
     * 
     * @param int $userId Идентификатор пользователя
     * @param string $token Токен восстановления (hex строка)
     * @param string $expiresAt Время истечения срока действия (формат MySQL DATETIME)
     * @return void
     * @throws \PDOException При ошибках выполнения SQL запроса
     * 
     */
    private function saveResetToken($userId, $token, $expiresAt) {
        $this->db->query(
            "INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?)",
            [$userId, $token, $expiresAt]
        );
    }

    /**
     * Отправка email с инструкциями по восстановлению пароля
     * 
     * Использует Email сервис для отправки персонализированного письма,
     * содержащего безопасную ссылку вида:
     * {BASE_URL}/reset-password?token={64_char_hex_token}
     * 
     * @param string $email Email адрес получателя
     * @param string $token Токен восстановления для формирования ссылки
     * @param string $username Имя пользователя для персонализации письма
     * @return bool true при успешной отправке, false при ошибке
     * @throws \Exception При ошибках отправки email
     * 
     * @see \Email::sendPasswordReset() Реализация отправки email
     * 
     * @email_template password_reset
     * @email_subject Восстановление пароля на {SITE_NAME}
     * @email_content Включает: приветствие, инструкции, ссылку, срок действия
     * @email_security Безопасная ссылка, без паролей в тексте, HTML+Plain текст
     */
    private function sendResetEmail($email, $token, $username) {
        return \Email::sendPasswordReset($email, $token, $username);
    }
    
}