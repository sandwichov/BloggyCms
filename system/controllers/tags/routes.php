<?php

return [
    'tags' => ['controller' => 'Tag', 'action' => 'index'],
    'tag/{slug}' => ['controller' => 'Tag', 'action' => 'show'],
    'admin/tags' => ['controller' => 'Tag', 'action' => 'adminIndex', 'admin' => true],
    'admin/tags/create' => ['controller' => 'Tag', 'action' => 'create', 'admin' => true],
    'admin/tags/edit/{id}' => ['controller' => 'Tag', 'action' => 'edit', 'admin' => true],
    'admin/tags/delete/{id}' => ['controller' => 'Tag', 'action' => 'delete', 'admin' => true],
    'admin/tags/search' => ['controller' => 'Tag', 'action' => 'search', 'admin' => true],
    'admin/tags/create-ajax' => ['controller' => 'Tag', 'action' => 'createAjax', 'admin' => true]
];