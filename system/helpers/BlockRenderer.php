<?php

/**
 * Класс для рендеринга различных типов блоков контента
 * Поддерживает пост-блоки (через PostBlockManager) и HTML-блоки (старая система)
 * 
 * @package Core
 */
class BlockRenderer {
    
    /** @var PostBlockManager|null Менеджер пост-блоков */
    private static $postBlockManager;
    
    /** @var PostBlock|null Модель пост-блоков */
    private static $postBlockModel;
    
    /** @var bool Флаг инициализации */
    private static $initialized = false;
    
    /**
     * Инициализирует менеджеры пост-блоков
     * Получает подключение к БД и создает необходимые объекты
     * 
     * @return void
     */
    private static function init() {
        if (!self::$initialized) {
            $db = Database::getInstance();
            self::$postBlockManager = new PostBlockManager($db);
            self::$postBlockModel = new PostBlock($db);
            self::$initialized = true;
        }
    }
    
    /**
     * Рендерит блок с учетом кастомных шаблонов из настроек
     * Определяет тип блока и вызывает соответствующий метод рендеринга
     * 
     * @param array $blockData Данные блока
     * @return string HTML-код блока или сообщение об ошибке
     */
    public static function render($blockData) {
        if (!is_array($blockData)) {
            return 'Неверные данные блока';
        }
        
        // Инициализация менеджеров
        self::init();
        
        // Определение типа блока
        if (isset($blockData['type'])) {
            // Это пост-блок (из таблицы page_blocks)
            return self::renderPostBlock($blockData);
        } elseif (isset($blockData['block_type'])) {
            // Это HTML блок (старая система)
            return self::renderHtmlBlock($blockData);
        }
        
        // Для обычных блоков контента
        return $blockData['content'] ?? '';
    }
    
