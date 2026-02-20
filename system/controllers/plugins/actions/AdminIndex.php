<?php

namespace plugins\actions;

/**
 * Действие отображения списка всех плагинов в административной панели
 * Главная страница управления плагинами, показывает все доступные плагины
 * и их статус (активен/неактивен)
 * 
 * @package plugins\actions
 * @extends PluginAction
 */
class AdminIndex extends PluginAction {
    
    /**
     * Метод выполнения отображения списка плагинов
     * Получает все доступные плагины и список активных плагинов,
     * передает их в шаблон для отображения
     * 
     * @return void
     */
    public function execute() {
        // Получение списка всех доступных плагинов
        $plugins = $this->pluginManager->discoverPlugins();
        
        // Получение списка активных плагинов
        $activePlugins = $this->pluginManager->getActivePlugins();
        
        // Отображение страницы со списком плагинов
        $this->render('admin/plugins/index', [
            'plugins' => $plugins,           // Все доступные плагины
            'activePlugins' => $activePlugins, // Список активных плагинов
            'pageTitle' => 'Управление плагинами' // Заголовок страницы
        ]);
    }
}