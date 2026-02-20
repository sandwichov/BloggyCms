<?php

namespace postblocks\actions;

/**
 * Действие обновления существующего пресета постблока
 * Используется для AJAX-запросов в административной панели
 * Обновляет название и/или шаблон указанного пресета в базе данных
 * 
 * @package postblocks\actions
 * @extends PostBlockAction
 */
class AdminPresetUpdate extends PostBlockAction {
    
    /**
     * Метод выполнения обновления пресета
     * Проверяет права доступа, валидирует входные данные,
     * проверяет существование пресета и уникальность нового имени,
     * обновляет пресет и возвращает результат в формате JSON
     * 
     * @return void
     */
    public function execute() {
        // Установка заголовка для JSON-ответа
        header('Content-Type: application/json');
        
        try {
            // Проверка прав доступа администратора
            if (!$this->checkAdminAccess()) {
                throw new \Exception('Доступ запрещен');
            }

            // Получение данных из POST-запроса
            $presetId = $_POST['preset_id'] ?? 0;
            $presetName = $_POST['preset_name'] ?? '';
            $presetTemplate = $_POST['preset_template'] ?? '';

            // Валидация обязательных полей
            if (empty($presetId) || empty($presetName)) {
                throw new \Exception('Не указаны обязательные параметры');
            }

            // Получение текущего пресета для проверки существования
            $preset = $this->postBlockModel->getPreset($presetId);
            if (!$preset) {
                throw new \Exception('Пресет не найден');
            }

            // Проверка уникальности нового имени пресета (кроме текущего)
            $existingPreset = $this->postBlockModel->getPresetByName($preset['block_system_name'], $presetName);
            if ($existingPreset && $existingPreset['id'] != $presetId) {
                throw new \Exception('Пресет с таким именем уже существует');
            }

            // Обновление пресета в базе данных
            $result = $this->postBlockModel->updatePreset($presetId, $presetName, $presetTemplate);

            // Возврат результата операции
            echo json_encode([
                'success' => $result !== false,
                'message' => $result ? 'Пресет успешно обновлен' : 'Ошибка при обновлении пресета'
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