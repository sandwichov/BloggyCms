<?php

namespace html_blocks\actions;

/**
 * Действие удаления типа HTML-блока в админ-панели
 * Удаляет тип блока из системы с проверкой связанных блоков и файлов
 * 
 * @package html_blocks\actions
 * @extends HtmlBlockAction
 */
class AdminTypeDelete extends HtmlBlockAction {
    
    /**
     * @var string Системное имя удаляемого типа блока
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
     * Метод выполнения удаления типа блока
     * Проверяет наличие связанных блоков, удаляет файл блока и запись из базы данных
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
        
        // Защита от удаления дефолтного типа блока
        if ($this->systemName === 'DefaultBlock') {
            \Notification::error('Нельзя удалить дефолтный тип блока');
            $this->redirect(ADMIN_URL . '/html-blocks/types');
            return;
        }
        
        try {
            // Проверка существования блоков этого типа
            $existingBlocks = $this->db->fetchAll(
                "SELECT COUNT(*) as count FROM html_blocks hb 
                 JOIN html_block_types hbt ON hb.type_id = hbt.id 
                 WHERE hbt.system_name = ?",
                [$this->systemName]
            );
            
            // Если есть созданные блоки этого типа - удаление запрещено
            if ($existingBlocks[0]['count'] > 0) {
                \Notification::error('Нельзя удалить тип блока, так как существуют созданные блоки этого типа');
                $this->redirect(ADMIN_URL . '/html-blocks/types');
                return;
            }
            
            // Определение пути к файлу класса блока
            $blockFile = __DIR__ . '/../../../html_blocks/' . $this->systemName . '.php';
            
            // Удаление файла блока если он существует
            if (file_exists($blockFile)) {
                if (unlink($blockFile)) {
                    \Notification::success('Файл блока успешно удален');
                } else {
                    \Notification::error('Не удалось удалить файл блока');
                    $this->redirect(ADMIN_URL . '/html-blocks/types');
                    return;
                }
            } else {
                // Предупреждение если файл не найден (но запись все равно удаляется)
                \Notification::warning('Файл блока не найден, но запись будет удалена из базы');
            }
            
            // Удаление записи типа блока из базы данных
            $this->db->query(
                "DELETE FROM html_block_types WHERE system_name = ?",
                [$this->systemName]
            );
            
            // Уведомление об успешном удалении
            \Notification::success('Тип блока успешно удален из системы');
            
        } catch (\Exception $e) {
            // Обработка исключений при удалении
            \Notification::error('Ошибка при удалении типа блока: ' . $e->getMessage());
        }
        
        // Перенаправление на страницу управления типами блоков
        $this->redirect(ADMIN_URL . '/html-blocks/types');
    }
}