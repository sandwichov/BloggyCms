<?php
return [
    'admin/pages' => ['controller' => 'AdminPage', 'action' => 'adminIndex', 'admin' => true],
    'admin/pages/create' => ['controller' => 'AdminPage', 'action' => 'create', 'admin' => true],
    'admin/pages/edit/{id}' => ['controller' => 'AdminPage', 'action' => 'edit', 'admin' => true],
    'admin/pages/delete/{id}' => ['controller' => 'AdminPage', 'action' => 'delete', 'admin' => true],
    'admin/pages/upload-image' => ['controller' => 'AdminPage', 'action' => 'uploadImage', 'admin' => true],
    'page/{slug}' => ['controller' => 'Page', 'action' => 'show'],
];