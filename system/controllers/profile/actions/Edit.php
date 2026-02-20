<?php

namespace profile\actions;

/**
 * Действие отображения формы редактирования профиля пользователя
 * Показывает форму с текущими данными пользователя для редактирования
 * 
 * @package profile\actions
 * @extends ProfileAction
 */
class Edit extends ProfileAction {
    
    /**
     * Метод выполнения отображения формы редактирования профиля
     * Проверяет аутентификацию пользователя, загружает его данные
     * и отображает форму редактирования с CSRF-токеном
     * 
     * @return void
     */
    public function execute() {
        // Проверка, что пользователь авторизован
        $this->checkAuthentication();
        
        // Получение данных текущего пользователя из сессии
        $user = $this->userModel->getById($_SESSION['user_id']);
        
        // Отображение формы редактирования
        $this->render('front/profile/edit', [
            'user' => $user,                    // Данные пользователя для заполнения формы
            'csrf_token' => $this->generateCsrfToken() // Токен для защиты от CSRF-атак
        ]);
    }
}