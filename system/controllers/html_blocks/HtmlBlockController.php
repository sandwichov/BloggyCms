<?php

/**
 * Контроллер HTML-блоков для фронтенда
 * Обрабатывает отображение HTML-блоков на сайте, загрузку их ресурсов и рендеринг контента
 * Поддерживает различные типы блоков через систему менеджеров типов
 * 
 * @package controllers
 * @extends Controller
 */
class HtmlBlockController extends Controller {
    
    /**
     * @var HtmlBlockModel Модель для работы с HTML-блоками
     */
    private $htmlBlockModel;
    
    /**
     * @var HtmlBlockTypeManager Менеджер типов HTML-блоков
     */
    private $blockTypeManager;
    
    /**
     * Конструктор контроллера HTML-блоков
     * Инициализирует модели для работы с блоками и их типами
     *
     * @param Database $db Объект подключения к базе данных
     */
    public function __construct($db) {
        parent::__construct($db);
        $this->htmlBlockModel = new HtmlBlockModel($db);
        $this->blockTypeManager = new HtmlBlockTypeManager($db);
    }
    
    /**
     * Действие: Отображение HTML-блока на фронтенде
     * Загружает и рендерит HTML-блок по его slug, подключает необходимые ресурсы
     * 
     * @param string|null $slug URL-идентификатор блока
     * @return void
     */
    public function showAction($slug = null) {
        // Проверка наличия slug блока
        if (!$slug) {
            \Notification::error('Slug блока не указан');
            $this->redirect(BASE_URL . '/404');
            return;
        }
        
        try {
            // Получение данных блока по slug
            $block = $this->htmlBlockModel->getBySlug($slug);
        
            // Проверка существования блока
            if (!$block) {
                \Notification::error('HTML-блок не найден');
                $this->redirect(BASE_URL . '/404');
                return;
            }
            
            // Загрузка ресурсов блока (CSS, JS файлы)
            $this->loadBlockAssetsFromDatabase($block);
            
            // Загрузка фронтенд-ресурсов для специфичных типов блоков
            if (!empty($block['block_type']) && $block['block_type'] !== 'DefaultBlock') {
                $this->blockTypeManager->loadBlockFrontendAssets($block['block_type']);
            }
            
            // Декодирование настроек блока из JSON
            $settings = [];
            if (!empty($block['settings'])) {
                $settings = json_decode($block['settings'], true);
            }
            
            // Рендеринг содержимого блока в зависимости от его типа
            $blockContent = '';
            if (!empty($block['block_type'])) {
                if ($block['block_type'] === 'DefaultBlock') {
                    // Для DefaultBlock используем HTML из настроек
                    $blockContent = $settings['html'] ?? '';
                    // Обработка шорткодов
                    if (function_exists('process_shortcodes')) {
                        $blockContent = process_shortcodes($blockContent);
                    }
                } else {
                    $blockContent = $this->blockTypeManager->renderBlockFront(
                        $block['block_type'], 
                        $settings,
                        $block['template'] ?? null
                    );
                }
            }
            
            // Отображение информационного сообщения для блоков без содержимого
            if (empty($blockContent)) {
                $blockContent = '<div class="alert alert-info">Блок "' . htmlspecialchars($block['name'] ?? '') . '" не имеет содержимого.</div>';
            }
            
            /**
             * Рендеринг страницы HTML-блока
             * 
             * @param string $template Путь к шаблону (front/html_block)
             * @param array $data Данные для шаблона:
             * - block: данные HTML-блока
             * - content: сгенерированное содержимое блока
             * - title: название блока для заголовка страницы
             */
            $this->render('front/html_block', [
                'block' => $block,
                'content' => $blockContent,
                'title' => $block['name']
            ]);
            
        } catch (\Exception $e) {
            // Обработка ошибок при загрузке блока
            \Notification::error('Ошибка при загрузке HTML-блока: ' . $e->getMessage());
            $this->redirect(BASE_URL);
        }
    }
    
    /**
     * Загрузка ресурсов блока из базы данных
     * Подключает CSS и JavaScript файлы, а также инлайн-стили и скрипты
     *
     * @param array $block Данные HTML-блока
     * @return void
     */
    private function loadBlockAssetsFromDatabase($block) {
        // Подключение CSS файлов блока
        if (!empty($block['css_files'])) {
            $cssFiles = json_decode($block['css_files'], true);
            foreach ($cssFiles as $cssFile) {
                add_frontend_css($cssFile);
            }
        }
        
        // Подключение JavaScript файлов блока
        if (!empty($block['js_files'])) {
            $jsFiles = json_decode($block['js_files'], true);
            foreach ($jsFiles as $jsFile) {
                add_frontend_js($jsFile);
            }
        }
        
        // Добавление инлайн CSS стилей
        if (!empty($block['inline_css'])) {
            add_inline_css($block['inline_css']);
        }
        
        // Добавление инлайн JavaScript кода
        if (!empty($block['inline_js'])) {
            add_inline_js($block['inline_js']);
        }
    }
    
    /**
     * Статический метод для рендеринга ресурсов блока
     * Используется для подключения ресурсов блока в других частях приложения
     *
     * @param array $block Данные HTML-блока
     * @return void
     */
    public static function renderBlockAssets($block) {
        // Подключение CSS файлов
        if (!empty($block['css_files'])) {
            $cssFiles = json_decode($block['css_files'], true);
            foreach ($cssFiles as $cssFile) {
                add_frontend_css($cssFile);
            }
        }
        
        // Подключение JavaScript файлов
        if (!empty($block['js_files'])) {
            $jsFiles = json_decode($block['js_files'], true);
            foreach ($jsFiles as $jsFile) {
                add_frontend_js($jsFile);
            }
        }
        
        // Добавление инлайн CSS стилей
        if (!empty($block['inline_css'])) {
            add_inline_css($block['inline_css']);
        }
        
        // Добавление инлайн JavaScript кода
        if (!empty($block['inline_js'])) {
            add_inline_js($block['inline_js']);
        }
    }
}