<?php

namespace postblocks\actions;

/**
 * Действие получения настроек по умолчанию для постблока
 * Используется для AJAX-запросов при создании нового блока
 * Возвращает JSON с настройками по умолчанию для указанного типа блока
 * 
 * @package postblocks\actions
 * @extends PostBlockAction
 */
class AdminGetDefaultSettings extends PostBlockAction {
    
    /**
     * Метод выполнения получения настроек по умолчанию
     * Получает системное имя блока из GET-параметров,
     * загружает блок и возвращает его настройки по умолчанию в формате JSON
     * 
     * @return void
     */
    public function execute() {
        // Установка заголовка для JSON-ответа
        header('Content-Type: application/json');
        
        try {
            // Получение системного имени блока из GET-параметров
            $systemName = $_GET['system_name'] ?? '';
            
            // Проверка наличия системного имени
            if (empty($systemName)) {
                throw new \Exception('Системное имя блока не указано');
            }

            // Получение данных постблока через менеджер
            $postBlock = $this->postBlockManager->getPostBlock($systemName);
            if (!$postBlock || !$postBlock['class']) {
                throw new \Exception('Блок не найден');
            }

            // Получение настроек по умолчанию из класса блока
            $defaultSettings = $postBlock['class']->getDefaultSettings();

            // Возврат успешного ответа с настройками
            echo json_encode([
                'success' => true,
                'settings' => $defaultSettings
            ]);

        } catch (\Exception $e) {
            // Возврат ответа с ошибкой
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage(),
                'settings' => []
            ]);
        }
    }
}