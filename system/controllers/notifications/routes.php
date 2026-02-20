<?php
return [
    'admin/notifications/get-unread-count' => ['controller' => 'AdminNotifications', 'action' => 'getUnreadCount', 'admin' => true],
    'admin/notifications/get-list' => ['controller' => 'AdminNotifications', 'action' => 'getList', 'admin' => true],
    'admin/notifications' => ['controller' => 'AdminNotifications', 'action' => 'adminIndex', 'admin' => true],
    'admin/notifications/mark-as-read/{id}' => ['controller' => 'AdminNotifications', 'action' => 'adminMarkAsRead', 'admin' => true],
    'admin/notifications/mark-all-read' => ['controller' => 'AdminNotifications', 'action' => 'adminMarkAllAsRead', 'admin' => true],
    'admin/notifications/delete/{id}' => ['controller' => 'AdminNotifications', 'action' => 'adminDelete', 'admin' => true],
    'admin/notifications/clear' => ['controller' => 'AdminNotifications', 'action' => 'adminClear', 'admin' => true],
];