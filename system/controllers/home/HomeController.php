<?php

/**
 * Контроллер главной страницы
 * Управляет отображением и настройками главной страницы сайта
 * Обеспечивает вывод постов, популярных записей и HTML-блоков на главной
 * 
 * @package controllers
 * @extends Controller
 */
class HomeController extends Controller {
    
    /**
     * @var PostModel Модель для работы с постами
     */
    private $postModel;
    
    /**
     * @var CategoryModel Модель для работы с категориями
     */
    private $categoryModel;
    
    /**
     * @var SettingsModel Модель для работы с настройками
     */
    private $settingsModel;
    
    /**
     * Конструктор контроллера главной страницы
     * Инициализирует модели постов, категорий и настроек
     *
     * @param Database $db Объект подключения к базе данных
     */
    public function __construct($db) {
        parent::__construct($db);
        
        $this->postModel = new PostModel($db);
        $this->categoryModel = new CategoryModel($db);
        $this->settingsModel = new SettingsModel($db);
    }
    
    /**
     * Действие: Главная страница сайта
     * Отображает главную страницу с постами, категориями и дополнительными блоками
     * 
     * @return void
     */
    public function indexAction() {
        try {
            // Получение настроек главной страницы
            $homeSettings = $this->settingsModel->get('home_page') ?? [];
            
            // Настройки отображения постов на главной
            $showRecentPosts = $homeSettings['show_recent_posts'] ?? true;
            $postsPerPage = $homeSettings['posts_per_page'] ?? 5;
            
            // Получение групп текущего пользователя для фильтрации постов по правам доступа
            $userGroups = $this->getUserGroups();
            
            // Получение постов для главной страницы (если включено в настройках)
            if ($showRecentPosts) {
                $result = $this->postModel->getAllPaginated(1, $postsPerPage, $userGroups);
                $posts = $result['posts'];
                
                // Получение количества комментариев для каждого поста
                $postIds = array_column($posts, 'id');
                $commentsCount = [];
                if (!empty($postIds)) {
                    $commentsCount = $this->postModel->getCommentsCountForPosts($postIds);
                }
                
                // Добавление количества комментариев к каждому посту
                foreach ($posts as &$post) {
                    $post['comments_count'] = $commentsCount[$post['id']] ?? 0;
                }
            } else {
                $posts = [];
            }
            
            // Получение категорий для меню навигации
            $categories = $this->categoryModel->getAll();
            
            // Получение популярных постов (по количеству просмотров)
            $popularPosts = $this->getPopularPosts(5, $userGroups);
            
            // Получение HTML-блоков для главной страницы
            $htmlBlocks = $this->getHomeHtmlBlocks();
            
            /**
             * Рендеринг главной страницы
             * 
             * @param string $template Путь к шаблону (front/home/index)
             * @param array $data Данные для шаблона:
             * - posts: массив последних постов
             * - popular_posts: массив популярных постов
             * - categories: массив категорий для меню
             * - html_blocks: массив HTML-блоков для главной
             * - home_settings: настройки главной страницы
             * - title: заголовок страницы из настроек
             * - meta_description: мета-описание для SEO
             * - meta_keywords: мета-ключевые слова для SEO
             */
            $this->render('front/home/index', [
                'posts' => $posts,
                'popular_posts' => $popularPosts,
                'categories' => $categories,
                'html_blocks' => $htmlBlocks,
                'home_settings' => $homeSettings,
                'title' => $homeSettings['title'] ?? 'Главная страница',
                'meta_description' => $homeSettings['meta_description'] ?? '',
                'meta_keywords' => $homeSettings['meta_keywords'] ?? ''
            ]);
            
        } catch (\Exception $e) {
            // Обработка ошибок - показ упрощенной главной страницы
            
            $this->render('front/home/index', [
                'posts' => [],
                'popular_posts' => [],
                'categories' => [],
                'html_blocks' => [],
                'home_settings' => [],
                'title' => 'Главная страница',
                'meta_description' => '',
                'meta_keywords' => ''
            ]);
        }
    }
    
    /**
     * Получение групп текущего пользователя
     * Определяет группы доступа пользователя для фильтрации контента
     * Всегда включает группу 'guest', добавляет группы авторизованного пользователя
     *
     * @return array Массив групп пользователя
     */
    private function getUserGroups() {
        $userGroups = [];
        
        // Все пользователи (включая неавторизованных) принадлежат к группе 'guest'
        $userGroups[] = 'guest';
        
        // Для авторизованных пользователей получаем их группы
        if (isset($_SESSION['user_id'])) {
            try {
                $userModel = new \UserModel($this->db);
                $userGroupIds = $userModel->getUserGroupIds($_SESSION['user_id']);
                
                if (!empty($userGroupIds)) {
                    // Преобразование ID групп в строки и добавление в список
                    $userGroupIds = array_map('strval', $userGroupIds);
                    $userGroups = array_merge($userGroups, $userGroupIds);
                }
                
            } catch (\Exception $e) {
                // Ошибки при получении групп игнорируются
            }
        }
        
        // Удаление дубликатов и возврат результата
        return array_unique($userGroups);
    }
    
