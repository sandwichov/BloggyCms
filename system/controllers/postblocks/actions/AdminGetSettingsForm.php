<?php

namespace postblocks\actions;

/**
 * Действие получения HTML-формы настроек для постблока
 * Используется для AJAX-запросов в административной панели
 * Возвращает готовую HTML-форму с табами для редактирования контента и настроек блока
 * 
 * @package postblocks\actions
 * @extends PostBlockAction
 */
class AdminGetSettingsForm extends PostBlockAction {
    
    /**
     * Метод выполнения получения формы настроек
     * Проверяет права доступа, получает системное имя блока и текущие данные,
     * генерирует и возвращает HTML-форму с табами для редактирования блока
     * 
     * @return void
     */
    public function execute() {
        // Проверка прав доступа администратора
        if (!$this->checkAdminAccess()) {
            echo '<div class="alert alert-danger">У вас нет прав доступа</div>';
            return;
        }
        
        // Получение системного имени блока из GET-параметров
        $systemName = $_GET['system_name'] ?? '';
        
        // Получение текущих настроек и контента (опционально)
        $currentSettings = isset($_GET['current_settings']) ? 
            json_decode($_GET['current_settings'], true) : [];
        $currentContent = isset($_GET['current_content']) ? 
            json_decode($_GET['current_content'], true) : [];
        
        // Если системное имя не указано, возвращаем пустую строку
        if (empty($systemName)) {
            echo '';
            return;
        }
        
        // Получение данных постблока через менеджер
        $postBlock = $this->postBlockManager->getPostBlock($systemName);
        
        if ($postBlock && $postBlock['class']) {
            $blockInstance = $postBlock['class'];
            
            // Создаем полную форму с табами
            $html = '
            <form id="post-block-form">
                <ul class="nav nav-tabs mb-4" id="blockTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="content-tab" data-bs-toggle="tab" data-bs-target="#content-tab-pane" type="button" role="tab">
                            <i class="bi bi-text-left me-1"></i>Содержимое
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="settings-tab" data-bs-toggle="tab" data-bs-target="#settings-tab-pane" type="button" role="tab">
                            <i class="bi bi-gear me-1"></i>Настройки
                        </button>
                    </li>
                </ul>
                
                <div class="tab-content" id="blockTabsContent">
                    <div class="tab-pane fade show active" id="content-tab-pane" role="tabpanel">
                        ' . $blockInstance->getContentForm($currentContent) . '
                    </div>
                    <div class="tab-pane fade" id="settings-tab-pane" role="tabpanel">
                        ' . $blockInstance->getSettingsForm($currentSettings) . '
                    </div>
                </div>
            </form>';
            
            echo $html;
        } else {
            echo '<div class="alert alert-warning">Блок не найден</div>';
        }
    }
}