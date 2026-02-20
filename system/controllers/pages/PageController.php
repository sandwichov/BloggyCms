<?php

/**
 * Контроллер для отображения страниц на фронтенде
 * Обрабатывает запросы к публичным страницам сайта, подготавливает и отображает контент
 * Интегрируется с блочной системой и пользовательскими полями
 * 
 * @package Controllers
 * @extends Controller
 */
class PageController extends Controller {
    
    /** @var PageModel Модель для работы со страницами */
    private $pageModel;
    
    /** @var PostBlockModel Модель для работы с блоками контента */
    private $postBlockModel;
    
    /** @var PostBlockManager Менеджер для обработки блоков и управления ассетами */
    private $postBlockManager;
    
    /**
     * Конструктор контроллера
     * Инициализирует модели и менеджер для работы со страницами и блоками
     * 
     * @param object $db Подключение к базе данных
     */
    public function __construct($db) {
        parent::__construct($db);
        $this->pageModel = new PageModel($db);
        $this->postBlockModel = new PostBlockModel($db);
        $this->postBlockManager = new PostBlockManager($db);
    }
    
    /**
     * Отображает страницу по её URL-адресу (slug)
     * Загружает страницу, её блоки и пользовательские поля, подготавливает контент для отображения
     * 
     * @param string|null $slug URL-адрес страницы
     * @return void
     */
    public function showAction($slug = null) {
        // Проверка наличия slug в URL
        if (!$slug) {
            $this->handleError('Slug страницы не указан', '/404');
            return;
        }
        
        try {
            // Загрузка страницы по slug
            $page = $this->pageModel->getBySlug($slug);
            if (!$page) {
                $this->handleError('Страница не найдена', BASE_URL);
                return;
            }
            
            // Загрузка и подготовка блоков страницы
            $processedBlocks = $this->preparePageBlocks($page['id']);
            
            // Загрузка пользовательских полей страницы
            $customFields = $this->prepareCustomFields('page', $page['id']);
            
            // Отображение страницы
            $this->render('front/pages/page', [
                'page' => $page,
                'title' => $page['title'],
                'fieldValues' => $customFields,
                'blocks' => $processedBlocks
            ]);
            
        } catch (\Exception $e) {
            $this->handleError('Ошибка при загрузке страницы', BASE_URL);
        }
    }
    
    /**
     * Подготавливает блоки страницы для отображения
     * Загружает ассеты, обрабатывает контент и объединяет настройки
     * 
     * @param int $pageId ID страницы
     * @return array Массив обработанных блоков
     */
    private function preparePageBlocks($pageId) {
        // Получение блоков страницы
        $blocks = $this->postBlockModel->getByPage($pageId);
        
        // Загрузка фронтенд-ассетов для всех блоков на странице
        $this->loadBlockAssets($blocks);
        
        $processedBlocks = [];
        foreach ($blocks as $block) {
            $processedBlocks[] = $this->processSingleBlock($block);
        }
        
        return $processedBlocks;
    }
    
    /**
     * Загружает фронтенд-ассеты (CSS, JS) для всех блоков на странице
     * 
     * @param array $blocks Массив блоков страницы
     * @return void
     */
    private function loadBlockAssets($blocks) {
        $blocksData = [];
        foreach ($blocks as $block) {
            $blocksData[] = [
                'type' => $block['type']
            ];
        }
        
        $this->postBlockManager->loadFrontendAssetsForBlocks($blocksData);
    }
    
    /**
     * Обрабатывает отдельный блок страницы
     * Декодирует JSON-данные, объединяет настройки и обрабатывает контент
     * 
     * @param array $block Данные блока из базы данных
     * @return array Обработанный блок готовый к отображению
     */
    private function processSingleBlock($block) {
        // Декодирование контента из JSON при необходимости
        $content = $this->decodeJsonIfNeeded($block['content']);
        
        // Декодирование настроек из JSON при необходимости
        $settings = $this->decodeJsonIfNeeded($block['settings'], true);
        
        // Получение настроек блока по умолчанию из базы данных
        $dbSettings = $this->postBlockModel->getBlockSettings($block['type']);
        
        // Объединение настроек по умолчанию с пользовательскими
        $mergedSettings = array_merge($dbSettings, $settings);
        
        // Обработка контента блока через менеджер
        $processedContent = $this->postBlockManager->processPostBlockContent(
            $content, 
            $block['type'], 
            $mergedSettings
        );
        
        return [
            'type' => $block['type'],
            'content' => $processedContent,
            'settings' => $mergedSettings
        ];
    }
    
    /**
     * Декодирует строку из JSON, если это необходимо
     * 
     * @param mixed $data Данные для декодирования
     * @param bool $defaultArray Возвращать массив по умолчанию
     * @return mixed Декодированные данные
     */
    private function decodeJsonIfNeeded($data, $defaultArray = false) {
        if (is_string($data)) {
            $decoded = json_decode($data, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }
        }
        return $defaultArray ? [] : $data;
    }
    
    /**
     * Подготавливает пользовательские поля для сущности
     * 
     * @param string $entityType Тип сущности (например, 'page')
     * @param int $entityId ID сущности
     * @return array Массив значений полей с метаданными
     */
    private function prepareCustomFields($entityType, $entityId) {
        $fieldModel = new \FieldModel($this->db);
        $customFields = $fieldModel->getActiveByEntityType($entityType);
        
        $fieldValues = [];
        foreach ($customFields as $field) {
            $fieldValues[$field['system_name']] = [
                'value' => $fieldModel->getFieldValue($entityType, $entityId, $field['system_name']),
                'field' => $field
            ];
        }
        
        return $fieldValues;
    }
    
    /**
     * Обрабатывает ошибки при загрузке страницы
     * 
     * @param string $message Сообщение об ошибке
     * @param string $redirectUrl URL для перенаправления
     * @return void
     */
    private function handleError($message, $redirectUrl) {
        \Notification::error($message);
        $this->redirect($redirectUrl);
    }
}