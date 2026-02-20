<?php

namespace menu\actions;

/**
 * Действие предпросмотра меню в админ-панели
 * Отображает визуальное представление меню с его текущей структурой и шаблоном
 * Позволяет администратору увидеть, как меню будет выглядеть на сайте
 * 
 * @package menu\actions
 * @extends MenuAction
 */
class AdminPreview extends MenuAction {
    
    /**
     * Метод выполнения предпросмотра меню
     * Получает данные меню по ID и отображает его в соответствии с выбранным шаблоном
     * 
     * @return void
     */
    public function execute() {
        // Получение ID меню из параметров запроса
        $id = $this->params['id'] ?? null;
        
        // Проверка наличия ID меню
        if (!$id) {
            \Notification::error('ID меню не указан');
            $this->redirect(ADMIN_URL . '/menu');
            return;
        }
        
        // Получение данных меню из базы данных
        $menu = $this->menuModel->getById($id);
        
        // Проверка существования меню
        if (!$menu) {
            \Notification::error('Меню не найдено');
            $this->redirect(ADMIN_URL . '/menu');
            return;
        }
        
        // Получение текущей темы оформления
        $currentTheme = $this->menuModel->getCurrentTheme();
        
        // Декодирование структуры меню из JSON
        $structure = json_decode($menu['structure'], true) ?: [];
        
        // Формирование пути к файлу шаблона меню
        $templateFile = TEMPLATES_PATH . '/' . $currentTheme . '/front/assets/menu/' . $menu['template'] . '.php';
        
        // Отображение страницы предпросмотра меню
        $this->render('admin/menu/preview', [
            'menu' => $menu,              // Данные меню
            'structure' => $structure,    // Структура пунктов меню
            'templateFile' => $templateFile, // Путь к файлу шаблона
            'currentTheme' => $currentTheme, // Текущая тема
            'pageTitle' => 'Предпросмотр меню: ' . $menu['name'] // Заголовок страницы
        ]);
    }
}