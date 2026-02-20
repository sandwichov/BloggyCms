<?php

/**
 * Вспомогательный класс для работы с настройками
 * Предоставляет удобный интерфейс для получения и сохранения настроек
 * с автоматическим кэшированием
 * 
 * @package Helpers
 */
class SettingsHelper {
    
    /** @var array Кэш настроек для уменьшения количества запросов к БД */
    private static $cache = [];
    
    /** @var SettingsModel|null Экземпляр модели настроек */
    private static $model = null;
    
    /**
     * Получает экземпляр модели настроек (ленивая инициализация)
     * 
     * @return SettingsModel Модель настроек
     */
    private static function getModel() {
        if (self::$model === null) {
            self::$model = new SettingsModel(Database::getInstance());
        }
        return self::$model;
    }
    
    /**
     * Получает настройки группы или конкретный ключ
     * 
     * @param string $group Название группы настроек
     * @param string|null $key Ключ настройки (если null, возвращает всю группу)
     * @param mixed $default Значение по умолчанию, если настройка не найдена
     * @return mixed Массив настроек группы или значение конкретного ключа
     */
    public static function get($group, $key = null, $default = null) {
        if (!is_string($group)) {
            return $key === null ? [] : $default;
        }
        
        if (array_key_exists($group, self::$cache)) {
        } else {
            try {
                $model = self::getModel();
                $settings = $model->get($group);
                self::$cache[$group] = is_array($settings) ? $settings : [];
            } catch (\Exception $e) {
                self::$cache[$group] = [];
            }
        }
        
        if ($key === null) {
            $result = self::$cache[$group];
            return $result;
        }
        
        if (!is_string($key)) {
            return $default;
        }
        
        $result = self::$cache[$group][$key] ?? $default;
        return $result;
    }
    
    /**
     * Очищает кэш настроек
     * 
     * @param string|null $group Если указана группа, очищает только её, иначе весь кэш
     * @return void
     */
    public static function clearCache($group = null) {
        if ($group === null) {
            self::$cache = [];
        } else {
            unset(self::$cache[$group]);
        }
    }
    
    /**
     * Получает название текущего активного шаблона
     * Приоритет: настройка в БД > константа DEFAULT_TEMPLATE > 'default'
     * 
     * @return string Название шаблона
     */
    public static function getCurrentTemplate() {
        $templateFromDb = self::get('general', 'template');
        if ($templateFromDb) {
            return $templateFromDb;
        }
        
        // Если в базе нет настройки, используем дефолтную константу
        return defined('DEFAULT_TEMPLATE') ? DEFAULT_TEMPLATE : 'default';
    }
    
    /**
     * Получает базовый URL сайта
     * 
     * @return string Базовый URL
     */
    public static function getBaseUrl() {
        return defined('BASE_URL') ? BASE_URL : 'http://localhost';
    }
    
    /**
     * Обновляет кэш для конкретной группы
     * 
     * @param string $group Название группы
     * @param array $settings Новые настройки
     * @return void
     */
    public static function updateCache($group, $settings) {
        self::$cache[$group] = $settings;
    }
    
    /**
     * Сохраняет настройки группы
     * Автоматически обновляет кэш после сохранения
     * 
     * @param string $group Название группы
     * @param array $settings Настройки для сохранения
     * @return bool Результат сохранения
     */
    public static function save($group, $settings) {
        $model = self::getModel();
        $result = $model->save($group, $settings);
        
        self::updateCache($group, $settings);
        
        return $result;
    }
    
    /**
     * Получает список всех групп настроек
     * 
     * @return array Массив групп настроек
     */
    public static function getAllGroups() {
        $model = self::getModel();
        return $model->getAllGroups();
    }
    
    /**
     * Объединяет новые настройки с существующими
     * Полезно для частичного обновления настроек
     * 
     * @param string $group Название группы
     * @param array $newSettings Новые настройки для объединения
     * @return bool Результат операции
     */
    public static function merge($group, $newSettings) {
        $model = self::getModel();
        $result = $model->merge($group, $newSettings);
        
        if ($result) {
            $updatedSettings = $model->get($group);
            self::updateCache($group, $updatedSettings);
        }
        
        return $result;
    }
}