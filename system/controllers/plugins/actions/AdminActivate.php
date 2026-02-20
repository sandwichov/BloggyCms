<?php

namespace plugins\actions;

/**
 * Действие активации плагина в административной панели
 * Активирует указанный плагин через менеджер плагинов
 * 
 * @package plugins\actions
 * @extends PluginAction
 */
class AdminActivate extends PluginAction {
    
    /**
     * Метод выполнения активации плагина
     * Получает имя плагина из параметров, активирует его через менеджер плагинов
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
        
        // Попытка активации плагина через менеджер
        if ($this->pluginManager->activatePlugin($pluginName)) {
            \Notification::success('Плагин успешно активирован');
        } else {
            \Notification::error('Ошибка при активации плагина');
        }
        
        // Перенаправление на страницу управления плагинами
        $this->redirect(ADMIN_URL . '/plugins');
    }
}