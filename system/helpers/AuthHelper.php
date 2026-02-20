<?php

/**
 * Вспомогательный класс для проверки прав доступа
 * Предоставляет удобные методы для проверки различных разрешений
 * Особенно для работы с комментариями
 * 
 * @package Helpers
 */
class AuthHelper {
    
    /** @var PermissionManager|null Менеджер прав доступа */
    private static $permissionManager = null;
    
    /**
     * Инициализирует менеджер прав доступа
     * Автоматически вызывается перед проверкой прав
     * 
     * @return void
     */
    public static function init() {
        if (!self::$permissionManager && isset($GLOBALS['db'])) {
            self::$permissionManager = new PermissionManager($GLOBALS['db']);
        }
    }
    
    /**
     * Проверяет, имеет ли текущий пользователь указанное право
     * Администраторы имеют все права
     * 
     * @param string $permissionKey Ключ права для проверки
     * @return bool true если пользователь имеет право
     */
    public static function can($permissionKey) {
        self::init();
        
        // Админы имеют все права
        if (Auth::isAdmin()) {
            return true;
        }
        
        // Для неавторизованных пользователей
        if (!Auth::isLoggedIn()) {
            return false;
        }
        
        // Проверяем право через PermissionManager
        if (self::$permissionManager) {
            return self::$permissionManager->userCan($permissionKey);
        }
        
        return false;
    }
    
    // ==================== МЕТОДЫ ДЛЯ КОММЕНТАРИЕВ ====================
    
    /**
     * Проверяет, может ли пользователь добавлять комментарии
     * 
     * @param int|null $postId ID поста (не используется)
     * @return bool true если может
     */
    public static function canAddComment($postId = null) {
        return self::can('comment_add');
    }
    
    /**
     * Проверяет, может ли пользователь добавлять комментарии без модерации
     * 
     * @param int|null $postId ID поста (не используется)
     * @return bool true если может
     */
    public static function canAddCommentWithoutModeration($postId = null) {
        return self::can('comment_add_no_moderations');
    }
    
    /**
     * Проверяет, может ли пользователь редактировать комментарий
     * Учитывает владельца комментария и права администратора
     * 
     * @param int|null $commentUserId ID автора комментария
     * @return bool true если может редактировать
     */
    public static function canEditComment($commentUserId = null) {
        $currentUserId = Auth::getUserId();
        
        if (Auth::isAdmin()) {
            return true;
        }
        
        if (self::can('comment_edit_no_moderations')) {
            return true;
        }
        
        if (self::can('comment_edit')) {
            return $currentUserId && $commentUserId && $currentUserId == $commentUserId;
        }
        
        return false;
    }
    
    /**
     * Проверяет, может ли пользователь удалять комментарий
     * Администраторы могут удалять любые, обычные пользователи - только свои
     * 
     * @param int|null $commentUserId ID автора комментария
     * @return bool true если может удалить
     */
    public static function canDeleteComment($commentUserId = null) {
        $currentUserId = Auth::getUserId();
        
        // Администратор может удалять ЛЮБЫЕ комментарии
        if (Auth::isAdmin()) {
            return true;
        }
        
        // Обычные пользователи - только свои комментарии
        if (self::can('comment_delete')) {
            return $currentUserId && $commentUserId && $currentUserId == $commentUserId;
        }
        
        return false;
    }
    
    // ==================== МЕТОДЫ ДЛЯ ПОСТОВ ====================
    
    /**
     * Проверяет, может ли пользователь оставлять комментарии к посту
     * 
     * @param int $postId ID поста
     * @return bool true если может
     */
    public static function canPostComments($postId) {
        return self::can('post_comments');
    }
    
    // ==================== ОБЩИЕ МЕТОДЫ ====================
    
    /**
     * Проверяет, может ли пользователь видеть все комментарии (включая ожидающие модерации)
     * 
     * @return bool true если может
     */
    public static function canViewAllComments() {
        return Auth::isAdmin() || self::can('comment_edit_no_moderations');
    }
}