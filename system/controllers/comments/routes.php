<?php
return [
    'admin/comments' => ['controller' => 'Comment', 'action' => 'adminIndex', 'admin' => true],
    'admin/comments/edit/{id}' => ['controller' => 'Comment', 'action' => 'adminEdit', 'admin' => true],
    'admin/comments/delete/{id}' => ['controller' => 'Comment', 'action' => 'adminDelete', 'admin' => true],
    'admin/comments/approve/{id}' => ['controller' => 'Comment', 'action' => 'adminApprove', 'admin' => true],
    'comment/add' => ['controller' => 'Comment', 'action' => 'add'],
    'comment/delete/{id}' => ['controller' => 'Comment', 'action' => 'delete'],
    'comment/edit/{id}' => ['controller' => 'Comment', 'action' => 'edit'],
    'comment/get/{id}' => ['controller' => 'Comment', 'action' => 'getComment']
];