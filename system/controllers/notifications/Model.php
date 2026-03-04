<?php

/**
 * Модель для работы с уведомлениями в базе данных
 * Предоставляет методы для создания, получения и управления уведомлениями пользователей
 * 
 * @package Models
 */
class NotificationModel implements ModelAPI {

    use APIAware;

    protected $allowedAPIMethods = [
        'getUserNotifications',
        'getUserNotificationsWithDetails',
        'getUnreadCount',
        'getStats',
        'markAsRead',
        'markAllAsRead',
        'delete',
        'clearRead'
    ];
    
    /** @var object Подключение к базе данных */
    private $db;

    /**
     * Конструктор модели
     * 
     * @param object $db Подключение к базе данных
     */
    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Создает таблицу уведомлений в базе данных, если она не существует
     * Вызывается при установке или обновлении системы
     * 
     * @return bool Результат выполнения запроса
     */
    public function createTable() {
        $sql = "CREATE TABLE IF NOT EXISTS notifications (
            id INT AUTO_INCREMENT PRIMARY KEY,
            type VARCHAR(50) NOT NULL,
            title VARCHAR(255) NOT NULL,
            message TEXT,
            data TEXT,
            is_read BOOLEAN DEFAULT FALSE,
            user_id INT,
            created_by INT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            read_at TIMESTAMP NULL,
            INDEX idx_user_id (user_id),
            INDEX idx_is_read (is_read),
            INDEX idx_type (type),
            INDEX idx_created_at (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        
        return $this->db->query($sql);
    }

    /**
     * Добавляет новое уведомление в базу данных
     * 
     * @param array $data Данные уведомления:
     *                    - type (string): Тип уведомления
     *                    - title (string): Заголовок
     *                    - message (string): Текст сообщения
     *                    - data (array): Дополнительные данные (опционально)
     *                    - user_id (int): ID получателя (опционально)
     *                    - created_by (int): ID создателя (опционально)
     * @return bool|int Результат выполнения запроса
     */
    public function add($data) {
        $sql = "INSERT INTO notifications (type, title, message, data, user_id, created_by) 
                VALUES (:type, :title, :message, :data, :user_id, :created_by)";
        
        return $this->db->query($sql, [
            ':type' => $data['type'],
            ':title' => $data['title'],
            ':message' => $data['message'],
            ':data' => json_encode($data['data'] ?? []),
            ':user_id' => $data['user_id'] ?? null,
            ':created_by' => $data['created_by'] ?? null
        ]);
    }

    /**
     * Получает список всех администраторов системы
     * Внутренний вспомогательный метод
     * 
     * @return array Массив пользователей с правами администратора
     */
    private function getAdminUsers() {
        $sql = "SELECT id FROM users WHERE is_admin = 1 OR role = 'admin'";
        return $this->db->fetchAll($sql);
    }

    /**
     * Получает уведомления для конкретного пользователя
     * 
     * @param int $userId ID пользователя
     * @param int $limit Максимальное количество записей (по умолчанию 10)
     * @param int $offset Смещение для пагинации (по умолчанию 0)
     * @param bool $unreadOnly Только непрочитанные (по умолчанию false)
     * @return array Массив уведомлений
     */
    public function getUserNotifications($userId, $limit = 10, $offset = 0, $unreadOnly = false) {
        $sql = "SELECT n.*, u.username as created_by_username
                FROM notifications n
                LEFT JOIN users u ON n.created_by = u.id
                WHERE n.user_id = :user_id";
        
        $params = [':user_id' => $userId];
        
        if ($unreadOnly) {
            $sql .= " AND n.is_read = 0";
        }
        
        $sql .= " ORDER BY n.created_at DESC 
                  LIMIT :limit OFFSET :offset";
        
        $params[':limit'] = $limit;
        $params[':offset'] = $offset;
        
        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Получает количество непрочитанных уведомлений пользователя
     * 
     * @param int $userId ID пользователя
     * @return int Количество непрочитанных уведомлений
     */
    public function getUnreadCount($userId) {
        $sql = "SELECT COUNT(*) as count 
                FROM notifications 
                WHERE user_id = :user_id AND is_read = 0";
        
        $result = $this->db->fetch($sql, [':user_id' => $userId]);
        return $result['count'] ?? 0;
    }

    /**
     * Отмечает конкретное уведомление как прочитанное
     * 
     * @param int $id ID уведомления
     * @param int $userId ID пользователя (для проверки владельца)
     * @return bool Результат выполнения запроса
     */
    public function markAsRead($id, $userId) {
        $sql = "UPDATE notifications 
                SET is_read = 1, read_at = CURRENT_TIMESTAMP 
                WHERE id = :id AND user_id = :user_id";
        
        return $this->db->query($sql, [
            ':id' => $id,
            ':user_id' => $userId
        ]);
    }

    /**
     * Отмечает все уведомления пользователя как прочитанные
     * 
     * @param int $userId ID пользователя
     * @return bool Результат выполнения запроса
     */
    public function markAllAsRead($userId) {
        $sql = "UPDATE notifications 
                SET is_read = 1, read_at = CURRENT_TIMESTAMP 
                WHERE user_id = :user_id AND is_read = 0";
        
        return $this->db->query($sql, [':user_id' => $userId]);
    }

    /**
     * Удаляет конкретное уведомление
     * 
     * @param int $id ID уведомления
     * @param int $userId ID пользователя (для проверки владельца)
     * @return bool Результат выполнения запроса
     */
    public function delete($id, $userId) {
        $sql = "DELETE FROM notifications WHERE id = :id AND user_id = :user_id";
        return $this->db->query($sql, [
            ':id' => $id,
            ':user_id' => $userId
        ]);
    }

    /**
     * Удаляет все прочитанные уведомления пользователя
     * 
     * @param int $userId ID пользователя
     * @return bool Результат выполнения запроса
     */
    public function clearRead($userId) {
        $sql = "DELETE FROM notifications WHERE user_id = :user_id AND is_read = 1";
        return $this->db->query($sql, [':user_id' => $userId]);
    }

    /**
     * Получает статистику уведомлений для пользователя
     * 
     * @param int $userId ID пользователя
     * @return array Статистика с полями:
     *               - total: Общее количество
     *               - unread: Непрочитанные
     *               - read_count: Прочитанные
     *               - types_count: Количество различных типов
     */
    public function getStats($userId) {
        $sql = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN is_read = 0 THEN 1 ELSE 0 END) as unread,
                    SUM(CASE WHEN is_read = 1 THEN 1 ELSE 0 END) as read_count,
                    COUNT(DISTINCT type) as types_count
                FROM notifications 
                WHERE user_id = :user_id";
        
        return $this->db->fetch($sql, [':user_id' => $userId]);
    }

    /**
     * Добавляет уведомление о новом комментарии с расширенными данными
     * Автоматически собирает информацию о комментарии, посте и авторе
     * Отправляет уведомления всем администраторам
     * 
     * @param int $commentId ID комментария
     * @param array $commentData Дополнительные данные комментария (опционально)
     * @return bool true при успешном добавлении, false при ошибке
     */
    public function addNewCommentNotification($commentId, $commentData = []) {
        try {
            // Получение полных данных о комментарии
            $commentModel = new \CommentModel($this->db);
            $comment = $commentModel->getCommentById($commentId);
            
            if (!$comment) {
                return false;
            }
            
            // Получение данных о посте
            $postModel = new \PostModel($this->db);
            $post = $postModel->getById($comment['post_id']);
            
            // Получение информации об авторе
            $authorName = $comment['author_name'] ?? 'Аноним';
            if (!empty($comment['user_id'])) {
                $userModel = new \UserModel($this->db);
                $user = $userModel->getById($comment['user_id']);
                if ($user) {
                    $authorName = $user['display_name'] ?? $user['username'] ?? $authorName;
                }
            }
            
            // Формирование превью текста комментария
            $contentPreview = $comment['content'];
            if (mb_strlen($contentPreview) > 150) {
                $contentPreview = mb_substr($contentPreview, 0, 150) . '...';
            }
            
            // Формирование данных для уведомления
            $data = [
                'type' => 'new_comment',
                'title' => 'Новый комментарий на модерацию',
                'message' => "{$authorName} оставил комментарий",
                'data' => [
                    'comment_id' => $commentId,
                    'post_id' => $comment['post_id'],
                    'post_title' => $post['title'] ?? 'Неизвестный пост',
                    'post_slug' => $post['slug'] ?? '',
                    'author_name' => $authorName,
                    'author_email' => $comment['author_email'] ?? null,
                    'content_preview' => $contentPreview,
                    'content_full' => $comment['content'],
                    'created_at' => $comment['created_at']
                ],
                'created_by' => $comment['user_id'] ?? null
            ];
            
            // Отправка уведомлений всем администраторам
            $admins = $this->getAdminUsers();
            foreach ($admins as $admin) {
                $data['user_id'] = $admin['id'];
                $this->add($data);
            }
            
            return true;
            
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Получает уведомления пользователя с расширенными данными
     * Дополняет уведомления информацией из связанных моделей
     * 
     * @param int $userId ID пользователя
     * @param int $limit Максимальное количество записей (по умолчанию 10)
     * @param int $offset Смещение для пагинации (по умолчанию 0)
     * @param bool $unreadOnly Только непрочитанные (по умолчанию false)
     * @return array Массив уведомлений с дополнительными данными
     */
    public function getUserNotificationsWithDetails($userId, $limit = 10, $offset = 0, $unreadOnly = false) {
        $sql = "SELECT n.*, u.username as created_by_username,
                       u.avatar as created_by_avatar,
                       u.display_name as created_by_display_name
                FROM notifications n
                LEFT JOIN users u ON n.created_by = u.id
                WHERE n.user_id = :user_id";
        
        $params = [':user_id' => $userId];
        
        if ($unreadOnly) {
            $sql .= " AND n.is_read = 0";
        }
        
        $sql .= " ORDER BY n.created_at DESC 
                  LIMIT :limit OFFSET :offset";
        
        $params[':limit'] = $limit;
        $params[':offset'] = $offset;
        
        $notifications = $this->db->fetchAll($sql, $params);
        
        // Обработка дополнительных данных для каждого уведомления
        foreach ($notifications as &$notification) {
            $data = json_decode($notification['data'] ?? '{}', true);
            
            // Для комментариев получаем дополнительные данные
            if ($notification['type'] === 'new_comment' && !empty($data['post_id'])) {
                try {
                    $postModel = new \PostModel($this->db);
                    $post = $postModel->getById($data['post_id']);
                    
                    if ($post) {
                        $data['post_url'] = BASE_URL . '/post/' . $post['slug'];
                        $data['post_title'] = $post['title'];
                        
                        // Если нет предварительного просмотра контента
                        if (empty($data['content_preview']) && !empty($data['content_full'])) {
                            $content = $data['content_full'];
                            $data['content_preview'] = mb_strlen($content) > 150 
                                ? mb_substr($content, 0, 150) . '...' 
                                : $content;
                        }
                    }
                } catch (\Exception $e) {
                }
            }
            
            $notification['data'] = json_encode($data);
        }
        
        return $notifications;
    }
}