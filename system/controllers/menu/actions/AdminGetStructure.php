<?php

namespace menu\actions;

/**
 * Действие получения структуры меню в формате JSON
 * Используется для AJAX-запросов при редактировании меню в админ-панели
 * Возвращает структуру указанного меню в виде JSON-ответа
 * 
 * @package menu\actions
 * @extends MenuAction
 */
class AdminGetStructure extends MenuAction {
    
    /**
     * Метод выполнения получения структуры меню
     * Получает данные меню по ID и возвращает его структуру в JSON-формате
     * 
     * @return void
     */
    public function execute() {
        // Получение ID меню из параметров запроса
        $id = $this->params['id'] ?? null;
        
        // Проверка наличия ID меню
        if (!$id) {
            $this->sendJsonResponse(false, 'ID меню не указан');
            return;
        }
        
        try {
            // Получение данных меню из базы данных
            $menu = $this->menuModel->getById($id);
            
            // Проверка существования меню
            if (!$menu) {
                throw new \Exception('Меню не найдено');
            }
            
            // Декодирование структуры меню из JSON
            $structure = json_decode($menu['structure'], true) ?: [];
            
            // Отправка успешного ответа со структурой меню
            $this->sendJsonResponse(true, null, $structure);
            
        } catch (\Exception $e) {
            // Отправка ответа с ошибкой
            $this->sendJsonResponse(false, $e->getMessage());
        }
    }
    
    /**
     * Отправляет JSON-ответ и завершает выполнение скрипта
     * Устанавливает соответствующий Content-Type header
     * 
     * @param bool $success Флаг успешности операции
     * @param string|null $message Сообщение об ошибке (для неуспешных ответов)
     * @param array|null $structure Структура меню (для успешных ответов)
     * @return void
     */
    private function sendJsonResponse($success, $message = null, $structure = null) {
        // Установка заголовка для JSON-ответа
        header('Content-Type: application/json');
        
        // Формирование ответа
        $response = ['success' => $success];
        
        if ($message !== null) {
            $response['message'] = $message;
        }
        
        if ($structure !== null) {
            $response['structure'] = $structure;
        }
        
        // Отправка JSON-ответа и завершение выполнения
        echo json_encode($response);
        exit;
    }
}