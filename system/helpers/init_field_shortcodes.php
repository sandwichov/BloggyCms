<?php

/**
 * Автоматически инициализирует шорткоды полей при загрузке системы
 * Функция использует статическую переменную для гарантии однократной инициализации
 * 
 * @return void
 */
function init_field_shortcodes_system() {
    global $db;
    
    // Проверка наличия подключения к БД
    if (!$db) {
        return;
    }
    
    static $initialized = false;
    
    // Инициализация только один раз
    if (!$initialized) {
        try {
            // Вызов основной функции инициализации шорткодов
            init_field_shortcodes();
            $initialized = true;
        } catch (Exception $e) {
            // Подавление исключений - не критично для работы системы
        }
    }
}