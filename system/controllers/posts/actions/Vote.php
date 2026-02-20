<?php

namespace posts\actions;

/**
 * Действие голосования за пост (устаревшее, использует систему лайков)
 * Оставлено для обратной совместимости, фактически вызывает toggleLike()
 * Используется для AJAX-запросов, переключает статус лайка пользователя для поста
 * 
 * @package posts\actions
 * @extends PostAction
 */
class Vote extends PostAction {
    
    /**
     * Метод выполнения голосования за пост
     * Проверяет авторизацию, метод запроса, существование поста,
     * вызывает toggleLike() для переключения лайка и возвращает JSON-ответ
     * 
     * @return void
     */
    public function execute() {
        // Получение ID поста из параметров
        $postId = $this->params['id'] ?? null;
        if (!$postId) {
            echo json_encode(['success' => false, 'message' => 'Post ID not provided']);
            return;
        }

        // Установка заголовка для JSON-ответа
        header('Content-Type: application/json');
        
        // Проверка метода запроса (только POST)
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            return;
        }
    
        try {
            // Проверка авторизации пользователя
            if (!isset($_SESSION['user_id'])) {
                echo json_encode([
                    'success' => false, 
                    'message' => 'Требуется авторизация',
                    'redirect' => BASE_URL . '/login'
                ]);
                return;
            }
            
            $userId = $_SESSION['user_id'];
    
            // Проверка существования поста
            $post = $this->postModel->getById($postId);
            if (!$post) {
                echo json_encode(['success' => false, 'message' => 'Пост не найден']);
                return;
            }
    
            // Используем новую систему лайков (обратная совместимость)
            $result = $this->postModel->toggleLike($postId, $userId);
            
            // Возврат успешного ответа
            echo json_encode([
                'success' => true,
                'liked' => $result['liked'],
                'likes_count' => $result['likes_count'],
                'message' => $result['liked'] ? 'Посту понравилось' : 'Лайк удален'
            ]);
            
        } catch (\Exception $e) {
            // Возврат ответа с ошибкой
            echo json_encode(['success' => false, 'message' => 'Ошибка сервера: ' . $e->getMessage()]);
        }
        exit;
    }
}