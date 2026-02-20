<?php
return [
    'admin/html-blocks' => ['controller' => 'AdminHtmlBlock', 'action' => 'adminIndex', 'admin' => true],
    'admin/html-blocks/select-type' => ['controller' => 'AdminHtmlBlock', 'action' => 'selectType', 'admin' => true],
    'admin/html-blocks/create' => ['controller' => 'AdminHtmlBlock', 'action' => 'create', 'admin' => true],
    'admin/html-blocks/edit/{id}' => ['controller' => 'AdminHtmlBlock', 'action' => 'edit', 'admin' => true],
    'admin/html-blocks/delete/{id}' => ['controller' => 'AdminHtmlBlock', 'action' => 'delete', 'admin' => true],
    'admin/html-blocks/types' => ['controller' => 'AdminHtmlBlockType', 'action' => 'adminIndex', 'admin' => true],
    'admin/html-blocks/types/delete/{systemName}' => ['controller' => 'AdminHtmlBlockType', 'action' => 'delete', 'admin' => true],
    'admin/html-blocks/types/toggle/{systemName}' => ['controller' => 'AdminHtmlBlockType', 'action' => 'toggle', 'admin' => true],
    'admin/html-blocks/get-block-settings' => ['controller' => 'AdminHtmlBlock', 'action' => 'getBlockSettings', 'admin' => true],
    'admin/html-blocks/get-block-templates' => ['controller' => 'AdminHtmlBlock', 'action' => 'getBlockTemplates', 'admin' => true],
    'block/{slug}' => ['controller' => 'HtmlBlock', 'action' => 'show'],
];