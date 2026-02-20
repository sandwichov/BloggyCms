<?php

namespace search\actions;

/**
 * Действие отображения страницы поиска и результатов поиска
 * Обрабатывает поисковый запрос из GET-параметров, выполняет поиск постов,
 * сохраняет запрос в историю и отображает результаты с пагинацией
 * 
 * @package search\actions
 * @extends SearchAction
 */
class Index extends SearchAction {
    
    /**
     * Метод выполнения поиска и отображения результатов
     * Получает поисковый запрос из GET-параметров, проверяет его длину,
     * выполняет поиск с пагинацией, загружает дополнительные данные и отображает результаты
     * 
     * @return void
     */
    public function execute() {
        try {
            // Получение и очистка поискового запроса
            $query = isset($_GET['q']) ? trim($_GET['q']) : '';
            
            // Проверка наличия запроса
            if (empty($query)) {
                \Notification::warning('Введите поисковый запрос');
                $this->redirect(BASE_URL);
                return;
            }

            // Проверка минимальной длины запроса (не менее 3 символов)
            if (mb_strlen($query) < 3) {
                \Notification::warning('Поисковый запрос должен содержать не менее 3 символов');
                $this->redirect(BASE_URL);
                return;
            }
            
            // Сохранение запроса в историю (ошибки игнорируются)
            try {
                $this->searchModel->saveSearchQuery($query);
            } catch (\Exception $e) {
                // Игнорируем ошибки сохранения запроса
            }
            
            // Получение и валидация номера страницы
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $page = max(1, $page);
            
            // Выполнение поиска постов с пагинацией
            $result = $this->searchModel->searchPosts($query, $page);
            
            // Уведомление, если ничего не найдено
            if ($result['total'] === 0) {
                \Notification::info('По вашему запросу ничего не найдено');
            }
            
            // Получение дополнительных моделей для шаблона
            $categoryModel = new \CategoryModel($this->db);
            $settingsModel = new \SettingsModel($this->db);
            
            // Загрузка дополнительных данных с обработкой ошибок
            try {
                $categories = $categoryModel->getAll();
                $suggestedSearches = $this->searchModel->getSuggestedSearches();
            } catch (\Exception $e) {
                // Если не удалось получить дополнительные данные, продолжаем с пустыми массивами
                $categories = [];
                $suggestedSearches = [];
                \Notification::warning('Не удалось загрузить некоторые элементы страницы');
            }
            
            // Получение настроек поиска (с обработкой ошибок)
            try {
                $searchSettings = $settingsModel->get('search');
                $searchTitle = isset($searchSettings['search_title']) ? $searchSettings['search_title'] : 'Поиск по запросу: ';
            } catch (\Exception $e) {
                $searchTitle = 'Поиск по запросу: ';
            }
            
            // Отображение страницы с результатами поиска
            $this->render('front/search', [
                'posts' => $result['posts'],               // Найденные посты
                'total_posts' => $result['total'],         // Общее количество результатов
                'total_pages' => $result['pages'],         // Всего страниц
                'current_page' => $result['current_page'], // Текущая страница
                'query' => $result['query'],               // Поисковый запрос
                'categories' => $categories,                // Категории для сайдбара
                'suggestedSearches' => $suggestedSearches,  // Предлагаемые поисковые запросы
                'title' => $searchTitle . $query            // Заголовок страницы
            ]);
            
        } catch (\Exception $e) {
            // Обработка критических ошибок при выполнении поиска
            \Notification::error('Ошибка при выполнении поиска');
            $this->redirect(BASE_URL);
        }
    }
}