    /**
     * Получение популярных постов
     * Возвращает посты, отсортированные по количеству просмотров
     *
     * @param int $limit Количество возвращаемых постов
     * @param array $userGroups Группы пользователя для фильтрации доступа
     * @return array Массив популярных постов
     */
    private function getPopularPosts($limit = 5, $userGroups = []) {
        try {
            // Запрос для получения популярных постов по просмотрам
            $sql = "SELECT p.*, c.name as category_name, c.slug as category_slug,
                           u.username as author_name, u.display_name as author_display_name
                    FROM posts p 
                    LEFT JOIN categories c ON p.category_id = c.id 
                    LEFT JOIN users u ON p.user_id = u.id
                    WHERE p.status = 'published'
                    ORDER BY p.views DESC, p.created_at DESC
                    LIMIT " . (int)$limit;
            
            $posts = $this->db->fetchAll($sql);
            
            // Фильтрация постов по правам доступа
            $visiblePosts = [];
            foreach ($posts as $post) {
                if ($this->postModel->checkPostVisibility($post['id'], $userGroups)) {
                    $visiblePosts[] = $post;
                }
            }
            
            return $visiblePosts;
            
        } catch (\Exception $e) {
            // В случае ошибки возвращается пустой массив
            return [];
        }
    }
    
    /**
     * Получение HTML-блоков для главной страницы
     * Загружает и обрабатывает HTML-блоки, расположенные на главной странице
     *
     * @return array Массив HTML-блоков с обработанным контентом
     */
    private function getHomeHtmlBlocks() {
        try {
            // Получение активных HTML-блоков для главной страницы
            $htmlBlocks = $this->db->fetchAll("
                SELECT * FROM html_blocks 
                WHERE is_active = 1 
                AND position LIKE '%home%'
                ORDER BY sort_order ASC
            ");
            
            // Обработка контента блоков (шорткоды и другие преобразования)
            foreach ($htmlBlocks as &$block) {
                $block['processed_content'] = process_shortcodes($block['content']);
            }
            
            return $htmlBlocks;
            
        } catch (\Exception $e) {
            return [];
        }
    }
    
    /**
     * Действие: Настройки главной страницы в админ-панели
     * Предоставляет интерфейс для изменения параметров главной страницы
     * 
     * @return void
     */
    public function adminSettingsAction() {
        // Проверка прав администратора
        if (!$this->checkAdminAccess()) {
            $this->redirect(ADMIN_URL . '/login');
            return;
        }
        
        // Обработка сохранения настроек
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                // Подготовка данных настроек из формы
                $settings = [
                    'title' => $_POST['title'] ?? 'Главная страница',
                    'meta_description' => $_POST['meta_description'] ?? '',
                    'meta_keywords' => $_POST['meta_keywords'] ?? '',
                    'show_recent_posts' => isset($_POST['show_recent_posts']) ? 1 : 0,
                    'posts_per_page' => (int)($_POST['posts_per_page'] ?? 5),
                    'show_popular_posts' => isset($_POST['show_popular_posts']) ? 1 : 0,
                    'popular_posts_count' => (int)($_POST['popular_posts_count'] ?? 5),
                    'custom_welcome_text' => $_POST['custom_welcome_text'] ?? ''
                ];
                
                // Сохранение настроек в базе данных
                $this->settingsModel->set('home_page', $settings);
                \Notification::success('Настройки главной страницы сохранены');
                
            } catch (\Exception $e) {
                \Notification::error('Ошибка при сохранении настроек: ' . $e->getMessage());
            }
            
            $this->redirect(ADMIN_URL . '/home/settings');
        }
        
        // Получение текущих настроек
        $currentSettings = $this->settingsModel->get('home_page') ?? [];
        
        /**
         * Рендеринг страницы настроек главной страницы
         * 
         * @param string $template Путь к шаблону (admin/home/settings)
         * @param array $data Данные для шаблона:
         * - settings: текущие настройки главной страницы
         * - pageTitle: заголовок страницы
         */
        $this->render('admin/home/settings', [
            'settings' => $currentSettings,
            'pageTitle' => 'Настройки главной страницы'
        ]);
    }
}