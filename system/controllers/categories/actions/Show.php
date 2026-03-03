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

        $slug = $this->params['slug'] ?? null;
        
        if (!$slug) {
            \Notification::error('Slug категории не указан');
            $this->redirect(BASE_URL);
            return;
        }

        try {
            $category = $this->categoryModel->getBySlug($slug);

            if (!$category) {
                \Notification::error('Категория не найдена');
                $this->redirect(BASE_URL);
                return;
            }
            
            $this->addBreadcrumb('Главная', BASE_URL);
            
            if (!empty($category['parent_id'])) {
                $this->addParentCategoryBreadcrumbs($category['parent_id']);
            }
            
            $this->addBreadcrumb('Категории', BASE_URL . '/categories');
            $this->addBreadcrumb($category['name']);
            $this->setPageTitle($category['name']);
            
            $hasAccess = true;
            if (isset($category['password_protected']) && $category['password_protected']) {
                $hasAccess = isset($_SESSION['category_access']) 
                    && isset($_SESSION['category_access'][$category['id']]);
            }
            
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $page = max(1, $page);
            
            $result = $hasAccess 
                ? $this->categoryModel->getPostsPaginated($category['id'], $page) 
                : [
                    'posts' => [],
                    'total' => 0,
                    'pages' => 0,
                    'current_page' => 1
                ];
        
            $categories = $this->categoryModel->getAll();
            
            if (!$hasAccess) {
                \Notification::warning('Эта категория защищена паролем');
            }
            
            /**
             * Рендеринг шаблона страницы категории
             */
            $this->render('front/category/category', [
                'category' => $category,
                'posts' => $result['posts'],
                'total_posts' => $result['total'],
                'total_pages' => $result['pages'],
                'current_page' => $result['current_page'],
                'categories' => $categories,
                'hasAccess' => $hasAccess
            ]);
            
        } catch (\Exception $e) {
            \Notification::error('Ошибка при загрузке категории');
            $this->redirect(BASE_URL);
        }
    }
    
    /**
     * Рекурсивно добавляет хлебные крошки для родительских категорий
     * 
     * @param int $parentId ID родительской категории
     * @return void
     */
    private function addParentCategoryBreadcrumbs($parentId) {
        $parentCategory = $this->categoryModel->getById($parentId);
        
        if ($parentCategory) {
            if (!empty($parentCategory['parent_id'])) {
                $this->addParentCategoryBreadcrumbs($parentCategory['parent_id']);
            }
            
            $this->addBreadcrumb(
                $parentCategory['name'],
                BASE_URL . '/category/' . $parentCategory['slug']
            );
        }
    }
}