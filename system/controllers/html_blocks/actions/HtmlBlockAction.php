<?php

namespace html_blocks\actions;

/**
 * Абстрактный базовый класс для действий управления HTML-блоками
 * Предоставляет общую функциональность для всех действий, связанных с HTML-блоками,
 * включая доступ к моделям, рендеринг шаблонов и обработку ресурсов блоков
 * 
 * @package html_blocks\actions
 * @abstract
 */
abstract class HtmlBlockAction {
    
    /**
     * @var \Database Объект подключения к базе данных
     */
    protected $db;
    
    /**
     * @var array Массив параметров, переданных действию
     */
    protected $params;
    
    /**
     * @var object|null Контроллер, управляющий действием
     */
    protected $controller;
    
    /**
     * @var \HtmlBlockModel Модель для работы с HTML-блоками
     */
    protected $htmlBlockModel;
    
    /**
     * @var \HtmlBlockTypeManager Менеджер типов HTML-блоков
     */
    protected $blockTypeManager;
    
    /**
     * @var int|null ID HTML-блока для операций
     */
    protected $id;
    
    /**
     * Конструктор базового класса действий HTML-блоков
     * Инициализирует подключение к БД и создает экземпляры моделей
     *
     * @param \Database $db Объект подключения к базе данных
     * @param array $params Дополнительные параметры действия
     */
    public function __construct($db, $params = []) {
        $this->db = $db;
        $this->params = $params;
        $this->htmlBlockModel = new \HtmlBlockModel($db);
        $this->blockTypeManager = new \HtmlBlockTypeManager($db);
        $this->id = $params['id'] ?? null;
    }
    
    /**
     * Установка контроллера для действия
     * Связывает действие с контроллером для доступа к его методам
     *
     * @param object $controller Объект контроллера
     * @return void
     */
    public function setController($controller) {
        $this->controller = $controller;
    }
    
    /**
     * Установка ID блока
     * Устанавливает идентификатор HTML-блока для операций
     *
     * @param int $id ID HTML-блока
     * @return void
     */
    public function setId($id) {
        $this->id = $id;
    }
    
    /**
     * Абстрактный метод выполнения действия
     * Должен быть реализован в дочерних классах
     *
     * @return mixed Результат выполнения действия
     * @abstract
     */
    abstract public function execute();
    
    /**
     * Рендеринг шаблона с данными
     * Передает управление методу рендеринга контроллера
     *
     * @param string $template Путь к файлу шаблона
     * @param array $data Массив данных для передачи в шаблон
     * @return void
     * @throws \Exception Если контроллер не установлен
     */
    protected function render($template, $data = []) {
        if ($this->controller) {
            $this->controller->render($template, $data);
        } else {
            throw new \Exception('Controller not set for Action');
        }
    }
    
    /**
     * Перенаправление на указанный URL
     * Использует метод перенаправления контроллера или стандартный header
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
     * Проверка доступа администратора
     * Проверяет наличие административных прав в сессии пользователя
     *
     * @return bool true если пользователь авторизован и имеет административные права
     */
    protected function checkAdminAccess() {
        return isset($_SESSION['user_id']) && isset($_SESSION['is_admin']) && $_SESSION['is_admin'];
    }
    
    /**
     * Получение доступных шаблонов из типов блоков
     * Собирает уникальные шаблоны из всех типов блоков
     *
     * @param array $blockTypes Массив типов блоков
     * @return array Ассоциативный массив шаблонов с ключами и значениями
     */
    protected function getAvailableTemplates($blockTypes = []): array {
        $uniqueTemplates = ['all' => 'Все шаблоны'];
        
        // Сбор уникальных шаблонов из всех типов блоков
        foreach($blockTypes as $type) {
            $template = $type['template'] ?? 'all';
            if (!isset($uniqueTemplates[$template])) {
                $uniqueTemplates[$template] = $template;
            }
        }
        
        return $uniqueTemplates;
    }
    
    /**
     * Обработка файлов ресурсов (CSS, JS)
     * Фильтрует массив файлов, удаляя пустые значения
     *
     * @param array $files Массив файлов ресурсов
     * @return array Отфильтрованный массив файлов
     */
    protected function processAssetFiles($files): array {
        return array_filter($files, function($file) {
            return !empty(trim($file));
        });
    }
    
