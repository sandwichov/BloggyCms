<?php

namespace pages\actions;

/**
 * Абстрактный базовый класс для всех действий модуля управления страницами
 * Предоставляет общую функциональность, доступ к моделям и вспомогательные методы
 * Специализируется на работе со страницами, блоками контента и их обработке
 * 
 * @package pages\actions
 */
abstract class PageAction {
    
    /** @var object Подключение к базе данных */
    protected $db;
    
    /** @var array Параметры запроса (GET, POST, маршрутные параметры) */
    protected $params;
    
    /** @var object Контроллер, вызывающий действие */
    protected $controller;
    
    /** @var \PageModel Модель для работы со страницами */
    protected $pageModel;
    
    /** @var \PostBlockModel Модель для работы с блоками контента */
    protected $postBlockModel;
    
    /** @var \PostBlockManager Менеджер для обработки блоков и управления ассетами */
    protected $postBlockManager;
    
    /**
     * Конструктор класса действия
     * Инициализирует подключение к БД, параметры и все необходимые модели
     * 
     * @param object $db Подключение к базе данных
     * @param array $params Параметры запроса (по умолчанию [])
     */
    public function __construct($db, $params = []) {
        $this->db = $db;
        $this->params = $params;
        
        // Инициализация моделей для работы с данными
        $this->pageModel = new \PageModel($db);
        $this->postBlockModel = new \PostBlockModel($db);
        $this->postBlockManager = new \PostBlockManager($db);
    }
    
    /**
     * Устанавливает контроллер, вызывающий действие
     * Необходимо для делегирования операций рендеринга и перенаправления
     * 
     * @param object $controller Контроллер
     * @return void
     */
    public function setController($controller) {
        $this->controller = $controller;
    }
    
    /**
     * Абстрактный метод выполнения действия
     * Должен быть реализован в классах-наследниках
     * Содержит основную логику конкретного действия
     * 
     * @return void
     */
    abstract public function execute();
    
    /**
     * Рендерит шаблон с переданными данными
     * Использует контроллер для рендеринга, если он установлен
     * 
     * @param string $template Путь к шаблону относительно папки views
     * @param array $data Данные для передачи в шаблон
     * @throws \Exception Если контроллер не установлен
     * @return void
     */
    protected function render($template, $data = []) {
        if ($this->controller) {
            $this->controller->render($template, $data);
        } else {
            throw new \Exception('Controller not set for Action');
        }
    }
    
    /**
     * Выполняет перенаправление на указанный URL
     * Использует контроллер для перенаправления, если он установлен,
     * иначе выполняет перенаправление через стандартный PHP-заголовок
     * 
     * @param string $url URL для перенаправления
     * @return void
     */
    protected function redirect($url) {
        if ($this->controller) {
            $this->controller->redirect($url);
        } else {
            header('Location: ' . $url);
            exit;
        }
    }
    
    /**
     * Проверяет, имеет ли текущий пользователь права администратора
     * Основана на проверке сессионных переменных
     * 
     * @return bool true если пользователь администратор, false в противном случае
     */
    protected function checkAdminAccess() {
        return isset($_SESSION['user_id']) && isset($_SESSION['is_admin']) && $_SESSION['is_admin'];
    }
    
    /**
     * Проверяет, является ли текущий запрос AJAX-запросом
     * Анализирует HTTP-заголовок X-Requested-With
     * 
     * @return bool true если запрос AJAX, false в противном случае
     */
    protected function isAjaxRequest() {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) 
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
    
    /**
     * Обрабатывает и сохраняет блоки контента для страницы
     * Удаляет существующие блоки и создает новые на основе переданных данных
     * Содержит специфичную логику для разных типов блоков (например, ListBlock)
     * 
     * @param int $pageId ID страницы
     * @param array $blocksData Массив данных блоков для сохранения
     * @throws \Exception При ошибке сохранения блоков
     * @return void
     */
    protected function processPageBlocks($pageId, $blocksData) {
        try {
            // Удаление всех существующих блоков страницы
            $this->postBlockModel->deleteByPage($pageId);
            
            // Создание новых блоков на основе переданных данных
            foreach ($blocksData as $index => $block) {
                $this->processSingleBlock($pageId, $block, $index);
            }
            
        } catch (\Exception $e) {
            // Проброс исключения для обработки на уровне действия
            throw $e;
        }
    }
    
    /**
     * Обрабатывает и сохраняет один блок страницы
     * Применяет специфичную нормализацию для разных типов блоков
     * 
     * @param int $pageId ID страницы
     * @param array $block Данные блока
     * @param int $index Порядковый индекс блока
     * @return void
     */
    private function processSingleBlock($pageId, $block, $index) {
        $blockType = $block['type'] ?? '';
        $content = $block['content'] ?? [];
        $settings = $block['settings'] ?? [];
        
        // Специфичная обработка для ListBlock
        if ($blockType === 'ListBlock') {
            $content = $this->normalizeListBlockContent($content);
        }
        
        // Создание блока в базе данных
        $this->postBlockModel->createForPage(
            $pageId,
            $blockType,
            $content,
            $settings,
            $index
        );
    }
    
    /**
     * Нормализует контент для блока типа ListBlock
     * Преобразует различные форматы входных данных в единую структуру
     * 
     * @param mixed $content Контент блока в разных форматах
     * @return array Нормализованный контент с массивом items
     */
    private function normalizeListBlockContent($content) {
        $normalizedContent = $content;
        
        // Обработка различных форматов items
        if (isset($content['items']) && is_array($content['items'])) {
            $normalizedContent['items'] = $this->normalizeListItems($content['items']);
        }
        elseif (isset($content['items']) && is_string($content['items'])) {
            $normalizedContent['items'] = $this->normalizeStringItems($content['items']);
        }
        else {
            $normalizedContent['items'] = [];
        }
        
        return $normalizedContent;
    }
    
    /**
     * Нормализует массив элементов списка
     * Фильтрует пустые элементы и приводит к единому формату
     * 
     * @param array $items Массив элементов в разных форматах
     * @return array Нормализованный массив элементов
     */
    private function normalizeListItems($items) {
        $normalizedItems = [];
        
        foreach ($items as $itemText) {
            $normalizedItem = $this->normalizeListItem($itemText);
            if ($normalizedItem !== null) {
                $normalizedItems[] = $normalizedItem;
            }
        }
        
        return $normalizedItems;
    }
    
    /**
     * Нормализует один элемент списка
     * 
     * @param mixed $item Элемент в разных форматах
     * @return array|null Нормализованный элемент или null если элемент пустой
     */
    private function normalizeListItem($item) {
        // Обработка строкового элемента
        if (is_string($item)) {
            $trimmedText = trim($item);
            return !empty($trimmedText) ? ['text' => $trimmedText] : null;
        }
        
        // Обработка элемента в формате массива с полем text
        if (is_array($item) && isset($item['text']) && is_string($item['text'])) {
            $trimmedText = trim($item['text']);
            return !empty($trimmedText) ? ['text' => $trimmedText] : null;
        }
        
        return null;
    }
    
    /**
     * Нормализует строковое представление элементов списка
     * 
     * @param string $itemsString Строка с элементами
     * @return array Массив нормализованных элементов
     */
    private function normalizeStringItems($itemsString) {
        $trimmedText = trim($itemsString);
        if (!empty($trimmedText)) {
            return [['text' => $trimmedText]];
        }
        
        return [];
    }
}