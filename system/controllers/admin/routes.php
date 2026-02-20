<?php
return [
    'admin' => ['controller' => 'Admin', 'action' => 'index', 'admin' => true],
    'admin/login' => ['controller' => 'Admin', 'action' => 'login'],
    'admin/logout' => ['controller' => 'Admin', 'action' => 'logout', 'admin' => true],
    'admin/templates' => ['controller' => 'Admin', 'action' => 'templates', 'admin' => true],
    'admin/templates/get-files' => ['controller' => 'Admin', 'action' => 'getTemplateFiles', 'admin' => true],
    'admin/templates/get-file' => ['controller' => 'Admin', 'action' => 'getTemplateFile', 'admin' => true],
    'admin/templates/save' => ['controller' => 'Admin', 'action' => 'saveTemplateFile', 'admin' => true],
    'admin/controllers' => ['controller' => 'Controllers', 'action' => 'adminIndex', 'admin' => true],
];