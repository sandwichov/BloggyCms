<?php

namespace tags\actions;

/**
 * Действие поиска тегов по названию (AJAX)
 * Используется для автодополнения в интерфейсе администратора
 * Принимает поисковый запрос и возвращает JSON с найденными тегами
 * 
 * @package tags\actions
 * @extends TagAction
 */
class Search extends TagAction {
    
    /**
     * Метод выполнения поиска тегов
     * Устанавливает JSON-заголовок, получает поисковый запрос из GET-параметров,
     * выполняет поиск через модель и возвращает результат в формате JSON
     * 
     * @return void
     */
    public function execute() {
        // Установка заголовка для JSON-ответа с поддержкой UTF-8
        header('Content-Type: application/json; charset=utf-8');
        
        try {
            // Проверка наличия поискового запроса
            if (!isset($_GET['q']) || empty(trim($_GET['q']))) {
                echo json_encode([]);
                exit;
            }
            
            // Получение и очистка поискового запроса
            $query = trim($_GET['q']);
            
            // Поиск тегов по названию (максимум 10 результатов)
            $tags = $this->tagModel->searchByName($query, 10);
            
            // Возврат результата в формате JSON
            echo json_encode($tags, JSON_UNESCAPED_UNICODE);
            exit;
            
        } catch (\Exception $e) {
            // Обработка ошибок сервера
            http_response_code(500);
            echo json_encode(['error' => 'Внутренняя ошибка сервера']);
            exit;
        }
    }
}