<?php

namespace postblocks\actions;

/**
 * Действие получения HTML-шаблона постблока с шорткодами
 * Используется для AJAX-запросов в административной панели
 * Возвращает JSON с шаблоном блока, содержащим шорткоды для редактирования
 * 
 * @package postblocks\actions
 * @extends PostBlockAction
 */
class AdminGetTemplate extends PostBlockAction {
    
    /**
     * Метод выполнения получения шаблона блока
     * Получает системное имя блока из GET-параметров,
     * загружает блок и возвращает его шаблон с шорткодами в формате JSON
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

            // Получение шаблона с шорткодами из класса блока
            $template = $postBlock['class']->getTemplateWithShortcodes();

            // Возврат успешного ответа с шаблоном
            echo json_encode([
                'success' => true,
                'template' => $template
            ]);

        } catch (\Exception $e) {
            // Возврат ответа с ошибкой
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
}