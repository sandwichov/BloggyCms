<?php

/**
 * Модель для работы с настройками в базе данных
 * Предоставляет методы для получения, сохранения и управления настройками,
 * сгруппированными по ключам, с поддержкой кэширования
 * 
 * @package Models
 */
class SettingsModel {
    
    /** @var object Подключение к базе данных */
    private $db;
    
    /** @var array Статический кэш настроек для уменьшения количества запросов к БД */
    private static $cache = [];
    
    /**
     * Конструктор модели
     * Инициализирует подключение к базе данных
     * 
     * @param object $db Подключение к базе данных
     */
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * Получить все настройки определенной группы
     * Использует статический кэш для оптимизации повторных запросов
     * 
     * @param string $group Ключ группы настроек
     * @return array Массив настроек группы
     */
    public function get($group) {
        // Проверка наличия в кэше
        if (isset(self::$cache[$group])) {
            return self::$cache[$group];
        }
        
        // Запрос к базе данных
        $result = $this->db->fetch(
            "SELECT settings FROM settings WHERE group_key = ?",
            [$group]
        );
        
        // Декодирование JSON или возврат пустого массива
        $settings = $result ? json_decode($result['settings'], true) : [];
        
        // Сохранение в кэш
        self::$cache[$group] = $settings;
        
        return $settings;
    }
    
    /**
     * Сохранить настройки группы в базу данных
     * Обновляет существующую запись или создает новую, обновляет кэш
     * 
     * @param string $group Ключ группы настроек
     * @param array $settings Массив настроек для сохранения
     * @return bool true при успешном сохранении
     */
    public function save($group, $settings) {
        // Более надежная проверка существования записи
        $existing = $this->db->fetch(
            "SELECT id, settings FROM settings WHERE group_key = ? ORDER BY id DESC LIMIT 1",
            [$group]
        );
        
        if ($existing) {
            // Обновляем существующую запись с максимальным ID
            $this->db->query(
                "UPDATE settings SET settings = ?, updated_at = NOW() WHERE id = ?",
                [json_encode($settings), $existing['id']]
            );
        } else {
            // Создаем новую запись
            $this->db->query(
                "INSERT INTO settings (group_key, settings) VALUES (?, ?)",
                [$group, json_encode($settings)]
            );
        }
        
        // Обновляем кэш
        self::$cache[$group] = $settings;
        
        // Обновляем глобальный кэш в SettingsHelper (если класс существует)
        if (class_exists('SettingsHelper')) {
            SettingsHelper::updateCache($group, $settings);
        }
        
        return true;
    }
    
    /**
     * Получить список всех групп настроек
     * 
     * @return array Массив с ключами групп настроек
     */
    public function getAllGroups() {
        return $this->db->fetchAll("SELECT group_key FROM settings");
    }
    
    /**
     * Объединить новые настройки с существующими
     * Полезно для частичного обновления настроек группы
     * 
     * @param string $group Ключ группы настроек
     * @param array $newSettings Массив новых настроек для объединения
     * @return bool true при успешном сохранении
     */
    public function merge($group, $newSettings) {
        $currentSettings = $this->get($group);
        $mergedSettings = array_merge($currentSettings, $newSettings);
        return $this->save($group, $mergedSettings);
    }
}