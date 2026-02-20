<?php

/**
 * Модель для работы с поиском и историей поисковых запросов
 * Предоставляет методы для поиска постов, управления историей запросов
 * и получения статистики по популярным запросам
 * 
 * @package Models
 */
class SearchModel {
    
    /** @var object Подключение к базе данных */
    private $db;
    
    /** @var SettingsModel Модель для работы с настройками */
    private $settings;
    
    /**
     * Конструктор модели
     * Инициализирует подключение к БД и модель настроек
     * 
     * @param object $db Подключение к базе данных
     * @param SettingsModel|null $settings Модель настроек (опционально)
     */
    public function __construct($db, SettingsModel $settings = null) {
        $this->db = $db;
        $this->settings = $settings ?? new SettingsModel($db);
    }
    
    /**
     * Поиск постов по ключевым словам в заголовке и содержимом
     * Возвращает результаты с пагинацией, информацией о категориях и тегах
     * 
     * @param string $query Поисковый запрос
     * @param int $page Номер страницы (по умолчанию 1)
     * @param int|null $perPage Количество результатов на странице (из настроек или по умолчанию 15)
     * @return array Массив с результатами поиска и информацией о пагинации
     */
    public function searchPosts($query, $page = 1, $perPage = null) {
        $perPage = $perPage ?? $this->settings->get('search')['search_per_page'] ?? 15;
        // Подготовка поискового запроса
        $searchQuery = '%' . $query . '%';
        
        // Подсчет общего количества результатов
        $countSql = "SELECT COUNT(*) as count FROM posts 
                     WHERE (title LIKE ? OR content LIKE ?) 
                     AND status = 'published'";
        
        $totalResults = $this->db->fetch($countSql, [$searchQuery, $searchQuery])['count'];
        
        // Расчет смещения для пагинации
        $offset = ($page - 1) * $perPage;
        
        // Получение результатов с информацией о категории и тегах
        $sql = "
            SELECT p.*, c.name as category_name, c.slug as category_slug,
                   GROUP_CONCAT(
                       CONCAT(t.name, ':::', t.slug) 
                       SEPARATOR '|||'
                   ) as tag_data
            FROM posts p 
            LEFT JOIN categories c ON p.category_id = c.id
            LEFT JOIN post_tags pt ON p.id = pt.post_id
            LEFT JOIN tags t ON pt.tag_id = t.id
            WHERE (p.title LIKE ? OR p.content LIKE ?) 
            AND p.status = 'published'
            GROUP BY p.id, c.name, c.slug
            ORDER BY p.created_at DESC
            LIMIT " . (int)$perPage . " 
            OFFSET " . (int)$offset;
        
        $posts = $this->db->fetchAll($sql, [$searchQuery, $searchQuery]);
        
        // Обрабатываем теги для каждого поста
        foreach ($posts as &$post) {
            $tags = [];
            if (!empty($post['tag_data'])) {
                $tagItems = explode('|||', $post['tag_data']);
                foreach ($tagItems as $tagItem) {
                    if (empty($tagItem)) continue;
                    $parts = explode(':::', $tagItem);
                    if (count($parts) >= 2) {
                        list($name, $slug) = $parts;
                        $tags[] = [
                            'name' => $name,
                            'slug' => $slug
                        ];
                    }
                }
            }
            $post['tags'] = $tags;
            unset($post['tag_data']);
        }
        
        return [
            'posts' => $posts,
            'total' => $totalResults,
            'pages' => ceil($totalResults / $perPage),
            'current_page' => $page,
            'query' => $query
        ];
    }
    
    /**
     * Получение популярных поисковых запросов (через теги)
     * 
     * @param int $limit Количество запросов (по умолчанию 6)
     * @return array Массив с популярными поисковыми запросами
     */
    public function getSuggestedSearches($limit = 6) {
        // Получаем популярные теги
        $sql = "SELECT t.name, t.slug, COUNT(pt.post_id) as post_count
                FROM tags t
                JOIN post_tags pt ON t.id = pt.tag_id
                GROUP BY t.id
                ORDER BY post_count DESC
                LIMIT " . (int)$limit;
        
        return $this->db->fetchAll($sql);
    }

