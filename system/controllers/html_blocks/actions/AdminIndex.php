<?php

namespace html_blocks\actions;

/**
 * Действие отображения списка HTML-блоков в админ-панели
 * Показывает все HTML-блоки системы с информацией о статусе их типов
 * 
 * @package html_blocks\actions
 * @extends HtmlBlockAction
 */
class AdminIndex extends HtmlBlockAction {
    
    /**
     * Метод выполнения отображения списка HTML-блоков
     * Загружает все блоки и информацию о типах, отображает их в табличном виде
     * 
     * @return void
     */
    public function execute() {
        // Проверка административных прав доступа
        if (!$this->checkAdminAccess()) {
            \Notification::error('У вас нет прав доступа к этому разделу');
            $this->redirect(ADMIN_URL . '/login');
            return;
        }
        
        try {
            // Получение всех HTML-блоков из базы данных
            $blocks = $this->htmlBlockModel->getAll();
            
            // Получение всех типов блоков из менеджера
            $allBlockTypes = $this->blockTypeManager->getAllBlockTypes();
            
            // Добавление информации о статусе типа для каждого блока
            foreach ($blocks as &$block) {
                $blockTypeName = $block['block_type'] ?? 'DefaultBlock';
                
                // Проверка активности типа блока (безопасно, если тип не найден)
                $block['type_is_active'] = $this->blockTypeManager->isBlockTypeActive($blockTypeName);
            }
            
            /**
             * Рендеринг страницы управления HTML-блоками
             * 
             * @param string $template Путь к шаблону (admin/html_blocks/index)
             * @param array $data Данные для шаблона:
             * - blocks: массив всех HTML-блоков с данными
             * - blockTypes: массив доступных типов блоков
             * - pageTitle: заголовок страницы
             */
            $this->render('admin/html_blocks/index', [
                'blocks' => $blocks,
                'blockTypes' => $this->blockTypeManager->getBlockTypes(),
                'pageTitle' => 'Управление HTML-блоками'
            ]);
            
        } catch (\Exception $e) {
            // Обработка ошибок при загрузке блоков
            \Notification::error('Ошибка при загрузке списка HTML-блоков: ' . $e->getMessage());
            $this->redirect(ADMIN_URL);
        }
    }
}