<?php

namespace posts\actions;

/**
 * Действие проверки пароля для защищенных постов
 * Обрабатывает отправку формы пароля, проверяет корректность пароля
 * и при успехе сохраняет доступ в сессии
 * 
 * @package posts\actions
 * @extends PostAction
 */
class CheckPassword extends PostAction {
    
    /**
     * Метод выполнения проверки пароля
     * Получает ID поста из параметров, проверяет пароль,
     * при успехе сохраняет доступ в сессии и перенаправляет на пост
     * 
     * @return void
     */
    public function execute() {
        // Получение ID поста из параметров
        $postId = $this->params['id'] ?? null;
        
        // Проверка наличия ID поста
        if (!$postId) {
            echo json_encode(['success' => false, 'message' => 'Post ID not provided']);
            return;
        }

        // Проверка метода запроса и наличия пароля
        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['password'])) {
            // Если не AJAX запрос, показываем форму снова с ошибкой
            $this->redirect(BASE_URL . '/post/' . $post['slug'] . '?error=password');
            return;
        }
        
        $password = $_POST['password'];
        
        // Проверка пароля через модель
        $result = $this->postModel->checkPassword($postId, $password);
        
        if ($result) {
            // Сохраняем факт доступа в сессии
            if (!isset($_SESSION['post_access'])) {
                $_SESSION['post_access'] = [];
            }
            $_SESSION['post_access'][$postId] = true;
            
            // Перенаправляем обратно на пост
            $redirectUrl = $_POST['redirect'] ?? BASE_URL . '/posts';
            $this->redirect($redirectUrl);
        } else {
            // Неверный пароль - возвращаем на форму с ошибкой
            $post = $this->postModel->getById($postId);
            if ($post) {
                $this->redirect(BASE_URL . '/post/' . $post['slug'] . '?error=password');
            } else {
                $this->redirect(BASE_URL);
            }
        }
    }
}