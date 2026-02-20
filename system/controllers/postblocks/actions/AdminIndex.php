<?php

namespace postblocks\actions;

/**
 * Действие отображения списка всех постблоков в административной панели
 * Главная страница управления постблоками, показывает все доступные блоки,
 * сгруппированные по категориям, с актуальными настройками из базы данных
 * 
 * @package postblocks\actions
 * @extends PostBlockAction
 */
class AdminIndex extends PostBlockAction {
    
    /**
     * Метод выполнения отображения списка постблоков
     * Проверяет права доступа, получает все блоки, объединяет с настройками из БД,
     * группирует по категориям и передает в шаблон для отображения
     * 
     * @return void
     */
    public function execute() {
        // Проверка прав доступа администратора
        if (!$this->checkAdminAccess()) {
            \Notification::error('У вас нет прав доступа к этому разделу');
            $this->redirect(ADMIN_URL . '/login');
            return;
        }
        
        try {
            // Получаем все постблоки и группируем по категориям
            $allBlocks = $this->postBlockManager->getAllPostBlocksInfo();
            
            // Получаем актуальные настройки из БД
            $dbSettings = $this->postBlockModel->getAllBlockSettings();
            
            // Объединяем информацию о блоках с настройками из БД
            $blocksWithSettings = $this->mergeBlocksWithSettings($allBlocks, $dbSettings);
            
            // Группируем по категориям
            $postBlocksByCategory = $this->groupBlocksByCategory($blocksWithSettings);
            
            // Отображение страницы со списком блоков
            $this->render('admin/post_blocks/index', [
                'postBlocksByCategory' => $postBlocksByCategory,
                'pageTitle' => 'Постблоки'
            ]);
            
        } catch (\Exception $e) {
            // Обработка ошибок
            \Notification::error('Ошибка при загрузке постблоков');
            $this->redirect(ADMIN_URL);
        }
    }
    
    /**
     * Объединяет информацию о блоках с настройками из базы данных
     * Приоритет отдается настройкам из БД, если они существуют
     * 
     * @param array $allBlocks Массив всех блоков из менеджера
     * @param array $dbSettings Массив настроек из БД
     * @return array Массив блоков с объединенными настройками
     */
    private function mergeBlocksWithSettings($allBlocks, $dbSettings) {
        $blocksWithSettings = [];
        
        foreach ($allBlocks as $block) {
            $systemName = $block['system_name'];
            $dbSetting = $dbSettings[$systemName] ?? null;
            
            // Используем настройки из БД, если они есть, иначе дефолтные из класса
            $blocksWithSettings[] = [
                'system_name' => $block['system_name'],
                'name' => $block['name'],
                'description' => $block['description'],
                'icon' => $block['icon'],
                'category' => $block['category'],
                'version' => $block['version'],
                'author' => $block['author'],
                'can_use_in_posts' => $dbSetting ? (bool)$dbSetting['enable_in_posts'] : $block['can_use_in_posts'],
                'can_use_in_pages' => $dbSetting ? (bool)$dbSetting['enable_in_pages'] : $block['can_use_in_pages']
            ];
        }
        
        return $blocksWithSettings;
    }
    
    /**
     * Группирует блоки по категориям для удобного отображения
     * 
     * @param array $blocks Массив блоков с настройками
     * @return array Блоки, сгруппированные по категориям
     */
    private function groupBlocksByCategory($blocks) {
        $grouped = [];
        
        foreach ($blocks as $block) {
            $category = $block['category'] ?? 'general';
            if (!isset($grouped[$category])) {
                $grouped[$category] = [];
            }
            $grouped[$category][] = $block;
        }
        
        return $grouped;
    }
}