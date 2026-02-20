<?php

namespace html_blocks\actions;

/**
 * Действие получения доступных шаблонов для типа блока через AJAX
 * Возвращает JSON-список доступных шаблонов оформления для указанного типа HTML-блока
 * Используется для динамического обновления выбора шаблонов в интерфейсе создания/редактирования
 * 
 * @package html_blocks\actions
 * @extends HtmlBlockAction
 */
class AdminGetBlockTemplates extends HtmlBlockAction {
    
    /**
     * Метод выполнения получения шаблонов блока
     * Возвращает JSON-ответ с доступными шаблонами для указанного типа блока
     * 
     * @return void
     */
    public function execute() {
        // Установка заголовка для JSON-ответа
        header('Content-Type: application/json');
        
        try {
            // Получение имени типа блока из GET-параметров
            $blockTypeName = $_GET['block_type'] ?? '';
            
            // Обработка случая с пустым типом или DefaultBlock
            if (empty($blockTypeName) || $blockTypeName === 'DefaultBlock') {
                echo json_encode([
                    'success' => true,
                    'templates' => ['default' => 'Стандартный шаблон']
                ]);
                return;
            }
            
            // Получение данных типа блока из менеджера
            $blockType = $this->blockTypeManager->getBlockType($blockTypeName);
            
            // Проверка наличия типа блока и его класса
            if ($blockType && $blockType['class']) {
                // Получение доступных шаблонов из класса типа блока
                $templates = $blockType['class']->getAvailableTemplates();
                
                // Успешный JSON-ответ с шаблонами
                echo json_encode([
                    'success' => true,
                    'templates' => $templates
                ]);
            } else {
                // Ошибка - тип блока не найден
                echo json_encode([
                    'success' => false,
                    'message' => 'Тип блока не найден'
                ]);
            }
        } catch (\Exception $e) {
            // Обработка исключений при получении шаблонов
            echo json_encode([
                'success' => false,
                'message' => 'Ошибка при получении шаблонов: ' . $e->getMessage()
            ]);
        }
    }
}