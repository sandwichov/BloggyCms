<?php

namespace postblocks\actions;

/**
 * Действие загрузки файлов для постблока
 * Используется для AJAX-запросов в административной панели
 * Обрабатывает загрузку файлов и данных формы для конкретного блока,
 * делегирует подготовку данных методам блока при их наличии
 * 
 * @package postblocks\actions
 * @extends PostBlockAction
 */
class AdminUploadBlockFiles extends PostBlockAction {
    
    /**
     * Метод выполнения загрузки файлов для блока
     * Проверяет права доступа, получает данные из POST,
     * загружает экземпляр блока, обрабатывает файлы и поля формы,
     * подготавливает данные через методы блока и возвращает результат
     * 
     * @return void
     */
    public function execute() {
        if (!$this->checkAdminAccess()) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Доступ запрещен'
            ]);
            return;
        }
        
        try {
            $blockId = $_POST['block_id'] ?? '';
            $blockType = $_POST['block_type'] ?? '';
            
            if (empty($blockType)) {
                throw new \Exception('Не указан тип блока');
            }

            $contentJson = $_POST['content_json'] ?? '{}';
            $settingsJson = $_POST['settings_json'] ?? '{}';
            $content = json_decode($contentJson, true) ?: [];
            $settings = json_decode($settingsJson, true) ?: [];
            $postBlock = $this->postBlockManager->getPostBlock($blockType);
            
            if (!$postBlock || !$postBlock['class']) {
                throw new \Exception('Блок не найден: ' . $blockType);
            }

            $blockInstance = $postBlock['class'];
            
            if (method_exists($blockInstance, 'prepareContent')) {
                $content = $blockInstance->prepareContent($content);
            }
            
            if (method_exists($blockInstance, 'prepareSettings')) {
                $settings = $blockInstance->prepareSettings($settings);
            }

            if (empty($content) && empty($_FILES)) {
                $this->processPostFields($content, $settings);
            }

            $this->jsonResponse([
                'success' => true,
                'message' => 'Данные блока успешно сохранены',
                'block_data' => [
                    'content' => $content,
                    'settings' => $settings
                ]
            ]);

        } catch (\Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => $e->getMessage(),
                'block_data' => [
                    'content' => [],
                    'settings' => []
                ]
            ]);
        }
    }

    /**
     * Обрабатывает поля формы из POST-запроса
     * Извлекает данные в формате content[ключ] и settings[ключ]
     * Поддерживает массивы (например, content[items][])
     * 
     * @param array &$content Ссылка на массив контента для заполнения
     * @param array &$settings Ссылка на массив настроек для заполнения
     * @return void
     */
    private function processPostFields(&$content, &$settings) {
        foreach ($_POST as $key => $value) {

            if (strpos($key, 'content[') === 0) {
                $this->processContentField($key, $value, $content);
            } 

            elseif (strpos($key, 'settings[') === 0) {
                $this->processSettingsField($key, $value, $settings);
            }
        }
    }

    /**
     * Обрабатывает одно поле контента
     * 
     * @param string $key Ключ поля (например, "content[text]" или "content[items][]")
     * @param mixed $value Значение поля
     * @param array &$content Ссылка на массив контента
     * @return void
     */
    private function processContentField($key, $value, &$content) {
        $contentKey = str_replace(['content[', ']'], '', $key);

        if (strpos($contentKey, '[]') !== false) {
            $arrayKey = str_replace('[]', '', $contentKey);
            if (!isset($content[$arrayKey])) {
                $content[$arrayKey] = [];
            }
            if (is_array($value)) {
                $content[$arrayKey] = array_merge($content[$arrayKey], $value);
            } else {
                $content[$arrayKey][] = $value;
            }
        } else {
            $content[$contentKey] = $value;
        }
    }

    /**
     * Обрабатывает одно поле настроек
     * 
     * @param string $key Ключ поля (например, "settings[align]")
     * @param mixed $value Значение поля
     * @param array &$settings Ссылка на массив настроек
     * @return void
     */
    private function processSettingsField($key, $value, &$settings) {
        $settingsKey = str_replace(['settings[', ']'], '', $key);
        $settings[$settingsKey] = $value;
    }

    /**
     * Отправляет JSON-ответ и завершает выполнение
     * 
     * @param array $data Данные для JSON-ответа
     * @return void
     */
    protected function jsonResponse($data) { 
        if (ob_get_level()) {
            ob_clean();
        }
        
        header('Content-Type: application/json');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
}