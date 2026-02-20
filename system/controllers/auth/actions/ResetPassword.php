<?php

namespace auth\actions;

/**
* Класс действия "Сброс пароля"
* Обрабатывает процесс установки нового пароля по токену восстановления
* Включает валидацию токена, проверку пароля и отправку уведомлений
*/
class ResetPassword extends AuthAction {
    
    /**
    * Выполнение действия сброса пароля
    * Управляет процессом установки нового пароля через токен восстановления
    *
    * Основной алгоритм:
    * 1. Проверка доступности восстановления пароля через системные настройки
    * 2. Валидация токена восстановления из URL
    * 3. Обработка POST-данных формы с новым паролем
    * 4. Обновление пароля в базе данных
    * 5. Отметка токена как использованного
    * 6. Отправка email-уведомления об изменении пароля
    *
    * @throws \Exception При недействительном токене или ошибках валидации
    */
    public function execute() {
        try {
            // Установка заголовка страницы
            $this->pageTitle = 'Сброс пароля';

            // Получение настроек авторизации
            $authSettings = $this->getFrontAuthSettings();
            $disableRestore = $authSettings['disable_restore'] ?? false;
            
            // Проверка доступности восстановления пароля
            if ($disableRestore) {
                \Notification::error('Восстановление пароля временно отключено администратором');
                $this->redirect(BASE_URL . '/login');
                return;
            }

            // Получение токена восстановления из GET-параметров
            $token = $_GET['token'] ?? '';
            
            if (empty($token)) {
                throw new \Exception('Токен восстановления не указан');
            }

            // Валидация токена восстановления
            $tokenData = $this->validateResetToken($token);
            
            if (!$tokenData) {
                throw new \Exception('Токен восстановления недействителен или срок его действия истек');
            }

            // Обработка POST-запроса (отправка формы нового пароля)
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                // Проверка CSRF-токена
                if (!$this->validateCsrfToken()) {
                    throw new \Exception('Неверный CSRF токен');
                }

                // Валидация нового пароля
                if (empty($_POST['password'])) {
                    throw new \Exception('Пароль обязателен');
                }

                // Проверка минимальной длины пароля
                if (strlen($_POST['password']) < 6) {
                    throw new \Exception('Пароль должен содержать не менее 6 символов');
                }

                // Проверка совпадения паролей
                if ($_POST['password'] !== $_POST['password_confirm']) {
                    throw new \Exception('Пароли не совпадают');
                }

                // Обновление пароля пользователя
                $hashedPassword = password_hash($_POST['password'], PASSWORD_DEFAULT);
                $this->userModel->update($tokenData['user_id'], [
                    'password' => $hashedPassword
                ]);

                // Отметка токена как использованного
                $this->markTokenAsUsed($tokenData['id']);

                // Отправка email-уведомления об изменении пароля
                $this->sendPasswordChangedEmail($tokenData['email'], $tokenData['username']);

                \Notification::success('Пароль успешно изменен. Теперь вы можете войти в систему с новым паролем.');
                $this->redirect(BASE_URL . '/login');
                return;
            }

            // Отображение формы сброса пароля при GET-запросе
            $this->render('front/auth/reset_password', [
                'csrf_token' => $this->generateCsrfToken(),
                'token' => $token,
                'error' => false
            ]);

        } catch (\Exception $e) {
            // Обработка ошибок сброса пароля
            \Notification::error($e->getMessage());
            
            $this->render('front/auth/reset_password', [
                'csrf_token' => $this->generateCsrfToken(),
                'token' => $token ?? '',
                'error' => true
            ]);
        }
    }

    /**
    * Получение настроек авторизации для фронтенда
    * Извлекает параметры восстановления пароля из системы настроек
    *
    * @return array Массив с настройками:
    *               - disable_restore: флаг отключения восстановления пароля
    */
    private function getFrontAuthSettings() {
        return [
            'disable_restore' => \SettingsHelper::get('controller_auth', 'disable_restore', false)
        ];
    }

    /**
    * Валидация токена восстановления пароля
    * Проверяет существование, срок действия и использование токена
    *
    * @param string $token Токен восстановления из URL
    * @return array|false Данные токена или false при недействительном токене
    */
    private function validateResetToken($token) {
        // Проверка существования таблицы password_resets
        $tableExists = $this->db->fetch("
            SELECT COUNT(*) as count 
            FROM information_schema.tables 
            WHERE table_schema = DATABASE() 
            AND table_name = 'password_resets'
        ");
        
        // Если таблица не существует, токен недействителен
        if (!$tableExists || $tableExists['count'] == 0) {
            return false;
        }
        
        // Поиск действительного токена
        $result = $this->db->fetch("
            SELECT pr.*, u.email, u.username 
            FROM password_resets pr
            JOIN users u ON pr.user_id = u.id
            WHERE pr.token = ? 
            AND pr.used = FALSE 
            AND pr.expires_at > NOW()
            ORDER BY pr.created_at DESC 
            LIMIT 1
        ", [$token]);
        
        return $result;
    }

    /**
    * Отметка токена восстановления как использованного
    * Предотвращает повторное использование токена
    *
    * @param int $resetId ID записи токена в таблице password_resets
    */
    private function markTokenAsUsed($resetId) {
        $this->db->query(
            "UPDATE password_resets SET used = TRUE WHERE id = ?",
            [$resetId]
        );
    }

    /**
    * Отправка email-уведомления об изменении пароля
    * Информирует пользователя об успешной смене пароля
    *
    * @param string $email Email адрес пользователя
    * @param string $username Имя пользователя
    * @return bool Результат отправки email
    */
    private function sendPasswordChangedEmail($email, $username) {
        try {
            // Получение настроек сайта для email
            $siteName = \SettingsHelper::get('general', 'site_name', 'BloggyCMS');
            $siteEmail = \SettingsHelper::get('general', 'contact_email', 'noreply@bloggycms.com');
            
            $subject = 'Пароль успешно изменен - ' . $siteName;
            
            // Формирование HTML-сообщения
            $message = "
                <!DOCTYPE html>
                <html>
                <head>
                    <meta charset='UTF-8'>
                    <title>Пароль изменен</title>
                    <style>
                        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                        .container { max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e0e0e0; border-radius: 8px; }
                        .button { background-color: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block; }
                        .footer { font-size: 12px; color: #666; margin-top: 30px; padding-top: 20px; border-top: 1px solid #e0e0e0; }
                        .warning-box { background-color: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #dc3545; }
                    </style>
                </head>
                <body>
                    <div class='container'>
                        <h2 style='color: #333; text-align: center;'>Пароль успешно изменен</h2>
                        <p>Здравствуйте, <strong>{$username}</strong>!</p>
                        <p>Это уведомление подтверждает, что пароль для вашего аккаунта на сайте <strong>{$siteName}</strong> был успешно изменен.</p>
                        <div class='warning-box'>
                            <p><strong>Важно:</strong> Если вы не меняли пароль, немедленно свяжитесь с администрацией сайта.</p>
                        </div>
                        <p>Для входа в систему используйте новый пароль:</p>
                        <div style='text-align: center; margin: 20px 0;'>
                            <a href='" . BASE_URL . "/login' class='button'>
                                Войти в аккаунт
                            </a>
                        </div>
                        <div class='footer'>
                            <p>Это письмо отправлено автоматически. Пожалуйста, не отвечайте на него.</p>
                        </div>
                    </div>
                </body>
                </html>
            ";

            // Формирование заголовков email
            $headers = "MIME-Version: 1.0" . "\r\n";
            $headers .= "Content-type: text/html; charset=UTF-8" . "\r\n";
            $headers .= "From: " . $siteName . " <" . $siteEmail . ">" . "\r\n";
            $headers .= "Reply-To: " . $siteEmail . "\r\n";
            $headers .= "X-Mailer: PHP/" . phpversion();
            $headers .= "X-Priority: 1 (Highest)" . "\r\n";
            
            // Отправка email с кодированием темы в UTF-8
            return mail($email, '=?UTF-8?B?' . base64_encode($subject) . '?=', $message, $headers);
            
        } catch (\Exception $e) {
            // В случае ошибки отправки email возвращаем false
            return false;
        }
    }
}