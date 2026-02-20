<?php

/**
 * Модель для работы со страницами в базе данных
 * Предоставляет методы для CRUD-операций со страницами, управления URL-адресами (slug)
 * и интеграции с блочной системой контента
 * 
 * @package Models
 */
class PageModel {
    
    /** @var object Подключение к базе данных */
    private $db;
    
    /** @var PostBlockModel Модель для работы с блоками контента */
    private $postBlockModel;
    
    /**
     * Конструктор модели
     * Инициализирует подключение к БД и модель блоков
     * 
     * @param object $db Подключение к базе данных
     */
    public function __construct($db) {
        $this->db = $db;
        $this->postBlockModel = new PostBlockModel($db);
    }
    
    /**
     * Получает список всех страниц
     * 
     * @return array Массив всех страниц, отсортированных по заголовку
     */
    public function getAll() {
        return $this->db->fetchAll("SELECT * FROM pages ORDER BY title");
    }
    
    /**
     * Получает страницу по её ID
     * 
     * @param int $id ID страницы
     * @return array|null Данные страницы или null, если страница не найдена
     */
    public function getById($id) {
        return $this->db->fetch("SELECT * FROM pages WHERE id = ?", [$id]);
    }
    
    /**
     * Получает страницу по её URL-адресу (slug)
     * 
     * @param string $slug URL-адрес страницы
     * @return array|null Данные страницы или null, если страница не найдена
     */
    public function getBySlug($slug) {
        return $this->db->fetch("SELECT * FROM pages WHERE slug = ?", [$slug]);
    }
    
    /**
     * Создает новую страницу
     * Автоматически генерирует уникальный URL-адрес на основе заголовка
     * 
     * @param array $data Данные страницы:
     *                    - title (string): Заголовок страницы
     *                    - status (string): Статус ('draft' или 'published')
     * @return int ID созданной страницы
     */
    public function create($data) {
        // Генерация уникального slug из заголовка
        $slug = $this->createUniqueSlug($data['title']);
        
        $sql = "INSERT INTO pages (title, slug, status) VALUES (?, ?, ?)";
        $this->db->query($sql, [
            $data['title'],
            $slug,
            $data['status'] ?? 'draft'
        ]);
        
        return $this->db->lastInsertId();
    }
    
    /**
     * Обновляет существующую страницу
     * При необходимости обновляет URL-адрес
     * 
     * @param int $id ID страницы
     * @param array $data Данные для обновления
     * @return bool Результат выполнения запроса
     */
    public function update($id, $data) {
        // Генерация уникального slug при обновлении заголовка
        $slug = $this->createUniqueSlug($data['title'], $id);
        
        $sql = "UPDATE pages SET title = ?, slug = ?, status = ? WHERE id = ?";
        return $this->db->query($sql, [
            $data['title'],
            $slug,
            $data['status'] ?? 'draft',
            $id
        ]);
    }
    
    /**
     * Удаляет страницу и все связанные с ней блоки
     * 
     * @param int $id ID страницы
     * @return bool Результат выполнения запроса
     */
    public function delete($id) {
        // Каскадное удаление: сначала блоки страницы, затем саму страницу
        $this->postBlockModel->deleteByPage($id);
        return $this->db->query("DELETE FROM pages WHERE id = ?", [$id]);
    }
    
    /**
     * Создает URL-адрес (slug) из заголовка
     * Транслитерирует кириллицу, удаляет спецсимволы, заменяет пробелы на дефисы
     * 
     * @param string $title Заголовок страницы
     * @return string Сгенерированный URL-адрес
     */
    private function createSlug($title) {
        // Приведение к нижнему регистру
        $slug = mb_strtolower($title, 'UTF-8');
        
        // Транслитерация кириллицы в латиницу
        $slug = $this->transliterate($slug);
        
        // Удаление всех символов, кроме букв, цифр и дефисов
        $slug = preg_replace('/[^a-z0-9-]/', '-', $slug);
        
        // Замена множественных дефисов на один
        $slug = preg_replace('/-+/', '-', $slug);
        
        // Удаление дефисов в начале и конце строки
        $slug = trim($slug, '-');
        
        return $slug;
    }
    
    /**
     * Создает уникальный URL-адрес, добавляя числовой суффикс при необходимости
     * 
     * @param string $title Заголовок страницы
     * @param int|null $excludeId ID страницы для исключения из проверки (при обновлении)
     * @return string Уникальный URL-адрес
     */
    private function createUniqueSlug($title, $excludeId = null) {
        $baseSlug = $this->createSlug($title);
        $slug = $baseSlug;
        $counter = 1;
        
        while ($this->isSlugExists($slug, $excludeId)) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }
        
