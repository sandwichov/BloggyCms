<?php

namespace html_blocks\actions;

/**
 * Действие переключения статуса типа HTML-блока в админ-панели
 * Включает или отключает тип блока без его удаления из системы
 * 
 * @package html_blocks\actions
 * @extends HtmlBlockAction
 */
class AdminTypeToggle extends HtmlBlockAction {
    
    /**
     * @var string Системное имя типа блока для переключения
     */
    private $systemName;
    
    /**
     * Установка системного имени типа блока
     *
     * @param string $systemName Системное имя типа блока
     * @return void
     */
    public function setSystemName($systemName) {
        $this->systemName = $systemName;
    }
    
    /**
     * Метод выполнения переключения статуса типа блока
     * Изменяет статус активности типа блока в базе данных
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
        
        // Проверка наличия системного имени типа блока
        if (!$this->systemName) {
            \Notification::error('Системное имя типа блока не указано');
            $this->redirect(ADMIN_URL . '/html-blocks/types');
            return;
        }

        // Защита от отключения дефолтного типа блока
        if ($this->systemName === 'DefaultBlock') {
            \Notification::error('Нельзя отключить дефолтный тип блока');
            $this->redirect(ADMIN_URL . '/html-blocks/types');
            return;
        }
        
        try {
            // Получение текущих данных типа блока из базы данных
            $blockType = $this->db->fetch(
                "SELECT * FROM html_block_types WHERE system_name = ?",
                [$this->systemName]
            );
            
            // Проверка существования типа блока
            if (!$blockType) {
                \Notification::error('Тип блока не найден');
                $this->redirect(ADMIN_URL . '/html-blocks/types');
                return;
            }
            
            // Определение нового статуса (инвертирование текущего)
            $newStatus = $blockType['is_active'] ? 0 : 1;
            
            // Обновление статуса активности в базе данных
            $this->db->query(
                "UPDATE html_block_types SET is_active = ? WHERE system_name = ?",
                [$newStatus, $this->systemName]
            );
            
            // Формирование текста уведомления в зависимости от нового статуса
            $statusText = $newStatus ? 'включен' : 'отключен';
            \Notification::success("Тип блока успешно $statusText");
            
        } catch (\Exception $e) {
            // Обработка исключений при изменении статуса
            \Notification::error('Ошибка при изменении статуса типа блока: ' . $e->getMessage());
        }
        
        // Перенаправление на страницу управления типами блоков
        $this->redirect(ADMIN_URL . '/html-blocks/types');
    }
}