    /**
     * Рендерит пост-блок через PostBlockManager
     * 
     * @param array $blockData Данные пост-блока
     * @return string HTML-код блока
     */
    private static function renderPostBlock($blockData) {
        try {
            $type = $blockData['type'];
            $content = $blockData['content'] ?? [];
            $settings = $blockData['settings'] ?? [];

            // Если контент уже HTML, возвращаем как есть
            if (is_string($content) && (strpos($content, '<') === 0)) {
                return $content;
            }
            
            // Декодирование JSON, если контент в JSON формате
            if (is_string($content)) {
                $decoded = json_decode($content, true);
                if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                    $content = $decoded;
                }
            }
            
            // Приведение к массиву
            if (!is_array($content)) {
                $content = ['text' => (string)$content];
            }

            // Получение настроек шаблона из базы данных
            $dbSettings = self::$postBlockModel->getBlockSettings($type);
            
            // Объединение настроек (приоритет у переданных)
            $mergedSettings = array_merge($settings, $dbSettings);
            
            // ШАБЛОН ВСЕГДА БЕРЕМ ИЗ БАЗЫ ДАННЫХ
            if (!empty($dbSettings['template'])) {
                $mergedSettings['template'] = $dbSettings['template'];
            }

            // Рендеринг через PostBlockManager
            $result = self::$postBlockManager->processPostBlockContent($content, $type, $mergedSettings);
            
            return $result;
            
        } catch (Exception $e) {
            return 'Ошибка при отображении пост-блока: ' . $e->getMessage();
        }
    }

    /**
     * Извлекает данные контента из HTML строки используя метод блока
     * 
     * @param string $blockType Тип блока
     * @param string $html HTML-строка для парсинга
     * @return array|null Извлеченные данные или null
     */
    private static function extractContentFromHtml($blockType, $html) {
        try {
            $blockInfo = self::$postBlockManager->getPostBlock($blockType);
            
            if ($blockInfo && $blockInfo['class']) {
                $blockInstance = $blockInfo['class'];
                
                // Использование специализированного метода блока, если есть
                if (method_exists($blockInstance, 'extractFromHtml')) {
                    $result = $blockInstance->extractFromHtml($html);
                    if ($result !== null) {
                        return $result;
                    }
                }
            }
            
            // Специальная обработка для ListBlock
            if ($blockType === 'ListBlock') {
                return self::extractListFromHtml($html);
            }
            
            // Базовая обработка для текстовых блоков
            $plainText = trim(strip_tags($html));
            if (!empty($plainText)) {
                return ['text' => $plainText];
            }
            
            return null;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Извлекает данные списка из HTML
     * Парсит UL/OL списки и возвращает структурированные данные
     * 
     * @param string $html HTML-код списка
     * @return array|null Данные списка с элементами
     */
    private static function extractListFromHtml($html) {
        if (preg_match('/<(ul|ol)[^>]*>(.*?)<\/\1>/s', $html, $listMatches)) {
            $listType = $listMatches[1];
            $listContent = $listMatches[2];
            preg_match_all('/<li[^>]*>(.*?)<\/li>/s', $listContent, $itemMatches);
            
            $items = [];
            foreach ($itemMatches[1] as $itemContent) {
                $text = trim(strip_tags($itemContent));
                if (!empty($text)) {
                    $items[] = ['text' => $text];
                }
            }
            
            if (!empty($items)) {
                return [
                    'list_type' => $listType,
                    'items' => $items
                ];
            }
        }
        return null;
    }
    
    /**
     * Рендерит HTML блок через HtmlBlockTypeManager (старая система)
     * 
     * @param array $blockData Данные HTML-блока
     * @return string HTML-код блока
     */
    private static function renderHtmlBlock($blockData) {
        try {
            $db = Database::getInstance();
            $blockTypeManager = new HtmlBlockTypeManager($db);
            
            $content = $blockData['content'] ?? '';
            $blockType = $blockData['block_type'] ?? 'DefaultBlock';
            $settings = [];
            
            // Парсинг настроек
            if (!empty($blockData['settings'])) {
                $settings = is_string($blockData['settings']) 
                    ? json_decode($blockData['settings'], true) 
                    : $blockData['settings'];
            }
            
            // Обработка через менеджер типов блоков
            if ($blockType !== 'DefaultBlock') {
                $processedContent = $blockTypeManager->processBlockContent($content, $blockType, $settings);
                if (!empty($processedContent)) {
                    $content = $processedContent;
                }
            }
            
            // Обработка шорткодов
            if (class_exists('Shortcodes')) {
                $posts = $db->fetchAll("
                    SELECT p.*, c.name as category_name, GROUP_CONCAT(t.name SEPARATOR ', ') as tags
                    FROM posts p
                    LEFT JOIN categories c ON p.category_id = c.id
                    LEFT JOIN post_tags pt ON p.id = pt.post_id
                    LEFT JOIN tags t ON pt.tag_id = t.id
                    WHERE p.status = 'published'
                    GROUP BY p.id
                    ORDER BY p.created_at DESC
                ");
                
                $content = process_shortcodes($content, $posts);
            }
            
            return $content;
            
        } catch (Exception $e) {
            return 'Ошибка при отображении HTML блока';
        }
    }
    
    /**
     * Альтернативный метод для рендера блока
     * Просто вызывает основной метод render()
     * 
     * @param array $blockData Данные блока
     * @return string HTML-код блока
     */
    public static function renderBlock($blockData) {
        return self::render($blockData);
    }
    
    /**
     * Рендерит массив пост-блоков
     * Последовательно вызывает renderPostBlock для каждого
     * 
     * @param array $blocks Массив блоков
     * @return string HTML-код всех блоков
     */
    public static function renderPostBlocks($blocks) {
        self::init();
        
        $output = '';
        foreach ($blocks as $block) {
            $output .= self::renderPostBlock($block);
        }
        
        return $output;
    }
    
    /**
     * Очищает данные контента от лишнего экранирования
     * 
     * @param mixed $content Данные контента
     * @return mixed Очищенные данные
     */
    private static function cleanContentData($content) {
        if (!is_array($content)) {
            return $content;
        }
        
        foreach ($content as $key => $value) {
            if (is_string($value)) {
                $value = html_entity_decode($value, ENT_QUOTES, 'UTF-8');
                $value = preg_replace('/^"(.*)"$/', '$1', $value);
                $content[$key] = trim($value);
            }
        }
        
        return $content;
    }
}