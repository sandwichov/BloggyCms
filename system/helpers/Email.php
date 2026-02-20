<?php

/**
 * Класс для отправки email-уведомлений пользователям
 * Использует стандартную PHP функцию mail() с HTML-форматированием
 * 
 * @package Core
 */
class Email {
    
    /**
     * Отправляет email для восстановления пароля
     * Формирует HTML-письмо с ссылкой для сброса пароля
     * 
     * @param string $email Email получателя
     * @param string $token Токен для сброса пароля
     * @param string $username Имя пользователя
     * @return bool Результат отправки (true/false)
     */
    public static function sendPasswordReset($email, $token, $username) {
        $siteName = \SettingsHelper::get('general', 'site_name', 'BloggyCMS');
        $siteEmail = \SettingsHelper::get('general', 'contact_email', 'noreply@bloggycms.com');
        $baseUrl = defined('BASE_URL') ? BASE_URL : 'http://localhost';
        
        $resetLink = $baseUrl . '/reset-password?token=' . $token;
        
        $subject = 'Восстановление пароля - ' . $siteName;
        
        $message = "
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset='UTF-8'>
                <title>Восстановление пароля</title>
                <style>
                    body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e0e0e0; border-radius: 8px; }
                    .button { background-color: #007bff; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block; }
                    .footer { font-size: 12px; color: #666; margin-top: 30px; padding-top: 20px; border-top: 1px solid #e0e0e0; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <h2 style='color: #333; text-align: center;'>Восстановление пароля</h2>
                    <p>Здравствуйте, <strong>{$username}</strong>!</p>
                    <p>Вы получили это письмо, потому что запросили восстановление пароля для вашего аккаунта на сайте <strong>{$siteName}</strong>.</p>
                    <p>Для установки нового пароля перейдите по ссылке ниже:</p>
                    <div style='text-align: center; margin: 30px 0;'>
                        <a href='{$resetLink}' class='button'>
                            Восстановить пароль
                        </a>
                    </div>
                    <p>Ссылка действительна в течение <strong>1 часа</strong>.</p>
                    <p>Если вы не запрашивали восстановление пароля, просто проигнорируйте это письмо.</p>
                    <div class='footer'>
                        <p>Это письмо отправлено автоматически. Пожалуйста, не отвечайте на него.</p>
                        <p>Если у вас возникли проблемы, свяжитесь с администрацией сайта.</p>
                    </div>
                </div>
            </body>
            </html>
        ";

        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8" . "\r\n";
        $headers .= "From: " . $siteName . " <" . $siteEmail . ">" . "\r\n";
        $headers .= "Reply-To: " . $siteEmail . "\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion();
        $headers .= "X-Priority: 1 (Highest)" . "\r\n";
        $headers .= "X-MSMail-Priority: High" . "\r\n";
        $headers .= "Importance: High" . "\r\n";
        
        return mail($email, '=?UTF-8?B?' . base64_encode($subject) . '?=', $message, $headers);
    }
    
    /**
     * Отправляет email с уведомлением об успешном изменении пароля
     * 
     * @param string $email Email получателя
     * @param string $username Имя пользователя
     * @return bool Результат отправки
     */
    public static function sendPasswordChanged($email, $username) {
        $siteName = \SettingsHelper::get('general', 'site_name', 'BloggyCMS');
        $siteEmail = \SettingsHelper::get('general', 'contact_email', 'noreply@bloggycms.com');
        
        $subject = 'Пароль успешно изменен - ' . $siteName;
        
        $message = "
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset='UTF-8'>
                <title>Пароль изменен</title>
            </head>
            <body style='font-family: Arial, sans-serif; line-height: 1.6;'>
                <div style='max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e0e0e0; border-radius: 8px;'>
                    <h2 style='color: #333; text-align: center;'>Пароль успешно изменен</h2>
                    <p>Здравствуйте, <strong>{$username}</strong>!</p>
                    <p>Это уведомление подтверждает, что пароль для вашего аккаунта на сайте <strong>{$siteName}</strong> был успешно изменен.</p>
                    <div style='background-color: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0;'>
                        <p><strong>Важно:</strong> Если вы не меняли пароль, немедленно свяжитесь с администрацией сайта.</p>
                    </div>
                    <p>Для входа в систему используйте новый пароль:</p>
                    <div style='text-align: center; margin: 20px 0;'>
                        <a href='" . BASE_URL . "/login' style='background-color: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;'>
                            Войти в аккаунт
                        </a>
                    </div>
                    <div style='margin-top: 30px; padding-top: 20px; border-top: 1px solid #e0e0e0; font-size: 12px; color: #666;'>
                        <p>Это письмо отправлено автоматически.</p>
                    </div>
                </div>
            </body>
            </html>
        ";

        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8" . "\r\n";
        $headers .= "From: " . $siteEmail . "\r\n";
        
        return mail($email, $subject, $message, $headers);
    }
    
    /**
     * Отправляет приветственное письмо после регистрации
     * 
     * @param string $email Email получателя
     * @param string $username Имя пользователя
     * @return bool Результат отправки
     */
    public static function sendWelcomeEmail($email, $username) {
        $siteName = \SettingsHelper::get('general', 'site_name', 'BloggyCMS');
        $siteEmail = \SettingsHelper::get('general', 'contact_email', 'noreply@bloggycms.com');
        
        $subject = 'Добро пожаловать на ' . $siteName;
        
        $message = "
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset='UTF-8'>
                <title>Добро пожаловать</title>
            </head>
            <body style='font-family: Arial, sans-serif; line-height: 1.6;'>
                <div style='max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e0e0e0; border-radius: 8px;'>
                    <h2 style='color: #333; text-align: center;'>Добро пожаловать на {$siteName}!</h2>
                    <p>Здравствуйте, <strong>{$username}</strong>!</p>
                    <p>Спасибо за регистрацию на нашем сайте. Ваш аккаунт был успешно создан.</p>
                    <div style='text-align: center; margin: 30px 0;'>
                        <a href='" . BASE_URL . "/login' style='background-color: #007bff; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; display: inline-block;'>
                            Войти в аккаунт
                        </a>
                    </div>
                    <p>Если у вас возникли вопросы или проблемы, не стесняйтесь обращаться к нам.</p>
                    <div style='margin-top: 30px; padding-top: 20px; border-top: 1px solid #e0e0e0; font-size: 12px; color: #666;'>
                        <p>Это письмо отправлено автоматически.</p>
                    </div>
                </div>
            </body>
            </html>
        ";

        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8" . "\r\n";
        $headers .= "From: " . $siteEmail . "\r\n";
        
        return mail($email, $subject, $message, $headers);
    }
}