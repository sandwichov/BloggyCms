<?php

namespace comments\actions;

/**
 * Действие одобрения комментария в админ-панели
 * Позволяет администраторам и модераторам одобрять комментарии, находящиеся на модерации
 * Поддерживает как AJAX, так и обычные запросы с различными типами ответов
 * 
 * @package comments\actions
 * @extends CommentAction
 */
class AdminApprove extends CommentAction {
    
    /**
     * Метод выполнения одобрения комментария
     * Проверяет права, существование комментария и выполняет его одобрение
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
            if ($isAjax) {
                http_response_code(400);
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'message' => 'ID комментария не указан'
                ]);
                return;
            } else {
                \Notification::error('ID комментария не указан');
                $this->redirect(ADMIN_URL . '/comments');
                return;
            }
        }
        
        try {
            // Проверка существования комментария
            $comment = $this->commentModel->getCommentById($id);
            if (!$comment) {
                if ($isAjax) {
                    http_response_code(404);
                    header('Content-Type: application/json');
                    echo json_encode([
                        'success' => false,
                        'message' => 'Комментарий не найден'
                    ]);
                    return;
                } else {
                    \Notification::error('Комментарий не найден');
                    $this->redirect(ADMIN_URL . '/comments');
                    return;
                }
            }
            
            // Проверка текущего статуса комментария
            if ($comment['status'] === 'approved') {
                if ($isAjax) {
                    http_response_code(400);
                    header('Content-Type: application/json');
                    echo json_encode([
                        'success' => false,
                        'message' => 'Комментарий уже одобрен'
                    ]);
                    return;
                } else {
                    \Notification::warning('Комментарий уже одобрен');
                    $this->redirect(ADMIN_URL . '/comments');
                    return;
                }
            }
            
            // Выполнение одобрения комментария
            $this->commentModel->approveComment($id);
            
            // Обработка AJAX-запросов
            if ($isAjax) {
                // Получение обновленного комментария
                $updatedComment = $this->commentModel->getCommentById($id);
                
                /**
                 * Определение типа страницы по HTTP_REFERER
                 * Для админ-панели и фронтенда возвращаются разные форматы ответов
                 */
                $isAdminPage = strpos($_SERVER['HTTP_REFERER'] ?? '', '/admin/') !== false;
                
                if ($isAdminPage) {
                    // Простой ответ для админ-панели
                    header('Content-Type: application/json');
                    echo json_encode([
                        'success' => true,
                        'message' => 'Комментарий успешно одобрен',
                        'comment_id' => $id,
                        'new_status' => 'approved'
                    ]);
                } else {
                    // Расширенный ответ для фронтенда с данными комментария
                    if ($this->controller) {
                        $commentData = $this->controller->getCommentWithUserData($updatedComment);
                        
                        header('Content-Type: application/json');
                        echo json_encode([
                            'success' => true,
                            'comment' => $commentData,
                            'message' => 'Комментарий одобрен',
                            'comment_id' => $id
                        ]);
                    } else {
                        // Резервный ответ если контроллер недоступен
                        header('Content-Type: application/json');
                        echo json_encode([
                            'success' => true,
                            'message' => 'Комментарий успешно одобрен',
                            'comment_id' => $id
                        ]);
                    }
                }
                return;
            } 
            // Обработка обычных (не-AJAX) запросов
            else {
                \Notification::success('Комментарий успешно одобрен');
            }
            
        } catch (\Exception $e) {
            // Обработка исключений
            $errorMessage = 'Ошибка при одобрении комментария: ' . $e->getMessage();
            
            if ($isAjax) {
                http_response_code(500);
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'message' => $errorMessage
                ]);
                return;
            } else {
                \Notification::error($errorMessage);
            }
        }
        
        // Перенаправление для не-AJAX запросов
        if (!$isAjax) {
            $this->redirect($_SERVER['HTTP_REFERER'] ?? ADMIN_URL . '/comments');
        }
    }
}