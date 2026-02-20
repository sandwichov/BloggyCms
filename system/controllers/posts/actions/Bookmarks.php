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
        // Проверка авторизации пользователя
        if (!isset($_SESSION['user_id'])) {
            \Notification::error('Требуется авторизация для просмотра закладок');
            $this->redirect(BASE_URL . '/login');
            return;
        }
        
        try {
            // Получение и валидация номера страницы
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $page = max(1, $page);
            
            // Количество закладок на странице
            $perPage = 10;
            
            $userId = $_SESSION['user_id'];
            
            // Получение закладок пользователя с пагинацией
            $result = $this->postModel->getUserBookmarks($userId, $page, $perPage);
            
            // Отображение страницы с закладками
            $this->render('front/posts/bookmarks', [
                'posts' => $result['posts'],           // Массив постов в закладках
                'total_posts' => $result['total'],     // Общее количество закладок
                'total_pages' => $result['pages'],     // Всего страниц
                'current_page' => $result['current_page'], // Текущая страница
                'title' => 'Мои закладки',              // Заголовок страницы
                'bookmarks_count' => $result['total']   // Количество закладок (для счетчика)
            ]);
            
        } catch (\Exception $e) {
            // Обработка ошибок при загрузке закладок
            \Notification::error('Ошибка при загрузке закладок: ' . $e->getMessage());
            $this->redirect(BASE_URL);
        }
    }
}