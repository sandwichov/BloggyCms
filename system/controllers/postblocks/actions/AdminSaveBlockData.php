<?php

namespace postblocks\actions;

/**
 * Действие сохранения данных постблока из формы
 * Используется для AJAX-запросов в административной панели
 * Принимает данные из POST-формы, обрабатывает настройки и контент блока,
 * подготавливает их через методы блока и возвращает результат
 * 
 * @package postblocks\actions
 * @extends PostBlockAction
 */
class AdminSaveBlockData extends PostBlockAction {
    
    /**
     * Метод выполнения сохранения данных блока
     * Проверяет права доступа, получает данные из POST,
     * извлекает настройки и контент (из массивов или точечных полей),
     * подготавливает их через методы блока и возвращает результат
     * 
     * @return void
     */
    public function execute() {
        // Установка заголовка для JSON-ответа с поддержкой UTF-8
        header('Content-Type: application/json; charset=utf-8');
        
        try {
            // Проверка прав доступа администратора через сессию
            if (!isset($_SESSION['is_admin'])) {
                throw new \Exception('Доступ запрещен');
            }

            // Получение данных из POST-запроса
            $blockId = $_POST['block_id'] ?? '';
            $blockType = $_POST['block_type'] ?? '';
            
            // Проверка наличия обязательных полей
            if (empty($blockId) || empty($blockType)) {
                throw new \Exception('Не указаны данные блока');
            }

            // Получение экземпляра блока через менеджер
            $postBlock = $this->postBlockManager->getPostBlock($blockType);
            if (!$postBlock || !$postBlock['class']) {
                throw new \Exception('Блок не найден: ' . $blockType);
            }

            $blockInstance = $postBlock['class'];
            
            // Извлечение настроек из POST
            $settings = $this->extractSettingsFromPost();
            
            // Извлечение контента из POST
            $content = $this->extractContentFromPost();

            // Подготовка настроек через метод блока (если существует)
            if (method_exists($blockInstance, 'prepareSettings')) {
                $settings = $blockInstance->prepareSettings($settings);
            }
            
            // Подготовка контента через метод блока (если существует)
            if (method_exists($blockInstance, 'prepareContent')) {
                $content = $blockInstance->prepareContent($content);
            }

            // Возврат успешного ответа с подготовленными данными
            echo json_encode([
                'success' => true,
                'content' => $content,
                'settings' => $settings,
                'message' => 'Данные успешно сохранены'
            ], JSON_UNESCAPED_UNICODE);

        } catch (\Exception $e) {
            // Возврат ответа с ошибкой
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage(),
                'content' => [],
                'settings' => []
            ], JSON_UNESCAPED_UNICODE);
        }
        
        exit;
    }

    /**
     * Извлекает настройки из POST-запроса
     * Поддерживает два формата:
     * - Массив $_POST['settings']
     * - Отдельные поля вида settings[ключ]=значение
     * 
     * @return array Массив настроек
     */
    private function extractSettingsFromPost() {
        $settings = [];
        
        if (isset($_POST['settings']) && is_array($_POST['settings'])) {
            // Прямой массив настроек
            $settings = $_POST['settings'];
        } else {
            // Поиск полей вида settings[ключ]
            foreach ($_POST as $key => $value) {
                if (strpos($key, 'settings[') === 0) {
                    $settingKey = str_replace(['settings[', ']'], '', $key);
                    $settings[$settingKey] = $value;
                }
            }
        }
        
        return $settings;
    }

    /**
     * Извлекает контент из POST-запроса
     * Поддерживает два формата:
     * - Массив $_POST['content']
     * - Отдельные поля вида content[ключ]=значение
     * 
     * @return array Массив контента
     */
    private function extractContentFromPost() {
        $content = [];
        
        if (isset($_POST['content']) && is_array($_POST['content'])) {
            // Прямой массив контента
            $content = $_POST['content'];
        } else {
            // Поиск полей вида content[ключ]
            foreach ($_POST as $key => $value) {
                if (strpos($key, 'content[') === 0) {
                    $contentKey = str_replace(['content[', ']'], '', $key);
                    $content[$contentKey] = $value;
                }
            }
        }
        
        return $content;
    }
}