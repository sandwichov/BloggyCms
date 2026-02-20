<?php

namespace profile\actions;

/**
 * Действие отображения профиля текущего авторизованного пользователя
 * Показывает личный профиль пользователя с его данными и постами
 * 
 * @package profile\actions
 * @extends ProfileAction
 */
class Index extends ProfileAction {
    
    /**
     * Метод выполнения отображения личного профиля
     * Проверяет аутентификацию пользователя, загружает его данные
     * и список его постов, отображает страницу профиля
     * 
     * @return void
     */
    public function execute() {
        // Проверка, что пользователь авторизован
        $this->checkAuthentication();
        
        // Получение данных текущего пользователя из сессии
        $user = $this->userModel->getById($_SESSION['user_id']);
        
        // Получение списка постов пользователя
        $userPosts = $this->postModel->getByUserId($user['id']);
        
        // Отображение страницы профиля
        $this->render('front/profile/index', [
            'user' => $user,               // Данные пользователя
            'posts' => $userPosts,          // Посты пользователя
            'is_own_profile' => true        // Флаг, указывающий что это свой профиль
        ]);
    }
}