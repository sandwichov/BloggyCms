<?php

/**
 * Контроллер управления плагинами в административной панели
 * Обрабатывает запросы, связанные с активацией, деактивацией, настройками и управлением плагинами
 * Делегирует выполнение конкретных операций специализированным классам действий
 * 
 * @package Controllers
 * @extends Controller
 */
class AdminPluginsController extends Controller {
    
    /** @var SettingsModel Модель для работы с настройками */
    private $settingsModel;
    
    /** @var PluginManager Менеджер для управления плагинами */
    private $pluginManager;
    
    /**
     * Конструктор контроллера
     * Инициализирует модели и менеджер для работы с плагинами
     * 
     * @param object $db Подключение к базе данных
     */
    public function __construct($db) {
        parent::__construct($db);
        $this->settingsModel = new SettingsModel($db);
        $this->pluginManager = new PluginManager($db);
    }
    
    /**
     * Отображает список всех плагинов в административной панели
     * Главная страница управления плагинами
     * 
     * @return void
     */
    public function adminIndexAction() {
        $action = new \plugins\actions\AdminIndex($this->db);
        $action->setController($this);
        return $action->execute();
    }

    /**
     * Активирует указанный плагин
     * 
     * @param string $pluginName Название плагина для активации
     * @return void
     */
    public function activateAction($pluginName) {
        $action = new \plugins\actions\AdminActivate($this->db, ['pluginName' => $pluginName]);
        $action->setController($this);
        return $action->execute();
    }

    /**
     * Деактивирует указанный плагин
     * 
     * @param string $pluginName Название плагина для деактивации
     * @return void
     */
    public function deactivateAction($pluginName) {
        $action = new \plugins\actions\AdminDeactivate($this->db, ['pluginName' => $pluginName]);
        $action->setController($this);
        return $action->execute();
    }
    
    /**
     * Отображает страницу настроек указанного плагина
     * 
     * @param string $pluginName Название плагина
     * @return void
     */
    public function settingsAction($pluginName) {
        $action = new \plugins\actions\AdminSettings($this->db, ['pluginName' => $pluginName]);
        $action->setController($this);
        return $action->execute();
    }

    /**
     * Обрабатывает пользовательские действия плагина
     * Позволяет плагинам определять собственные обработчики действий
     * 
     * @param string $pluginName Название плагина
     * @param string $action Название действия
     * @return void
     */
    public function handleAction($pluginName, $action) {
        $params = [
            'pluginName' => $pluginName,
            'action' => $action
        ];
        $handler = new \plugins\actions\AdminHandle($this->db, $params);
        $handler->setController($this);
        return $handler->execute();
    }
}