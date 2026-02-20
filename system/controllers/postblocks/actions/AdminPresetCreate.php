<?php

namespace postblocks\actions;

/**
 * Действие создания нового пресета для постблока
 * Используется для AJAX-запросов в административной панели
 * Сохраняет новый шаблон (пресет) для указанного блока в базу данных
 * 
 * @package postblocks\actions
 * @extends PostBlockAction
 */
class AdminPresetCreate extends PostBlockAction {
    
    /**
     * Метод выполнения создания пресета
     * Проверяет права доступа, валидирует входные данные,
     * проверяет существование блока и уникальность имени пресета,
     * создает новый пресет и возвращает результат в формате JSON
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
            $systemName = $_POST['system_name'] ?? '';
            $presetName = $_POST['preset_name'] ?? '';
            $presetTemplate = $_POST['preset_template'] ?? '';

            // Валидация обязательных полей
            if (empty($systemName) || empty($presetName)) {
                throw new \Exception('Не указано системное имя блока или имя пресета');
            }

            // Проверка существования блока
            $postBlock = $this->postBlockManager->getPostBlock($systemName);
            if (!$postBlock) {
                throw new \Exception('Блок не найден');
            }

            // Проверка уникальности имени пресета для данного блока
            $existingPreset = $this->postBlockModel->getPresetByName($systemName, $presetName);
            if ($existingPreset) {
                throw new \Exception('Пресет с таким именем уже существует');
            }

            // Создание пресета в базе данных
            $result = $this->postBlockModel->createPreset($systemName, $presetName, $presetTemplate);

            // Обработка результата
            if ($result) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Пресет успешно создан',
                    'preset_id' => $this->db->lastInsertId() // ID созданной записи
                ]);
            } else {
                throw new \Exception('Ошибка при создании пресета');
            }

        } catch (\Exception $e) {
            // Возврат ответа с ошибкой
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
}