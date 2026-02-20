<?php

/**
 * Контроллер управления HTML-блоками в админ-панели
 * Предоставляет интерфейс для создания, редактирования и управления HTML-блоками
 * Блоки могут использоваться для отображения произвольного HTML-контента в различных частях сайта
 * 
 * @package controllers
 * @extends Controller
 */
class AdminHtmlBlockController extends Controller {
    
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
     * Действие: Главная страница управления HTML-блоками
     * Отображает список всех HTML-блоков в системе
     * 
     * @return mixed
     */
    public function adminIndexAction() {
        $action = new \html_blocks\actions\AdminIndex($this->db);
        $action->setController($this);
        return $action->execute();
    }
    
    /**
     * Действие: Выбор типа блока при создании
     * Показывает доступные типы HTML-блоков для создания нового блока
     * 
     * @return mixed
     */
    public function selectTypeAction() {
        $action = new \html_blocks\actions\AdminSelectType($this->db);
        $action->setController($this);
        return $action->execute();
    }
    
    /**
     * Действие: Создание нового HTML-блока
     * Отображает форму создания блока выбранного типа
     * 
     * @return mixed
     */
    public function createAction() {
        $action = new \html_blocks\actions\AdminCreate($this->db);
        $action->setController($this);
        return $action->execute();
    }
    
    /**
     * Действие: Редактирование существующего HTML-блока
     * Отображает форму редактирования блока с указанным ID
     * 
     * @param int|null $id ID редактируемого блока
     * @return mixed
     */
    public function editAction($id = null) {
        // Проверка наличия ID блока
        if (!$id) {
            \Notification::error('ID блока не указан');
            $this->redirect(ADMIN_URL . '/html-blocks');
            return;
        }
        
        $action = new \html_blocks\actions\AdminEdit($this->db);
        $action->setController($this);
        $action->setId($id);
        return $action->execute();
    }
    
    /**
     * Действие: Удаление HTML-блока
     * Удаляет блок по указанному ID
     * 
     * @param int|null $id ID удаляемого блока
     * @return mixed
     */
    public function deleteAction($id = null) {
        // Проверка наличия ID блока
        if (!$id) {
            \Notification::error('ID блока не указан');
            $this->redirect(ADMIN_URL . '/html-blocks');
            return;
        }
        
        $action = new \html_blocks\actions\AdminDelete($this->db);
        $action->setController($this);
        $action->setId($id);
        return $action->execute();
    }
    
    /**
     * Действие: Получение настроек типа блока через AJAX
     * Возвращает HTML-форму с настройками для выбранного типа блока
     * Используется для динамического обновления формы создания/редактирования
     * 
     * @return mixed JSON-ответ с настройками типа блока
     */
    public function getBlockSettingsAction() {
        $action = new \html_blocks\actions\AdminGetBlockSettings($this->db);
        $action->setController($this);
        return $action->execute();
    }

    /**
     * Действие: Получение шаблонов блоков через AJAX
     * Возвращает список доступных шаблонов для выбранного типа блока
     * Используется для выбора предустановленных шаблонов оформления
     * 
     * @return mixed JSON-ответ со списком шаблонов
     */
    public function getBlockTemplatesAction() {
        $action = new \html_blocks\actions\AdminGetBlockTemplates($this->db);
        $action->setController($this);
        return $action->execute();
    }
    
}