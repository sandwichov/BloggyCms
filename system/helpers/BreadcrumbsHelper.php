<?php

/**
 * Хелпер для работы с хлебными крошками
 * Предоставляет глобальный доступ к менеджеру хлебных крошек
 * 
 * @package Helpers
 */
class BreadcrumbsHelper {
    
    /** @var BreadcrumbsManager|null Экземпляр менеджера хлебных крошек */
    private static $instance = null;
    
    /**
     * Устанавливает экземпляр менеджера (вызывается из экшенов)
     * 
     * @param BreadcrumbsManager $manager
     * @return void
     */
    public static function setManager($manager) {
        self::$instance = $manager;
    }
    
    /**
     * Возвращает менеджер хлебных крошек
     * 
     * @return BreadcrumbsManager|null
     */
    public static function getManager() {
        return self::$instance;
    }
}