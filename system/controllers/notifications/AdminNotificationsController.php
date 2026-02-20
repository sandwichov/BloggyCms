<?php

/**
 * Контроллер управления уведомлениями в административной панели
 * Обрабатывает запросы, связанные с отображением и управлением уведомлениями
 * Поддерживает как обычные страницы, так и AJAX-запросы
 * 
 * @package Controllers
 * @extends Controller
 */
class AdminNotificationsController extends Controller {
    
    /** @var NotificationModel Модель для работы с уведомлениями */
    private $notificationModel;
    
    /** @var UserModel Модель для работы с пользователями */
    private $userModel;
    
    /** @var array Информация о контроллере */
    protected $controllerInfo = [
        'name' => 'Уведомления',
        'author' => 'BloggyCMS',
        'version' => '1.0.0',
        'has_settings' => false,
        'description' => 'Система уведомлений администратора'
    ];
    
    /**
     * Конструктор контроллера
     * Инициализирует модели и проверяет права доступа
     * 
     * @param object $db Подключение к базе данных
     */
    public function __construct($db) {
        parent::__construct($db);
        
        $this->notificationModel = new NotificationModel($db);
        $this->userModel = new UserModel($db);
        
        // Проверка прав доступа
        if (!$this->checkAdminAccess()) {
            $this->handleAccessDenied();
        }
    }
    
    /**
     * Проверяет права доступа администратора
     * 
     * @return bool true если пользователь администратор, false в противном случае
     */
    private function checkAdminAccess() {
        return Auth::isAdmin();
    }
    
    /**
     * Обрабатывает ситуацию с отсутствием прав доступа
     * В зависимости от типа запроса возвращает JSON-ошибку или редирект
     * 
     * @return void
     */
    private function handleAccessDenied() {
        if ($this->isAjaxRequest()) {
            http_response_code(403);
            header('Content-Type: application/json');
            die(json_encode([
                'success' => false,
                'message' => 'Доступ запрещен'
            ]));
        } else {
            Notification::error('У вас нет прав доступа к этому разделу');
            $this->redirect(ADMIN_URL . '/login');
            exit;
        }
    }
    
    /**
     * Проверяет, является ли текущий запрос AJAX-запросом
     * 
     * @return bool true если запрос AJAX, false в противном случае
     */
    private function isAjaxRequest() {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) 
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
    
