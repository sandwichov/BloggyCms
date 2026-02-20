<?php

/**
 * Middleware для проверки доступа к административной панели
 */
class AdminAuthMiddleware {
    /**
     * Основной метод обработки middleware
     * Проверяет доступ к административным разделам
     */
    public static function handle() {
        $currentUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        // Пропускаем статические файлы
        if (self::isStaticFile($currentUri)) {
            return;
        }
        
        // Пропускаем страницу логина
        if ($currentUri === '/admin/login' || strpos($currentUri, '/admin/login') === 0) {
            return;
        }
        
        // Если это админка (начинается с /admin)
        if (strpos($currentUri, '/admin') === 0) {
            // Проверка авторизации
            if (!isset($_SESSION['user_id'])) {
                Notification::error('Пожалуйста, авторизуйтесь для доступа к панели управления');
                header('Location: ' . ADMIN_URL . '/login');
                exit;
            }
            
            // Проверка прав администратора
            if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
                Notification::error('У вас нет прав доступа к панели управления');
                header('Location: ' . BASE_URL);
                exit;
            }
        }
    }
    
    /**
     * Проверяет, является ли URI статическим файлом
     *
     * @param string $uri URI для проверки
     * @return bool True если это статический файл
     */
    private static function isStaticFile($uri) {
        $staticExtensions = [
            'css', 'js', 'png', 'jpg', 'jpeg', 'gif', 'ico', 'svg',
            'woff', 'woff2', 'ttf', 'eot', 'map', 'txt', 'xml'
        ];
        
        $extension = pathinfo($uri, PATHINFO_EXTENSION);
        return in_array(strtolower($extension), $staticExtensions);
    }
}