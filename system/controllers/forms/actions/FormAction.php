<?php

namespace forms\actions;

abstract class FormAction {
    protected $db;
    protected $params;
    protected $controller;
    protected $formModel;
    
    public function __construct($db, $params = []) {
        $this->db = $db;
        $this->params = $params;
        $this->formModel = new \FormModel($db);
    }
    
    public function setController($controller) {
        $this->controller = $controller;
    }
    
    abstract public function execute();
    
    protected function render($template, $data = []) {
        if ($this->controller) {
            $this->controller->render($template, $data);
        } else {
            throw new \Exception('Controller not set for Action');
        }
    }
    
    protected function redirect($url) {
        if ($this->controller) {
            $this->controller->redirect($url);
        } else {
            header('Location: ' . $url);
            exit;
        }
    }
    
    protected function isAjaxRequest() {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
    
    protected function jsonResponse($data) {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }
    
    protected function getFormSettings() {
        return [
            'ajax_enabled' => true,
            'show_labels' => true,
            'show_descriptions' => true,
            'recaptcha_site_key' => '',
            'recaptcha_secret_key' => '',
            'store_submissions' => true,
            'redirect_after_submit' => false,
            'redirect_url' => '',
            'success_message' => 'Форма успешно отправлена!',
            'error_message' => 'Произошла ошибка при отправке формы.'
        ];
    }
    
    protected function getDefaultNotifications() {
        return [
            [
                'enabled' => true,
                'type' => 'admin',
                'to' => 'admin@example.com',
                'subject' => 'Новая отправка формы',
                'message' => 'Поступила новая отправка формы. Данные: {form_data}'
            ],
            [
                'enabled' => false,
                'type' => 'user',
                'to' => '{email}',
                'subject' => 'Ваша форма отправлена',
                'message' => 'Спасибо за вашу заявку! Мы свяжемся с вами в ближайшее время.'
            ]
        ];
    }
    
    protected function getDefaultActions() {
        return [
            [
                'enabled' => true,
                'type' => 'save_to_db',
                'name' => 'Сохранить в базу данных'
            ],
            [
                'enabled' => false,
                'type' => 'redirect',
                'name' => 'Редирект после отправки',
                'url' => ''
            ],
            [
                'enabled' => false,
                'type' => 'webhook',
                'name' => 'Отправить на вебхук',
                'url' => '',
                'method' => 'POST',
                'headers' => []
            ]
        ];
    }
}