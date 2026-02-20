<?php

/**
 * Модель HTML-блоков
 * Управляет созданием, хранением и извлечением HTML-блоков из базы данных
 * Обеспечивает работу с динамическими блоками контента на сайте
 * 
 * @package models
 */
class HtmlBlockModel {
    
    /**
     * @var Database Объект подключения к базе данных
     */
    private $db;
    
    /**
     * Конструктор модели HTML-блоков
     * Инициализирует подключение к базе данных
     *
     * @param Database $db Объект подключения к базе данных
     */
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * Получение всех HTML-блоков
     * Возвращает список всех блоков с информацией об их типах
     *
     * @return array Массив всех HTML-блоков с данными о типах
     */
    public function getAll() {
        return $this->db->fetchAll("
            SELECT hb.*, 
                hbt.name as type_name, 
                hbt.system_name as block_type,
                hbt.template as block_type_template 
            FROM html_blocks hb 
            LEFT JOIN html_block_types hbt ON hb.type_id = hbt.id 
            ORDER BY hb.created_at DESC
        ");
    }
    
    /**
     * Получение блока по ID
     * Возвращает данные HTML-блока по его идентификатору
     *
     * @param int $id ID блока
     * @return array|null Данные блока или null если не найден
     */
    public function getById($id) {
        return $this->db->fetch("
            SELECT hb.*, hbt.name as type_name, hbt.system_name as block_type 
            FROM html_blocks hb 
            LEFT JOIN html_block_types hbt ON hb.type_id = hbt.id 
            WHERE hb.id = ?
        ", [$id]);
    }
    
    /**
     * Получение блока по slug
     * Возвращает данные HTML-блока по его URL-идентификатору
     *
     * @param string $slug URL-идентификатор блока
     * @return array|null Данные блока или null если не найден
     */
    public function getBySlug($slug) {
        return $this->db->fetch("
            SELECT hb.*, hbt.name as type_name, hbt.system_name as block_type 
            FROM html_blocks hb 
            LEFT JOIN html_block_types hbt ON hb.type_id = hbt.id 
            WHERE hb.slug = ?
        ", [$slug]);
    }
    
    /**
     * Создание нового HTML-блока
     * Добавляет новый блок в базу данных с проверкой уникальности slug
     *
     * @param array $data Массив данных блока:
     * - name: название блока
     * - slug: URL-идентификатор (обязательно)
     * - type_id: ID типа блока
     * - settings: настройки блока в JSON
     * - css_files: CSS файлы в JSON
     * - js_files: JavaScript файлы в JSON
     * - inline_css: инлайн CSS стили
     * - inline_js: инлайн JavaScript код
     * - template: шаблон блока
     * @return int ID созданного блока
     * @throws Exception При ошибках валидации или дублировании slug
     */
    public function create($data) {
        // Проверка корректности slug (только латинские буквы, цифры и дефисы)
        if (!preg_match('/^[a-z0-9-]+$/', $data['slug'])) {
            throw new Exception('Имя может содержать только латинские буквы, цифры и дефисы.');
        }
    
        // Проверка уникальности slug
        if ($this->isSlugExists($data['slug'])) {
            throw new Exception('Имя уже существует.');
        }
    
        $sql = "INSERT INTO html_blocks (name, slug, content, type_id, settings, css_files, js_files, inline_css, inline_js, template) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $this->db->query($sql, [
            $data['name'],
            $data['slug'],
            '', // Контент всегда пустой (генерируется динамически)
            $data['type_id'] ?? null,
            isset($data['settings']) ? json_encode($data['settings']) : null,
            isset($data['css_files']) ? json_encode($data['css_files']) : null,
            isset($data['js_files']) ? json_encode($data['js_files']) : null,
            $data['inline_css'] ?? '',
            $data['inline_js'] ?? '',
            $data['template'] ?? 'default'
        ]);
    
        return $this->db->lastInsertId();
    }
    
    /**
     * Обновление существующего HTML-блока
     * Изменяет данные блока с автоматической генерацией slug если не указан
     *
     * @param int $id ID обновляемого блока
     * @param array $data Массив данных для обновления (аналогично create)
     * @return bool Результат выполнения запроса
     */
    public function update($id, $data) {
        // Генерация slug если он не передан
        if (empty($data['slug'])) {
            $slug = $this->createUniqueSlug($data['name'], $id);
        } else {
            // Использование переданного slug с проверкой уникальности
            $slug = $this->createUniqueSlug($data['slug'], $id);
        }
    
        $sql = "UPDATE html_blocks SET name = ?, slug = ?, content = '', type_id = ?, settings = ?, css_files = ?, js_files = ?, inline_css = ?, inline_js = ?, template = ? WHERE id = ?";
        return $this->db->query($sql, [
            $data['name'],
            $slug,
            $data['type_id'] ?? null,
            isset($data['settings']) ? json_encode($data['settings']) : null,
            isset($data['css_files']) ? json_encode($data['css_files']) : null,
            isset($data['js_files']) ? json_encode($data['js_files']) : null,
            $data['inline_css'] ?? '',
            $data['inline_js'] ?? '',
            $data['template'] ?? 'default',
            $id
        ]);
    }
    
    /**
     * Удаление HTML-блока
     * Удаляет блок из базы данных по его ID
     *
     * @param int $id ID удаляемого блока
     * @return bool Результат выполнения запроса
     */
    public function delete($id) {
        return $this->db->query("DELETE FROM html_blocks WHERE id = ?", [$id]);
    }
    
    /**
     * Создание slug на основе имени
     * Преобразует строку в URL-дружественный формат
     *
     * @param string $name Исходное имя блока
     * @return string Сгенерированный slug
     */
    private function createSlug($name) {
        $slug = mb_strtolower($name, 'UTF-8');
        $slug = preg_replace('/[^a-z0-9-]/', '-', $slug);
        $slug = preg_replace('/-+/', '-', $slug);
        $slug = trim($slug, '-');
        return $slug;
    }
    
    /**
     * Создание уникального slug с проверкой на дубликаты
     * Генерирует slug и гарантирует его уникальность в базе данных
     *
     * @param string $name Исходное имя или slug
     * @param int|null $excludeId ID блока для исключения (при обновлении)
     * @return string Уникальный slug
     */
    private function createUniqueSlug($name, $excludeId = null) {
        $baseSlug = $this->createSlug($name);
        $slug = $baseSlug;
        $counter = 1;
        
        // Поиск уникального slug путем добавления числового суффикса
        while ($this->isSlugExists($slug, $excludeId)) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }
        
        return $slug;
    }
    
    /**
     * Проверка существования slug в базе данных
     * Определяет, используется ли slug другими блоками
     *
     * @param string $slug Проверяемый slug
     * @param int|null $excludeId ID блока для исключения из проверки
     * @return bool true если slug уже существует
     */
    private function isSlugExists($slug, $excludeId = null) {
        $sql = "SELECT COUNT(*) as count FROM html_blocks WHERE slug = ?";
        $params = [$slug];
        
        // Исключение текущего блока при обновлении
        if ($excludeId !== null) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        $result = $this->db->fetch($sql, $params);
        return $result['count'] > 0;
    }
}