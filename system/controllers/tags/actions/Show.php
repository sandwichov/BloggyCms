<?php

namespace tags\actions;

/**
 * Действие отображения постов по тегу в публичной части
 * Показывает страницу с постами, привязанными к указанному тегу
 * @package tags\actions
 * @extends TagAction
 */
class Show extends TagAction {
    
    /**
     * Метод выполнения отображения постов по тегу
     * @return void
     */
    public function execute() {
        $slug = $this->params['slug'] ?? null;
        
        if (!$slug) {
            \Notification::error('Тег не найден');
            $this->redirect(BASE_URL);
            return;
        }
        
        try {

            $tag = $this->tagModel->getBySlug($slug);
            
            if (!$tag) {
                \Notification::error('Тег не найден');
                $this->redirect(BASE_URL);
                return;
            }
            
            $this->addBreadcrumb('Главная', BASE_URL);
            $this->addBreadcrumb('Теги', BASE_URL . '/tags');
            $this->addBreadcrumb('Тег: ' . $tag['name']);
            $this->setPageTitle('Тег: ' . $tag['name']);
            
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $page = max(1, $page);
            
            $result = $this->tagModel->getPostsByTag($tag['id'], $page);
            
            $categories = $this->categoryModel->getAll();
            
            $ipAddress = $_SERVER['REMOTE_ADDR'];
            foreach ($result['posts'] as &$post) {
                $post['userVote'] = $this->postModel->getUserVote($post['id'], $ipAddress);
            }

            $this->render('front/tags/tag', [
                'tag' => $tag,
                'posts' => $result['posts'],
                'total_posts' => $result['total'],
                'total_pages' => $result['pages'],
                'current_page' => $result['current_page'],
                'categories' => $categories
            ]);
            
        } catch (\Exception $e) {
            \Notification::error('Ошибка при загрузке тега');
            $this->redirect(BASE_URL);
        }
    }
}