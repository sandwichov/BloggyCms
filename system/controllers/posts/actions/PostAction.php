<?php

namespace posts\actions;

/**
 * Абстрактный базовый класс для всех действий модуля постов
 * Предоставляет общую функциональность, доступ ко всем необходимым моделям
 * и вспомогательные методы для работы с постами, категориями, тегами, блоками
 * 
 * @package posts\actions
 */
abstract class PostAction {
    
    /** @var object Подключение к базе данных */
    protected $db;
    
    /** @var array Параметры запроса (GET, POST, маршрутные параметры) */
    protected $params;
    
    /** @var object Контроллер, вызывающий действие */
    protected $controller;
    
    /** @var \PostModel Модель для работы с постами */
    protected $postModel;
    
    /** @var \CategoryModel Модель для работы с категориями */
    protected $categoryModel;
    
    /** @var \TagModel Модель для работы с тегами */
    protected $tagModel;
    
    /** @var \CommentModel Модель для работы с комментариями */
    protected $commentModel;
    
    /** @var \PostBlockModel Модель для работы с блоками контента */
    protected $postBlockModel;
    
    /** @var \FieldModel Модель для работы с пользовательскими полями */
    protected $fieldModel;
    
    /** @var \PostBlockManager Менеджер для работы с постблоками */
    protected $postBlockManager;
    
    /** @var \BreadcrumbsManager Менеджер для работы с хлебными крошками */
    protected $breadcrumbs;
    
    /** @var string Заголовок страницы для отображения в шаблоне */
    protected $pageTitle;
    
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
        $this->postModel = new \PostModel($db);
        $this->categoryModel = new \CategoryModel($db);
        $this->tagModel = new \TagModel($db);
        $this->commentModel = new \CommentModel($db);
        $this->postBlockModel = new \PostBlockModel($db);
        $this->fieldModel = new \FieldModel($db);
        $this->postBlockManager = new \PostBlockManager($db);
        $this->breadcrumbs = new \BreadcrumbsManager($db);
        $this->pageTitle = '';
        \BreadcrumbsHelper::setManager($this->breadcrumbs);
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
     * Добавляет элемент в хлебные крошки
     * 
     * @param string $title Название элемента
     * @param string|null $url URL элемента (null для текущего элемента)
     * @return self
     */
    protected function addBreadcrumb($title, $url = null) {
        $this->breadcrumbs->add($title, $url);
        return $this;
    }
    
    /**
     * Добавляет элемент в начало хлебных крошек
     * 
     * @param string $title Название элемента
     * @param string|null $url URL элемента
     * @return self
     */
    protected function prependBreadcrumb($title, $url = null) {
        $this->breadcrumbs->prepend($title, $url);
        return $this;
    }
    
    /**
     * Очищает все хлебные крошки
     * 
     * @return self
     */
    protected function clearBreadcrumbs() {
        $this->breadcrumbs->clear();
        return $this;
    }
    
    /**
     * Устанавливает заголовок страницы
     * 
     * @param string $title Заголовок
     * @return self
     */
    protected function setPageTitle($title) {
        $this->pageTitle = $title;
        return $this;
    }
    
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
        if (!$this->controller) {
            throw new \Exception('Controller not set for Action');
        }
        
        if (!isset($data['breadcrumbs'])) {
            $data['breadcrumbs'] = $this->breadcrumbs;
        }
        
        if (!isset($data['title']) && $this->pageTitle) {
            $data['title'] = $this->pageTitle;
        }
        
        $this->controller->render($template, $data);
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
     * Основана на проверке сессионной переменной is_admin
     * 
     * @return bool true если пользователь администратор, false в противном случае
     */
    protected function checkAdminAccess() {
        return isset($_SESSION['is_admin']) && $_SESSION['is_admin'];
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
     * Возвращает менеджер хлебных крошек
     * 
     * @return \BreadcrumbsManager
     */
    protected function getBreadcrumbs() {
        return $this->breadcrumbs;
    }
    
    /**
     * Обрабатывает и сохраняет блоки контента для поста
     * Удаляет существующие блоки, валидирует и подготавливает настройки через класс блока,
     * затем создает новые блоки в базе данных
     * 
     * @param int $postId ID поста
     * @param array $blocksData Массив данных блоков для сохранения
     * @throws \Exception При ошибке валидации настроек или сохранения
     * @return void
     */
    protected function processPostBlocks($postId, $blocksData) {
        try {
            $this->postBlockModel->deleteByPost($postId);

            foreach ($blocksData as $index => $block) {
                $this->processSingleBlock($postId, $block, $index);
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Обрабатывает и сохраняет один блок поста
     * 
     * @param int $postId ID поста
     * @param array $block Данные блока
     * @param int $index Порядковый индекс
     * @throws \Exception При ошибке валидации настроек
     * @return void
     */
    private function processSingleBlock($postId, $block, $index) {
        $blockType = $block['type'];
        $content = $block['content'] ?? [];
        $settings = $block['settings'] ?? [];

        $postBlock = $this->postBlockManager->getPostBlock($blockType);
        if ($postBlock && $postBlock['class']) {
            list($isValid, $errors) = $postBlock['class']->validateSettings($settings);
            if (!$isValid) {
                throw new \Exception('Ошибки в настройках блока "' . $postBlock['name'] . '": ' . implode(', ', $errors));
            }
            
            $settings = $postBlock['class']->prepareSettings($settings);
        }

        $this->postBlockModel->createForPost(
            $postId,
            $blockType,
            $content,
            $settings,
            $index
        );
    }
}