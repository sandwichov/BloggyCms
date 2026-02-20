<?php
return [
    'profile' => ['controller' => 'Profile', 'action' => 'index'],
    'profile/{username}' => ['controller' => 'Profile', 'action' => 'show'],
    'profile/edit' => ['controller' => 'Profile', 'action' => 'edit'],
    'profile/update' => ['controller' => 'Profile', 'action' => 'update'],
];