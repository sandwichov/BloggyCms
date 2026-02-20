<?php

namespace plugins\actions;

/**
 * Действие обработки пользовательских запросов к плагину в административной панели
 * Позволяет плагинам обрабатывать собственные AJAX-запросы и пользовательские действия
 * 
 * @package plugins\actions
 * @extends PluginAction
 */
class AdminHandle extends PluginAction {
    
    /**
     * Метод выполнения обработки пользовательского действия плагина
     * Получает имя плагина и название действия из параметров,
     * передает управление соответствующему методу плагина и обрабатывает результат
     * 
     * @return void
     */
    public function execute() {
        // Получение параметров из запроса
        $pluginName = $this->params['pluginName'] ?? null;
        $action = $this->params['action'] ?? null;
        
        // Проверка наличия обязательных параметров
        if (!$pluginName || !$action) {
            \Notification::error('Параметры не указаны');
            $this->redirect(ADMIN_URL . '/plugins');
            return;
        }
        
        // Получение экземпляра плагина
        $plugin = $this->pluginManager->getPlugin($pluginName);
        
        // Проверка существования плагина
        if (!$plugin) {
            \Notification::error('Плагин не найден');
            $this->redirect(ADMIN_URL . '/plugins');
            return;
        }
        
        // Вызов метода обработки запроса у плагина
        // В $result может быть:
        // - строка для прямого вывода (обычно HTML или JSON)
        // - другой результат - перенаправление на страницу настроек
        $result = $plugin->handleAdminRequest($action, $_REQUEST);
        
        // Обработка результата
        if (is_string($result)) {
            // Если результат - строка, выводим её напрямую
            echo $result;
        } else {
            // Иначе перенаправляем на страницу настроек плагина
            $this->redirect(ADMIN_URL . '/plugins/settings/' . $pluginName);
        }
    }
}