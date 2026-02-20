<?php

namespace html_blocks\actions;

/**
 * Действие отображения списка типов HTML-блоков в админ-панели
 * Показывает все типы блоков с их статусом активности и доступностью в создании
 * 
 * @package html_blocks\actions
 * @extends HtmlBlockAction
 */
class AdminTypeIndex extends HtmlBlockAction {
    
    /**
     * Метод выполнения отображения списка типов блоков
     * Загружает все типы блоков с информацией о статусе активности
     * 
     * @return void
     */
    public function execute() {
        // Проверка административных прав доступа
        if (!$this->checkAdminAccess()) {
            \Notification::error('У вас нет прав доступа к этом разделу');
            $this->redirect(ADMIN_URL . '/login');
            return;
        }
        
        try {
            // Получение ВСЕХ типов блоков (включая неактивные)
            $allBlockTypes = $this->blockTypeManager->getAllBlockTypes();
            
            // Получение только активных типов блоков (для создания)
            $activeBlockTypes = $this->blockTypeManager->getBlockTypes();
            
            // Добавление информации о статусе активности для каждого типа блока
            foreach ($allBlockTypes as $systemName => &$type) {
                // Для всех типов кроме DefaultBlock проверяем статус в базе данных
                if ($systemName !== 'DefaultBlock') {
                    $dbBlock = $this->db->fetch(
                        "SELECT is_active FROM html_block_types WHERE system_name = ?",
                        [$systemName]
                    );
                    // Если запись найдена - используем ее статус, иначе считаем активным
                    $type['is_active'] = $dbBlock ? $dbBlock['is_active'] : true;
                } else {
                    // DefaultBlock всегда активен
                    $type['is_active'] = true;
                }
                
                // Флаг отображения типа в списке создания (только активные типы)
                $type['is_visible_in_creation'] = isset($activeBlockTypes[$systemName]);
            }
            
            /**
             * Рендеринг страницы управления типами HTML-блоков
             * 
             * @param string $template Путь к шаблону (admin/html_blocks/types_index)
             * @param array $data Данные для шаблона:
             * - blockTypes: массив всех типов блоков с информацией о статусе
             * - pageTitle: заголовок страницы
             */
            $this->render('admin/html_blocks/types_index', [
                'blockTypes' => $allBlockTypes,
                'pageTitle' => 'Управление типами HTML-блоков'
            ]);
            
        } catch (\Exception $e) {
            // Обработка ошибок при загрузке типов блоков
            \Notification::error('Ошибка при загрузке типов блоков: ' . $e->getMessage());
            $this->redirect(ADMIN_URL . '/html-blocks');
        }
    }
}