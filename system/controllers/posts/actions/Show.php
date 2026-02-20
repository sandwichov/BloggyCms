<?php

namespace posts\actions;

/**
 * Действие отображения отдельного поста (публичная часть)
 * Загружает и отображает пост по его URL-адресу (slug),
 * проверяет права доступа, защиту паролем, обрабатывает блоки контента,
 * комментарии, лайки, закладки и пользовательские поля
 * 
 * @package posts\actions
 * @extends PostAction
 */
class Show extends PostAction {
    
    /**
     * Метод выполнения отображения поста
     * Получает slug из параметров, проверяет существование поста,
     * видимость для пользователя, защиту паролем, загружает все связанные данные
     * и отображает пост
     * 
     * @return void
     * @throws \Exception Если slug не указан
     */
    public function execute() {
        // Получение slug поста из параметров
        $slug = $this->params['slug'] ?? null;
        if (!$slug) {
            throw new \Exception('Slug поста не указан');
        }

        try {
            // Загрузка поста по slug
            $post = $this->postModel->getBySlug($slug);
        
            if (!$post) {
                \Notification::error('Запись не найдена');
                $this->redirect(BASE_URL);
                return;
            }
            
            // Проверка видимости поста для пользователя
            $userGroups = $this->getUserGroups();
            $isVisible = $this->postModel->checkPostVisibility($post['id'], $userGroups);
            
            if (!$isVisible) {
                // Показываем 404 страницу вместо редиректа на главную
                http_response_code(404);
                $this->render('front/404');
                return;
            }
            
            // Проверка защиты паролем
            $isPasswordProtected = $post['password_protected'] == 1;
            
            if ($isPasswordProtected) {
                // Проверка доступа в сессии
                $hasAccess = $this->checkPostAccess($post['id']);
                
                if (!$hasAccess) {
                    // Отображение формы ввода пароля
                    $this->renderPasswordForm($post);
                    return;
                }
            }
            
            // Если пост доступен - отображаем его
            $this->showPost($post);
            
        } catch (\Exception $e) {
            \Notification::error('Ошибка при загрузке записи');
            $this->redirect(BASE_URL);
        }
    }
    
    /**
     * Проверяет, есть ли у пользователя доступ к защищенному посту
     * 
     * @param int $postId ID поста
     * @return bool true если доступ есть
     */
    private function checkPostAccess($postId) {
        // Проверяем сессию на наличие доступа
        return isset($_SESSION['post_access'][$postId]) && $_SESSION['post_access'][$postId] === true;
    }
    
    /**
     * Отображает форму ввода пароля для защищенного поста
     * 
     * @param array $post Данные поста
     * @return void
     */
    private function renderPasswordForm($post) {
        // Подготовка данных для шаблона ввода пароля
        $categories = $this->categoryModel->getAll();
        $category = $this->categoryModel->getById($post['category_id']);
        
        // Форматирование даты
        $createdDate = new \DateTime($post['created_at']);
        $formattedDate = $createdDate->format('d M Y');
        
        $this->render('front/posts/password', [
            'post' => $post,
            'title' => $post['title'],
            'categories' => $categories,
            'category' => $category,
            'formattedDate' => $formattedDate,
            'error' => isset($_GET['error']) && $_GET['error'] == 'password'
        ]);
    }
    
    /**
     * Отображает полную страницу поста со всем контентом
     * 
     * @param array $post Данные поста
     * @return void
     */
    private function showPost($post) {
        // Увеличение счетчика просмотров
        $this->postModel->incrementViews($post['id']);
        
        $isAdmin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'];
        
        // Проверка лайка и закладки пользователя
        $userLiked = false;
        $userBookmarked = false;
        
        if (isset($_SESSION['user_id'])) {
            $userLiked = $this->postModel->hasUserLiked($post['id'], $_SESSION['user_id']);
            $userBookmarked = $this->postModel->hasBookmark($post['id'], $_SESSION['user_id']);
        }
        
        // Получение комментариев
        $commentsData = $this->getComments($post['id'], $isAdmin);
        $comments = $commentsData['tree'];
        $totalComments = $commentsData['total'];
        
        // Получение тегов и категорий
        $tags = $this->tagModel->getForPost($post['id']);
        $post['tags'] = $tags;
        $category = $this->categoryModel->getById($post['category_id']);
        $categories = $this->categoryModel->getAll();
        
        // Получение и обработка блоков поста
        $processedBlocks = $this->getProcessedBlocks($post['id']);
        
        // Получение пользовательских полей
        $fieldValues = $this->getCustomFields($post['id']);
        
        // Параметры для комментариев
        $scrollToComment = $_GET['scroll_to_comment'] ?? null;
        $pendingComment = $_GET['pending_comment'] ?? null;
        
        // Добавление количества комментариев в данные поста
        $post['comments_count'] = $totalComments;
        
        // Рендеринг шаблона
        $this->render('front/posts/show', [
            'post' => $post,
            'tags' => $tags,
            'title' => $post['title'],
            'categories' => $categories,
            'category' => $category,
            'comments' => $comments,
            'totalComments' => $totalComments,
            'commentController' => new \CommentController($this->db),
            'userLiked' => $userLiked,
            'userBookmarked' => $userBookmarked,
            'fieldValues' => $fieldValues,
            'blocks' => $processedBlocks,
            'scrollToComment' => $scrollToComment,
            'pendingComment' => $pendingComment,
            'isAdmin' => $isAdmin,
            'likes_count' => $post['likes_count'] ?? 0
        ]);
    }

