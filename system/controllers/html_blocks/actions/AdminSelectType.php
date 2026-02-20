<?php

namespace html_blocks\actions;

/**
 * Действие выбора типа HTML-блока при создании
 * Показывает доступные типы блоков с информацией о поддержке шаблонов
 * 
 * @package html_blocks\actions
 * @extends HtmlBlockAction
 */
class AdminSelectType extends HtmlBlockAction {
    
    /**
     * Метод выполнения выбора типа блока
     * Отображает интерфейс выбора типа блока с информацией о поддержке шаблонов
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
        
        // Получение доступных типов блоков из менеджера
        $blockTypes = $this->blockTypeManager->getBlockTypes();
        
        // Получение текущего активного шаблона системы
        $currentTemplate = get_current_template();
        
        // Настройка дефолтного блока как базового типа
        $defaultBlock = [
            'DefaultBlock' => [
                'name' => 'Дефолтный блок',
                'system_name' => 'DefaultBlock',
                'description' => 'Произвольный HTML-код с поддержкой шорткодов',
                'icon' => 'bi bi-code-slash',
                'author' => 'BloggyCMS',
                'version' => '1.0.0',
                'author_website' => '',
                'short_description' => 'Создавайте произвольные HTML-блоки с поддержкой всех системных шорткодов',
                'template' => 'all'
            ]
        ];
        
        // Объединение дефолтного блока с остальными типами блоков
        $allBlocks = $defaultBlock + $blockTypes;
        
        // Получение информации о доступных шаблонах для каждого типа блока
        $availableTemplates = $this->getAvailableTemplates($blockTypes);
        
        /**
         * Рендеринг страницы выбора типа HTML-блока
         * 
         * @param string $template Путь к шаблону (admin/html_blocks/select_type)
         * @param array $data Данные для шаблона:
         * - blockTypes: массив всех доступных типов блоков (включая DefaultBlock)
         * - availableTemplates: информация о поддержке шаблонов для типов блоков
         * - currentTemplate: текущий активный шаблон системы
         * - pageTitle: заголовок страницы
         */
        $this->render('admin/html_blocks/select_type', [
            'blockTypes' => $allBlocks,
            'availableTemplates' => $availableTemplates,
            'currentTemplate' => $currentTemplate,
            'pageTitle' => 'Выбор типа HTML-блока'
        ]);
    }
}