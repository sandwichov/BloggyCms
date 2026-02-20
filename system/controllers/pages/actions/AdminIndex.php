<?php

namespace pages\actions;

/**
 * Действие отображения списка всех страниц в административной панели
 * Главная страница управления страницами, показывает все созданные страницы
 * с возможностью их редактирования, удаления и создания новых
 * 
 * @package pages\actions
 * @extends PageAction
 */
class AdminIndex extends PageAction {
    
    /**
     * Метод выполнения отображения списка страниц
     * Проверяет права доступа, получает все страницы из базы данных
     * и передает их в шаблон для отображения
     * 
     * @return void
     */
    public function execute() {
        // Проверка прав доступа администратора
        if (!$this->checkAdminAccess()) {
            $this->handleAccessDenied();
            return;
        }
        
        try {
            // Получение списка всех страниц из базы данных
            $pages = $this->loadPages();
            
            // Отображение страницы со списком страниц
            $this->renderPageList($pages);
            
        } catch (\Exception $e) {
            // Обработка ошибок при загрузке списка
            $this->handleLoadError($e);
        }
    }
    
    /**
     * Загружает список всех страниц из базы данных
     * 
     * @return array Массив всех страниц
     */
    private function loadPages() {
        return $this->pageModel->getAll();
    }
    
    /**
     * Отображает страницу со списком страниц
     * 
     * @param array $pages Массив страниц для отображения
     * @return void
     */
    private function renderPageList($pages) {
        $this->render('admin/pages/index', [
            'pages' => $pages,              // Массив страниц для отображения
            'pageTitle' => 'Управление страницами'  // Заголовок страницы
        ]);
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
    
    /**
     * Обрабатывает ошибку при загрузке списка страниц
     * 
     * @param \Exception $e Исключение
     * @return void
     */
    private function handleLoadError($e) {
        // Общее сообщение об ошибке для пользователя
        \Notification::error('Ошибка при загрузке списка страниц');
        
        // В режиме отладки можно добавить детали ошибки
        if (defined('DEBUG_MODE') && DEBUG_MODE) {
            \Notification::error('Детали: ' . $e->getMessage());
        }
        
        // Перенаправление на главную страницу административной панели
        $this->redirect(ADMIN_URL);
    }
}