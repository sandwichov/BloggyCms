<?php

/**
 * Контроллер управления настройками в административной панели
 * Обрабатывает запросы, связанные с просмотром, изменением и сбросом настроек системы,
 * а также с загрузкой изображений для настроек
 * 
 * @package Controllers
 * @extends Controller
 */
class AdminSettingsController extends Controller {
    
    /** @var SettingsModel Модель для работы с настройками */
    private $settingsModel;
    
    /**
     * Конструктор контроллера
     * Инициализирует модель настроек и проверяет права доступа
     * 
     * @param object $db Подключение к базе данных
     */
    public function __construct($db) {
        parent::__construct($db);
        $this->settingsModel = new SettingsModel($db);
        
        // Проверка авторизации пользователя
        if (!isset($_SESSION['user_id'])) {
            Notification::error('Пожалуйста, авторизуйтесь для доступа к настройкам');
            $this->redirect(ADMIN_URL . '/login');
            exit;
        }
        
        // Проверка прав администратора
        if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
            Notification::error('У вас нет прав для доступа к настройкам');
            $this->redirect(ADMIN_URL);
            exit;
        }
    }
    
    /**
     * Отображает главную страницу управления настройками
     * Показывает список всех доступных настроек с возможностью их редактирования
     * 
     * @return void
     */
    public function adminIndexAction() {
        $action = new \settings\actions\AdminIndex($this->db);
        $action->setController($this);
        return $action->execute();
    }
    
    /**
     * Выполняет сброс настроек к значениям по умолчанию
     * 
     * @return void
     */
    public function resetAction() {
        $action = new \settings\actions\AdminReset($this->db);
        $action->setController($this);
        return $action->execute();
    }

    /**
     * Выполняет очистку старых резервных копий настроек
     * 
     * @return void
     */
    public function cleanupBackupsAction() {
        $action = new \settings\actions\AdminCleanupBackups($this->db);
        $action->setController($this);
        return $action->execute();
    }

    /**
     * Загружает изображения для настроек (логотипы, иконки и т.д.)
     * Использует FileUpload хелпер для обработки загрузки
     * Возвращает JSON-ответ с URL загруженного изображения
     * 
     * @return void
     */
    public function uploadImageAction() {
        // Установка заголовка для JSON-ответа
        header('Content-Type: application/json');
        
        try {
            // Проверка наличия файла
            if (empty($_FILES['image'])) {
                throw new Exception('Файл не был загружен');
            }
            
            // Получение параметров загрузки
            $uploadPath = $_POST['upload_path'] ?? 'uploads/images/';
            $fieldName = $_POST['field_name'] ?? 'unknown';
            
            // Создание полного пути для загрузки
            $fullUploadPath = BASE_PATH . '/' . trim($uploadPath, '/');
            
            // Использование FileUpload хелпера для загрузки файла
            $fileName = FileUpload::upload($_FILES['image'], $fullUploadPath, ['jpg', 'jpeg', 'png', 'gif', 'webp'], 5120);
            
            // Возврат успешного ответа
            echo json_encode([
                'success' => true,
                'filename' => $fileName,
                'url' => BASE_URL . '/' . trim($uploadPath, '/') . '/' . $fileName,
                'message' => 'Изображение успешно загружено'
            ]);
            
        } catch (Exception $e) {
            // Возврат ответа с ошибкой
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
        
        exit;
    }
}