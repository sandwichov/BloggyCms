<?php

namespace settings\actions;

/**
 * Действие сброса настроек к значениям по умолчанию
 * Восстанавливает настройки указанной вкладки до заводских значений
 * 
 * @package settings\actions
 * @extends SettingsAction
 */
class AdminReset extends SettingsAction {
    
    /**
     * Метод выполнения сброса настроек
     * Проверяет права доступа, получает активную вкладку из GET-параметров,
     * загружает настройки по умолчанию, сохраняет их и перенаправляет обратно
     * 
     * @return void
     */
    public function execute() {
        // Проверка прав доступа администратора
        if (!$this->checkAdminAccess()) {
            \Notification::error('У вас нет прав для доступа к настройкам');
            $this->redirect(ADMIN_URL);
            return;
        }
        
        try {
            // Получение активной вкладки из GET-параметров (по умолчанию 'general')
            $activeTab = $_GET['tab'] ?? 'general';
            
            // Получение настроек по умолчанию для данной вкладки
            $defaultSettings = $this->getDefaultSettings($activeTab);
            
            // Сохранение настроек по умолчанию в базу данных
            $this->settingsModel->save($activeTab, $defaultSettings);
            
            // Уведомление об успешном сбросе
            \Notification::success('Настройки успешно сброшены к значениям по умолчанию');
            
        } catch (\Exception $e) {
            // Уведомление об ошибке при сбросе
            \Notification::error('Ошибка при сбросе настроек');
        }
        
        // Перенаправление обратно на страницу настроек с той же вкладкой
        $this->redirect(ADMIN_URL . '/settings?tab=' . $activeTab);
    }
}