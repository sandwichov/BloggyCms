<?php
return [
    'search' => ['controller' => 'Search', 'action' => 'index'],
    'admin/search-history' => ['controller' => 'AdminSearch', 'action' => 'adminIndex', 'admin' => true],
    'admin/search-history/delete/{id}' => ['controller' => 'AdminSearch', 'action' => 'delete', 'admin' => true],
    'admin/search-history/clear' => ['controller' => 'AdminSearch', 'action' => 'clear', 'admin' => true]
];