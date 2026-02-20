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
        // Проверка прав доступа администратора
        if (!$this->checkAdminAccess()) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Доступ запрещен'
            ]);
            return;
        }
        
        try {
            // Получение данных из POST-запроса
            $blockId = $_POST['block_id'] ?? '';
            $blockType = $_POST['block_type'] ?? '';
            
            // Проверка наличия типа блока
            if (empty($blockType)) {
                throw new \Exception('Не указан тип блока');
            }

            // Получение экземпляра блока через менеджер
            $postBlock = $this->postBlockManager->getPostBlock($blockType);
            
            if (!$postBlock || !$postBlock['class']) {
                throw new \Exception('Блок не найден: ' . $blockType);
            }

            $blockInstance = $postBlock['class'];
            
            // Инициализация данных
            $content = [];
            $settings = [];
            
            // Обработка загрузки файлов через методы блока (если существуют)
            if (method_exists($blockInstance, 'prepareContent')) {
                $content = $blockInstance->prepareContent($content);
            }
            
            if (method_exists($blockInstance, 'prepareSettings')) {
                $settings = $blockInstance->prepareSettings($settings);
            }

            // Для блоков без специальной обработки файлов, обрабатываем обычные поля
            if (empty($content) && empty($_FILES)) {
                // Обработка полей формы из POST
                $this->processPostFields($content, $settings);
            }

            // Возврат успешного ответа с данными блока
            $this->jsonResponse([
                'success' => true,
                'message' => 'Данные блока успешно сохранены',
                'block_data' => [
                    'content' => $content,
                    'settings' => $settings
                ]
            ]);

        } catch (\Exception $e) {
            // Возврат ответа с ошибкой
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
            // Обработка полей контента
            if (strpos($key, 'content[') === 0) {
                $this->processContentField($key, $value, $content);
            } 
            // Обработка полей настроек
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
        
        // Обработка массивов (например, items[])
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
            // Обычное поле
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
    private function jsonResponse($data) {
        header('Content-Type: application/json');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }
}