    /**
     * Сохранение поискового запроса в историю
     * Если запрос уже существует, увеличивает счетчик, иначе создает новую запись
     * 
     * @param string $query Поисковый запрос
     * @return void
     */
    public function saveSearchQuery($query) {
        $query = trim($query);
        if (empty($query) || strlen($query) < 3) {
            return;
        }
        
        // Проверяем, существует ли уже такой запрос
        $existingQuery = $this->db->fetch(
            "SELECT id, count FROM search_queries WHERE query = ?", 
            [$query]
        );
        
        if ($existingQuery) {
            // Увеличиваем счетчик и обновляем время
            $this->db->query(
                "UPDATE search_queries SET count = count + 1, last_searched_at = NOW() WHERE id = ?", 
                [$existingQuery['id']]
            );
        } else {
            // Добавляем новый запрос
            $this->db->query(
                "INSERT INTO search_queries (query, count) VALUES (?, 1)", 
                [$query]
            );
        }
    }
    
    /**
     * Получение популярных поисковых запросов (по частоте)
     * 
     * @param int $limit Количество запросов (по умолчанию 10)
     * @return array Массив с популярными поисковыми запросами
     */
    public function getPopularSearchQueries($limit = 10) {
        $limit = (int)$limit; // Приводим к целому числу
        return $this->db->fetchAll(
            "SELECT id, query, count, last_searched_at, created_at 
             FROM search_queries 
             ORDER BY count DESC, last_searched_at DESC 
             LIMIT $limit"  // Используем число напрямую
        );
    }
    
    /**
     * Получение последних поисковых запросов (по времени)
     * 
     * @param int $limit Количество запросов (по умолчанию 10)
     * @return array Массив с последними поисковыми запросами
     */
    public function getRecentSearchQueries($limit = 10) {
        $limit = (int)$limit; // Приводим к целому числу
        return $this->db->fetchAll(
            "SELECT id, query, count, last_searched_at, created_at 
             FROM search_queries 
             ORDER BY last_searched_at DESC 
             LIMIT $limit"  // Используем число напрямую
        );
    }
    
    /**
     * Получение всех поисковых запросов с пагинацией
     * 
     * @param int $page Номер страницы (по умолчанию 1)
     * @param int $perPage Количество запросов на странице (по умолчанию 20)
     * @return array Массив с поисковыми запросами и информацией о пагинации
     */
    public function getAllSearchQueries($page = 1, $perPage = 20) {
        // Приводим параметры к целым числам
        $page = (int)$page;
        $perPage = (int)$perPage;
    
        // Подсчет общего количества запросов
        $totalQueries = $this->db->fetch(
            "SELECT COUNT(*) as count FROM search_queries"
        )['count'];
    
        // Расчет смещения для пагинации
        $offset = ($page - 1) * $perPage;
    
        // Формируем SQL-запрос с явным использованием чисел
        $sql = "SELECT id, query, count, last_searched_at, created_at 
                FROM search_queries 
                ORDER BY last_searched_at DESC 
                LIMIT $perPage OFFSET $offset";
    
        // Выполняем запрос
        $queries = $this->db->fetchAll($sql);
    
        return [
            'queries' => $queries,
            'total' => $totalQueries,
            'pages' => ceil($totalQueries / $perPage),
            'current_page' => $page
        ];
    }
    
    /**
     * Удаление конкретного поискового запроса по ID
     * 
     * @param int $id ID запроса
     * @return bool Результат операции
     */
    public function deleteSearchQuery($id) {
        return $this->db->query(
            "DELETE FROM search_queries WHERE id = ?", 
            [$id]
        );
    }
    
    /**
     * Очистка всей истории поисковых запросов
     * 
     * @return bool Результат операции
     */
    public function clearSearchHistory() {
        return $this->db->query("TRUNCATE TABLE search_queries");
    }
}