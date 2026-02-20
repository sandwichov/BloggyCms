<?php

namespace posts\actions;

/**
 * Действие отображения главной страницы с постами (публичная часть)
 * Показывает список опубликованных постов с пагинацией, учитывая права доступа,
 * добавляет информацию о комментариях и лайках пользователя
 * 
 * @package posts\actions
 * @extends PostAction
 */
class Index extends PostAction {
    
    /**
     * Метод выполнения отображения главной страницы
     * Получает номер страницы из GET-параметров, определяет группы пользователя,
     * загружает посты с пагинацией, дополняет их информацией о комментариях и лайках,
     * передает в шаблон для отображения
     * 
     * @return void
     */
    public function execute() {
        try {
            // Получение и валидация номера страницы
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $page = max(1, $page);
        
            // Получение групп текущего пользователя для фильтрации видимости
            $userGroups = $this->getUserGroups();
            
            // Получение постов с пагинацией и учетом видимости
            $result = $this->postModel->getAllPaginated($page, null, $userGroups);
            
            // Получение ID всех загруженных постов для подсчета комментариев
            $postIds = array_column($result['posts'], 'id');
            
            // Получение количества комментариев для всех постов одним запросом
            $commentsCount = [];
            if (!empty($postIds)) {
                $commentsCount = $this->postModel->getCommentsCountForPosts($postIds);
            }
            
            // Получение всех категорий для отображения в сайдбаре
            $categories = $this->categoryModel->getAll();
            
            // Обработка каждого поста: добавление дополнительной информации
            foreach ($result['posts'] as &$post) {
                $postId = $post['id'];
                $count = $commentsCount[$postId] ?? 0;
                
                // Добавление количества комментариев
                $post['comments_count'] = $count;
                
                // Добавление проверки лайка пользователя (новая система)
                if (isset($_SESSION['user_id'])) {
                    $post['userLiked'] = $this->postModel->hasUserLiked($post['id'], $_SESSION['user_id']);
                } else {
                    $post['userLiked'] = false;
                }
                
                // Добавление проверки на защиту паролем
                $post['password_protected'] = $post['password_protected'] == 1;
            }
            
            // Формирование данных для пагинации
            $pagination = [
                'current_page' => $result['current_page'],
                'total_pages' => $result['pages'],
                'has_more' => $page < $result['pages'],
                'next_url' => $this->getNextPageUrl($page)
            ];
        
            // Отображение главной страницы
            $this->render('front/index', [
                'posts' => $result['posts'],
                'total_posts' => $result['total'],
                'total_pages' => $result['pages'],
                'current_page' => $result['current_page'],
                'pagination' => $pagination,
                'title' => 'Главная страница',
                'categories' => $categories
            ]);
            
        } catch (\Exception $e) {
            // Обработка ошибок при загрузке постов
            \Notification::error('Ошибка при загрузке постов: ' . $e->getMessage());
            $this->redirect(BASE_URL);
        }
    }
    
    /**
     * Получить URL следующей страницы для пагинации
     * 
     * @param int $currentPage Текущая страница
     * @return string URL следующей страницы
     */
    private function getNextPageUrl($currentPage) {
        $nextPage = $currentPage + 1;
        return BASE_URL . '/posts?page=' . $nextPage;
    }
    
    /**
     * Получает группы текущего пользователя для фильтрации видимости постов
     * Всегда включает группу 'guest', добавляет группы пользователя если авторизован
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
                // Подавление исключения - если не удалось получить группы,
                // используем только 'guest'
            }
        }
        
        // Убираем дубликаты
        $userGroups = array_unique($userGroups);
        
        return $userGroups;
    }
}