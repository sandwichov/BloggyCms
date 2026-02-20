<?php

namespace categories\actions;

/**
 * Действие отображения категории на фронтенде
 * Показывает страницу категории с постами, включая пагинацию и проверку доступа к защищенным категориям
 * 
 * @package categories\actions
 * @extends CategoryAction
 */
class Show extends CategoryAction {
    
    /**
     * Метод выполнения отображения категории
     * Загружает данные категории и ее постов с пагинацией, проверяет доступ для защищенных категорий
     * 
     * @return void
     */
    public function execute() {
        // Получение slug категории из параметров
        $slug = $this->params['slug'] ?? null;
        
        // Проверка наличия slug категории
        if (!$slug) {
            \Notification::error('Slug категории не указан');
            $this->redirect(BASE_URL);
            return;
        }

        try {
            // Получение данных категории по slug
            $category = $this->categoryModel->getBySlug($slug);

            // Проверка существования категории
            if (!$category) {
                \Notification::error('Категория не найдена');
                $this->redirect(BASE_URL);
                return;
            }
            
            // Проверка доступа к защищенной паролем категории
            $hasAccess = true;
            if (isset($category['password_protected']) && $category['password_protected']) {
                // Проверка наличия доступа в сессии
                $hasAccess = isset($_SESSION['category_access']) 
                    && isset($_SESSION['category_access'][$category['id']]);
            }
            
            // Определение текущей страницы для пагинации
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $page = max(1, $page); // Гарантия, что страница не меньше 1
            
            // Получение постов категории с пагинацией (если есть доступ)
            $result = $hasAccess 
                ? $this->categoryModel->getPostsPaginated($category['id'], $page) 
                : [
                    'posts' => [],
                    'total' => 0,
                    'pages' => 0,
                    'current_page' => 1
                ];
        
            // Получение всех категорий для отображения в сайдбаре/навигации
            $categories = $this->categoryModel->getAll();
            
            // Уведомление о необходимости ввода пароля для защищенной категории
            if (!$hasAccess) {
                \Notification::warning('Эта категория защищена паролем');
            }
            
            /**
             * Рендеринг шаблона страницы категории
             * 
             * @param string $template Путь к шаблону (front/category/category)
             * @param array $data Данные для шаблона:
             * - category: данные текущей категории
             * - posts: массив постов категории
             * - total_posts: общее количество постов
             * - total_pages: общее количество страниц пагинации
             * - current_page: текущая страница
             * - categories: список всех категорий для навигации
             * - hasAccess: флаг доступа к защищенной категории
             * - title: заголовок страницы (название категории)
             */
            $this->render('front/category/category', [
                'category' => $category,
                'posts' => $result['posts'],
                'total_posts' => $result['total'],
                'total_pages' => $result['pages'],
                'current_page' => $result['current_page'],
                'categories' => $categories,
                'hasAccess' => $hasAccess,
                'title' => $category['name']
            ]);
            
        } catch (\Exception $e) {
            // Обработка ошибок при загрузке категории
            \Notification::error('Ошибка при загрузке категории');
            $this->redirect(BASE_URL);
        }
    }
}