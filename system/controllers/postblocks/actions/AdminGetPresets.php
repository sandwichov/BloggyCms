<?php

namespace postblocks\actions;

/**
 * Действие получения списка пресетов для постблока
 * Используется для AJAX-запросов в административной панели
 * Возвращает JSON со списком сохраненных пресетов для указанного блока
 * 
 * @package postblocks\actions
 * @extends PostBlockAction
 */
class AdminGetPresets extends PostBlockAction {
    
    /**
     * Метод выполнения получения списка пресетов
     * Проверяет права доступа, получает системное имя блока из GET-параметров
     * и возвращает список пресетов в формате JSON
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

            // Получение системного имени блока из GET-параметров
            $systemName = $_GET['system_name'] ?? '';

            // Проверка наличия системного имени
            if (empty($systemName)) {
                throw new \Exception('Не указано системное имя блока');
            }

            // Получение списка пресетов для блока через модель
            $presets = $this->postBlockModel->getBlockPresets($systemName);

            // Возврат успешного ответа со списком пресетов
            echo json_encode([
                'success' => true,
                'presets' => $presets
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