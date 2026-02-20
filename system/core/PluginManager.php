<?php

/**
 * Менеджер для управления плагинами системы
 */
class PluginManager {
    /**
     * @var Database Подключение к базе данных
     */
    private $db;
    
    /**
     * @var array Загруженные плагины
     */
    private $plugins = [];
    
    /**
     * @var array Активные плагины
     */
    private $activePlugins = [];
    
    /**
     * @var array Маршруты плагинов
     */
    private $pluginRoutes = [];

    /**
     * Конструктор PluginManager
     *
     * @param Database $db Подключение к базе данных
     */
    public function __construct(Database $db) {
        $this->db = $db;
        $this->loadActivePlugins();
    }
    
    /**
     * Загрузка списка активных плагинов из БД
     */
    private function loadActivePlugins(): void {
        $result = $this->db->fetchAll("SELECT system_name FROM plugins WHERE is_active = 1");
        $this->activePlugins = array_column($result, 'system_name');
    }

    /**
     * Получение списка активных плагинов
     *
     * @return array Массив активных плагинов
     */
    public function getActivePlugins(): array {
        return $this->activePlugins;
    }
    
    /**
     * Поиск всех установленных плагинов
     *
     * @return array Массив плагинов
     */
    public function discoverPlugins(): array {
        $plugins = [];
        
        if (!is_dir(PLUGINS_PATH)) {
            return $plugins;
        }
        
        $dirs = array_filter(glob(PLUGINS_PATH . '/*'), 'is_dir');
        
        foreach ($dirs as $dir) {
            $pluginFile = $dir . '/plugin.json';
            
            if (file_exists($pluginFile)) {
                $pluginData = json_decode(file_get_contents($pluginFile), true);
                
                if ($pluginData) {
                    $pluginData['system_name'] = basename($dir);
                    $plugins[$pluginData['system_name']] = $pluginData;
                }
            }
        }
        
        return $plugins;
    }
    
    /**
     * Загрузка всех активных плагинов
     */
    public function loadPlugins(): void {
        foreach ($this->activePlugins as $pluginName) {
            $this->loadPlugin($pluginName);
        }
    }
    
    /**
     * Загрузка конкретного плагина
     *
     * @param string $pluginName Имя плагина
     * @return Plugin|null Экземпляр плагина
     */
    private function loadPlugin(string $pluginName): ?Plugin {
        $pluginFile = PLUGINS_PATH . '/' . $pluginName . '/Plugin.php';
        
        if (!file_exists($pluginFile)) {
            return null;
        }
        
        require_once $pluginFile;
        $className = $pluginName . 'Plugin';
        
        if (!class_exists($className)) {
            return null;
        }
        
        try {
            $plugin = new $className($this->db);
            $this->plugins[$pluginName] = $plugin;
            return $plugin;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Загрузка информации о плагине из JSON файла
     *
     * @param string $pluginName Имя плагина
     * @return array|null Информация о плагине
     */
    private function loadPluginInfo(string $pluginName): ?array {
        $jsonFile = PLUGINS_PATH . '/' . $pluginName . '/plugin.json';
        
        if (file_exists($jsonFile)) {
            $data = json_decode(file_get_contents($jsonFile), true);
            if ($data) {
                return $data;
            }
        }
        
        return null;
    }
    
    /**
     * Подготовка маршрутов плагина
     *
     * @param Plugin $plugin Экземпляр плагина
     * @return array Подготовленные маршруты
     */
    private function preparePluginRoutes(Plugin $plugin): array {
        $routes = [];
        $pluginRoutes = $plugin->getRoutes();
        $pluginName = $plugin->getSystemName();
        
        foreach ($pluginRoutes as $pattern => $route) {
            if (!empty($route['admin'])) {
                $pattern = 'admin/plugins/' . $pluginName . '/' . ltrim($pattern, '/');
            }
            
            $route['plugin'] = $pluginName;
            $routes[$pattern] = $route;
        }
        
        return $routes;
    }
    
    /**
     * Получение всех маршрутов плагинов
     *
     * @return array Маршруты плагинов
     */
    public function getPluginRoutes(): array {
        return $this->pluginRoutes;
    }
    
    /**
     * Получение экземпляра плагина
     *
     * @param string $pluginName Имя плагина
     * @return Plugin|null Экземпляр плагина
     */
    public function getPlugin(string $pluginName): ?Plugin {
        if (isset($this->plugins[$pluginName])) {
            return $this->plugins[$pluginName];
        }
        
        if (in_array($pluginName, $this->activePlugins)) {
            return $this->loadPlugin($pluginName);
        }
        
        return null;
    }
    
    /**
     * Активация плагина
     *
     * @param string $pluginName Имя плагина
     * @return bool Результат активации
     */
    public function activatePlugin(string $pluginName): bool {
        $pluginFile = PLUGINS_PATH . '/' . $pluginName . '/Plugin.php';
        
        if (!file_exists($pluginFile)) {
            return false;
        }
        
        $pluginInfo = $this->loadPluginInfo($pluginName);
        
        if (!$pluginInfo) {
            return false;
        }
        
        try {
            $this->db->query(
                "INSERT INTO plugins (name, system_name, version, is_active) 
                 VALUES (?, ?, ?, 1) 
                 ON DUPLICATE KEY UPDATE 
                    name = VALUES(name),
                    version = VALUES(version),
                    is_active = 1",
                [
                    $pluginInfo['name'],
                    $pluginName,
                    $pluginInfo['version']
                ]
            );
            
            if (!in_array($pluginName, $this->activePlugins)) {
                $this->activePlugins[] = $pluginName;
            }
            
            require_once $pluginFile;
            $className = $pluginName . 'Plugin';
            
            if (class_exists($className)) {
                $plugin = new $className($this->db);
                $this->plugins[$pluginName] = $plugin;
                
                $plugin->activate();
                
                return true;
            }
            
        } catch (Exception $e) {
            return false;
        }
        
        return false;
    }
    
    /**
     * Деактивация плагина
     *
     * @param string $pluginName Имя плагина
     * @return bool Результат деактивации
     */
    public function deactivatePlugin(string $pluginName): bool {
        if (in_array($pluginName, $this->activePlugins)) {
            $this->db->query(
                "UPDATE plugins SET is_active = 0 WHERE system_name = ?",
                [$pluginName]
            );
            
            if (isset($this->plugins[$pluginName])) {
                $this->plugins[$pluginName]->deactivate();
                unset($this->plugins[$pluginName]);
            }
            
            $this->activePlugins = array_diff($this->activePlugins, [$pluginName]);
            return true;
        }
        return false;
    }

    /**
     * Обработка шорткодов плагинов в контенте
     *
     * @param string $content Исходный контент
     * @return string Обработанный контент
     */
    public function processPluginShortcodes(string $content): string {
        if (preg_match_all('/\{([a-zA-Z0-9-]+)(?:\s+([^}]+))?\}/', $content, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $pluginName = $match[1];
                $params = [];

                if (isset($match[2])) {
                    preg_match_all('/(\w+)=["\'](.*?)["\']/', $match[2], $paramMatches, PREG_SET_ORDER);
                    foreach ($paramMatches as $paramMatch) {
                        $params[$paramMatch[1]] = $paramMatch[2];
                    }
                }

                $plugin = $this->getPlugin($pluginName);
                if ($plugin) {
                    $replacement = $plugin->processShortcode($pluginName, $params);
                    $content = str_replace($match[0], $replacement, $content);
                }
            }
        }

        return $content;
    }
}