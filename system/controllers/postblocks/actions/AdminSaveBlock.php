<?php

namespace postblocks\actions;

/**
 * Действие сохранения постблока
 * Используется для AJAX-запросов в административной панели
 * Принимает данные блока, валидирует их через класс блока и возвращает результат
 * 
 * @package postblocks\actions
 * @extends PostBlockAction
 */
class AdminSaveBlock extends PostBlockAction {
    
    /**
     * Метод выполнения сохранения блока
     * Проверяет права доступа, метод запроса, получает данные из JSON,
     * валидирует настройки через класс блока и возвращает результат
     * 
     * @return void
     */
    public function execute() {
        // Проверка прав доступа администратора
        if (!$this->checkAdminAccess()) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'У вас нет прав доступа'
            ]);
            return;
        }
        
        try {
            // Проверка метода запроса
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new \Exception('Invalid request method');
            }

            // Получение и декодирование JSON из тела запроса
            $input = json_decode(file_get_contents('php://input'), true);
            
            // Извлечение данных из запроса
            $blockType = $input['type'] ?? '';
            $content = $input['content'] ?? [];
            $settings = $input['settings'] ?? [];

            // Проверка наличия типа блока
            if (empty($blockType)) {
                throw new \Exception('Block type is required');
            }

            // Валидация настроек через класс блока
            $postBlock = $this->postBlockManager->getPostBlock($blockType);
            if ($postBlock && $postBlock['class']) {
                // Валидация настроек
                list($isValid, $errors) = $postBlock['class']->validateSettings($settings);
                if (!$isValid) {
                    throw new \Exception('Validation errors: ' . implode(', ', $errors));
                }
                
                // Подготовка настроек (очистка, нормализация)
                $settings = $postBlock['class']->prepareSettings($settings);
            }

            // Возврат успешного ответа с данными
            $this->jsonResponse([
                'success' => true,
                'message' => 'Block saved successfully',
                'content' => $content,
                'settings' => $settings
            ]);

        } catch (\Exception $e) {
            // Возврат ответа с ошибкой
            $this->jsonResponse([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Отправляет JSON-ответ и завершает выполнение
     * 
     * @param array $data Данные для JSON-ответа
     * @return void
     */
    private function jsonResponse($data) {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
}