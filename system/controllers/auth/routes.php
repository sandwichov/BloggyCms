<?php
return [
    'login' => ['controller' => 'Auth', 'action' => 'login'],
    'register' => ['controller' => 'Auth', 'action' => 'register'], 
    'logout' => ['controller' => 'Auth', 'action' => 'logout'],
    'admin/login' => ['controller' => 'Auth', 'action' => 'adminLogin'],
    'forgot-password' => ['controller' => 'Auth', 'action' => 'forgotPassword'],
    'reset-password' => ['controller' => 'Auth', 'action' => 'resetPassword'],
];