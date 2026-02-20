<?php

/**
 * Модель комментариев
 * Обеспечивает взаимодействие с таблицей комментариев в базе данных
 * Включает CRUD-операции, пагинацию, рекурсивное удаление и работу с древовидной структурой комментариев
 * 
 * @package models
 */
class CommentModel {
    
    /**
     * @var Database Объект подключения к базе данных
     */
    private $db;
    
    /**
     * Конструктор модели комментариев
     * Инициализирует подключение к базе данных
     *
     * @param Database $db Объект подключения к базе данных
     */
    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Добавление нового комментария
     * Создает запись комментария в базе данных с указанием автора, содержимого и статуса
     *
     * @param array $data Массив данных комментария:
     * - post_id: ID поста (обязательно)
     * - user_id: ID пользователя (опционально)
     * - parent_id: ID родительского комментария (опционально)
     * - author_name: имя автора (обязательно для неавторизованных)
     * - author_email: email автора (опционально)
     * - content: текст комментария (обязательно)
     * - status: статус ('pending', 'approved', 'spam')
     * @return bool Результат выполнения запроса
     * @throws Exception При ошибке вставки в базу данных
     */
    public function addComment($data) {
        try {
            $sql = "INSERT INTO comments (post_id, user_id, parent_id, author_name, author_email, content, status) 
 VALUES (:post_id, :user_id, :parent_id, :author_name, :author_email, :content, :status)";
            
            $result = $this->db->query($sql, [
                ':post_id' => $data['post_id'],
                ':user_id' => $data['user_id'],
                ':parent_id' => $data['parent_id'],
                ':author_name' => $data['author_name'],
                ':author_email' => $data['author_email'],
                ':content' => $data['content'],
                ':status' => $data['status']
            ]);
            
            return $result;
            
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Получение комментариев для поста с учетом прав доступа
     * Возвращает комментарии к посту с фильтрацией по статусу и правам пользователя
     *
     * @param int $postId ID поста
     * @param bool $includePending Включать ли комментарии на модерации
     * @return array Массив комментариев с информацией об авторах
     */
    public function getCommentsByPost($postId, $includePending = false) {
        // Проверка прав администратора через Auth
        $isAdmin = Auth::isAdmin();
        $currentUserId = Auth::getUserId();
        
        // Проверка специальных прав через AuthHelper
        $canSeeAllPending = false;
        if (class_exists('AuthHelper')) {
            $canSeeAllPending = AuthHelper::canViewAllComments();
        }
        
        $sql = "SELECT c.*, 
 u.username as author_username,
 u.display_name as author_display_name,
 u.avatar as author_avatar
                FROM comments c 
                LEFT JOIN users u ON c.user_id = u.id 
                WHERE c.post_id = ?";
        
        $params = [$postId];
        
        // Логика фильтрации комментариев по статусу и правам
        if (!$includePending && !$canSeeAllPending) {
            // Проверяем, авторизован ли пользователь
            if ($currentUserId) {
                // Показываем approved комментарии + pending комментарии текущего пользователя
                $sql .= " AND (c.status = 'approved' OR (c.status = 'pending' AND c.user_id = ?))";
                $params[] = $currentUserId;
            } else {
                // Для неавторизованных - только approved
                $sql .= " AND c.status = 'approved'";
            }
        } else if (!$includePending) {
            // Для админов и пользователей с правами - показываем все кроме спама
            $sql .= " AND c.status != 'spam'";
        }
        
        // Сортировка по parent_id и дате для удобного построения дерева
        $sql .= " ORDER BY 
 COALESCE(parent_id, 0) ASC,
 created_at ASC";
        
        return $this->db->fetchAll($sql, $params);
    }

    /**
     * Получение комментария по ID
     * Возвращает данные одного комментария по его идентификатору
     *
     * @param int $id ID комментария
     * @return array|null Данные комментария или null если не найден
     */
    public function getCommentById($id) {
        $sql = "SELECT * FROM comments WHERE id = :id";
        return $this->db->fetch($sql, [':id' => $id]);
    }

    /**
     * Удаление комментария
     * Удаляет один комментарий из базы данных
     *
     * @param int $id ID удаляемого комментария
     * @return bool Результат выполнения запроса
     */
    public function deleteComment($id) {
        $sql = "DELETE FROM comments WHERE id = :id";
        return $this->db->query($sql, [':id' => $id]);
    }

    /**
     * Получение вложенных комментариев
     * Возвращает прямые ответы на указанный комментарий
     *
     * @param int $parentId ID родительского комментария
     * @return array Массив дочерних комментариев
     */
    public function getReplies($parentId) {
        $sql = "SELECT c.*, u.username as author_username 
                FROM comments c 
                LEFT JOIN users u ON c.user_id = u.id 
                WHERE c.parent_id = :parent_id AND c.status = 'approved' 
                ORDER BY c.created_at ASC";
        return $this->db->fetchAll($sql, [':parent_id' => $parentId]);
    }

    /**
     * Получение всех комментариев с пагинацией
     * Возвращает комментарии с информацией о постах и авторах с разбивкой на страницы
     *
     * @param int $page Текущая страница (начинается с 1)
     * @param int $perPage Количество комментариев на странице
     * @return array Массив с данными:
     *               - comments: список комментариев
     *               - total: общее количество комментариев
     *               - pages: общее количество страниц
     *               - current_page: текущая страница
     */
    public function getAllComments($page = 1, $perPage = 20) {
        // Приведение параметров к целым числам
        $page = (int)$page;
        $perPage = (int)$perPage;
        
        // Подсчет общего количества комментариев
        $totalComments = $this->db->fetch(
            "SELECT COUNT(*) as count FROM comments"
        )['count'];
        
        // Расчет смещения для пагинации
        $offset = ($page - 1) * $perPage;
        
        // Формирование SQL-запроса с LIMIT и OFFSET как числами
        $sql = "SELECT c.*, p.title as post_title, u.username as author_username 
                FROM comments c 
                LEFT JOIN posts p ON c.post_id = p.id 
                LEFT JOIN users u ON c.user_id = u.id 
                ORDER BY c.created_at DESC 
                LIMIT " . (int)$perPage . " OFFSET " . (int)$offset;
        
        // Выполнение запроса без параметров
        $comments = $this->db->fetchAll($sql);
        
        return [
            'comments' => $comments,
            'total' => $totalComments,
            'pages' => ceil($totalComments / $perPage),
            'current_page' => $page
        ];
    }

    /**
     * Получение общего количества комментариев
     * Возвращает общее число комментариев в системе
     *
     * @return int Количество комментариев
     */
    public function getTotalComments() {
        return $this->db->fetch("SELECT COUNT(*) as count FROM comments")['count'];
    }

    /**
     * Одобрение комментария
     * Изменяет статус комментария на 'approved'
     *
     * @param int $id ID комментария
     * @return bool Результат выполнения запроса
     * @throws Exception Если комментарий не найден или уже одобрен
     */
    public function approveComment($id) {
        try {
            $id = (int)$id;
            $sql = "UPDATE comments SET status = 'approved' WHERE id = :id";
            $result = $this->db->query($sql, [':id' => $id]);
            
            if ($result->rowCount() === 0) {
                throw new \Exception('Комментарий не найден или уже одобрен');
            }
            
            return true;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Редактирование комментария
     * Обновляет данные комментария с возможностью изменения содержимого, статуса и информации об авторе
     *
     * @param int $id ID редактируемого комментария
     * @param array $data Массив данных для обновления:
     * - content: новый текст комментария
     * - status: новый статус
     * - author_name: новое имя автора
     * - author_email: новый email автора
     * @return bool Результат выполнения запроса
     * @throws Exception Если нет данных для обновления или комментарий не найден
     */
    public function updateComment($id, $data) {
        try {
            $id = (int)$id;
            
            // Подготовка SQL с учетом всех возможных полей
            $fields = [];
            $params = [];
            
            if (isset($data['content'])) {
                $fields[] = 'content = :content';
                $params[':content'] = $data['content'];
            }
            
            if (isset($data['status'])) {
                $fields[] = 'status = :status';
                $params[':status'] = $data['status'];
            }
            
            if (isset($data['author_name'])) {
                $fields[] = 'author_name = :author_name';
                $params[':author_name'] = $data['author_name'];
            }
            
            if (isset($data['author_email'])) {
                $fields[] = 'author_email = :author_email';
                $params[':author_email'] = $data['author_email'];
            }
            
            if (empty($fields)) {
                throw new \Exception('Нет данных для обновления');
            }
            
            $params[':id'] = $id;
            
            $sql = "UPDATE comments SET " . implode(', ', $fields) . ", updated_at = CURRENT_TIMESTAMP WHERE id = :id";
            
            $result = $this->db->query($sql, $params);
            
            if ($result->rowCount() === 0) {
                throw new \Exception('Комментарий не найден или данные не изменились');
            }
            
            return true;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Получение общего количества комментариев для поста
     * Подсчитывает комментарии поста с учетом статуса и прав пользователя
     *
     * @param int $postId ID поста
     * @param bool $includePending Включать ли комментарии на модерации
     * @return int Количество комментариев
     */
    public function getTotalCommentsByPost($postId, $includePending = false) {
        $sql = "SELECT COUNT(*) as count FROM comments WHERE post_id = ?";
        $params = [$postId];
        
        if (!$includePending) {
            $currentUserId = Auth::getUserId();
            
            if ($currentUserId) {
                $sql .= " AND (status = 'approved' OR (status = 'pending' AND user_id = ?))";
                $params[] = $currentUserId;
            } else {
                $sql .= " AND status = 'approved'";
            }
        } else {
            $sql .= " AND (status = 'approved' OR status = 'pending')";
        }
        
        return $this->db->fetch($sql, $params)['count'];
    }

    /**
     * Получение всех дочерних комментариев (рекурсивно)
     * Возвращает ID всех вложенных комментариев для указанного родительского
     *
     * @param int $parentId ID родительского комментария
     * @return array Массив ID дочерних комментариев
     */
    public function getChildCommentsRecursive($parentId) {
        $sql = "SELECT id FROM comments WHERE parent_id = ?";
        $childIds = $this->db->fetchAll($sql, [$parentId]);
        
        $allChildIds = [];
        foreach ($childIds as $child) {
            $allChildIds[] = $child['id'];
            // Рекурсивное получение вложенных комментариев
            $nestedIds = $this->getChildCommentsRecursive($child['id']);
            $allChildIds = array_merge($allChildIds, $nestedIds);
        }
        
        return $allChildIds;
    }

    /**
     * Удаление комментария и всех его дочерних комментариев
     * Рекурсивное удаление всей ветки комментариев
     *
     * @param int $id ID корневого комментария
     * @return bool Результат выполнения запроса
     * @throws Exception При ошибке удаления
     */
    public function deleteCommentRecursive($id) {
        try {
            // Получение всех дочерних ID
            $childIds = $this->getChildCommentsRecursive($id);
            $allIds = array_merge([$id], $childIds);
            
            // Удаление всех комментариев одним запросом
            $placeholders = implode(',', array_fill(0, count($allIds), '?'));
            $sql = "DELETE FROM comments WHERE id IN ($placeholders)";
            
            return $this->db->query($sql, $allIds);
            
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Получение групп пользователя из комментария
     * Определяет группы пользователя на основе ID автора комментария
     *
     * @param array $comment Данные комментария
     * @return array Массив групп пользователя
     */
    public function getUserGroupsFromComment($comment) {
        $userId = $comment['user_id'] ?? null;
        if (!$userId) {
            return [];
        }
        
        try {
            // Получение групп пользователя через UserModel
            $groupIds = $this->userModel->getUserGroups($userId);
            
            if (empty($groupIds)) {
                return [];
            }
            
            // Получение названий групп
            $groups = [];
            foreach ($groupIds as $groupId) {
                $group = $this->userModel->getGroupById($groupId);
                if ($group) {
 $groups[] = [
     'id' => $group['id'],
     'name' => $group['name']
 ];
                }
            }
            
            return $groups;
            
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Получение данных комментария с информацией о пользователе, правами и группами
     * Формирует расширенный набор данных комментария для отображения
     *
     * @param array $comment Базовые данные комментария
     * @return array Расширенные данные комментария
     */
    public function getCommentWithUserData($comment) {
        // Приведение parent_id к целому числу или null
        $parentId = isset($comment['parent_id']) && $comment['parent_id'] !== null 
            ? (int)$comment['parent_id'] 
            : null;
        
        $userId = isset($comment['user_id']) ? (int)$comment['user_id'] : null;
        $currentUserId = Auth::getUserId();
        
        // Определение прав для этого комментария
        $canEdit = AuthHelper::canEditComment($userId);
        $canDelete = AuthHelper::canDeleteComment($userId);
        $canReply = AuthHelper::canAddComment();
        $isOwnComment = $currentUserId && $userId && $currentUserId == $userId;
        
        // Получение групп пользователя
        $userGroups = $this->getUserGroupsFromComment($comment);
        
        // Проверка, является ли пользователь администратором
        $isAdmin = false;
        if ($userId) {
            try {
                $user = $this->userModel->getById($userId);
                $isAdmin = $user && (!empty($user['is_admin']) || $user['role'] === 'admin');
            } catch (Exception $e) {
                // Ошибка при проверке прав игнорируется
            }
        }
        
        return [
            'id' => (int)$comment['id'],
            'post_id' => (int)$comment['post_id'],
            'user_id' => $userId,
            'parent_id' => $parentId,
            'content' => $comment['content'],
            'status' => $comment['status'],
            'created_at' => $comment['created_at'],
            'updated_at' => $comment['updated_at'],
            'author_name' => $this->getUserDisplayName($comment),
            'author_avatar' => $this->getUserAvatarFromComment($comment),
            'is_pending' => $comment['status'] === 'pending',
            'is_own_comment' => $isOwnComment,
            'is_admin' => $isAdmin,
            'user_groups' => $userGroups,
            'can_edit' => $canEdit,
            'can_delete' => $canDelete,
            'can_reply' => $canReply
        ];
    }
    
}