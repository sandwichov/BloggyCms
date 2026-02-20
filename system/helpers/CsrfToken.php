<?php

/**
 * Класс для защиты форм от CSRF-атак (Cross-Site Request Forgery)
 * Генерирует, хранит и проверяет одноразовые токены с ограниченным сроком жизни
 * 
 * @package Security
 */
class CsrfToken {
    
    /**
     * Генерирует и сохраняет CSRF токен в сессии
     * Токен привязывается к имени формы для возможности использования нескольких форм
     * 
     * @param string $formName Имя формы (по умолчанию 'default')
     * @return string Сгенерированный токен
     */
    public static function generate($formName = 'default') {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $token = bin2hex(random_bytes(32));
        
        if (!isset($_SESSION['csrf_tokens'])) {
            $_SESSION['csrf_tokens'] = [];
        }
        
        $_SESSION['csrf_tokens'][$formName] = [
            'token' => $token,
            'created_at' => time()
        ];
        
        // Очищаем старые токены (старше 1 часа)
        self::cleanupOldTokens();
        
        return $token;
    }
    
    /**
     * Проверяет CSRF токен
     * Токен проверяется и затем удаляется (одноразовый)
     * 
     * @param string $token Токен для проверки
     * @param string $formName Имя формы (по умолчанию 'default')
     * @return bool true если токен валидный, false в противном случае
     */
    public static function verify($token, $formName = 'default') {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['csrf_tokens'][$formName])) {
            return false;
        }
        
        $storedToken = $_SESSION['csrf_tokens'][$formName];
        
        // Проверяем время жизни токена (1 час)
        if (time() - $storedToken['created_at'] > 3600) {
            unset($_SESSION['csrf_tokens'][$formName]);
            return false;
        }
        
        // Сравниваем токены с защитой от timing-атак
        if (!hash_equals($storedToken['token'], $token)) {
            return false;
        }
        
        // После успешной проверки удаляем токен (одноразовый)
        unset($_SESSION['csrf_tokens'][$formName]);
        
        return true;
    }
    
    /**
     * Очищает старые токены (старше 1 часа)
     * Автоматически вызывается при генерации нового токена
     * 
     * @return void
     */
    private static function cleanupOldTokens() {
        if (!isset($_SESSION['csrf_tokens'])) {
            return;
        }
        
        foreach ($_SESSION['csrf_tokens'] as $formName => $tokenData) {
            if (time() - $tokenData['created_at'] > 3600) {
                unset($_SESSION['csrf_tokens'][$formName]);
            }
        }
    }
    
    /**
     * Генерирует HTML input с токеном для вставки в форму
     * 
     * @param string $formName Имя формы (по умолчанию 'default')
     * @return string HTML-код скрытого поля с токеном
     */
    public static function field($formName = 'default') {
        $token = self::generate($formName);
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
    }
}