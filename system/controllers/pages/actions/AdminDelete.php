<?php

namespace pages\actions;

/**
 * Действие удаления страницы в административной панели
 * Удаляет указанную страницу из базы данных вместе со всеми связанными блоками
 * 
 * @package pages\actions
 * @extends PageAction
 */
class AdminDelete extends PageAction {
    
    /** @var int|null ID страницы для удаления */
    protected $id;
    
    /**
     * Устанавливает ID страницы для удаления
     * 
     * @param int|null $id ID страницы
     * @return void
     */
    public function setId($id) {
        $this->id = $id;
    }
    
    /**
     * Метод выполнения удаления страницы
     * Проверяет права доступа, наличие ID и выполняет удаление страницы
     * При успехе или ошибке показывает соответствующее уведомление
     * 
     * @return void
     */
    public function execute() {
        // Проверка прав доступа администратора
        if (!$this->checkAdminAccess()) {
            $this->handleAccessDenied();
            return;
        }
        
        // Проверка наличия ID страницы
        if (!$this->validatePageId()) {
            return;
        }
        
        // Выполнение удаления страницы
        $this->deletePage();
        
        // Перенаправление на страницу со списком страниц
        $this->redirect(ADMIN_URL . '/pages');
    }
    
    /**
     * Проверяет наличие ID страницы для удаления
     * 
     * @return bool true если ID указан, false в противном случае
     */
    private function validatePageId() {
        if (!$this->id) {
            \Notification::error('ID страницы не указан');
            $this->redirect(ADMIN_URL . '/pages');
            return false;
        }
        return true;
    }
    
    /**
     * Выполняет удаление страницы из базы данных
     * Модель PageModel автоматически удаляет связанные блоки (каскадное удаление)
     * 
     * @return void
     */
    private function deletePage() {
        try {
            // Удаление страницы (блоки удаляются каскадно через модель)
            $this->pageModel->delete($this->id);
            
            // Уведомление об успешном удалении
            \Notification::success('Страница успешно удалена');
            
        } catch (\Exception $e) {
            // Уведомление об ошибке при удалении
            \Notification::error('Ошибка при удалении страницы');
            
            // В режиме отладки можно добавить детали ошибки
            if (defined('DEBUG_MODE') && DEBUG_MODE) {
                \Notification::error('Детали: ' . $e->getMessage());
            }
        }
    }
    
    /**
     * Обрабатывает ситуацию с отсутствием прав доступа
     * 
     * @return void
     */
    private function handleAccessDenied() {
        \Notification::error('У вас нет прав доступа к этому разделу');
        $this->redirect(ADMIN_URL . '/login');
    }
}