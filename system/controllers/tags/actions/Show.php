<?php

namespace tags\actions;

/**
 * Действие отображения постов по тегу в публичной части
 * Показывает страницу с постами, привязанными к указанному тегу,
 * с поддержкой пагинации и информацией о голосовании
 * 
 * @package tags\actions
 * @extends TagAction
 */
class Show extends TagAction {
    
    /**
     * Метод выполнения отображения постов по тегу
     * Получает slug тега из параметров, загружает тег и его посты,
     * добавляет информацию о голосовании и отображает страницу
     * 
     * @return void
     */
    public function execute() {
        // Получение slug тега из параметров
        $slug = $this->params['slug'] ?? null;
        
        // Проверка наличия slug
        if (!$slug) {
            \Notification::error('Тег не найден');
            $this->redirect(BASE_URL);
            return;
        }
        
        try {
            // Получение тега по slug
            $tag = $this->tagModel->getBySlug($slug);
            
            // Проверка существования тега
            if (!$tag) {
                \Notification::error('Тег не найден');
                $this->redirect(BASE_URL);
                return;
            }
            
            // Получение и валидация номера страницы
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $page = max(1, $page);
            
            // Получение постов по тегу с пагинацией
            $result = $this->tagModel->getPostsByTag($tag['id'], $page);
            
            // Получение категорий для сайдбара
            $categories = $this->categoryModel->getAll();
            
            // Добавление информации о голосовании для каждого поста
            $ipAddress = $_SERVER['REMOTE_ADDR'];
            foreach ($result['posts'] as &$post) {
                $post['userVote'] = $this->postModel->getUserVote($post['id'], $ipAddress);
            }
            
            // Отображение страницы с постами по тегу
            $this->render('front/tags/tag', [
                'tag' => $tag,                       // Данные тега
                'posts' => $result['posts'],          // Посты на текущей странице
                'total_posts' => $result['total'],    // Общее количество постов
                'total_pages' => $result['pages'],    // Всего страниц
                'current_page' => $result['current_page'], // Текущая страница
                'title' => 'Тег: ' . $tag['name'],    // Заголовок страницы
                'categories' => $categories            // Категории для сайдбара
            ]);
            
        } catch (\Exception $e) {
            // Обработка ошибок
            \Notification::error('Ошибка при загрузке тега');
            $this->redirect(BASE_URL);
        }
    }
}