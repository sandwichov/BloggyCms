<?php

namespace search\actions;

/**
 * Действие для отображения результатов поиска на фронтенде
 * Обрабатывает поисковый запрос и отображает результаты по всем типам контента
 * 
 * @package search\actions
 */
class Index extends SearchAction {
    
    /**
     * Выполняет действие поиска
     * 
     * @return void
     */
    public function execute() {
        try {
            $query = trim($_GET['q'] ?? '');
            $type = $_GET['type'] ?? 'all';
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            
            if ($page < 1) $page = 1;
            
            if (empty($query)) {
                $popularQueries = $this->searchModel->getPopularSearchQueries(10);
                $suggestedSearches = $this->searchModel->getSuggestedSearches(6);
                
                $this->render('front/search/index', [
                    'query' => '',
                    'results' => [],
                    'total' => 0,
                    'pages' => 0,
                    'current_page' => 1,
                    'type' => 'all',
                    'popularQueries' => $popularQueries,
                    'suggestedSearches' => $suggestedSearches
                ]);
                return;
            }
            
            $this->searchModel->saveSearchQuery($query);
            $results = $this->searchModel->searchAll($query, $type, $page);
            $popularQueries = $this->searchModel->getPopularSearchQueries(10);
            $suggestedSearches = $this->searchModel->getSuggestedSearches(6);
            
            $this->render('front/search/index', [
                'query' => $query,
                'results' => $results['items'],
                'total' => $results['total'],
                'pages' => $results['pages'],
                'current_page' => $results['current_page'],
                'type' => $type,
                'popularQueries' => $popularQueries,
                'suggestedSearches' => $suggestedSearches
            ]);
            
        } catch (\Exception $e) {
            error_log('Search error: ' . $e->getMessage());
            
            $this->render('front/search/index', [
                'error' => 'Произошла ошибка при выполнении поиска. Пожалуйста, попробуйте позже.',
                'query' => $_GET['q'] ?? '',
                'popularQueries' => $this->searchModel->getPopularSearchQueries(10),
                'suggestedSearches' => $this->searchModel->getSuggestedSearches(6)
            ]);
        }
    }
}