<?php

namespace search\actions;

/**
 * Действие отображения списка всех поисковых запросов в административной панели
 * Показывает историю поисковых запросов с пагинацией для администрирования
 * 
 * @package search\actions
 * @extends SearchAction
 */
class AdminIndex extends SearchAction {
    
    /**
     * Метод выполнения отображения списка поисковых запросов
     * Проверяет авторизацию пользователя, получает номер страницы,
     * загружает историю запросов с пагинацией и отображает их
     * 
     * @return void
     */
    public function execute() {
        // Проверка авторизации пользователя
        if (!$this->checkAuth()) {
            \Notification::error('Пожалуйста, авторизуйтесь для доступа к истории поиска');
            $this->redirect(ADMIN_URL . '/login');
            return;
        }
        
        try {
            // Получение и валидация номера страницы из GET-параметров
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $page = max(1, $page);
            
            // Получение всех поисковых запросов с пагинацией
            $result = $this->searchModel->getAllSearchQueries($page);
            
            // Отображение страницы со списком запросов
            $this->render('admin/search/index', [
                'queries' => $result['queries'],        // Массив поисковых запросов
                'total' => $result['total'],            // Общее количество запросов
                'pages' => $result['pages'],            // Всего страниц
                'current_page' => $result['current_page'], // Текущая страница
                'pageTitle' => 'История поисковых запросов' // Заголовок страницы
            ]);
            
        } catch (\Exception $e) {
            // Обработка ошибок при загрузке списка
            \Notification::error('Ошибка при загрузке истории поисковых запросов');
            $this->redirect(ADMIN_URL);
        }
    }
}