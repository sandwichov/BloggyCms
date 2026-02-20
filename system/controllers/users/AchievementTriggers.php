<?php

/**
 * Класс-триггер для проверки и разблокировки достижений пользователей
 * Вызывается при различных событиях (регистрация, лайки, закладки, комментарии)
 * и проверяет выполнение условий для автоматических ачивок
 * 
 * @package Models
 */
class AchievementTriggers {
    
    /** @var object Подключение к базе данных */
    private $db;
    
    /**
     * Конструктор класса
     * Инициализирует подключение к базе данных
     * 
     * @param object $db Подключение к базе данных
     */
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * Вызывать после успешной регистрации пользователя
     * Проверяет доступные ачивки для нового пользователя
     * 
     * @param int $userId ID пользователя
     * @return bool true при успешной проверке (ошибки игнорируются)
     */
    public function onUserRegistered($userId) {
        try {
            $this->checkAchievementsForUser($userId);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Вызывать после добавления лайка
     * Проверяет доступные ачивки для пользователя
     * 
     * @param int $userId ID пользователя
     * @param int|null $postId ID поста (не используется)
     * @return bool true при успешной проверке (ошибки игнорируются)
     */
    public function onPostLiked($userId, $postId = null) {
        try {
            $this->checkAchievementsForUser($userId);
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Вызывать после добавления в закладки
     * Проверяет доступные ачивки и ведет дополнительную отладку
     * 
     * @param int $userId ID пользователя
     * @param int|null $postId ID поста (не используется)
     * @return bool true при успешной проверке (ошибки игнорируются)
     */
    public function onPostBookmarked($userId, $postId = null) {
        try {
            // Проверка количества закладок у пользователя
            $result = $this->db->fetch(
                "SELECT COUNT(*) as count FROM bookmarks WHERE user_id = ?",
                [$userId]
            );
            $currentBookmarks = (int)($result['count'] ?? 0);
            
            $this->checkAchievementsForUser($userId);
            
            // Дополнительная отладка: проверка конкретной ачивки (id = 14)
            $achievement14 = $this->db->fetch(
                "SELECT * FROM user_achievements WHERE id = 14"
            );
            if ($achievement14) {
                $conditions = $this->db->fetchAll(
                    "SELECT * FROM achievement_conditions WHERE achievement_id = 14",
                    []
                );
                
                // Проверка выполнения условий для отладки
                foreach ($conditions as $condition) {
                    $userValue = $this->getUserStatValue($userId, $condition['condition_type']);
                    $isMet = $this->evaluateCondition($userValue, $condition['operator'], $condition['value']);
                }
            }
            
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
    
    /**
     * Проверяет все автоматические ачивки для пользователя
     * 
     * @param int $userId ID пользователя
     * @return bool Результат проверки (ошибки игнорируются)
     */
    private function checkAchievementsForUser($userId) {
        // Получаем все активные автоматические ачивки
        $achievements = $this->db->fetchAll(
            "SELECT ua.* FROM user_achievements ua 
             WHERE ua.type = 'auto' AND ua.is_active = 1"
        );
        
        foreach ($achievements as $achievement) {
            $this->checkSingleAchievement($userId, $achievement);
        }
    }
    
    /**
     * Проверяет одну ачивку для пользователя
     * 
     * @param int $userId ID пользователя
     * @param array $achievement Данные ачивки
     * @return bool true если все условия выполнены
     */
    private function checkSingleAchievement($userId, $achievement) {
        // Получение условий ачивки
        $conditions = $this->db->fetchAll(
            "SELECT * FROM achievement_conditions WHERE achievement_id = ?",
            [$achievement['id']]
        );
        
        if (empty($conditions)) {
            return false;
        }
        
        $allConditionsMet = true;
        
        foreach ($conditions as $condition) {
            $userValue = $this->getUserStatValue($userId, $condition['condition_type']);
            
            if (!$this->evaluateCondition($userValue, $condition['operator'], $condition['value'])) {
                $allConditionsMet = false;
                break;
            }
        }
        
        if ($allConditionsMet) {
            $this->unlockAchievement($userId, $achievement['id']);
        }
        
        return $allConditionsMet;
    }
    
    /**
     * Получает значение статистики пользователя по типу
     * 
     * @param int $userId ID пользователя
     * @param string $statType Тип статистики
     * @return int|float Значение статистики
     */
    private function getUserStatValue($userId, $statType) {
        switch ($statType) {
            case 'registration_days':
                $user = $this->db->fetch(
                    "SELECT created_at FROM users WHERE id = ?",
                    [$userId]
                );
                
                if ($user && $user['created_at']) {
                    $regDate = new DateTime($user['created_at']);
                    $now = new DateTime();
                    $interval = $regDate->diff($now);
                    $days = (int)$interval->days;
                    return $days;
                }
                return 0;
                
            case 'comments_count':
                $result = $this->db->fetch(
                    "SELECT COUNT(*) as count FROM comments WHERE user_id = ? AND status = 'approved'",
                    [$userId]
                );
                $count = (int)($result['count'] ?? 0);
                return $count;
                
            case 'likes_count':
                $result = $this->db->fetch(
                    "SELECT COUNT(*) as count FROM post_likes WHERE user_id = ?",
                    [$userId]
                );
                $count = (int)($result['count'] ?? 0);
                return $count;
                
            case 'bookmarks_count':
                $result = $this->db->fetch(
                    "SELECT COUNT(*) as count FROM bookmarks WHERE user_id = ?",
                    [$userId]
                );
                $count = (int)($result['count'] ?? 0);
                return $count;
                
            case 'login_days':
                $result = $this->db->fetch(
                    "SELECT COUNT(DISTINCT DATE(last_login)) as count FROM users WHERE id = ? AND last_login IS NOT NULL",
                    [$userId]
                );
                $count = (int)($result['count'] ?? 0);
                return $count;
                
            default:
                return 0;
        }
    }
    
    /**
     * Проверяет выполнение условия сравнения
     * 
     * @param mixed $value Значение пользователя
     * @param string $operator Оператор сравнения (>, <, =, >=, <=, !=)
     * @param mixed $expected Ожидаемое значение
     * @return bool true если условие выполнено
     */
    private function evaluateCondition($value, $operator, $expected) {
        // Преобразование expected к числу если это число
        if (is_numeric($expected)) {
            $expected = (float)$expected;
            $value = (float)$value;
        }
        
        switch ($operator) {
            case '>': return $value > $expected;
            case '<': return $value < $expected;
            case '=': return $value == $expected;
            case '>=': return $value >= $expected;
            case '<=': return $value <= $expected;
            case '!=': return $value != $expected;
            default: return false;
        }
    }
    
    /**
     * Разблокирует ачивку для пользователя
     * 
     * @param int $userId ID пользователя
     * @param int $achievementId ID ачивки
     * @return bool true при успешной разблокировке
     */
    private function unlockAchievement($userId, $achievementId) {
        // Проверка, не разблокирована ли уже
        $exists = $this->db->fetch(
            "SELECT id FROM user_achievements_data 
             WHERE user_id = ? AND achievement_id = ? AND is_unlocked = 1",
            [$userId, $achievementId]
        );
        
        if ($exists) {
            return true; // Уже разблокировано
        }
        
        // Поиск существующей записи прогресса
        $data = $this->db->fetch(
            "SELECT id FROM user_achievements_data 
             WHERE user_id = ? AND achievement_id = ?",
            [$userId, $achievementId]
        );
        
        if ($data) {
            // Обновление существующей записи
            $this->db->query(
                "UPDATE user_achievements_data 
                 SET is_unlocked = 1, unlocked_at = NOW(), updated_at = NOW()
                 WHERE user_id = ? AND achievement_id = ?",
                [$userId, $achievementId]
            );
        } else {
            // Создание новой записи
            $this->db->query(
                "INSERT INTO user_achievements_data (user_id, achievement_id, progress, max_value, is_unlocked, unlocked_at)
                 VALUES (?, ?, 100, 100, 1, NOW())",
                [$userId, $achievementId]
            );
        }
        
        return true;
    }
    
    /**
     * Вызывать после создания комментария
     * 
     * @param int $userId ID пользователя
     * @return bool Результат проверки
     */
    public function onCommentCreated($userId) {
        return $this->checkAchievementsForUser($userId);
    }
    
    /**
     * Вызывать после успешного входа пользователя
     * 
     * @param int $userId ID пользователя
     * @return bool Результат проверки
     */
    public function onUserLogin($userId) {
        return $this->checkAchievementsForUser($userId);
    }
}