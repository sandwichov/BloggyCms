<?php

namespace posts\actions;

/**
 * Действие отображения списка всех постов в административной панели
 * Главная страница управления постами блога, показывает все посты
 * с возможностью фильтрации по категории и статусу
 * 
 * @package posts\actions
 * @extends PostAction
 */
class AdminIndex extends PostAction {
    
    /**
     * Метод выполнения отображения списка постов в админ-панели
     * Получает фильтры из GET-параметров, загружает посты и категории,
     * передает их в шаблон для отображения
     * 
     * @return void
     */
    public function execute() {
        $this->pageTitle = 'Управление постами';
        
        try {
            // Получение параметров фильтрации из GET-запроса
            $categoryId = $_GET['category'] ?? null;
            $status = $_GET['status'] ?? null;
            
            // Загрузка постов с применением фильтров
            $posts = $this->postModel->getAllWithFilters($categoryId, $status);
            
            // Загрузка всех категорий для фильтра
            $categories = $this->categoryModel->getAll();
            
            // Отображение страницы со списком постов
            $this->render('admin/posts/index', [
                'posts' => $posts,           // Массив постов для отображения
                'categories' => $categories,  // Массив категорий для фильтра
                'pageTitle' => 'Управление постами блога' // Заголовок страницы
            ]);
            
        } catch (\Exception $e) {
            // Обработка ошибок при загрузке списка
            \Notification::error('Ошибка при загрузке списка записей');
            $this->redirect(ADMIN_URL);
        }
    }
}