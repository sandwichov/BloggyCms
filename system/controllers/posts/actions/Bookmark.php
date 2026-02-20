<?php

namespace posts\actions;

/**
 * Действие добавления/удаления поста в закладки пользователя
 * Используется для AJAX-запросов, переключает статус закладки
 * и при добавлении триггерит проверку ачивок
 * 
 * @package posts\actions
 * @extends PostAction
 */
class Bookmark extends PostAction {
    
    /**
     * Метод выполнения переключения закладки
     * Проверяет авторизацию, метод запроса, существование поста,
     * переключает закладку через модель и возвращает JSON-ответ
     * 
     * @return void
     */
    public function execute() {
        // Установка заголовка для JSON-ответа
        header('Content-Type: application/json');
        
        try {
            // Проверка авторизации пользователя
            if (!isset($_SESSION['user_id'])) {
                throw new \Exception('Требуется авторизация для добавления в закладки');
            }
            
            // Получение ID поста из параметров
            $postId = $this->params['id'] ?? null;
            if (!$postId) {
                throw new \Exception('ID поста не указан');
            }

            // Проверка метода запроса (только POST)
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new \Exception('Invalid request method');
            }
            
            $userId = $_SESSION['user_id'];
            
            // Проверка существования поста
            $post = $this->postModel->getById($postId);
            if (!$post) {
                throw new \Exception('Пост не найден');
            }
            
            // Переключение закладки (добавление или удаление)
            $result = $this->postModel->toggleBookmark($postId, $userId);
            
            // Проверка ачивок после добавления в закладки (только при добавлении)
            if ($result['bookmarked']) {
                try {
                    $achievementTriggers = new \AchievementTriggers($this->db);
                    $achievementTriggers->onPostBookmarked($userId, $postId);
                } catch (\Exception $e) {
                    // Подавление исключений ачивок - не влияют на основной функционал
                }
            }
            
            // Возврат успешного ответа
            echo json_encode([
                'success' => true,
                'bookmarked' => $result['bookmarked'],
                'message' => $result['message']
            ]);
            
        } catch (\Exception $e) {
            // Возврат ответа с ошибкой
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
        exit;
    }
}