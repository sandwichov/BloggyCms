<?php

namespace postblocks\actions;

/**
 * Действие получения контента по умолчанию для постблока
 * Используется для AJAX-запросов при создании нового блока
 * Возвращает JSON с контентом по умолчанию для указанного типа блока
 * 
 * @package postblocks\actions
 * @extends PostBlockAction
 */
class AdminGetDefaultContent extends PostBlockAction {
    
    /**
     * Метод выполнения получения контента по умолчанию
     * Получает системное имя блока из GET-параметров,
     * загружает блок и возвращает его контент по умолчанию в формате JSON
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

            // Получение контента по умолчанию из класса блока
            $defaultContent = $postBlock['class']->getDefaultContent();

            // Возврат успешного ответа с контентом
            echo json_encode([
                'success' => true,
                'content' => $defaultContent
            ]);

        } catch (\Exception $e) {
            // Возврат ответа с ошибкой
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage(),
                'content' => []
            ]);
        }
    }
}