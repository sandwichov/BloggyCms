<?php

/**
 * Менеджер для управления типами HTML блоков
 */
class HtmlBlockTypeManager {
    /**
     * @var array Зарегистрированные типы блоков
     */
    private $blockTypes = [];
    
    /**
     * @var mixed Подключение к базе данных
     */
    private $db;

    /**
     * Конструктор HtmlBlockTypeManager
     *
     * @param mixed $db Подключение к базе данных
     */
    public function __construct($db) {
        $this->db = $db;
        $this->loadBlockTypes();
    }

    /**
     * Загружает все типы блоков из папки и базы данных
     */
    private function loadBlockTypes() {
        $baseBlockFile = __DIR__ . '/../html_blocks/BaseHtmlBlock.php';
        if (file_exists($baseBlockFile)) {
            require_once $baseBlockFile;
        }

        $blocksDir = __DIR__ . '/../html_blocks';
        if (is_dir($blocksDir)) {
            $files = scandir($blocksDir);
            foreach ($files as $file) {
                if (pathinfo($file, PATHINFO_EXTENSION) === 'php' && $file !== 'BaseHtmlBlock.php') {
                    $className = pathinfo($file, PATHINFO_FILENAME);
                    $filePath = $blocksDir . '/' . $file;
                    
                    require_once $filePath;
                    
                    if (class_exists($className)) {
                        $reflection = new ReflectionClass($className);
                        if (!$reflection->isAbstract() && $reflection->isSubclassOf('BaseHtmlBlock')) {
                            $blockInstance = new $className();
                            
                            $dbBlock = $this->db->fetch(
                                "SELECT * FROM html_block_types WHERE system_name = ?",
                                [$className]
                            );
                            
                            if (!$dbBlock) {
                                $this->db->query(
                                    "INSERT INTO html_block_types (name, system_name, description, template, is_active) VALUES (?, ?, ?, ?, 1)",
                                    [
                                        $blockInstance->getName(),
                                        $className,
                                        $blockInstance->getDescription(),
                                        $blockInstance->getTemplate()
                                    ]
                                );
                                $blockId = $this->db->lastInsertId();
                            } else {
                                $blockId = $dbBlock['id'];
                                $this->db->query(
                                    "UPDATE html_block_types SET name = ?, description = ?, template = ? WHERE id = ?",
                                    [
                                        $blockInstance->getName(),
                                        $blockInstance->getDescription(),
                                        $blockInstance->getTemplate(),
                                        $blockId
                                    ]
                                );
                            }
                            
                            $this->blockTypes[$className] = [
                                'id' => $blockId,
                                'name' => $blockInstance->getName(),
                                'system_name' => $className,
                                'description' => $blockInstance->getDescription(),
                                'class' => $blockInstance,
                                'template' => $blockInstance->getTemplate(),
                                'icon' => $blockInstance->getIcon(),
                                'author' => $blockInstance->getAuthor(),
                                'version' => $blockInstance->getVersion(),
                                'author_website' => $blockInstance->getAuthorWebsite(),
                                'short_description' => $blockInstance->getShortDescription()
                            ];
                            
                        }
                    }
                }
            }
        }
    }

    /**
     * Возвращает все доступные типы блоков
     *
     * @return array Активные типы блоков
     */
    public function getBlockTypes() {
        $allBlockTypes = $this->getAllBlockTypes();
        
        return array_filter($allBlockTypes, function($blockType) {
            return isset($blockType['is_active']) ? $blockType['is_active'] : true;
        });
    }

    /**
     * Проверяет, активен ли тип блока
     *
     * @param string $systemName Системное имя блока
     * @return bool Активен ли блок
     */
    public function isBlockTypeActive($systemName) {
        $allBlockTypes = $this->getAllBlockTypes();
        
        if (!isset($allBlockTypes[$systemName])) {
            return true;
        }
        
        return $allBlockTypes[$systemName]['is_active'] ?? true;
    }

