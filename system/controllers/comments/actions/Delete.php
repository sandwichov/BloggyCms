<?php

namespace comments\actions;

/**
 * Действие удаления комментария пользователем
 * Позволяет пользователям удалять свои собственные комментарии с проверкой прав
 * Поддерживает рекурсивное удаление дочерних комментариев и обработку AJAX-запросов
 * 
 * @package comments\actions
 * @extends CommentAction
 */
class Delete extends CommentAction {
    
    /**
     * Метод выполнения удаления комментария
     * Проверяет права пользователя и удаляет комментарий с возможностью каскадного удаления ответов
     * 
     * @return void
     */
    public function execute() {
        // Получение ID комментария из параметров
        $id = $this->params['id'] ?? null;
        
        // Определение типа запроса
        $isAjax = $this->isAjaxRequest();
        
        // Проверка наличия ID комментария
        if (!$id) {
            $this->sendError('ID комментария не указан', $isAjax);
            return;
        }
        
        try {
            // Получение ID текущего пользователя
            $currentUserId = $this->getCurrentUserId();
            if (!$currentUserId) {
                $this->sendError('Необходимо авторизоваться для удаления комментария', $isAjax);
                return;
            }

            // Получение данных комментария
            $comment = $this->commentModel->getCommentById($id);
            
            // Проверка существования комментария
            if (!$comment) {
                $this->sendError('Комментарий не найден', $isAjax);
                return;
            }

            // Проверка прав удаления комментария
            $userId = $comment['user_id'] ?? null;
            
            if (!\AuthHelper::canDeleteComment($userId)) {
                $this->sendError('У вас нет прав для удаления этого комментария', $isAjax);
                return;
            }

            // Выполнение удаления комментария
            $deleteRecursive = true; // Может быть конфигурируемой настройкой
            if ($deleteRecursive && $this->hasChildComments($id)) {
                // Рекурсивное удаление комментария со всеми ответами
                $result = $this->commentModel->deleteCommentRecursive($id);
            } else {
                // Удаление только указанного комментария
                $result = $this->commentModel->deleteComment($id);
            }
            
            // Обработка результата удаления
            if ($result) {
                if ($isAjax) {
                    // Успешный AJAX-ответ
                    header('Content-Type: application/json');
                    echo json_encode([
                        'success' => true,
                        'message' => 'Комментарий успешно удален',
                        'comment_id' => $id,
                        'has_replies' => $this->hasChildComments($id)
                    ]);
                    return;
                } else {
                    // Уведомление об успехе для обычных запросов
                    \Notification::success('Комментарий успешно удален');
                }
            } else {
                throw new \Exception('Не удалось удалить комментарий');
            }
            
        } catch (\Exception $e) {
            // Обработка исключений
            $this->sendError('Ошибка при удалении комментария: ' . $e->getMessage(), $isAjax);
        }
        
        // Перенаправление для не-AJAX запросов
        if (!$isAjax) {
            $this->redirect($_SERVER['HTTP_REFERER'] ?? BASE_URL);
        }
    }
    
    /**
     * Проверка наличия дочерних комментариев
     * Определяет, есть ли у указанного комментария ответы
     *
     * @param int $parentId ID родительского комментария
     * @return bool true если у комментария есть дочерние комментарии
     */
    private function hasChildComments($parentId) {
        $sql = "SELECT COUNT(*) as count FROM comments WHERE parent_id = ?";
        $result = $this->db->fetch($sql, [$parentId]);
        return ($result['count'] ?? 0) > 0;
    }
    
    /**
     * Отправка сообщения об ошибке
     * Универсальный метод для отправки ошибок в зависимости от типа запроса
     *
     * @param string $message Текст сообщения об ошибке
     * @param bool $isAjax Является ли запрос AJAX-запросом
     * @return void
     */
    private function sendError($message, $isAjax) {
        if ($isAjax) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => $message
            ]);
        } else {
            \Notification::error($message);
            $this->redirect($_SERVER['HTTP_REFERER'] ?? BASE_URL);
        }
    }
}