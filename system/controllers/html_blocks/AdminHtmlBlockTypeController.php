<?php

/**
 * Контроллер управления типами HTML-блоков в админ-панели
 * Предоставляет интерфейс для управления типами HTML-блоков: просмотр, включение/выключение, удаление
 * Работает с менеджером типов блоков для управления их доступностью в системе
 * 
 * @package controllers
 * @extends Controller
 */
class AdminHtmlBlockTypeController extends Controller {
    
    /**
     * @var HtmlBlockTypeManager Менеджер типов HTML-блоков
     */
    private $blockTypeManager;
    
    /**
     * Конструктор контроллера типов HTML-блоков
     * Инициализирует менеджер типов блоков для управления доступными типами
     *
     * @param Database $db Объект подключения к базе данных
     */
    public function __construct($db) {
        parent::__construct($db);
        $this->blockTypeManager = new HtmlBlockTypeManager($db);
    }
    
    /**
     * Действие: Главная страница управления типами HTML-блоков
     * Отображает список всех доступных типов HTML-блоков с их статусом
     * 
     * @return mixed
     */
    public function adminIndexAction() {
        $action = new \html_blocks\actions\AdminTypeIndex($this->db);
        $action->setController($this);
        return $action->execute();
    }
    
    /**
     * Действие: Удаление типа HTML-блока
     * Удаляет указанный тип блока из системы (по системному имени)
     * 
     * @param string|null $systemName Системное имя удаляемого типа блока
     * @return mixed
     */
    public function deleteAction($systemName = null) {
        $action = new \html_blocks\actions\AdminTypeDelete($this->db);
        $action->setController($this);
        $action->setSystemName($systemName);
        return $action->execute();
    }
    
    /**
     * Действие: Переключение статуса типа HTML-блока
     * Включает или отключает указанный тип блока без его удаления из системы
     * 
     * @param string|null $systemName Системное имя типа блока для переключения
     * @return mixed
     */
    public function toggleAction($systemName = null) {
        $action = new \html_blocks\actions\AdminTypeToggle($this->db);
        $action->setController($this);
        $action->setSystemName($systemName);
        return $action->execute();
    }
}