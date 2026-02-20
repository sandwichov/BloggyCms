<?php

namespace postblocks\actions;

/**
 * Действие получения HTML-предпросмотра постблока
 * Используется для AJAX-запросов в административной панели
 * Принимает данные блока и возвращает сгенерированный HTML для предпросмотра
 * 
 * @package postblocks\actions
 * @extends PostBlockAction
 */
class AdminGetPreview extends PostBlockAction {
    
    /**
     * Метод выполнения получения предпросмотра блока
     * Принимает JSON с данными блока, создает экземпляр блока,
     * загружает ассеты и генерирует HTML для предпросмотра
     * 
     * @return void
     */
    public function execute() {
        // Установка заголовка для JSON-ответа
        header('Content-Type: application/json');
        
        try {
            // Получение и декодирование JSON из тела запроса
            $input = json_decode(file_get_contents('php://input'), true);
            
            // Проверка наличия типа блока
            if (!isset($input['block_type'])) {
                throw new Exception('Тип блока не указан');
            }
            
            // Извлечение данных из запроса
            $blockType = $input['block_type'];
            $content = $input['content'] ?? [];
            $settings = $input['settings'] ?? [];
            
            // Получение экземпляра блока через менеджер
            $blockInstance = $this->postBlockManager->getBlockInstance($blockType);
            
            // Проверка существования блока
            if (!$blockInstance) {
                throw new Exception("Блок {$blockType} не найден");
            }
            
            // Загрузка ассетов (CSS/JS) для предпросмотра
            $blockInstance->loadPreviewAssets();
            
            // Получение HTML предпросмотра от экземпляра блока
            $html = $blockInstance->getPreviewHtml($content, $settings);
            
            // Замена плейсхолдера {block_id} на реальный ID если передан
            if (isset($input['block_id'])) {
                $html = str_replace('{block_id}', $input['block_id'], $html);
            }
            
            // Возврат успешного ответа с HTML
            echo json_encode([
                'success' => true,
                'html' => $html,
                'block_type' => $blockType
            ]);
            
        } catch (Exception $e) {
            // Возврат ответа с ошибкой
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage(),
                'html' => $this->renderErrorHtml($e->getMessage())
            ]);
        }
        exit;
    }
    
    /**
     * Генерирует HTML для отображения ошибки в предпросмотре
     * 
     * @param string $error Текст ошибки
     * @return string HTML-код с сообщением об ошибке
     */
    private function renderErrorHtml($error): string {
        return '<div class="alert alert-danger small">' . 
               htmlspecialchars($error) . 
               '</div>';
    }
}