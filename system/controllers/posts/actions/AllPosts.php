<?php

namespace posts\actions;

/**
 * Действие отображения всех постов с пагинацией (публичная часть)
 * Отображает список всех опубликованных постов с учетом прав доступа
 * и информацией о лайках пользователя
 * 
 * @package posts\actions
 * @extends PostAction
 */
class AllPosts extends PostAction {
    
    /**
     * Метод выполнения отображения всех постов
     * Получает номер страницы из GET-параметров, определяет группы пользователя,
     * загружает посты с пагинацией, добавляет информацию о лайках
     * и передает в шаблон для отображения
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
            
            // Получение всех категорий для отображения
            $categories = $this->categoryModel->getAll();
            
            // Обработка каждого поста: добавление информации о лайках пользователя
            foreach ($result['posts'] as &$post) {
                // Проверка лайка для авторизованного пользователя
                if (isset($_SESSION['user_id'])) {
                    $post['userLiked'] = $this->postModel->hasUserLiked($post['id'], $_SESSION['user_id']);
                } else {
                    $post['userLiked'] = false;
                }
            }
        
            // Отображение страницы со списком постов
            $this->render('front/posts/posts', [
                'posts' => $result['posts'],
                'total_posts' => $result['total'],
                'total_pages' => $result['pages'],
                'current_page' => $result['current_page'],
                'title' => 'Все записи',
                'categories' => $categories
            ]);
            
        } catch (\Exception $e) {
            // Обработка ошибок при загрузке постов
            \Notification::error('Ошибка при загрузке списка записей: ' . $e->getMessage());
            $this->redirect(BASE_URL);
        }
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