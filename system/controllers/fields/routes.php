<?php
return [
    'admin/fields' => ['controller' => 'AdminFields', 'action' => 'adminIndex', 'admin' => true],
    'admin/fields/entity/{entityType}' => ['controller' => 'AdminFields', 'action' => 'entity', 'admin' => true],
    'admin/fields/create/{entityType}' => ['controller' => 'AdminFields', 'action' => 'create', 'admin' => true],
    'admin/fields/edit/{id}' => ['controller' => 'AdminFields', 'action' => 'edit', 'admin' => true],
    'admin/fields/delete/{id}' => ['controller' => 'AdminFields', 'action' => 'delete', 'admin' => true],
    'admin/fields/toggle/{id}' => ['controller' => 'AdminFields', 'action' => 'toggle', 'admin' => true],
    'admin/fields/get-settings/{type}' => ['controller' => 'AdminFields', 'action' => 'getSettings', 'admin' => true],
];