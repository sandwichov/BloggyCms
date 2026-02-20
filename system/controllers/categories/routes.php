<?php
return [
    'category/{slug}' => ['controller' => 'Category', 'action' => 'show'],
    'category/check-password/{id}' => ['controller' => 'Category', 'action' => 'checkPassword'],
    'admin/categories' => ['controller' => 'Category', 'action' => 'adminIndex', 'admin' => true],
    'admin/categories/create' => ['controller' => 'Category', 'action' => 'create', 'admin' => true],
    'admin/categories/edit/{id}' => ['controller' => 'Category', 'action' => 'edit', 'admin' => true],
    'admin/categories/delete/{id}' => ['controller' => 'Category', 'action' => 'delete', 'admin' => true],
    'admin/categories/reorder' => ['controller' => 'Category', 'action' => 'reorder', 'admin' => true],
    'admin/categories/upload-image' => ['controller' => 'Category', 'action' => 'uploadImage', 'admin' => true],
];