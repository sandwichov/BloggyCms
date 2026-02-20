<?php

/**
* Контроллер категорий блога
* Управляет всеми операциями с категориями, включая:
* - Просмотр категорий на фронтенде
* - Административное управление категориями
* - CRUD-операции с категориями
* - Загрузка изображений
*/
class CategoryController extends Controller {
    /**
    * @var CategoryModel Модель для работы с категориями
    */
    private $categoryModel;

    /**
    * @var array Метаинформация о контроллере
    * Содержит описание функциональности контроллера
    */
    protected $controllerInfo = [
        'name' => 'Категории',
        'author' => 'BloggyCMS', 
        'version' => '1.0.0',
        'has_settings' => true,
        'description' => 'Управление категориями блога'
    ];
    
    /**
    * Получение настроек категорий по умолчанию
    * Определяет стандартные параметры отображения категорий
    *
    * @return array Массив настроек по умолчанию:
    *               - category_layout: тип раскладки (grid/list)
    *               - show_category_images: показывать изображения категорий
    *               - category_posts_per_page: количество постов на странице
    */
    public function getDefaultSettings() {
        return [
            'category_layout' => 'grid',
            'show_category_images' => true,
            'category_posts_per_page' => 12
        ];
    }
    
    /**
    * Конструктор контроллера категорий
    * Инициализирует модель категорий и проверяет права доступа к админ-методам
    *
    * @param Database $db Объект подключения к базе данных
    */
    public function __construct($db) {
        parent::__construct($db);
        $this->categoryModel = new CategoryModel($db);
        
        // Проверка прав доступа для админ-методов
        $currentAction = $_GET['action'] ?? '';
        
        // Если действие начинается с 'admin', проверяем права администратора
        if (strpos($currentAction, 'admin') === 0) {
            if (!$this->checkAdminAccess()) {
                if ($this->isAjaxRequest()) {
                    // Ответ в формате JSON для AJAX-запросов
                    http_response_code(403);
                    header('Content-Type: application/json');
                    die(json_encode([
                        'success' => false,
                        'message' => 'Доступ запрещен'
                    ]));
                } else {
                    // Редирект на страницу логина для обычных запросов
                    Notification::error('У вас нет прав доступа к этому разделу');
                    $this->redirect(ADMIN_URL . '/login');
                    exit;
                }
            }
        }
    }

    /**
    * Проверка прав администратора
    * Определяет, авторизован ли пользователь как администратор
    *
    * @return bool true если пользователь является администратором
    */
    private function checkAdminAccess() {
        return isset($_SESSION['is_admin']) && $_SESSION['is_admin'];
    }

    /**
    * Определение типа запроса (AJAX или обычный)
    * Проверяет заголовки запроса на наличие XMLHttpRequest
    *
    * @return bool true если запрос выполнен через AJAX
    */
    private function isAjaxRequest() {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) 
            && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
    
    /**
    * Действие админ-панели категорий
    * Отображает список всех категорий для управления
    *
    * @return mixed Результат выполнения действия
    */
    public function adminIndexAction() {
        $action = new \categories\actions\AdminIndex($this->db);
        $action->setController($this);
        return $action->execute();
    }
    
    /**
    * Действие создания новой категории
    * Обрабатывает форму добавления категории
    *
    * @return mixed Результат выполнения действия
    */
    public function createAction() {
        $action = new \categories\actions\Create($this->db);
        $action->setController($this);
        return $action->execute();
    }
    
    /**
    * Действие редактирования существующей категории
    * Обрабатывает форму изменения данных категории
    *
    * @param int $id Идентификатор редактируемой категории
    * @return mixed Результат выполнения действия
    */
    public function editAction($id) {
        $action = new \categories\actions\Edit($this->db, ['id' => $id]);
        $action->setController($this);
        return $action->execute();
    }
    
    /**
    * Действие удаления категории
    * Удаляет категорию и связанные с ней данные
    *
    * @param int $id Идентификатор удаляемой категории
    * @return mixed Результат выполнения действия
    */
    public function deleteAction($id) {
        $action = new \categories\actions\Delete($this->db, ['id' => $id]);
        $action->setController($this);
        return $action->execute();
    }
    
    /**
    * Действие отображения категории на фронтенде
    * Показывает страницу категории со списком постов
    *
    * @param string $slug URL-идентификатор категории
    * @return mixed Результат выполнения действия
    */
    public function showAction($slug) {
        $action = new \categories\actions\Show($this->db, ['slug' => $slug]);
        $action->setController($this);
        return $action->execute();
    }
    
    /**
    * Действие проверки пароля защищенной категории
    * Обрабатывает доступ к категориям с ограниченным доступом
    *
    * @param int $id Идентификатор категории
    * @return mixed Результат выполнения действия
    */
    public function checkPasswordAction($id) {
        $action = new \categories\actions\CheckPassword($this->db, ['id' => $id]);
        $action->setController($this);
        return $action->execute();
    }
    
    /**
    * Действие изменения порядка категорий
    * Обрабатывает перетаскивание и сортировку категорий
    *
    * @return mixed Результат выполнения действия
    */
    public function reorderAction() {
        $action = new \categories\actions\Reorder($this->db);
        $action->setController($this);
        return $action->execute();
    }

    /**
    * Действие загрузки изображения категории
    * Обрабатывает загрузку файлов через интерфейс администратора
    *
    * @return mixed Результат выполнения действия
    */
    public function uploadImageAction() {
        $action = new \categories\actions\UploadImage($this->db);
        $action->setController($this);
        return $action->execute();
    }
}