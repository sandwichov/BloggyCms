<?php

namespace comments\actions;

/**
 * Действие редактирования комментария в админ-панели
 * Позволяет администраторам и модераторам изменять содержимое и статус комментариев
 * Обеспечивает интерфейс для полного редактирования комментариев
 * 
 * @package comments\actions
 * @extends CommentAction
 */
class AdminEdit extends CommentAction {
    
    /**
     * Метод выполнения редактирования комментария
     * Обрабатывает форму редактирования и обновляет данные комментария
     * 
     * @return void
     */
    public function execute() {
        // Получение ID комментария из параметров
        $id = $this->params['id'] ?? null;
        
        // Проверка наличия ID комментария
        if (!$id) {
            \Notification::error('ID комментария не указан');
            $this->redirect(ADMIN_URL . '/comments');
            return;
        }
        
        try {
            // Получение данных комментария из базы данных
            $comment = $this->commentModel->getCommentById($id);
            
            // Проверка существования комментария
            if (!$comment) {
                \Notification::error('Комментарий не найден');
                $this->redirect(ADMIN_URL . '/comments');
                return;
            }

            // Обработка POST-запроса (отправка формы редактирования)
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                // Подготовка данных для обновления
                $data = [
 'content' => $_POST['content'] ?? '',
 'status' => $_POST['status'] ?? 'pending'
                ];
                
                // Обновление комментария в базе данных
                $this->commentModel->updateComment($id, $data);
                
                // Уведомление об успешном обновлении
                \Notification::success('Комментарий успешно обновлен');
                
                // Перенаправление обратно на страницу управления комментариями
                $this->redirect(ADMIN_URL . '/comments');
                return;
            }

            /**
             * Рендеринг формы редактирования комментария
             * 
             * @param string $template Путь к шаблону (admin/comments/edit)
             * @param array $data Данные для шаблона:
             * - comment: данные редактируемого комментария
             * - pageTitle: заголовок страницы
             */
            $this->render('admin/comments/edit', [
                'comment' => $comment,
                'pageTitle' => 'Редактирование комментария'
            ]);
            
        } catch (\Exception $e) {
            // Обработка исключений при редактировании
            \Notification::error('Ошибка при редактировании комментария');
            $this->redirect(ADMIN_URL . '/comments');
        }
    }
}