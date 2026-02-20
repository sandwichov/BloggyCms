<?php
return [
    // Административные маршруты
    'admin/forms' => ['controller' => 'AdminForm', 'action' => 'adminIndex', 'admin' => true],
    'admin/forms/create' => ['controller' => 'AdminForm', 'action' => 'create', 'admin' => true],
    'admin/forms/edit/{id}' => ['controller' => 'AdminForm', 'action' => 'edit', 'admin' => true],
    'admin/forms/delete/{id}' => ['controller' => 'AdminForm', 'action' => 'delete', 'admin' => true],
    'admin/forms/preview/{id}' => ['controller' => 'AdminForm', 'action' => 'preview', 'admin' => true],
    'admin/forms/settings/{id}' => ['controller' => 'AdminForm', 'action' => 'settings', 'admin' => true],
    'admin/forms/toggle-status/{id}' => ['controller' => 'AdminForm', 'action' => 'toggleStatus', 'admin' => true],
    'admin/forms/show/{id}' => ['controller' => 'AdminForm', 'action' => 'show', 'admin' => true],
    'admin/forms/get-structure/{id}' => ['controller' => 'AdminForm', 'action' => 'getStructure', 'admin' => true],
    'admin/forms/export/{id}' => ['controller' => 'AdminForm', 'action' => 'export', 'admin' => true],
    'admin/forms/generate-captcha-example' => ['controller' => 'AdminForm', 'action' => 'generateCaptchaExample', 'admin' => true],
    
    // Дополнительные маршруты для работы с отправками
    'admin/forms/delete-submission/{id}' => ['controller' => 'AdminForm', 'action' => 'deleteSubmission', 'admin' => true],
    'admin/forms/update-submission-status/{id}' => ['controller' => 'AdminForm', 'action' => 'updateSubmissionStatus', 'admin' => true,'method' => 'GET'],
    'admin/forms/get-submission/{id}' => ['controller' => 'AdminForm', 'action' => 'getSubmission', 'admin' => true],
    'admin/forms/delete-all-submissions/{id}' => ['controller' => 'AdminForm', 'action' => 'deleteAllSubmissions', 'admin' => true],
    
    // Публичные маршруты - ИСПРАВЛЕНО: правильное название контроллера
    'form/{slug}' => ['controller' => 'Form', 'action' => 'show'],
    'form/{slug}/submit' => ['controller' => 'Form', 'action' => 'process'],
    
];