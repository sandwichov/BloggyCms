<?php

/**
 * Менеджер для отслеживания активности пользователей (упрощенная версия)
 */
class UserActivityManager {
    /**
     * @var mixed Подключение к базе данных
     */
    private $db;
    
    /**
     * @var self|null Единственный экземпляр класса
     */
    private static $instance = null;
    
    /**
     * @var array Кэш статусов пользователей
     */
    private $cache = [];
    
    /**
     * Конструктор UserActivityManager
     *
     * @param mixed $db Подключение к базе данных
     */
    private function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * Получает экземпляр UserActivityManager (Singleton)
     *
     * @param mixed $db Подключение к базе данных
     * @return self Экземпляр UserActivityManager
     */
    public static function getInstance($db = null) {
        if (self::$instance === null && $db) {
            self::$instance = new self($db);
        }
        return self::$instance;
    }
    
    /**
     * Обновляет активность пользователя
     *
     * @param int $userId ID пользователя
     * @return bool Результат обновления
     */
    public function touch($userId) {
        if (!$userId) return false;
        
        try {
            $tableCheck = $this->db->fetch("SELECT 1 FROM information_schema.tables 
                                          WHERE table_schema = DATABASE() 
                                          AND table_name = 'user_activity'");
            
            if (!$tableCheck) {
                return false;
            }
            
            $sql = "INSERT INTO user_activity 
                    (user_id, last_activity, session_id, ip_address, user_agent) 
                    VALUES (?, NOW(), ?, ?, ?)
                    ON DUPLICATE KEY UPDATE 
                    last_activity = NOW(),
                    session_id = VALUES(session_id),
                    ip_address = VALUES(ip_address),
                    user_agent = VALUES(user_agent)";
            
            $params = [
                $userId,
                session_id(),
                $_SERVER['REMOTE_ADDR'] ?? '',
                substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255)
            ];
            
            return $this->db->query($sql, $params);
            
        } catch (\Exception $e) {
            return false;
        }
    }
    
    /**
     * Проверяет онлайн статус пользователя
     *
     * @param int $userId ID пользователя
     * @return bool Онлайн ли пользователь
     */
    public function isOnline($userId) {
        if (!$userId) return false;
        
        $cacheKey = "online_{$userId}";
        $currentTime = time();
        
        if (isset($this->cache[$cacheKey])) {
            list($status, $timestamp) = $this->cache[$cacheKey];
            if ($currentTime - $timestamp < 5) {
                return $status;
            }
        }
        
        try {
            $tableCheck = $this->db->fetch("SELECT 1 FROM information_schema.tables 
                                          WHERE table_schema = DATABASE() 
                                          AND table_name = 'user_activity'");
            
            if ($tableCheck) {
                $sql = "SELECT 
                            CASE 
                                WHEN TIMESTAMPDIFF(SECOND, last_activity, NOW()) < 300 
                                THEN 1 ELSE 0 
                            END as is_online
                        FROM user_activity 
                        WHERE user_id = ? 
                        LIMIT 1";
                
                $result = $this->db->fetch($sql, [$userId]);
                
                if ($result) {
                    $isOnline = $result['is_online'] == 1;
                    $this->cache[$cacheKey] = [$isOnline, $currentTime];
                    return $isOnline;
                }
            }
            
            $sql = "SELECT 
                        CASE 
                            WHEN TIMESTAMPDIFF(SECOND, last_login, NOW()) < 300 
                            THEN 1 ELSE 0 
                        END as is_online
                    FROM users 
                    WHERE id = ? 
                    AND last_login IS NOT NULL
                    LIMIT 1";
            
            $result = $this->db->fetch($sql, [$userId]);
            
            $isOnline = $result && $result['is_online'] == 1;
            $this->cache[$cacheKey] = [$isOnline, $currentTime];
            
            return $isOnline;
            
        } catch (\Exception $e) {
            return false;
        }
    }
    
    /**
     * Получает информацию о последней активности
     *
     * @param int $userId ID пользователя
     * @return array Информация об активности
     */
    public function getLastActivityInfo($userId) {
        try {
            $tableCheck = $this->db->fetch("SELECT 1 FROM information_schema.tables 
                                          WHERE table_schema = DATABASE() 
                                          AND table_name = 'user_activity'");
            
            if ($tableCheck) {
                $sql = "SELECT last_activity 
                        FROM user_activity 
                        WHERE user_id = ? 
                        LIMIT 1";
                
                $result = $this->db->fetch($sql, [$userId]);
                
                if ($result && !empty($result['last_activity'])) {
                    return $this->formatActivityTime($result['last_activity']);
                }
            }
            
            $sql = "SELECT last_login FROM users WHERE id = ? LIMIT 1";
            $result = $this->db->fetch($sql, [$userId]);
            
            if ($result && !empty($result['last_login'])) {
                return $this->formatActivityTime($result['last_login']);
            }
            
            return ['human' => 'никогда', 'days' => 0];
            
        } catch (\Exception $e) {
            return ['human' => 'неизвестно', 'days' => 0];
        }
    }
    
    /**
     * Форматирует время активности
     *
     * @param string $timestamp Временная метка
     * @return array Форматированное время
     */
    private function formatActivityTime($timestamp) {
        if (empty($timestamp)) {
            return ['human' => 'никогда', 'days' => 0];
        }
        
        $lastActivityTimestamp = strtotime($timestamp);
        $currentTimestamp = time();
        $secondsAgo = $currentTimestamp - $lastActivityTimestamp;
        
        if ($secondsAgo < 60) {
            $human = 'только что';
        } elseif ($secondsAgo < 3600) {
            $minutesAgo = floor($secondsAgo / 60);
            $human = $minutesAgo . ' мин назад';
        } elseif ($secondsAgo < 86400) {
            $hoursAgo = floor($secondsAgo / 3600);
            $human = $hoursAgo . ' ч назад';
        } else {
            $daysAgo = floor($secondsAgo / 86400);
            $human = $daysAgo . ' д назад';
        }
        
        return [
            'human' => $human,
            'days' => floor($secondsAgo / 86400)
        ];
    }
    
    /**
     * Очищает кэш
     */
    public function clearCache() {
        $this->cache = [];
    }
}