<?php

namespace postblocks\actions;

/**
 * Действие удаления пресета постблока
 * Используется для AJAX-запросов в административной панели
 * Удаляет указанный пресет из базы данных
 * 
 * @package postblocks\actions
 * @extends PostBlockAction
 */
class AdminPresetDelete extends PostBlockAction {
    
    /**
     * Метод выполнения удаления пресета
     * Проверяет права доступа, получает ID пресета из POST-запроса,
     * удаляет пресет через модель и возвращает результат в формате JSON
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

            // Получение ID пресета из POST-запроса
            $presetId = $_POST['preset_id'] ?? 0;

            // Проверка наличия ID пресета
            if (empty($presetId)) {
                throw new \Exception('Не указан ID пресета');
            }

            // Удаление пресета через модель
            $result = $this->postBlockModel->deletePreset($presetId);

            // Возврат результата операции
            echo json_encode([
                'success' => $result !== false,
                'message' => $result ? 'Пресет успешно удален' : 'Ошибка при удалении пресета'
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