    /**
     * Получает и обрабатывает комментарии к посту
     * 
     * @param int $postId ID поста
     * @param bool $isAdmin Флаг администратора
     * @return array Массив с деревом комментариев и общим количеством
     */
    private function getComments($postId, $isAdmin) {
        $commentController = new \CommentController($this->db);
        return $commentController->getCommentsByPostWithUserData($postId, $isAdmin);
    }

    /**
     * Получает и обрабатывает блоки поста
     * 
     * @param int $postId ID поста
     * @return array Массив обработанных блоков
     */
    private function getProcessedBlocks($postId) {
        $blocks = $this->postBlockModel->getByPost($postId);
        $processedBlocks = [];
        
        // Загрузка ассетов для блоков
        $blocksData = [];
        foreach ($blocks as $block) {
            $blocksData[] = [
                'type' => $block['type']
            ];
        }
        
        if (!empty($blocksData)) {
            $this->postBlockManager->loadFrontendAssetsForBlocks($blocksData);
        }
        
        // Обработка каждого блока
        foreach ($blocks as $block) {
            $processedBlocks[] = $this->processSingleBlock($block);
        }
        
        return $processedBlocks;
    }

    /**
     * Обрабатывает один блок поста
     * 
     * @param array $block Данные блока
     * @return array Обработанный блок
     */
    private function processSingleBlock($block) {
        $content = $block['content'];
        $settings = $block['settings'];
        
        // Декодирование JSON
        if (is_string($content)) {
            $decoded = json_decode($content, true);
            $content = (json_last_error() === JSON_ERROR_NONE) ? $decoded : $content;
        }
        
        if (is_string($settings)) {
            $decodedSettings = json_decode($settings, true);
            $settings = (json_last_error() === JSON_ERROR_NONE) ? $decodedSettings : [];
        }
        
        // Объединение настроек с настройками по умолчанию
        $dbSettings = $this->postBlockModel->getBlockSettings($block['type']);
        $mergedSettings = array_merge($dbSettings, $settings);
        
        // Обработка контента через менеджер
        $processedContent = $this->postBlockManager->processPostBlockContent(
            $content, 
            $block['type'], 
            $mergedSettings
        );
        
        return [
            'type' => $block['type'],
            'content' => $processedContent,
            'settings' => $mergedSettings
        ];
    }

    /**
     * Получает пользовательские поля для поста
     * 
     * @param int $postId ID поста
     * @return array Массив значений полей
     */
    private function getCustomFields($postId) {
        $fieldModel = new \FieldModel($this->db);
        $customFields = $fieldModel->getActiveByEntityType('post');
        $fieldValues = [];
        
        foreach ($customFields as $field) {
            $fieldValues[$field['system_name']] = [
                'value' => $fieldModel->getFieldValue('post', $postId, $field['system_name']),
                'field' => $field
            ];
        }
        
        return $fieldValues;
    }
    
    /**
     * Получает группы текущего пользователя для проверки видимости
     * 
     * @return array Массив групп пользователя
     */
    private function getUserGroups() {
        $userGroups = [];
        
        // Всегда добавляем 'guest'
        $userGroups[] = 'guest';
        
        // Если пользователь авторизован, добавляем его группы
        if (isset($_SESSION['user_id'])) {
            try {
                $userModel = new \UserModel($this->db);
                $userGroupIds = $userModel->getUserGroupIds($_SESSION['user_id']);
                
                if (!empty($userGroupIds)) {
                    // Преобразуем ID групп в строки и добавляем к основному списку
                    $userGroupIds = array_map('strval', $userGroupIds);
                    $userGroups = array_merge($userGroups, $userGroupIds);
                }
                
            } catch (\Exception $e) {
                // Подавление исключений
            }
        }
        
        // Убираем дубликаты
        $userGroups = array_unique($userGroups);
        
        return $userGroups;
    }
}