<?php

namespace categories\actions;

/**
 * Действие изменения порядка сортировки категорий
 * Обрабатывает AJAX-запросы для обновления порядка отображения категорий через drag-and-drop
 * 
 * @package categories\actions
 * @extends CategoryAction
 */
class Reorder extends CategoryAction {
    
    /**
     * Метод выполнения изменения порядка категорий
     * Обрабатывает POST-запрос с JSON-данными и обновляет порядок сортировки в базе данных
     * Все ответы возвращаются в формате JSON
     * 
     * @return void
     */
    public function execute() {
        // Установка заголовка для JSON-ответа
        header('Content-Type: application/json');
        
        try {
            // Проверка метода запроса (допустим только POST)
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new \Exception('Invalid request method');
            }
            
            // Чтение и декодирование JSON-данных из тела запроса
            $input = json_decode(file_get_contents('php://input'), true);
            
            // Валидация структуры полученных данных
            if (!isset($input['order']) || !is_array($input['order'])) {
                throw new \Exception('Invalid order data');
            }
            
            // Обработка каждого элемента порядка сортировки
            foreach ($input['order'] as $item) {
                // Проверка наличия обязательных полей в каждом элементе
                if (!isset($item['id']) || !isset($item['order'])) {
                    continue; // Пропускаем элементы с некорректной структурой
                }
                
                // Обновление порядка сортировки категории в базе данных
                $this->categoryModel->updateOrder($item['id'], $item['order']);
            }
            
            // Успешный ответ в формате JSON
            echo json_encode([
                'success' => true, 
                'message' => 'Порядок категорий обновлен'
            ]);
            
        } catch (\Exception $e) {
            // Ответ с ошибкой в формате JSON
            echo json_encode([
                'success' => false, 
                'message' => $e->getMessage()
            ]);
        }
        
        // Завершение выполнения скрипта после отправки JSON-ответа
        exit;
    }
}