    /**
     * Возвращает ВСЕ типы блоков (включая неактивные)
     *
     * @return array Все типы блоков
     */
    public function getAllBlockTypes() {
        foreach ($this->blockTypes as $systemName => &$type) {
            $dbBlock = $this->db->fetch(
                "SELECT is_active FROM html_block_types WHERE system_name = ?",
                [$systemName]
            );
            
            if ($dbBlock) {
                $type['is_active'] = (bool)$dbBlock['is_active'];
            } else {
                $type['is_active'] = true;
                
                $this->db->query(
                    "INSERT INTO html_block_types (name, system_name, description, template, is_active) VALUES (?, ?, ?, ?, 1)",
                    [
                        $type['name'] ?? $systemName,
                        $systemName,
                        $type['description'] ?? '',
                        $type['template'] ?? 'all'
                    ]
                );
            }
        }
        
        return $this->blockTypes;
    }

    /**
     * Возвращает конкретный тип блока
     *
     * @param string $systemName Системное имя блока
     * @return array|null Данные типа блока
     */
    public function getBlockType($systemName) {
        return $this->blockTypes[$systemName] ?? null;
    }

    /**
     * Загружает CSS и JS файлы для типа блока в админке
     *
     * @param string $systemName Системное имя блока
     */
    public function loadBlockAssets($systemName) {
        $blockType = $this->getBlockType($systemName);
        if ($blockType && $blockType['class']) {
            $blockInstance = $blockType['class'];
            
            $cssFiles = $blockInstance->getAdminCss();
            foreach ($cssFiles as $cssFile) {
                add_admin_css($cssFile);
            }
            
            $jsFiles = $blockInstance->getAdminJs();
            foreach ($jsFiles as $jsFile) {
                add_admin_js($jsFile);
            }
        } 
    }

    /**
     * Обрабатывает контент блока на фронтенде
     *
     * @param string $systemName Системное имя блока
     * @param array $settings Настройки блока
     * @param string|null $template Шаблон блока
     * @return string Обработанный HTML
     */
    public function processBlockContent($systemName, $settings = [], $template = null) {
        $blockType = $this->getBlockType($systemName);
        
        if ($blockType && $blockType['class']) {
            $templateName = $template ?? ($settings['template'] ?? null);
            $result = $blockType['class']->processFrontend($settings, $templateName);
            
            if (is_array($result)) {
                return implode('', $result);
            }
            return (string)$result;
        }

        return '';
    }

    /**
     * Загружает CSS и JS файлы для фронтенда типа блока
     *
     * @param string $systemName Системное имя блока
     */
    public function loadBlockFrontendAssets($systemName) {
        $blockType = $this->getBlockType($systemName);
        if ($blockType && $blockType['class']) {
            $blockInstance = $blockType['class'];
            
            $cssFiles = $blockInstance->getSystemCss();
            foreach ($cssFiles as $cssFile) {
                add_html_block_css($cssFile);
            }
            
            $jsFiles = $blockInstance->getSystemJs();
            foreach ($jsFiles as $jsFile) {
                add_html_block_js($jsFile);
            }
            
            $inlineCss = $blockInstance->getFrontendInlineCss();
            if (!empty($inlineCss)) {
                add_inline_css($inlineCss);
            }
            
            $inlineJs = $blockInstance->getFrontendInlineJs();
            if (!empty($inlineJs)) {
                add_inline_js($inlineJs);
            }
        } 
    }

    /**
     * Рендерит блок на фронтенде с подключением активов
     *
     * @param string $systemName Системное имя блока
     * @param array $settings Настройки блока
     * @param string|null $template Шаблон блока
     * @return string Отрендеренный HTML
     */
    public function renderBlockFront($systemName, $settings = [], $template = null): string {
        $this->loadBlockFrontendAssets($systemName);
        
        $result = $this->processBlockContent($systemName, $settings, $template);
        
        if (is_array($result)) {
            return implode('', $result);
        }
        
        return (string)$result;
    }

    /**
     * Загружает активы для всех используемых блоков на странице
     *
     * @param string $entityType Тип сущности
     * @param int $entityId ID сущности
     */
    public function loadPageBlocksAssets($entityType, $entityId) {
        $contentBlockModel = new ContentBlock($this->db);
        $blocks = $contentBlockModel->getForEntity($entityType, $entityId);
        
        foreach ($blocks as $block) {
            $this->loadBlockFrontendAssets($block['block_type']);
        }
    }
}