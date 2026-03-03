<?php

namespace posts\actions;

/**
 * Действие отображения закладок пользователя
 * Показывает список постов, добавленных текущим пользователем в закладки,
 * с поддержкой пагинации
 * 
 * @package posts\actions
 * @extends PostAction
 */
class Bookmarks extends PostAction {
    
    /**
     * Метод выполнения отображения закладок пользователя
     * Проверяет авторизацию, получает номер страницы,
     * загружает закладки пользователя с пагинацией и отображает их
     * 
     * @return void
     */
    public function execute() {
        if (!isset($_SESSION['user_id'])) {
            \Notification::error('Требуется авторизация для просмотра закладок');
            $this->redirect(BASE_URL . '/login');
            return;
        }
        
        try {
            $this->addBreadcrumb('Главная', BASE_URL);
            $this->addBreadcrumb('Профиль', BASE_URL . '/profile');
            $this->addBreadcrumb('Мои закладки');
            $this->setPageTitle('Мои закладки');
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $page = max(1, $page);
            $perPage = 10;
            $userId = $_SESSION['user_id'];
            $result = $this->postModel->getUserBookmarks($userId, $page, $perPage);
            
            $this->render('front/posts/bookmarks', [
                'posts' => $result['posts'],
                'total_posts' => $result['total'],
                'total_pages' => $result['pages'],
                'current_page' => $result['current_page'],
                'bookmarks_count' => $result['total']
            ]);
            
        } catch (\Exception $e) {
            \Notification::error('Ошибка при загрузке закладок: ' . $e->getMessage());
            $this->redirect(BASE_URL);
        }
    }
}