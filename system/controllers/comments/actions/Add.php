<?php

namespace comments\actions;

/**
 * Действие добавления нового комментария
 * Обрабатывает добавление комментариев как через AJAX, так и через обычные POST-запросы
 * Включает проверку прав, валидацию, модерацию и систему уведомлений
 * 
 * @package comments\actions
 * @extends CommentAction
 */
class Add extends CommentAction {
    
    /**
     * Метод выполнения добавления комментария
     * Обрабатывает форму добавления комментария с поддержкой AJAX и стандартных запросов
     * 
     * @return void
     */
    public function execute() {
        // Определение типа запроса (AJAX или обычный)
        $isAjax = $this->isAjaxRequest();
        
        // Проверка метода запроса (допустим только POST)
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            if ($isAjax) {
                http_response_code(405);
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'message' => 'Неверный метод запроса'
                ]);
            } else {
                \Notification::error('Неверный метод запроса');
                $this->redirect(BASE_URL);
            }
            return;
        }
        
        try {
            // Проверка прав пользователя на добавление комментариев
            if (!\AuthHelper::canAddComment()) {
                $this->sendError('У вас нет прав на добавление комментариев', $isAjax);
                return;
            }

            // Блок валидации данных
            
            // Проверка наличия ID поста
            if (empty($_POST['post_id'])) {
                $this->sendError('Ошибка: ID поста не указан', $isAjax);
                return;
            }

            // Проверка наличия текста комментария
            if (empty($_POST['content'])) {
                $this->sendError('Пожалуйста, напишите комментарий', $isAjax);
                return;
            }

            // Подготовка данных комментария
            
            // Определение имени автора
            $authorName = 'Аноним';
            $currentUserId = $this->getCurrentUserId();
            
            if ($currentUserId) {
                // Для авторизованных пользователей - имя из профиля
                $user = $this->userModel->getById($currentUserId);
                $authorName = $user['display_name'] ?? $user['username'] ?? 'Пользователь';
            } elseif (!empty($_POST['author_name'])) {
                // Для неавторизованных - имя из формы
                $authorName = $_POST['author_name'];
            }

            // Определение статуса комментария (модерация)
            $status = 'pending'; // По умолчанию - на модерации
            if (\AuthHelper::canAddCommentWithoutModeration() || $this->isAdmin()) {
                $status = 'approved'; // Автоматическое одобрение для доверенных пользователей
            }

            // Формирование массива данных для сохранения
            $data = [
                'post_id' => (int)$_POST['post_id'],
                'user_id' => $currentUserId,
                'parent_id' => !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null,
                'author_name' => $authorName,
                'author_email' => $_POST['author_email'] ?? null,
                'content' => trim($_POST['content']),
                'status' => $status
            ];

            // Добавление комментария в базу данных
            $result = $this->commentModel->addComment($data);
            
            if ($result) {
                $commentId = $this->db->lastInsertId();
                
                // Активация системы достижений (если применимо)
                if ($currentUserId) {
                    try {
                        $achievementTriggers = new \AchievementTriggers($this->db);
                        $achievementTriggers->onCommentCreated($currentUserId);
                    } catch (\Exception $e) {
                        // Ошибки в системе достижений игнорируются
                    }
                }
                
                // Проверка настроек уведомлений
                $notificationSetting = \SettingsHelper::get('controller_notifications', 'variables', 'pending');
        
                // Отправка уведомлений согласно настройкам системы
                if ($notificationSetting === 'all' || 
                    ($notificationSetting === 'pending' && $status === 'pending')) {
                    $this->sendNewCommentNotification($commentId, $data);
                }
                
                // Обработка ответа для AJAX-запросов
                if ($isAjax) {
                    // Получение полных данных добавленного комментария
                    $comment = $this->commentModel->getCommentById($commentId);
                    $commentData = $this->controller->getCommentWithUserData($comment);
                    
                    // Формирование JSON-ответа
                    $response = [
                        'success' => true,
                        'comment' => $commentData,
                        'is_admin' => $this->isAdmin(),
                        'needs_moderation' => $status === 'pending',
                        'message' => $status === 'approved' 
                            ? 'Комментарий успешно добавлен' 
                            : 'Комментарий отправлен на модерацию'
                    ];
                    
                    header('Content-Type: application/json');
                    echo json_encode($response);
                    return;
                } 
                // Обработка ответа для обычных запросов
                else {
                    if ($status === 'pending') {
                        \Notification::success('Комментарий отправлен на модерацию. Он появится после проверки администратором.');
                        
                        // Перенаправление на страницу поста с указанием ожидания модерации
                        $post = $this->postModel->getById($_POST['post_id']);
                        if ($post) {
                            $redirectUrl = BASE_URL . '/post/' . $post['slug'] . '?pending_comment=1&scroll_to_comment=1';
                            $this->redirect($redirectUrl);
                            return;
                        }
                    } else {
                        \Notification::success('Комментарий успешно добавлен');
                    }
                }
            } else {
                throw new \Exception('Не удалось сохранить комментарий');
            }
            
        } catch (\Exception $e) {
            // Обработка исключений
            $message = 'Ошибка при добавлении комментария: ' . $e->getMessage();
            
            if ($isAjax) {
                http_response_code(500);
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'message' => $message
                ]);
            } else {
                \Notification::error($message);
            }
        }
        
        // Перенаправление для не-AJAX запросов
        if (!$isAjax) {
            $redirectUrl = $_SERVER['HTTP_REFERER'] ?? BASE_URL . '/post/' . ($_POST['post_id'] ?? '');
            $this->redirect($redirectUrl);
        }
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

    /**
     * Отправка уведомления о новом комментарии
     * Создает системное уведомление для администраторов и модераторов
     *
     * @param int $commentId ID созданного комментария
     * @param array $commentData Данные комментария
     * @return bool Результат отправки уведомления
     */
    private function sendNewCommentNotification($commentId, $commentData) {
        try {
            // Проверка доступности модели уведомлений
            if (class_exists('NotificationModel')) {
                $notificationModel = new \NotificationModel($this->db);
                
                // Использование улучшенного метода добавления уведомлений
                return $notificationModel->addNewCommentNotification($commentId, [
                    'user_id' => $commentData['user_id'] ?? null,
                    'author_name' => $commentData['author_name'] ?? 'Аноним',
                    'content' => $commentData['content'] ?? ''
                ]);
            }
        } catch (\Exception $e) {
            // Ошибки при отправке уведомлений логируются молча
        }
        
        return false;
    }
}