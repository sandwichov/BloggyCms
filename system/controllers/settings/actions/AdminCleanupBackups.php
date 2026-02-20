<?php

namespace settings\actions;

/**
 * Действие очистки старых резервных копий настроек
 * Удаляет устаревшие резервные копии через BackupHelper
 * 
 * @package settings\actions
 * @extends SettingsAction
 */
class AdminCleanupBackups extends SettingsAction {
    
    /**
     * Метод выполнения очистки резервных копий
     * Проверяет права администратора, вызывает метод очистки
     * и показывает количество удаленных копий
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
        
        // Очистка всех резервных копий через хелпер
        $deletedCount = \BackupHelper::cleanupAllBackups();
        
        // Уведомление о количестве удаленных копий
        \Notification::success("Удалено резервных копий: {$deletedCount}");
        
        // Перенаправление на страницу настроек с открытой вкладкой "site"
        $this->redirect(ADMIN_URL . '/settings?tab=site');
    }
}