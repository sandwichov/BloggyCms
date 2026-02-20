<?php

/**
 * Контроллер управления профилями пользователей
 * Обрабатывает запросы, связанные с отображением и редактированием профилей
 * Делегирует выполнение конкретных операций специализированным классам действий
 * 
 * @package Controllers
 * @extends Controller
 */
class ProfileController extends Controller {
    
    /**
     * Отображает профиль текущего авторизованного пользователя
     * Главная страница профиля пользователя
     * 
     * @return void
     */
    public function indexAction() {
        $action = new \profile\actions\Index($this->db);
        $action->setController($this);
        return $action->execute();
    }
    
    /**
     * Отображает публичный профиль пользователя по его имени пользователя
     * 
     * @param string|null $username Имя пользователя
     * @return void
     */
    public function showAction($username = null) {
        $action = new \profile\actions\Show($this->db);
        $action->setController($this);
        $action->setUsername($username);
        return $action->execute();
    }
    
    /**
     * Отображает форму редактирования профиля текущего пользователя
     * 
     * @return void
     */
    public function editAction() {
        $action = new \profile\actions\Edit($this->db);
        $action->setController($this);
        return $action->execute();
    }
    
    /**
     * Обрабатывает обновление данных профиля текущего пользователя
     * Принимает POST-запрос с данными формы и сохраняет изменения
     * 
     * @return void
     */
    public function updateAction() {
        $action = new \profile\actions\Update($this->db);
        $action->setController($this);
        return $action->execute();
    }
}