        return $slug;
    }
    
    /**
     * Проверяет, существует ли уже указанный URL-адрес в базе данных
     * 
     * @param string $slug URL-адрес для проверки
     * @param int|null $excludeId ID страницы для исключения из проверки
     * @return bool true если URL уже существует, false если свободен
     */
    private function isSlugExists($slug, $excludeId = null) {
        $sql = "SELECT COUNT(*) as count FROM pages WHERE slug = ?";
        $params = [$slug];
        
        if ($excludeId !== null) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        $result = $this->db->fetch($sql, $params);
        return $result['count'] > 0;
    }
    
    /**
     * Транслитерирует кириллические символы в латиницу
     * 
     * @param string $string Строка для транслитерации
     * @return string Транслитерированная строка
     */
    private function transliterate($string) {
        $converter = array(
            'а' => 'a',    'б' => 'b',    'в' => 'v',    'г' => 'g',    'д' => 'd',
            'е' => 'e',    'ё' => 'e',    'ж' => 'zh',   'з' => 'z',    'и' => 'i',
            'й' => 'y',    'к' => 'k',    'л' => 'l',    'м' => 'm',    'н' => 'n',
            'о' => 'o',    'п' => 'p',    'р' => 'r',    'с' => 's',    'т' => 't',
            'у' => 'u',    'ф' => 'f',    'х' => 'h',    'ц' => 'c',    'ч' => 'ch',
            'ш' => 'sh',   'щ' => 'sch',  'ь' => '',     'ы' => 'y',    'ъ' => '',
            'э' => 'e',    'ю' => 'yu',   'я' => 'ya',
            
            'А' => 'A',    'Б' => 'B',    'В' => 'V',    'Г' => 'G',    'Д' => 'D',
            'Е' => 'E',    'Ё' => 'E',    'Ж' => 'Zh',   'З' => 'Z',    'И' => 'I',
            'Й' => 'Y',    'К' => 'K',    'Л' => 'L',    'М' => 'M',    'Н' => 'N',
            'О' => 'O',    'П' => 'P',    'Р' => 'R',    'С' => 'S',    'Т' => 'T',
            'У' => 'U',    'Ф' => 'F',    'Х' => 'H',    'Ц' => 'C',    'Ч' => 'Ch',
            'Ш' => 'Sh',   'Щ' => 'Sch',  'Ь' => '',     'Ы' => 'Y',    'Ъ' => '',
            'Э' => 'E',    'Ю' => 'Yu',   'Я' => 'Ya'
        );
        
        return strtr($string, $converter);
    }

    /**
     * Получает блоки контента для указанной страницы
     * 
     * @param int $pageId ID страницы
     * @return array Массив блоков контента
     */
    public function getBlocks($pageId) {
        return $this->postBlockModel->getByPage($pageId);
    }
    
    /**
     * Создает страницу вместе с её блоками (для обратной совместимости)
     * Выполняет операцию в транзакционном стиле
     * 
     * @param array $data Данные страницы
     * @param array $blocks Массив блоков контента
     * @return int ID созданной страницы
     * @throws Exception При ошибке создания
     */
    public function createWithBlocks($data, $blocks) {
        try {
            // Создание страницы
            $pageId = $this->create($data);
            
            // Добавление блоков
            foreach ($blocks as $order => $block) {
                $this->postBlockModel->createForPage(
                    $pageId,
                    $block['type'],
                    $block['content'],
                    $block['settings'] ?? [],
                    $order
                );
            }
            
            return $pageId;
        } catch (Exception $e) {
            throw $e;
        }
    }
    
    /**
     * Обновляет страницу вместе с её блоками (для обратной совместимости)
     * Удаляет старые блоки и создает новые
     * 
     * @param int $pageId ID страницы
     * @param array $data Данные для обновления
     * @param array $blocks Массив новых блоков контента
     * @return bool true при успешном обновлении
     * @throws Exception При ошибке обновления
     */
    public function updateWithBlocks($pageId, $data, $blocks) {
        try {
            // Обновление страницы
            $this->update($pageId, $data);
            
            // Удаление старых блоков
            $this->postBlockModel->deleteByPage($pageId);
            
            // Добавление новых блоков
            foreach ($blocks as $order => $block) {
                $this->postBlockModel->createForPage(
                    $pageId,
                    $block['type'],
                    $block['content'],
                    $block['settings'] ?? [],
                    $order
                );
            }
            
            return true;
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Получает статистику по страницам
     * 
     * @return array Статистика с полями:
     *               - total: Общее количество страниц
     *               - published: Количество опубликованных
     *               - draft: Количество черновиков
     */
    public function getStats() {
        return $this->db->fetch("
            SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'published' THEN 1 ELSE 0 END) as published,
                SUM(CASE WHEN status = 'draft' THEN 1 ELSE 0 END) as draft
            FROM pages
        ");
    }

    /**
     * Ищет страницы по заголовку или URL-адресу
     * 
     * @param string $query Поисковый запрос
     * @return array Массив найденных страниц
     */
    public function search($query) {
        return $this->db->fetchAll("
            SELECT * FROM pages 
            WHERE title LIKE ? OR slug LIKE ?
            ORDER BY title
        ", ["%$query%", "%$query%"]);
    }

    /**
     * Получает последние созданные страницы
     * 
     * @param int $limit Максимальное количество страниц (по умолчанию 10)
     * @return array Массив последних страниц
     */
    public function getRecent($limit = 10) {
        return $this->db->fetchAll("
            SELECT * FROM pages 
            ORDER BY created_at DESC 
            LIMIT ?
        ", [$limit]);
    }
}