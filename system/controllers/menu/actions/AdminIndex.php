<?php

namespace menu\actions;

/**
 * Действие отображения списка всех меню в админ-панели
 * Главная страница управления меню, показывает все созданные меню
 * 
 * @package menu\actions
 * @extends MenuAction
 */
class AdminIndex extends MenuAction {
    
    /**
     * Метод выполнения отображения списка меню
     * Получает все меню из базы данных и передает их в шаблон для отображения
     * 
     * @return void
     */
    public function execute() {
        // Получение списка всех меню из базы данных
        $menus = $this->menuModel->getAll();
        
        // Отображение страницы со списком меню
        $this->render('admin/menu/index', [
            'menus' => $menus,           // Массив объектов меню для отображения
            'pageTitle' => 'Управление меню'  // Заголовок страницы
        ]);
    }
}