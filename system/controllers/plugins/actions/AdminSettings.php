<?php

namespace plugins\actions;

/**
 * Действие отображения и обработки настроек плагина в административной панели
 * Позволяет просматривать и изменять конфигурацию конкретного плагина
 * 
 * @package plugins\actions
 * @extends PluginAction
 */
class AdminSettings extends PluginAction {
    
    /**
     * Метод выполнения отображения и обработки настроек плагина
     * Получает имя плагина из параметров, загружает плагин,
     * обрабатывает POST-запрос для сохранения настроек или отображает форму настроек
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
        
        // Получение экземпляра плагина
        $plugin = $this->pluginManager->getPlugin($pluginName);
        
        // Проверка существования плагина
        if (!$plugin) {
            \Notification::error('Плагин не найден');
            $this->redirect(ADMIN_URL . '/plugins');
            return;
        }
        
        // Обработка POST-запроса (сохранение настроек)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                // Получение данных настроек из POST
                $settings = $_POST['settings'] ?? [];
                
                // Сохранение настроек через метод плагина
                $plugin->saveSettings($settings);
                
                // Уведомление об успехе и перенаправление
                \Notification::success('Настройки сохранены');
                $this->redirect(ADMIN_URL . '/plugins/settings/' . $pluginName);
                return;
                
            } catch (\Exception $e) {
                // Уведомление об ошибке при сохранении
                \Notification::error('Ошибка сохранения настроек: ' . $e->getMessage());
            }
        }
        
        // Отображение формы настроек плагина
        $this->render('admin/plugins/settings', [
            'plugin' => $plugin,                                        // Экземпляр плагина
            'settings' => $plugin->getSettings(),                       // Текущие настройки
            'pluginName' => $pluginName,                                // Имя плагина
            'pageTitle' => 'Настройки плагина: ' . $plugin->getName()   // Заголовок
        ]);
    }
}