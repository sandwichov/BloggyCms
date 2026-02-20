<?php
return [
    'admin/plugins' => ['controller' => 'AdminPlugins', 'action' => 'adminIndex', 'admin' => true],
    'admin/plugins/activate/{pluginName}' => ['controller' => 'AdminPlugins', 'action' => 'activate', 'admin' => true],
    'admin/plugins/deactivate/{pluginName}' => ['controller' => 'AdminPlugins', 'action' => 'deactivate', 'admin' => true],
    'admin/plugins/settings/{pluginName}' => ['controller' => 'AdminPlugins', 'action' => 'settings', 'admin' => true],
    'admin/plugins/handle/{pluginName}/{action}' => ['controller' => 'AdminPlugins', 'action' => 'handle', 'admin' => true],
];