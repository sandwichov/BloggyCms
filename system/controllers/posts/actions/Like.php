<?php

namespace posts\actions;

/**
 * Действие лайка/дизлайка поста
 * Используется для AJAX-запросов, переключает статус лайка пользователя для поста
 * и при добавлении лайка триггерит проверку ачивок
 * 
 * @package posts\actions
 * @extends PostAction
 */
class Like extends PostAction {
    
    /**
     * Метод выполнения переключения лайка
     * Проверяет авторизацию, метод запроса, существование поста,
     * переключает лайк через модель и возвращает JSON-ответ с обновленным количеством
     * 
     * @return void
     */
    public function execute() {
        // Установка заголовка для JSON-ответа
        header('Content-Type: application/json');
        
        try {
            // Проверка авторизации пользователя
            if (!isset($_SESSION['user_id'])) {
                throw new \Exception('Требуется авторизация');
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
            
            // Переключение лайка (добавление или удаление)
            $result = $this->postModel->toggleLike($postId, $userId);
            
            // Проверка ачивок после лайка (только при добавлении)
            if ($result['liked']) {
                try {
                    $achievementTriggers = new \AchievementTriggers($this->db);
                    $achievementTriggers->onPostLiked($userId, $postId);
                } catch (\Exception $e) {
                    // Подавление исключений ачивок - не влияют на основной функционал
                }
            }
            
            // Возврат успешного ответа с обновленными данными
            echo json_encode([
                'success' => true,
                'liked' => $result['liked'],
                'likes_count' => $result['likes_count'],
                'message' => $result['liked'] ? 'Пост добавлен в избранное' : 'Пост удален из избранного'
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