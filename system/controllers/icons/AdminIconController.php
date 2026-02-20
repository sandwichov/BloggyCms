<?php

/**
 * Контроллер управления иконками в админ-панели
 * Предоставляет интерфейс для просмотра и управления иконками системы
 * 
 * @package controllers
 * @extends Controller
 */
class AdminIconController extends Controller {
    
    /**
     * Действие: Главная страница управления иконками
     * Отображает интерфейс для просмотра и выбора иконок
     * 
     * @return mixed
     */
    public function adminIndexAction() {
        $action = new \icons\actions\AdminIndex();
        $action->setController($this);
        return $action->execute();
    }
    
    /**
     * Действие: Получение данных об иконках через AJAX
     * Возвращает JSON с информацией об иконках для динамической загрузки
     * 
     * @return mixed JSON-ответ с данными об иконках
     */
    public function adminIconsDataAction() {
        $action = new \icons\actions\AdminIconsData();
        $action->setController($this);
        return $action->execute();
    }
}