<?php

class AdminController extends Controller {
    private $userModel;

    protected $controllerInfo = [
        'name' => 'Панель управления',
        'author' => 'BloggyCMS', 
        'version' => '1.0.0',
        'has_settings' => true,
        'description' => 'Управление админ-панелью, блоками статистики и многим другим'
    ];
    
    /**
    * Конструктор контроллера администратора
    * Инициализирует модель пользователя и проверяет аутентификацию
    * для всех действий, кроме логина
    *
    * @param Database $db Объект подключения к базе данных
    */
    public function __construct($db) {
        parent::__construct($db);
        $this->userModel = new UserModel($db);
        
        $currentAction = $this->getCurrentAction();
        
        if ($currentAction !== 'login') {
            if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
                Notification::error('Доступ запрещен');
                $this->redirect(BASE_URL);
                exit;
            }
        }
    }

    /**
    * Определяет текущее действие из URI
    * Парсит URL для получения названия вызываемого метода
    *
    * @return string Название текущего действия или пустая строка
    */
    private function getCurrentAction() {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $pathParts = explode('/', trim($uri, '/'));
        
        if (count($pathParts) >= 2 && $pathParts[0] === 'admin') {
            return $pathParts[1];
        }
        
        return '';
    }
    
    /**
    * Главная страница панели управления
    * Отображает дашборд с ключевой статистикой и виджетами:
    * - Общая статистика (посты, категории, теги и т.д.)
    * - Последние и популярные посты
    * - Посты с комментариями
    * - Черновики
    * - История поисковых запросов
    *
    * @throws Exception При ошибках выполнения SQL-запросов
    */
    public function indexAction() {
        $this->pageTitle = 'Bloggy';
        
        try {
            // Получение базовой статистики системы
            $stats = [
                'posts' => $this->db->fetch("SELECT COUNT(*) as count FROM posts")['count'],
                'categories' => $this->db->fetch("SELECT COUNT(*) as count FROM categories")['count'],
                'tags' => $this->db->fetch("SELECT COUNT(*) as count FROM tags")['count'],
                'pages' => $this->db->fetch("SELECT COUNT(*) as count FROM pages")['count'],
                'comments' => $this->db->fetch("SELECT COUNT(*) as count FROM comments")['count'],
                'users' => $this->db->fetch("SELECT COUNT(*) as count FROM users")['count'],
                'content_blocks' => $this->db->fetch("SELECT COUNT(*) as count FROM html_block_types")['count'],
                'plugins' => $this->db->fetch("SELECT COUNT(*) as count FROM plugins")['count']
            ];

            // Получение настроек количества постов для отображения
            $count_posts = SettingsHelper::get('controller_admin', 'count_posts', 4);

            // Получение различных категорий постов для виджетов
            $recentPosts = $this->db->fetchAll("SELECT * FROM posts WHERE status = 'published' ORDER BY created_at DESC LIMIT $count_posts");
            $popularPosts = $this->db->fetchAll("SELECT * FROM posts WHERE status = 'published' AND views > 0 ORDER BY views DESC LIMIT $count_posts");
            $commentedPosts = $this->db->fetchAll("
                SELECT 
                    p.*,
                    COUNT(c.id) as comments_count
                FROM posts p
                LEFT JOIN comments c ON p.id = c.post_id AND c.status = 'approved'
                WHERE p.status = 'published'
                GROUP BY p.id
                HAVING comments_count > 0  -- ← добавляем это условие
                ORDER BY comments_count DESC
                LIMIT $count_posts
            ");
            $draftPosts = $this->db->fetchAll("SELECT * FROM posts WHERE status = 'draft' ORDER BY created_at DESC LIMIT $count_posts");
            $recentSearches = [];
            $popularSearches = [];
            
            // Получение поисковых запросов через модель поиска
            try {
                $searchModel = new SearchModel($this->db);
                $recentSearches = $searchModel->getRecentSearchQueries(5);
                $popularSearches = $searchModel->getPopularSearchQueries(5);
            } catch (Exception $e) {
                Notification::warning('Не удалось загрузить данные о поисковых запросах');
                $recentSearches = [];
                $popularSearches = [];
            }
            
            // Рендеринг страницы дашборда с передачей всех данных
            $this->render('admin/dashboard', [
                'stats' => $stats,
                'recentPosts' => $recentPosts,
                'popularPosts' => $popularPosts,
                'commentedPosts' => $commentedPosts,
                'draftPosts' => $draftPosts,
                'recentSearches' => $recentSearches,
                'popularSearches' => $popularSearches,
                'hasQuickActions' => $this->hasQuickActions()
            ]);
            
        } catch (Exception $e) {
            Notification::error('Ошибка при загрузке данных панели управления');
            $this->redirect(ADMIN_URL);
        }
    }
    
    /**
    * Страница аутентификации администратора
    * Обрабатывает POST-запрос с данными входа и устанавливает сессию
    * При успешной аутентификации перенаправляет на главную админ-панели
    */
    public function loginAction() {

        if (isset($_SESSION['user_id'])) {
            Notification::info('Вы уже авторизованы');
            $this->redirect(ADMIN_URL);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $username = $_POST['username'] ?? '';
                $password = $_POST['password'] ?? '';
        
                $user = $this->userModel->authenticate($username, $password);
        
                if ($user) {
                    // Установка данных сессии при успешной аутентификации
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['is_admin'] = $user['is_admin'];
                    Notification::success('Добро пожаловать, ' . $user['username'] . '!');
                    $this->redirect(ADMIN_URL);
                    return;
                } else {
                    Notification::error('Неверные имя пользователя или пароль');
                    $this->render('admin/login');
                }
            } catch (Exception $e) {
                Notification::error('Ошибка при попытке авторизации');
                $this->render('admin/login');
            }
        } else {
            $this->render('admin/login');
        }
        
    }
    
    /**
    * Завершение сессии администратора
    * Уничтожает все данные сессии и перенаправляет на страницу входа
    */
    public function logoutAction() {
        try {
            $username = $_SESSION['username'] ?? 'Пользователь';
            session_destroy();
            Notification::success($username . ', вы успешно вышли из системы');
        } catch (Exception $e) {
            Notification::error('Ошибка при выходе из системы');
        }
        $this->redirect(ADMIN_URL . '/login');
    }

    /**
    * Управление шаблонами сайта
    * Отображает список доступных шаблонов и текущий активный шаблон
    */
    public function templatesAction() {
        $this->pageTitle = 'Управление шаблонами';
        
        $templates = $this->getAvailableTemplates();
        $currentTemplate = SettingsHelper::get('site', 'site_template', 'default');
        
        $this->render('admin/templates/index', [
            'templates' => $templates,
            'currentTemplate' => $currentTemplate
        ]);
    }

    /**
    * API: Получение списка файлов шаблона
    * Возвращает JSON с информацией о всех файлах указанного шаблона
    * Используется для AJAX-запросов из интерфейса редактора шаблонов
    */
    public function getTemplateFilesAction() {
        header('Content-Type: application/json');
        
        $template = $_GET['template'] ?? 'default';
        $files = $this->getTemplateFiles($template);
        
        echo json_encode($files);
        exit;
    }

    /**
    * API: Получение содержимого файла шаблона
    * Возвращает JSON с содержимым файла и метаинформацией
    * Проверяет безопасность пути и права доступа
    *
    * @throws Exception При попытке доступа к файлам вне директории шаблона
    */
    public function getTemplateFileAction() {
        header('Content-Type: application/json');
        
        $template = $_GET['template'] ?? 'default';
        $filePath = $_GET['file'] ?? '';
        
        if (empty($filePath) || strpos($filePath, '..') !== false) {
            echo json_encode(['success' => false, 'error' => 'Некорректный путь к файлу']);
            exit;
        }
        
        $fullPath = TEMPLATES_PATH . '/' . $template . '/' . $filePath;
        
        $normalizedPath = $this->normalizePath($fullPath);
        $templateBasePath = $this->normalizePath(TEMPLATES_PATH . '/' . $template);
        
        if (strpos($normalizedPath, $templateBasePath) !== 0) {
            echo json_encode(['success' => false, 'error' => 'Доступ запрещен']);
            exit;
        }
        
        if (!file_exists($fullPath)) {
            echo json_encode(['success' => false, 'error' => 'Файл не найден: ' . $fullPath]);
            exit;
        }
        
        if (!is_file($fullPath)) {
            echo json_encode(['success' => false, 'error' => 'Это директория, а не файл']);
            exit;
        }
        
        $content = file_get_contents($fullPath);
        $fileInfo = $this->getFileInfo($fullPath, $filePath);
        
        echo json_encode([
            'success' => true,
            'content' => $content,
            'info' => $fileInfo
        ]);
        exit;
    }

    /**
    * API: Сохранение изменений в файле шаблона
    * Принимает JSON с содержимым файла и сохраняет его
    * Автоматически создает резервную копию перед сохранением
    *
    * @return JSON Ответ с результатом операции
    */
    public function saveTemplateFileAction() {
        header('Content-Type: application/json');
        
        $input = json_decode(file_get_contents('php://input'), true);
        $template = $input['template'] ?? 'default';
        $filePath = $input['file'] ?? '';
        $content = $input['content'] ?? '';
        
        if (empty($filePath) || strpos($filePath, '..') !== false) {
            echo json_encode(['success' => false, 'error' => 'Некорректный путь к файлу']);
            exit;
        }
        
        $fullPath = TEMPLATES_PATH . '/' . $template . '/' . $filePath;
        
        $normalizedPath = $this->normalizePath($fullPath);
        $templateBasePath = $this->normalizePath(TEMPLATES_PATH . '/' . $template);
        
        if (strpos($normalizedPath, $templateBasePath) !== 0) {
            echo json_encode(['success' => false, 'error' => 'Доступ запрещен']);
            exit;
        }
        
        $dir = dirname($fullPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        
        $backupCreated = false;
        if (file_exists($fullPath)) {
            $backupCreated = BackupHelper::createBackup($fullPath);
        }
        
        if (file_put_contents($fullPath, $content) !== false) {
            $response = ['success' => true];
            if ($backupCreated) {
                $response['backup_created'] = true;
                $response['message'] = 'Файл сохранен. Резервная копия создана.';
            } else {
                $response['message'] = 'Файл сохранен.';
            }
            echo json_encode($response);
        } else {
            echo json_encode(['success' => false, 'error' => 'Ошибка сохранения файла']);
        }
        exit;
    }

    /**
    * Нормализация пути к файлу
    * Убирает относительные переходы (..), лишние слеши и символы
    * Используется для защиты от атак с помощью относительных путей
    *
    * @param string $path Исходный путь к файлу
    * @return string Нормализованный абсолютный путь
    */
    private function normalizePath($path) {
        $path = str_replace('\\', '/', $path);
        $path = preg_replace('#/+#', '/', $path);
        $parts = array_filter(explode('/', $path), 'strlen');
        $absolutes = [];
        
        foreach ($parts as $part) {
            if ('.' == $part) continue;
            if ('..' == $part) {
                array_pop($absolutes);
            } else {
                $absolutes[] = $part;
            }
        }
        
        return implode('/', $absolutes);
    }

    /**
    * Получение списка доступных шаблонов
    * Сканирует директорию TEMPLATES_PATH и возвращает массив
    * с информацией о каждом шаблоне
    *
    * @return array Массив шаблонов с ключами 'name' и 'path'
    */
    private function getAvailableTemplates() {
        $templates = [];
        $templatesPath = TEMPLATES_PATH;
        
        if (is_dir($templatesPath)) {
            $items = scandir($templatesPath);
            foreach ($items as $item) {
                if ($item !== '.' && $item !== '..' && is_dir($templatesPath . '/' . $item)) {
                    $templates[] = [
                        'name' => $item,
                        'path' => $templatesPath . '/' . $item
                    ];
                }
            }
        }
        
        return $templates;
    }

    /**
    * Рекурсивное получение файлов шаблона
    * Обходит директорию шаблона и возвращает массив файлов
    * с расширением .php, содержащих Template Name
    *
    * @param string $template Название шаблона
    * @return array Массив файлов с метаинформацией
    */
    private function getTemplateFiles($template) {
        $files = [];
        $templatePath = TEMPLATES_PATH . '/' . $template;
        
        if (!is_dir($templatePath)) {
            return $files;
        }
        
        $scanDir = function($dir, $basePath) use (&$scanDir, &$files) {
            $items = scandir($dir);
            
            foreach ($items as $item) {
                if ($item === '.' || $item === '..') {
                    continue;
                }
                
                $fullPath = $dir . DIRECTORY_SEPARATOR . $item;
                $relativePath = str_replace($basePath . DIRECTORY_SEPARATOR, '', $fullPath);
                $relativePath = str_replace('\\', '/', $relativePath);
                
                if (is_dir($fullPath)) {
                    // Рекурсивно сканируем поддиректорию
                    $scanDir($fullPath, $basePath);
                } elseif (is_file($fullPath) && $this->isTemplateFile($fullPath)) {
                    $files[] = $this->getFileInfo($fullPath, $relativePath);
                }
            }
        };
        
        $scanDir($templatePath, $templatePath);

        usort($files, function($a, $b) {
            return strcmp($a['path'], $b['path']);
        });
        
        return $files;
    }

    /**
    * Проверка, является ли файл редактируемым шаблоном
    * Проверяет расширение файла (.php) и наличие Template Name в содержимом
    *
    * @param string $filePath Полный путь к файлу
    * @return bool true если файл является шаблоном
    */
    private function isTemplateFile($filePath) {
        if (pathinfo($filePath, PATHINFO_EXTENSION) !== 'php') {
            return false;
        }
        
        $content = file_get_contents($filePath);
        
        if (preg_match('/\/\*\*\s*\*\s*Template Name:\s*(.*?)\s*\*\//s', $content)) {
            return true;
        }
        
        if (preg_match('/\/\/\s*Template Name:\s*(.*?)$/m', $content)) {
            return true;
        }
        
        return false;
    }

    /**
    * Проверка, является ли файл редактируемым по расширению
    *
    * @param string $filename Имя файла
    * @return bool true если файл имеет расширение .php
    */
    private function isEditableFile($filename) {
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        return $extension === 'php';
    }

    /**
    * Получение метаинформации о файле
    * Включает размер, описание и полный путь
    *
    * @param string $fullPath Полный путь к файлу
    * @param string $relativePath Относительный путь к файлу
    * @return array Массив с информацией о файле
    */
    private function getFileInfo($fullPath, $relativePath) {
        $size = filesize($fullPath);
        $description = $this->getFileDescription($fullPath, $relativePath);
        
        return [
            'name' => basename($relativePath),
            'path' => $relativePath,
            'size' => $this->formatFileSize($size),
            'description' => $description,
            'full_path' => $fullPath
        ];
    }

    /**
    * Извлечение описания шаблона из содержимого файла
    * Ищет Template Name в комментариях файла
    *
    * @param string $fullPath Полный путь к файлу
    * @param string $relativePath Относительный путь к файлу
    * @return string Описание шаблона или пустая строка
    */
    private function getFileDescription($fullPath, $relativePath) {
        $content = file_get_contents($fullPath);
        
        if (preg_match('/\/\*\*\s*\*\s*Template Name:\s*(.*?)\s*\*\//s', $content, $matches)) {
            return trim($matches[1]);
        }
        
        if (preg_match('/\/\/\s*Template Name:\s*(.*?)$/m', $content, $matches)) {
            return trim($matches[1]);
        }
        
        return '';
    }

    /**
    * Форматирование размера файла в удобочитаемый вид
    * Автоматически выбирает единицы измерения (B, KB, MB)
    *
    * @param int $size Размер файла в байтах
    * @return string Отформатированный размер файла
    */
    private function formatFileSize($size) {
        if ($size < 1024) {
            return $size . ' B';
        } elseif ($size < 1048576) {
            return round($size / 1024, 2) . ' KB';
        } else {
            return round($size / 1048576, 2) . ' MB';
        }
    }

    /**
    * Проверяет, есть ли активные быстрые действия
    * Сканирует настройки контроллера администратора на наличие
    * включенных быстрых действий (quick actions)
    *
    * @return bool true если хотя бы одно быстрое действие активно
    */
    public function hasQuickActions() {
        $actions = [
            'add_post',
            'add_page', 
            'add_category',
            'add_tag',
            'add_user',
            'add_content_block',
            'add_field',
            'add_form'
        ];
        
        foreach ($actions as $action) {
            if (SettingsHelper::get('controller_admin', $action, false)) {
                return true;
            }
        }
        
        return false;
    }

}