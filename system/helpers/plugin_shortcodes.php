<?php

/**
 * Обрабатывает шорткоды плагинов в контенте
 * Шорткоды имеют формат: {plugin param1="value1" param2="value2"}
 * 
 * @param string $content Исходный контент с шорткодами
 * @return string Контент с обработанными шорткодами
 */
function process_plugin_shortcodes(string $content): string {
    return preg_replace_callback(
        '/\{([a-zA-Z0-9-]+)(?:\s+([^}]+))?\}/',
        function($matches) {
            $pluginName = $matches[1];
            $paramsString = $matches[2] ?? '';
            
            $params = [];
            if (!empty($paramsString)) {
                preg_match_all('/(\w+)=["\'](.*?)["\']/', $paramsString, $paramMatches, PREG_SET_ORDER);
                foreach ($paramMatches as $paramMatch) {
                    $params[$paramMatch[1]] = $paramMatch[2];
                }
            }
            
            $pluginManager = new PluginManager(new Database());
            $plugin = $pluginManager->getPlugin($pluginName);
            
            if ($plugin) {
                return $plugin->processShortcode($params);
            }
            
            return '';
        },
        $content
    );
}