<?php

namespace html_blocks\actions;

/**
 * Действие получения настроек типа блока через AJAX
 * Возвращает HTML-форму с настройками для указанного типа HTML-блока
 * Используется для динамического обновления интерфейса при смене типа блока
 * 
 * @package html_blocks\actions
 * @extends HtmlBlockAction
 */
class AdminGetBlockSettings extends HtmlBlockAction {
    
    /**
     * Метод выполнения получения настроек типа блока
     * Возвращает HTML-форму настроек для указанного системного имени типа блока
     * 
     * @return void
     */
    public function execute() {
        // Получение системного имени типа блока из GET-параметров
        $systemName = $_GET['system_name'] ?? '';
        
        // Получение текущих настроек из GET-параметров (если переданы)
        $currentSettings = isset($_GET['current_settings']) ? 
            json_decode($_GET['current_settings'], true) : [];
        
        // Обработка случая с пустым системным именем или DefaultBlock
        if (empty($systemName) || $systemName === 'DefaultBlock') {
            echo '';
            return;
        }
        
        // Получение данных типа блока из менеджера
        $blockType = $this->blockTypeManager->getBlockType($systemName);
        
        // Генерация формы настроек если тип блока найден и имеет класс
        if ($blockType && $blockType['class']) {
            // Вывод HTML-формы с текущими настройками (если переданы)
            echo $blockType['class']->getSettingsForm($currentSettings);
        } else {
            // Пустой вывод если тип блока не найден
            echo '';
        }
    }
}