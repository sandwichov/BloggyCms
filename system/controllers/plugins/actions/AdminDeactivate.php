<?php

namespace plugins\actions;

/**
 * Действие деактивации плагина в административной панели
 * Деактивирует указанный плагин через менеджер плагинов
 * 
 * @package plugins\actions
 * @extends PluginAction
 */
class AdminDeactivate extends PluginAction {
    
    /**
     * Метод выполнения деактивации плагина
     * Получает имя плагина из параметров, деактивирует его через менеджер плагинов
     * и перенаправляет обратно на страницу управления плагинами
     * 
     * @return void
     */
    public function execute() {
        // Получение имени плагина из параметров запроса
        $pluginName = $this->params['pluginName'] ?? null;
        
        // Проверка наличия имени плагина
        if (!$pluginName) {
            \Notification::error('Имя плагина не указано');
            $this->redirect(ADMIN_URL . '/plugins');
            return;
        }
        
        // Попытка деактивации плагина через менеджер
        if ($this->pluginManager->deactivatePlugin($pluginName)) {
            \Notification::success('Плагин успешно деактивирован');
        } else {
            \Notification::error('Ошибка при деактивации плагина');
        }
        
        // Перенаправление на страницу управления плагинами
        $this->redirect(ADMIN_URL . '/plugins');
    }
}