    /**
     * Рендеринг формы с данными для повторного отображения
     * Используется при ошибках валидации для показа формы с заполненными данными
     *
     * @param array $data Данные из формы
     * @param string $blockTypeName Имя типа блока
     * @param array|null $block Исходные данные блока (при редактировании)
     * @return void
     */
    protected function renderFormWithData($data, $blockTypeName, $block = null) {
        // Получение типов блоков и выбор текущего типа
        $blockTypes = $this->blockTypeManager->getBlockTypes();
        $selectedType = $blockTypeName;
        
        // Получение настроек из данных или существующего блока
        $settings = $data['settings'] ?? ($block ? json_decode($block['settings'], true) : []);
        
        // Получение данных о ресурсах (CSS, JS файлы, инлайн стили и скрипты)
        $cssFiles = $data['css_files'] ?? ($block && $block['css_files'] ? json_decode($block['css_files'], true) : []);
        $jsFiles = $data['js_files'] ?? ($block && $block['js_files'] ? json_decode($block['js_files'], true) : []);
        $inlineCss = $data['inline_css'] ?? ($block['inline_css'] ?? '');
        $inlineJs = $data['inline_js'] ?? ($block['inline_js'] ?? '');
        
        $systemCss = [];
        $systemJs = [];

        // Получение доступных шаблонов для выбранного типа блока
        $availableTemplates = ['default' => 'Стандартный шаблон'];
        if ($blockTypeName !== 'DefaultBlock') {
            $blockType = $this->blockTypeManager->getBlockType($blockTypeName);
            if ($blockType && $blockType['class']) {
                $availableTemplates = $blockType['class']->getAvailableTemplates();
                $systemCss = $blockType['class']->getSystemCss();
                $systemJs = $blockType['class']->getSystemJs();
            }
        }

        // Определение выбранного шаблона
        $selectedTemplate = $data['template'] ?? ($block['template'] ?? 'default');

        // Рендеринг формы с переданными данными
        $this->render('admin/html_blocks/form', [
            'block' => $block,
            'data' => $data,
            'blockTypes' => $blockTypes,
            'selectedType' => $selectedType,
            'settings' => $settings,
            'cssFiles' => $cssFiles,
            'jsFiles' => $jsFiles,
            'inlineCss' => $inlineCss,
            'inlineJs' => $inlineJs,
            'systemCss' => $systemCss,
            'systemJs' => $systemJs,
            'availableTemplates' => $availableTemplates,
            'selectedTemplate' => $selectedTemplate
        ]);
    }
    
    /**
     * Рендеринг пустой формы или формы для редактирования
     * Отображает форму создания/редактирования с данными блока
     *
     * @param array|null $block Данные блока для редактирования (null для создания)
     * @param string $blockTypeName Имя типа блока
     * @return void
     */
    protected function renderForm($block = null, $blockTypeName = 'DefaultBlock') {
        // Получение типов блоков и выбор текущего типа
        $blockTypes = $this->blockTypeManager->getBlockTypes();
        $selectedType = $blockTypeName;
        
        // Получение настроек из блока
        $settings = $block ? json_decode($block['settings'], true) : [];
        
        // Получение данных о ресурсах из блока или инициализация пустых значений
        $cssFiles = [];
        $jsFiles = [];
        $inlineCss = '';
        $inlineJs = '';
        
        if ($block) {
            $cssFiles = $block['css_files'] ? json_decode($block['css_files'], true) : [];
            $jsFiles = $block['js_files'] ? json_decode($block['js_files'], true) : [];
            $inlineCss = $block['inline_css'] ?? '';
            $inlineJs = $block['inline_js'] ?? '';
        }
        
        // Получение системных ресурсов для типа блока (если не DefaultBlock)
        $systemCss = [];
        $systemJs = [];
        if ($blockTypeName !== 'DefaultBlock') {
            $blockType = $this->blockTypeManager->getBlockType($blockTypeName);
            if ($blockType && $blockType['class']) {
                $systemCss = $blockType['class']->getSystemCss();
                $systemJs = $blockType['class']->getSystemJs();
            }
        }

        // Рендеринг формы
        $this->render('admin/html_blocks/form', [
            'block' => $block,
            'blockTypes' => $blockTypes,
            'selectedType' => $selectedType,
            'settings' => $settings,
            'cssFiles' => $cssFiles,
            'jsFiles' => $jsFiles,
            'inlineCss' => $inlineCss,
            'inlineJs' => $inlineJs,
            'systemCss' => $systemCss,
            'systemJs' => $systemJs
        ]);
    }
}