<?php

namespace auth\actions;

/**
 * Действие для безопасного выхода пользователя из системы
 *
 */
class Logout extends AuthAction {
    /**
     * Основной метод выполнения процесса выхода из системы
     *
     * @return void
     * @throws \Exception При критических ошибках управления сессией
     * 
     */
    public function execute() {

        session_destroy();
        session_start();
        
        \Notification::success('Вы успешно вышли из системы');
        
        $this->redirect(BASE_URL);
    }
}