    /**
     * Получить количество непрочитанных уведомлений (AJAX)
     * Используется для обновления счетчика в интерфейсе
     * 
     * @return void
     */
    public function getUnreadCountAction() {
        try {
            $userId = Auth::getUserId();
            $count = $this->notificationModel->getUnreadCount($userId);
            
            $this->jsonSuccess(['count' => (int)$count]);
            
        } catch (\Exception $e) {
            $this->jsonError('Ошибка при получении уведомлений: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Получить список уведомлений (AJAX)
     * Поддерживает пагинацию и фильтрацию по статусу прочтения
     * 
     * @return void
     */
    public function getListAction() {
        try {
            $userId = Auth::getUserId();
            $limit = (int)($_GET['limit'] ?? 10);
            $offset = (int)($_GET['offset'] ?? 0);
            $unreadOnly = isset($_GET['unread_only']) ? (bool)$_GET['unread_only'] : false;
            
            // Получение уведомлений с деталями
            $notifications = $this->notificationModel->getUserNotificationsWithDetails(
                $userId, 
                $limit, 
                $offset,
                $unreadOnly
            );
            
            // Форматирование уведомлений для вывода
            $formatted = [];
            foreach ($notifications as $notification) {
                $formatted[] = $this->formatNotification($notification);
            }
            
            $this->jsonSuccess([
                'notifications' => $formatted,
                'total' => count($formatted)
            ]);
            
        } catch (\Exception $e) {
            $this->jsonError('Ошибка при получении уведомлений: ' . $e->getMessage(), 500);
        }
    }
    
    /**
     * Главная страница уведомлений
     * Отображает список всех уведомлений пользователя
     * 
     * @return void
     */
    public function adminIndexAction() {
        $action = new \notifications\actions\AdminIndex($this->db);
        $action->setController($this);
        return $action->execute();
    }
    
    /**
     * Отметить конкретное уведомление как прочитанное
     * 
     * @param int $id ID уведомления
     * @return void
     */
    public function adminMarkAsReadAction($id) {
        $action = new \notifications\actions\AdminMarkAsRead($this->db, ['id' => $id]);
        $action->setController($this);
        return $action->execute();
    }
    
    /**
     * Отметить все уведомления пользователя как прочитанные
     * 
     * @return void
     */
    public function adminMarkAllAsReadAction() {
        $action = new \notifications\actions\AdminMarkAllAsRead($this->db);
        $action->setController($this);
        return $action->execute();
    }
    
    /**
     * Удалить конкретное уведомление
     * 
     * @param int $id ID уведомления
     * @return void
     */
    public function adminDeleteAction($id) {
        $action = new \notifications\actions\AdminDelete($this->db, ['id' => $id]);
        $action->setController($this);
        return $action->execute();
    }
    
    /**
     * Очистить все прочитанные уведомления
     * 
     * @return void
     */
    public function adminClearAction() {
        $action = new \notifications\actions\AdminClear($this->db);
        $action->setController($this);
        return $action->execute();
    }
    
    /**
     * Форматирует уведомление для вывода в интерфейсе
     * Добавляет иконку, цвет, форматированное время и обрабатывает специфичные типы
     * 
     * @param array $notification Данные уведомления из БД
     * @return array Отформатированное уведомление
     */
    private function formatNotification($notification) {
        $data = json_decode($notification['data'] ?? '{}', true);
        
        // Базовое форматирование
        $result = [
            'id' => (int)$notification['id'],
            'type' => $notification['type'],
            'data' => $data,
            'is_read' => (bool)$notification['is_read'],
            'icon' => $this->getNotificationIcon($notification['type']),
            'color' => $this->getNotificationColor($notification['type']),
            'time' => $this->formatTime($notification['created_at']),
            'time_full' => date('d.m.Y H:i', strtotime($notification['created_at'])),
            'created_by' => $notification['created_by_display_name'] ?? 
                           $notification['created_by_username'] ?? null,
            'created_at' => $notification['created_at']
        ];
        
        // Специфичная обработка для разных типов уведомлений
        switch ($notification['type']) {
            case 'new_comment':
                return $this->formatCommentNotification($notification, $data, $result);
                
            case 'new_user':
                return $this->formatNewUserNotification($notification, $data, $result);
                
            case 'system':
                return $this->formatSystemNotification($notification, $data, $result);
                
            default:
                return array_merge($result, [
                    'title' => $notification['title'],
                    'message' => $notification['message']
                ]);
        }
    }
    
    /**
     * Форматирует уведомление о новом комментарии
     * 
     * @param array $notification Исходное уведомление
     * @param array $data Дополнительные данные
     * @param array $result Базовый результат форматирования
     * @return array Отформатированное уведомление
     */
    private function formatCommentNotification($notification, $data, $result) {
        $postTitle = $data['post_title'] ?? 'Неизвестный пост';
        $authorName = $data['author_name'] ?? 'Аноним';
        $contentPreview = $data['content_preview'] ?? '';
        
        $message = "<strong>{$authorName}</strong> оставил комментарий к записи <strong>\"{$postTitle}\"</strong>";
        
        if (!empty($contentPreview)) {
            $message .= "<br><br><em>\"{$contentPreview}\"</em>";
        }
        
        return array_merge($result, [
            'title' => 'Новый комментарий',
            'message' => $message
        ]);
    }
    
    /**
     * Форматирует уведомление о новом пользователе
     * 
     * @param array $notification Исходное уведомление
     * @param array $data Дополнительные данные
     * @param array $result Базовый результат форматирования
     * @return array Отформатированное уведомление
     */
    private function formatNewUserNotification($notification, $data, $result) {
        $userName = $data['username'] ?? $notification['created_by_username'] ?? 'Новый пользователь';
        
        return array_merge($result, [
            'title' => 'Новая регистрация',
            'message' => "Пользователь <strong>{$userName}</strong> зарегистрировался на сайте"
        ]);
    }
    
    /**
     * Форматирует системное уведомление
     * 
     * @param array $notification Исходное уведомление
     * @param array $data Дополнительные данные
     * @param array $result Базовый результат форматирования
     * @return array Отформатированное уведомление
     */
    private function formatSystemNotification($notification, $data, $result) {
        return array_merge($result, [
            'title' => $notification['title'] ?? 'Системное уведомление',
            'message' => $notification['message'] ?? ''
        ]);
    }
    
    /**
     * Получить иконку для типа уведомления
     * 
     * @param string $type Тип уведомления
     * @return string Название иконки Bootstrap Icons
     */
    private function getNotificationIcon($type) {
        $icons = [
            'new_comment' => 'chat-dots',
            'new_user' => 'person-plus',
            'system' => 'gear',
            'warning' => 'exclamation-triangle',
            'info' => 'info-circle'
        ];
        
        return $icons[$type] ?? 'bell';
    }
    
    /**
     * Получить цвет для типа уведомления (для Bootstrap классов)
     * 
     * @param string $type Тип уведомления
     * @return string Название цвета Bootstrap
     */
    private function getNotificationColor($type) {
        $colors = [
            'new_comment' => 'primary',
            'new_user' => 'success',
            'system' => 'secondary',
            'warning' => 'warning',
            'info' => 'info'
        ];
        
        return $colors[$type] ?? 'secondary';
    }
    
    /**
     * Форматирует время в человекочитаемый формат
     * Примеры: "Только что", "5 минут назад", "2 часа назад"
     * 
     * @param string $datetime Дата и время в формате MySQL
     * @return string Отформатированное время
     */
    private function formatTime($datetime) {
        $now = time();
        $time = strtotime($datetime);
        $diff = $now - $time;
        
        if ($diff < 60) {
            return 'Только что';
        } elseif ($diff < 3600) {
            $minutes = floor($diff / 60);
            return "$minutes " . $this->pluralize($minutes, ['минуту', 'минуты', 'минут']) . ' назад';
        } elseif ($diff < 86400) {
            $hours = floor($diff / 3600);
            return "$hours " . $this->pluralize($hours, ['час', 'часа', 'часов']) . ' назад';
        } elseif ($diff < 604800) {
            $days = floor($diff / 86400);
            return "$days " . $this->pluralize($days, ['день', 'дня', 'дней']) . ' назад';
        } else {
            return date('d.m.Y', $time);
        }
    }
    
    /**
     * Склонение числительных для русского языка
     * 
     * @param int $number Число
     * @param array $forms Массив форм слова [именительный, родительный единственное, родительное множественное]
     * @return string Правильная форма слова
     */
    private function pluralize($number, $forms) {
        $cases = [2, 0, 1, 1, 1, 2];
        return $forms[($number % 100 > 4 && $number % 100 < 20) ? 2 : $cases[min($number % 10, 5)]];
    }
    
    /**
     * Отправляет успешный JSON-ответ
     * 
     * @param array $data Дополнительные данные для ответа
     * @return void
     */
    private function jsonSuccess($data = []) {
        header('Content-Type: application/json');
        echo json_encode(array_merge(['success' => true], $data));
        exit;
    }
    
    /**
     * Отправляет JSON-ответ с ошибкой
     * 
     * @param string $message Сообщение об ошибке
     * @param int $code HTTP-код ответа (по умолчанию 400)
     * @return void
     */
    private function jsonError($message, $code = 400) {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => $message
        ]);
        exit;
    }
}