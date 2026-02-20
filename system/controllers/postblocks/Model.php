<?php

/**
 * Модель для работы с блоками контента (постблоками) в базе данных
 * Предоставляет методы для работы с блоками в постах и страницах,
 * управления настройками блоков и пресетами
 * 
 * @package Models
 */
class PostBlockModel {
    
    /** @var object Подключение к базе данных */
    private $db;
    
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
     * Получить все блоки поста
     * 
     * @param int $postId ID поста
     * @return array Массив блоков с декодированными JSON-данными
     */
    public function getByPost($postId) {
        $blocks = $this->db->fetchAll("
            SELECT * FROM post_blocks 
            WHERE post_id = ? 
            ORDER BY `order` ASC
        ", [$postId]);
        
        // Декодируем JSON данные
        foreach ($blocks as &$block) {
            if (is_string($block['content'])) {
                $decoded = json_decode($block['content'], true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $block['content'] = $decoded;
                }
            }
            
            if (!empty($block['settings']) && is_string($block['settings'])) {
                $decoded = json_decode($block['settings'], true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $block['settings'] = $decoded;
                } else {
                    $block['settings'] = [];
                }
            } else {
                $block['settings'] = [];
            }
        }
        
        return $blocks;
    }
    
    /**
     * Получить все блоки страницы
     * 
     * @param int $pageId ID страницы
     * @return array Массив блоков с декодированными JSON-данными
     */
    public function getByPage($pageId) {
        $blocks = $this->db->fetchAll("
            SELECT * FROM page_blocks 
            WHERE page_id = ? 
            ORDER BY `order` ASC
        ", [$pageId]);
        
        // Декодируем JSON данные
        foreach ($blocks as &$block) {
            if (is_string($block['content']) && (strpos($block['content'], '{') === 0 || strpos($block['content'], '[') === 0)) {
                $decoded = json_decode($block['content'], true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $block['content'] = $decoded;
                }
            }
            
            if (!empty($block['settings']) && is_string($block['settings'])) {
                $decoded = json_decode($block['settings'], true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $block['settings'] = $decoded;
                } else {
                    $block['settings'] = [];
                }
            } else {
                $block['settings'] = [];
            }
        }
        
        return $blocks;
    }
    
    /**
     * Создать блок для поста
     * 
     * @param int $postId ID поста
     * @param string $type Тип блока
     * @param mixed $content Контент блока
     * @param array $settings Настройки блока
     * @param int $order Порядковый номер
     * @return bool Результат выполнения запроса
     */
    public function createForPost($postId, $type, $content, $settings = [], $order = 0) {
        $sql = "INSERT INTO post_blocks (post_id, type, content, settings, `order`) VALUES (?, ?, ?, ?, ?)";
        return $this->db->query($sql, [
            $postId,
            $type,
            is_array($content) ? json_encode($content, JSON_UNESCAPED_UNICODE) : $content,
            json_encode($settings, JSON_UNESCAPED_UNICODE),
            $order
        ]);
    }
    
    /**
     * Создать блок для страницы
     * Содержит дополнительную обработку и очистку данных
     * 
     * @param int $pageId ID страницы
     * @param string $type Тип блока
     * @param mixed $content Контент блока
     * @param array $settings Настройки блока
     * @param int $order Порядковый номер
     * @return bool Результат выполнения запроса
     */
    public function createForPage($pageId, $type, $content, $settings = [], $order = 0) {
        if (!is_array($content)) {
            // Если это JSON строка, декодируем
            if (is_string($content) && (strpos($content, '{') === 0 || strpos($content, '[') === 0)) {
                $decoded = json_decode($content, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $content = $decoded;
                } else {
                    // Если не JSON, создаем базовую структуру
                    $content = ['text' => $content];
                }
            } else {
                // Простая строка
                $content = ['text' => $content];
            }
        }

        // Очищаем данные от лишнего экранирования
        foreach ($content as $key => $value) {
            if (is_string($value)) {
                $content[$key] = html_entity_decode($value, ENT_QUOTES, 'UTF-8');
                // Убираем лишние слеши
                $content[$key] = stripslashes($content[$key]);
            }
        }

        // Убедимся, что settings - массив
        if (!is_array($settings)) {
            $settings = [];
        }

        $sql = "INSERT INTO page_blocks (page_id, type, content, settings, `order`) VALUES (?, ?, ?, ?, ?)";
        
        // ВАЖНО: Сохраняем как JSON
        $contentJson = json_encode($content, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $settingsJson = json_encode($settings, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        return $this->db->query($sql, [
            $pageId,
            $type,
            $contentJson,
            $settingsJson,
            $order
        ]);
    }
    
    /**
     * Удалить все блоки поста
     * 
     * @param int $postId ID поста
     * @return bool Результат выполнения запроса
     */
    public function deleteByPost($postId) {
        return $this->db->query("DELETE FROM post_blocks WHERE post_id = ?", [$postId]);
    }
    
    /**
     * Удалить все блоки страницы
     * 
     * @param int $pageId ID страницы
     * @return bool Результат выполнения запроса
     */
    public function deleteByPage($pageId) {
        return $this->db->query("DELETE FROM page_blocks WHERE page_id = ?", [$pageId]);
    }
    
    /**
     * Обновить порядок блоков
     * 
     * @param array $blocks Массив ID блоков с новым порядком
     * @return void
     */
    public function updateOrder($blocks) {
        foreach ($blocks as $order => $blockId) {
            $this->db->query("UPDATE post_blocks SET `order` = ? WHERE id = ?", [$order, $blockId]);
        }
    }

    /**
     * Получить блок по ID
     * 
     * @param int $id ID блока
     * @return array|null Данные блока или null, если не найден
     */
    public function getById($id) {
        $block = $this->db->fetch("SELECT * FROM page_blocks WHERE id = ?", [$id]);
        
        if ($block) {
            // Декодируем JSON данные как в getByPage
            if (is_string($block['content'])) {
                $decoded = json_decode($block['content'], true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $block['content'] = $decoded;
                }
            }
            
            if (!empty($block['settings']) && is_string($block['settings'])) {
                $decoded = json_decode($block['settings'], true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $block['settings'] = $decoded;
                } else {
                    $block['settings'] = [];
                }
            } else {
                $block['settings'] = [];
            }
        }
        
        return $block;
    }

    /**
     * Обновить блок
     * 
     * @param int $id ID блока
     * @param array $data Данные для обновления
     * @return bool Результат выполнения запроса
     */
    public function update($id, $data) {
        $sql = "UPDATE post_blocks SET type = ?, content = ?, settings = ?, `order` = ? WHERE id = ?";
        return $this->db->query($sql, [
            $data['type'],
            is_array($data['content']) ? json_encode($data['content'], JSON_UNESCAPED_UNICODE) : $data['content'],
            !empty($data['settings']) ? json_encode($data['settings'], JSON_UNESCAPED_UNICODE) : null,
            $data['order'],
            $id
        ]);
    }

    /**
     * Получить настройки блока из БД
     * 
     * @param string $systemName Системное имя блока
     * @return array Настройки блока
     */
    public function getBlockSettings($systemName) {
        $settings = $this->db->fetch(
            "SELECT * FROM post_block_settings WHERE system_name = ?",
            [$systemName]
        );

        if (!$settings) {
            // Создаем запись с настройками по умолчанию
            return $this->createDefaultSettings($systemName);
        }

        // Декодируем template из JSON, если он есть
        $template = $settings['template'] ?? '';
        
        if (!empty($template) && is_string($template)) {
            // Пытаемся декодировать JSON
            $decoded = json_decode($template, true);
            
            if (json_last_error() === JSON_ERROR_NONE && is_string($decoded)) {
                $template = $decoded;
            }
        }

        $result = [
            'enable_in_posts' => (bool)$settings['enable_in_posts'],
            'enable_in_pages' => (bool)$settings['enable_in_pages'],
            'template' => $template
        ];
        
        return $result;
    }

    /**
     * Обновить настройки блока
     * 
     * @param string $systemName Системное имя блока
     * @param array $settings Новые настройки
     * @return bool true при успехе, false при ошибке
     */
    public function updateBlockSettings($systemName, $settings) {
        try {
            $existing = $this->db->fetch(
                "SELECT id FROM post_block_settings WHERE system_name = ?",
                [$systemName]
            );

            // Подготавливаем template для сохранения
            $template = $settings['template'] ?? '';
            
            if (!empty($template)) {
                // Проверяем, не является ли уже JSON
                $decoded = json_decode($template, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    // Уже JSON, сохраняем как есть
                    $templateToSave = $template;
                } else {
                    // Не JSON, кодируем
                    $templateToSave = json_encode($template, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                }
            } else {
                $templateToSave = '';
            }

            if ($existing) {
                $result = $this->db->query(
                    "UPDATE post_block_settings SET 
                    enable_in_posts = ?, 
                    enable_in_pages = ?, 
                    template = ?,
                    updated_at = CURRENT_TIMESTAMP 
                    WHERE system_name = ?",
                    [
                        $settings['enable_in_posts'],
                        $settings['enable_in_pages'],
                        $templateToSave,
                        $systemName
                    ]
                );
                
                return $result !== false;
            } else {
                $result = $this->db->query(
                    "INSERT INTO post_block_settings (system_name, enable_in_posts, enable_in_pages, template) 
                    VALUES (?, ?, ?, ?)",
                    [
                        $systemName,
                        $settings['enable_in_posts'],
                        $settings['enable_in_pages'],
                        $templateToSave
                    ]
                );
                
                return $result !== false;
            }
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Создать настройки по умолчанию для блока
     * 
     * @param string $systemName Системное имя блока
     * @return array Настройки по умолчанию
     */
    private function createDefaultSettings($systemName) {
        $postBlockManager = new PostBlockManager($this->db);
        $block = $postBlockManager->getPostBlock($systemName);
        $blockInstance = $block['class'] ?? null;
        
        $defaultSettings = [
            'enable_in_posts' => $blockInstance ? $blockInstance->canUseInPosts() : true,
            'enable_in_pages' => $blockInstance ? $blockInstance->canUseInPages() : true,
            'template' => ''
        ];

        $this->db->query(
            "INSERT INTO post_block_settings (system_name, enable_in_posts, enable_in_pages, template) 
            VALUES (?, ?, ?, ?)",
            [
                $systemName,
                $defaultSettings['enable_in_posts'],
                $defaultSettings['enable_in_pages'],
                $defaultSettings['template']
            ]
        );

        return $defaultSettings;
    }

    /**
     * Получить все настройки блоков
     * 
     * @return array Массив настроек, индексированный по системным именам
     */
    public function getAllBlockSettings() {
        $settings = $this->db->fetchAll("SELECT * FROM post_block_settings");
        
        $result = [];
        foreach ($settings as $setting) {
            $result[$setting['system_name']] = [
                'enable_in_posts' => (bool)$setting['enable_in_posts'],
                'enable_in_pages' => (bool)$setting['enable_in_pages'],
                'template' => $setting['template'] ?? ''
            ];
        }
        
        return $result;
    }

    /**
     * Получить все пресеты для блока
     * 
     * @param string $systemName Системное имя блока
     * @return array Массив пресетов
     */
    public function getBlockPresets($systemName) {
        return $this->db->fetchAll("
            SELECT * FROM post_block_presets 
            WHERE block_system_name = ? 
            ORDER BY preset_name ASC
        ", [$systemName]);
    }

    /**
     * Получить конкретный пресет по ID
     * 
     * @param int $id ID пресета
     * @return array|null Данные пресета или null, если не найден
     */
    public function getPreset($id) {
        return $this->db->fetch("
            SELECT * FROM post_block_presets WHERE id = ?
        ", [$id]);
    }

    /**
     * Создать новый пресет для блока
     * 
     * @param string $systemName Системное имя блока
     * @param string $presetName Название пресета
     * @param string $presetTemplate Шаблон пресета
     * @return bool Результат выполнения запроса
     */
    public function createPreset($systemName, $presetName, $presetTemplate) {
        return $this->db->query(
            "INSERT INTO post_block_presets (block_system_name, preset_name, preset_template) 
             VALUES (?, ?, ?)",
            [$systemName, $presetName, $presetTemplate]
        );
    }

    /**
     * Обновить существующий пресет
     * 
     * @param int $id ID пресета
     * @param string $presetName Новое название пресета
     * @param string $presetTemplate Новый шаблон пресета
     * @return bool Результат выполнения запроса
     */
    public function updatePreset($id, $presetName, $presetTemplate) {
        return $this->db->query(
            "UPDATE post_block_presets SET 
                preset_name = ?,
                preset_template = ?,
                updated_at = CURRENT_TIMESTAMP 
             WHERE id = ?",
            [$presetName, $presetTemplate, $id]
        );
    }

    /**
     * Удалить пресет
     * 
     * @param int $id ID пресета
     * @return bool Результат выполнения запроса
     */
    public function deletePreset($id) {
        return $this->db->query("DELETE FROM post_block_presets WHERE id = ?", [$id]);
    }

    /**
     * Получить пресет по имени
     * 
     * @param string $systemName Системное имя блока
     * @param string $presetName Название пресета
     * @return array|null Данные пресета или null, если не найден
     */
    public function getPresetByName($systemName, $presetName) {
        return $this->db->fetch("
            SELECT * FROM post_block_presets 
            WHERE block_system_name = ? AND preset_name = ?
        ", [$systemName, $presetName]);
    }
}