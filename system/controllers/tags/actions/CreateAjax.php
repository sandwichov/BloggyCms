<?php

namespace tags\actions;

/**
 * Действие создания тега через AJAX (для автодополнения)
 * Используется при создании постов для динамического добавления новых тегов
 * Проверяет существование тега и создает новый, если его нет
 * 
 * @package tags\actions
 * @extends TagAction
 */
class CreateAjax extends TagAction {
    
    /**
     * Метод выполнения создания тега через AJAX
     * Принимает POST-запрос с названием тега, проверяет существование,
     * создает новый тег (если не существует) и возвращает JSON-ответ
     * 
     * @return void
     */
    public function execute() {
        // Установка заголовка для JSON-ответа
        header('Content-Type: application/json');
        
        try {
            // Проверка метода запроса (только POST)
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new \Exception('Invalid request method');
            }
            
            // Получение и очистка названия тега
            $name = trim($_POST['name'] ?? '');
            
            // Валидация: название не может быть пустым
            if (empty($name)) {
                throw new \Exception('Название тега не может быть пустым');
            }
            
            // Поиск существующего тега по названию (регистронезависимо)
            $allTags = $this->tagModel->getAll();
            $existingTag = null;
            
            foreach ($allTags as $tag) {
                if (mb_strtolower($tag['name']) === mb_strtolower($name)) {
                    $existingTag = $tag;
                    break;
                }
            }
            
            // Если тег уже существует, возвращаем его данные
            if ($existingTag) {
                echo json_encode([
                    'success' => true,
                    'tag' => $existingTag,
                    'message' => 'Тег уже существует'
                ]);
                exit;
            }
            
            // Создание нового тега
            $tagId = $this->tagModel->createWithSlug($name);
            $tag = $this->tagModel->getById($tagId);
            
            // Возврат успешного ответа с данными нового тега
            echo json_encode([
                'success' => true,
                'tag' => $tag,
                'message' => 'Тег успешно создан'
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