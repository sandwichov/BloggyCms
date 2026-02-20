<?php
return [
    'admin/menu' => ['controller' => 'AdminMenu', 'action' => 'adminIndex', 'admin' => true],
    'admin/menu/create' => ['controller' => 'AdminMenu', 'action' => 'create', 'admin' => true],
    'admin/menu/edit/{id}' => ['controller' => 'AdminMenu', 'action' => 'edit', 'admin' => true],
    'admin/menu/delete/{id}' => ['controller' => 'AdminMenu', 'action' => 'delete', 'admin' => true],
    'admin/menu/get-structure/{id}' => ['controller' => 'AdminMenu', 'action' => 'getStructure', 'admin' => true],
    'admin/menu/preview/{id}' => ['controller' => 'AdminMenu', 'action' => 'preview', 'admin' => true],
];