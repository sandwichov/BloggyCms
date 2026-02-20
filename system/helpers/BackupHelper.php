<?php

/**
 * Вспомогательный класс для управления резервными копиями шаблонов
 * Предоставляет методы для создания, очистки и получения статистики
 * по резервным копиям файлов
 * 
 * @package Helpers
 */
class BackupHelper {
    
    /** @var object Подключение к базе данных (для нестатических методов) */
    private $db;
    
    /**
     * Конструктор класса
     * 
     * @param object $db Подключение к базе данных
     */
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * Получает статистику по резервным копиям
     * Сканирует директорию шаблонов на наличие файлов с расширением .backup.
     * 
     * @return array Статистика с полями:
     *               - total_files: общее количество файлов
     *               - total_size: общий размер (отформатированный)
     *               - oldest_backup: дата самой старой копии
     */
    public static function getBackupStats() {
        $stats = [
            'total_files' => 0,
            'total_size' => 0,
            'oldest_backup' => 'нет'
        ];
        
        $templatesPath = TEMPLATES_PATH;
        $backupFiles = [];
        
        // Рекурсивный поиск всех файлов .backup.
        try {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($templatesPath, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::SELF_FIRST
            );
            
            $oldestTime = null;
            
            foreach ($iterator as $file) {
                if ($file->isFile() && strpos($file->getFilename(), '.backup.') !== false) {
                    $stats['total_files']++;
                    $stats['total_size'] += $file->getSize();
                    $backupFiles[] = $file->getPathname();
                    
                    $fileTime = $file->getMTime();
                    if ($oldestTime === null || $fileTime < $oldestTime) {
                        $oldestTime = $fileTime;
                    }
                }
            }
            
            if ($oldestTime !== null) {
                $stats['oldest_backup'] = date('d.m.Y H:i', $oldestTime);
            }
        } catch (Exception $e) {
            // Игнорируем ошибки
        }
        
        // Форматируем размер
        $stats['total_size'] = self::formatFileSize($stats['total_size']);
        
        return $stats;
    }
    
    /**
     * Форматирует размер файла в человекочитаемый вид
     * 
     * @param int $size Размер в байтах
     * @return string Отформатированный размер (B, KB, MB)
     */
    private static function formatFileSize($size) {
        if ($size < 1024) {
            return $size . ' B';
        } elseif ($size < 1048576) {
            return round($size / 1024, 2) . ' KB';
        } else {
            return round($size / 1048576, 2) . ' MB';
        }
    }
    
    /**
     * Очищает старые резервные копии для конкретного файла
     * Оставляет только указанное количество последних копий
     * 
     * @param string $filePath Путь к исходному файлу
     * @return void
     */
    public static function cleanupOldBackups($filePath) {
        // Получаем настройки с правильными значениями по умолчанию
        $backupsEnabled = SettingsHelper::get('site', 'template_backups_enabled', false);
        $backupsCount = SettingsHelper::get('site', 'template_backups_count', 5);
        $cleanupMode = SettingsHelper::get('site', 'template_backups_cleanup', 'auto');
        
        // Проверка существования настройки для обратной совместимости
        $settingExists = self::settingExists('site', 'template_backups_enabled');
        if (!$settingExists) {
            $backupsEnabled = true; // обратная совместимость
        }
        
        // Если бэкапы отключены или режим "никогда не удалять" - выход
        if (!$backupsEnabled || $cleanupMode === 'never') {
            return;
        }
        
        $dir = dirname($filePath);
        $filename = basename($filePath);
        $pattern = $dir . '/' . $filename . '.backup.*';
        
        $backups = glob($pattern);
        
        // Сортировка по времени создания (новые сначала)
        usort($backups, function($a, $b) {
            return filemtime($b) - filemtime($a);
        });
        
        // Удаление старых бэкапов, оставляя только $backupsCount последних
        if (count($backups) > $backupsCount) {
            for ($i = $backupsCount; $i < count($backups); $i++) {
                @unlink($backups[$i]);
            }
        }
    }
    
    /**
     * Создает резервную копию файла
     * Копия создается с добавлением временной метки: .backup.ГГГГ-ММ-ДД-ЧЧ-ММ-СС
     * 
     * @param string $filePath Путь к исходному файлу
     * @return bool true при успешном создании, false при ошибке
     */
    public static function createBackup($filePath) {
        // Получение настройки
        $backupsEnabled = SettingsHelper::get('site', 'template_backups_enabled', false);
        
        // Правильная проверка с учетом разных типов данных
        $isEnabled = ($backupsEnabled === true || $backupsEnabled === '1' || $backupsEnabled === 1);
        
        if (!$isEnabled) {
            return false;
        }
        
        if (!file_exists($filePath)) {
            return false;
        }
        
        $backupPath = $filePath . '.backup.' . date('Y-m-d-H-i-s');
        
        if (copy($filePath, $backupPath)) {
            self::cleanupOldBackups($filePath);
            return true;
        }
        
        return false;
    }

    /**
     * Проверяет, существует ли настройка в базе данных
     * Используется для обратной совместимости
     * 
     * @param string $section Секция настроек
     * @param string $key Ключ настройки
     * @return bool true если настройка существует
     */
    private static function settingExists($section, $key) {
        try {
            $db = Database::getInstance();
            $result = $db->fetch(
                "SELECT COUNT(*) as count FROM settings WHERE section = ? AND setting_key = ?",
                [$section, $key]
            );
            return $result['count'] > 0;
        } catch (Exception $e) {
            // Если произошла ошибка, считаем что настройка не существует
            return false;
        }
    }
    
    /**
     * Очищает все резервные копии
     * Удаляет все файлы с расширением .backup. в директории шаблонов
     * 
     * @return int Количество удаленных файлов
     */
    public static function cleanupAllBackups() {
        $templatesPath = TEMPLATES_PATH;
        $deletedCount = 0;
        
        // Рекурсивный поиск и удаление всех файлов .backup.
        try {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($templatesPath, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::SELF_FIRST
            );
            
            foreach ($iterator as $file) {
                if ($file->isFile() && strpos($file->getFilename(), '.backup.') !== false) {
                    if (@unlink($file->getPathname())) {
                        $deletedCount++;
                    }
                }
            }
        } catch (Exception $e) {
            // Игнорируем ошибки
        }
        
        return $deletedCount;
    }
}