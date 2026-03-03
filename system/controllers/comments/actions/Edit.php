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
        $id = $this->params['id'] ?? null;
        
        if (!$id) {
            \Notification::error('ID комментария не указан');
            $this->redirect($_SERVER['HTTP_REFERER'] ?? BASE_URL);
            return;
        }
        
        $id = (int)$id;
        
        try {
            $currentUserId = $this->getCurrentUserId();
            
            if (!$currentUserId) {
                \Notification::error('Необходимо авторизоваться для редактирования комментария');
                $_SESSION['return_url'] = $_SERVER['REQUEST_URI'];
                $this->redirect(BASE_URL . '/auth/login');
                return;
            }
            
            $comment = $this->commentModel->getCommentById($id);
            
            if (!$comment) {
                \Notification::error('Комментарий не найден');
                $this->redirect(BASE_URL);
                return;
            }
            
            $post = $this->postModel->getById($comment['post_id']);
            
            if (!$post) {
                \Notification::error('Пост, к которому относится комментарий, не найден');
                $this->redirect(BASE_URL);
                return;
            }
            
            $userId = $comment['user_id'] ?? null;
            
            if (!\AuthHelper::canEditComment($userId)) {
                \Notification::error('У вас нет прав для редактирования этого комментария');
                $this->redirect($_SERVER['HTTP_REFERER'] ?? BASE_URL);
                return;
            }
            
            $this->addBreadcrumb('Главная', BASE_URL);
            $this->addBreadcrumb('Все записи', BASE_URL . '/posts');
            
            if (!empty($post['category_id'])) {
                $category = $this->categoryModel->getById($post['category_id']);
                if ($category) {
                    $this->addBreadcrumb(
                        $category['name'],
                        BASE_URL . '/category/' . $category['slug']
                    );
                }
            }
            
            $this->addBreadcrumb($post['title'], BASE_URL . '/post/' . $post['slug']);
            $this->addBreadcrumb('Комментарий #' . $id, BASE_URL . '/post/' . $post['slug'] . '#comment-' . $id);
            $this->addBreadcrumb('Редактирование');
            
            $isAdmin = $this->isAdmin();
            $pageTitle = $isAdmin ? 'Редактирование комментария (админ)' : 'Редактирование комментария';
            $this->setPageTitle($pageTitle);
            
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $content = trim($_POST['content'] ?? '');
                
                if (empty($content)) {
                    \Notification::error('Текст комментария не может быть пустым');
                    
                    $this->renderForm($comment, $post, $isAdmin);
                    return;
                }
                
                $status = $comment['status'];
                
                if (\AuthHelper::can('comment_edit_no_moderations')) {
                    $status = $_POST['status'] ?? $status;
                } else {
                    $status = 'pending';
                }
                
                $data = [
                    'content' => $content,
                    'status' => $status
                ];
                
                if ($isAdmin && isset($_POST['author_name'])) {
                    $data['author_name'] = trim($_POST['author_name']);
                }
                
                if ($isAdmin && isset($_POST['author_email'])) {
                    $data['author_email'] = trim($_POST['author_email']);
                }
                
                $this->commentModel->updateComment($id, $data);
                
                if ($status === 'approved') {
                    \Notification::success('Комментарий успешно обновлен и одобрен');
                } elseif ($status === 'pending') {
                    \Notification::success('Комментарий успешно обновлен и отправлен на модерацию');
                } else {
                    \Notification::success('Комментарий успешно обновлен');
                }
                
                $this->redirect(BASE_URL . '/post/' . $post['slug'] . '#comment-' . $id);
                return;
            }
            
            $this->renderForm($comment, $post, $isAdmin);
            
        } catch (\Exception $e) {
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
        $this->render('front/comments/edit', [
            'comment' => $comment,
            'post' => $post,
            'postModel' => $this->postModel,
            'isAdmin' => $isAdmin,
            'pageTitle' => $isAdmin ? 'Редактирование комментария (админ)' : 'Редактирование комментария'
        ]);
    }
}