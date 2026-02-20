<?php

namespace comments\actions;

/**
 * Действие редактирования комментария пользователем
 * Позволяет пользователям редактировать свои собственные комментарии с учетом прав модерации
 * Поддерживает различные статусы после редактирования в зависимости от прав пользователя
 * 
 * @package comments\actions
 * @extends CommentAction
 */
class Edit extends CommentAction {
    
    /**
     * Метод выполнения редактирования комментария
     * Обрабатывает форму редактирования с проверкой прав и управлением статусами
     * 
     * @return void
     */
    public function execute() {
        // Получение ID комментария из параметров
        $id = $this->params['id'] ?? null;
        
        // Проверка наличия ID комментария
        if (!$id) {
            \Notification::error('ID комментария не указан');
            $this->redirect($_SERVER['HTTP_REFERER'] ?? BASE_URL);
            return;
        }
        
        // Приведение ID к целому числу
        $id = (int)$id;
        
        try {
            // Получение ID текущего пользователя
            $currentUserId = $this->getCurrentUserId();
            
            // Проверка авторизации пользователя
            if (!$currentUserId) {
                \Notification::error('Необходимо авторизоваться для редактирования комментария');
                
                // Сохранение URL для возврата после авторизации
                $_SESSION['return_url'] = $_SERVER['REQUEST_URI'];
                $this->redirect(BASE_URL . '/auth/login');
                return;
            }
            
            // Получение данных комментария
            $comment = $this->commentModel->getCommentById($id);
            
            // Проверка существования комментария
            if (!$comment) {
                \Notification::error('Комментарий не найден');
                $this->redirect(BASE_URL);
                return;
            }
            
            // Проверка прав на редактирование комментария
            $userId = $comment['user_id'] ?? null;
            
            if (!\AuthHelper::canEditComment($userId)) {
                \Notification::error('У вас нет прав для редактирования этого комментария');
                $this->redirect($_SERVER['HTTP_REFERER'] ?? BASE_URL);
                return;
            }
            
            // Обработка POST-запроса (отправка формы редактирования)
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $content = trim($_POST['content'] ?? '');
                
                // Валидация содержимого комментария
                if (empty($content)) {
 \Notification::error('Текст комментария не может быть пустым');
 
 // Повторный показ формы с ошибкой
 $post = $this->postModel->getById($comment['post_id']);
 $this->renderForm($comment, $post, $this->isAdmin());
 return;
                }
                
                // Определение статуса после редактирования
                $status = $comment['status'];
                
                // Проверка права на редактирование без модерации
                if (\AuthHelper::can('comment_edit_no_moderations')) {
 $status = $_POST['status'] ?? $status;
                } else {
 // Комментарий отправляется на повторную модерацию
 $status = 'pending';
                }
                
                // Подготовка данных для обновления
                $data = [
 'content' => $content,
 'status' => $status
                ];
                
                // Дополнительные поля для администраторов
                if ($this->isAdmin() && isset($_POST['author_name'])) {
 $data['author_name'] = trim($_POST['author_name']);
                }
                
                if ($this->isAdmin() && isset($_POST['author_email'])) {
 $data['author_email'] = trim($_POST['author_email']);
                }
                
                // Обновление комментария в базе данных
                $this->commentModel->updateComment($id, $data);
                
                // Получение данных поста для редиректа
                $post = $this->postModel->getById($comment['post_id']);
                
                if ($post) {
 // Формирование уведомления в зависимости от статуса
 if ($status === 'approved') {
     \Notification::success('Комментарий успешно обновлен и одобрен');
 } elseif ($status === 'pending') {
     \Notification::success('Комментарий успешно обновлен и отправлен на модерацию');
 } else {
     \Notification::success('Комментарий успешно обновлен');
 }
 
 // Редирект на страницу поста с якорем к комментарию
 $this->redirect(BASE_URL . '/post/' . $post['slug'] . '#comment-' . $id);
                } else {
 \Notification::success('Комментарий успешно обновлен');
 $this->redirect(BASE_URL);
                }
                
                return;
            }
            
            // Обработка GET-запроса (отображение формы редактирования)
            $post = $this->postModel->getById($comment['post_id']);
            
            if (!$post) {
                \Notification::error('Пост, к которому относится комментарий, не найден');
                $this->redirect(BASE_URL);
                return;
            }
            
            // Отображение формы редактирования
            $this->renderForm($comment, $post, $this->isAdmin());
            
        } catch (\Exception $e) {
            // Обработка исключений
            \Notification::error('Ошибка при редактировании комментария: ' . $e->getMessage());
            $this->redirect($_SERVER['HTTP_REFERER'] ?? BASE_URL);
        }
    }
    
    /**
     * Рендеринг формы редактирования комментария
     * Отображает форму с различными опциями в зависимости от прав пользователя
     *
     * @param array $comment Данные редактируемого комментария
     * @param array $post Данные поста, к которому относится комментарий
     * @param bool $isAdmin Флаг административных прав
     * @return void
     */
    private function renderForm($comment, $post, $isAdmin = false) {
        $pageTitle = $isAdmin ? 'Редактирование комментария (админ)' : 'Редактирование комментария';
        
        /**
         * Рендеринг шаблона формы редактирования комментария
         * 
         * @param string $template Путь к шаблону (front/comments/edit)
         * @param array $data Данные для шаблона:
         * - comment: данные редактируемого комментария
         * - post: данные поста
         * - postModel: модель постов для дополнительных операций
         * - isAdmin: флаг административных прав
         * - pageTitle: заголовок страницы
         */
        $this->render('front/comments/edit', [
            'comment' => $comment,
            'post' => $post,
            'postModel' => $this->postModel,
            'isAdmin' => $isAdmin,
            'pageTitle' => $pageTitle
        ]);
    }
}