<?php

namespace auth\actions;

/**
 * Действие для восстановления пароля пользователя
 */
class ForgotPassword extends AuthAction {
    
    /**
     * Основной метод выполнения процесса восстановления пароля
     */
    public function execute() {
        try {

            $this->addBreadcrumb('Главная', BASE_URL);
            $this->addBreadcrumb('Вход в систему', BASE_URL . '/login');
            $this->addBreadcrumb('Восстановление пароля');
            $this->pageTitle = 'Восстановление пароля';

            $authSettings = $this->getFrontAuthSettings();
            $disableRestore = $authSettings['disable_restore'] ?? false;
            
            if ($disableRestore) {
                \Notification::error('Восстановление пароля временно отключено администратором');
                $this->redirect(BASE_URL . '/login');
                return;
            }

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                if (!$this->validateCsrfToken()) {
                    throw new \Exception('Неверный CSRF токен');
                }

                if (empty($_POST['email'])) {
                    throw new \Exception('Email обязателен');
                }

                $email = $_POST['email'];
                
                $user = $this->userModel->getByEmail($email);
                if (!$user) {
                    throw new \Exception('Пользователь с таким email не найден');
                }

                $resetToken = bin2hex(random_bytes(32));
                $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));

                $this->saveResetToken($user['id'], $resetToken, $expiresAt);
                $this->sendResetEmail($user['email'], $resetToken, $user['username']);

                \Notification::success('На вашу почту отправлена ссылка для восстановления пароля. Ссылка действительна 1 час.');
                $this->redirect(BASE_URL . '/login');
                return;
            }

            $this->render('front/auth/forgot_password', [
                'csrf_token' => $this->generateCsrfToken()
            ]);

        } catch (\Exception $e) {
            \Notification::error($e->getMessage());
            
            $this->render('front/auth/forgot_password', [
                'csrf_token' => $this->generateCsrfToken(),
                'email' => $_POST['email'] ?? ''
            ]);
        }
    }

    /**
     * Получение настроек авторизации для фронтенда из базы данных
     */
    private function getFrontAuthSettings() {
        return [
            'disable_restore' => \SettingsHelper::get('controller_auth', 'disable_restore', false)
        ];
    }

    /**
     * Сохранение токена восстановления пароля в базе данных
     */
    private function saveResetToken($userId, $token, $expiresAt) {
        $this->db->query(
            "INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?)",
            [$userId, $token, $expiresAt]
        );
    }

    /**
     * Отправка email с инструкциями по восстановлению пароля
     */
    private function sendResetEmail($email, $token, $username) {
        return \Email::sendPasswordReset($email, $token, $username);
    }
    
}