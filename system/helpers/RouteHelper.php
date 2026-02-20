<?php

/**
 * Вспомогательный класс для работы с маршрутами
 * Предоставляет методы для получения информации о всех фронтенд-маршрутах,
 * их фильтрации, генерации имен и определения текущего маршрута
 * 
 * @package Core
 */
class RouteHelper {
    
    /**
     * Получает ВСЕ фронтенд маршруты из всех контроллеров
     * Включает базовые системные маршруты и маршруты из файлов routes.php
     * 
     * @return array Массив маршрутов с полями: route, controller, action, name, module
     */
    public static function getAllFrontendRoutes() {
        $routes = [];
        
        // Базовые системные страницы
        $routes[] = [
            'route' => '*',
            'controller' => 'All',
            'action' => 'All',
            'name' => 'Все страницы'
        ];
        
        $routes[] = [
            'route' => 'home',
            'controller' => 'Home',
            'action' => 'index',
            'name' => 'Главная страница'
        ];
        
        $routes[] = [
            'route' => '404',
            'controller' => 'Error',
            'action' => 'notFound',
            'name' => 'Страница 404'
        ];
        
        $routes[] = [
            'route' => '500',
            'controller' => 'Error',
            'action' => 'serverError',
            'name' => 'Страница 500'
        ];
        
        // Сканируем директорию контроллеров
        $controllersDir = __DIR__ . '/../controllers/';
        
        if (!is_dir($controllersDir)) {
            return $routes;
        }
        
        // Получаем все папки контроллеров
        $controllerFolders = scandir($controllersDir);
        
        foreach ($controllerFolders as $folder) {
            if ($folder === '.' || $folder === '..' || !is_dir($controllersDir . $folder)) {
                continue;
            }
            
            $routesFile = $controllersDir . $folder . '/routes.php';
            
            if (file_exists($routesFile)) {
                $controllerRoutes = include $routesFile;
                
                foreach ($controllerRoutes as $routePattern => $config) {
                    // Пропускаем админские роуты
                    if (isset($config['admin']) && $config['admin'] === true) {
                        continue;
                    }
                    
                    // Генерируем имя для маршрута
                    $name = self::generateRouteName($config['controller'], $config['action'], $folder);
                    
                    $routes[] = [
                        'route' => $routePattern,
                        'controller' => $config['controller'],
                        'action' => $config['action'],
                        'name' => $name,
                        'module' => $folder
                    ];
                }
            }
        }
        
        // Сортируем по алфавиту для удобства
        usort($routes, function($a, $b) {
            return strcmp($a['name'], $b['name']);
        });
        
        return $routes;
    }
    
    /**
     * Получает маршруты для определенного контроллера
     * 
     * @param string $controllerName Имя контроллера (например 'Post', 'User')
     * @return array Отфильтрованные маршруты
     */
    public static function getRoutesForController($controllerName) {
        $allRoutes = self::getAllFrontendRoutes();
        
        return array_filter($allRoutes, function($route) use ($controllerName) {
            return $route['controller'] === $controllerName && $route['route'] !== '*';
        });
    }
    
    /**
     * Генерирует понятное имя для маршрута
     * Использует предопределенный маппинг или создает имя из controller::action
     * 
     * @param string $controller Имя контроллера
     * @param string $action Имя действия
     * @param string $module Имя модуля (опционально)
     * @return string Понятное название маршрута
     */
    private static function generateRouteName($controller, $action, $module = '') {
        // Сначала проверяем по контроллеру и действию
        $nameMap = [
            'Post' => [
                'index' => 'Список постов',
                'show' => 'Страница поста',
                'all' => 'Все посты'
            ],
            'Category' => [
                'index' => 'Список категорий',
                'show' => 'Страница категории'
            ],
            'Tag' => [
                'index' => 'Список тегов',
                'show' => 'Страница тега'
            ],
            'Page' => [
                'index' => 'Страницы',
                'show' => 'Страница'
            ],
            'User' => [
                'index' => 'Список пользователей',
                'show' => 'Профиль пользователя'
            ],
            'Search' => [
                'index' => 'Поиск'
            ],
            'Archive' => [
                'index' => 'Архив'
            ],
            'HtmlBlock' => [
                'show' => 'HTML-блок'
            ],
            'Profile' => [
                'index' => 'Профиль',
                'show' => 'Профиль пользователя',
                'edit' => 'Редактирование профиля'
            ],
            'Auth' => [
                'login' => 'Вход',
                'register' => 'Регистрация',
                'logout' => 'Выход'
            ]
        ];
        
        if (isset($nameMap[$controller][$action])) {
            return $nameMap[$controller][$action];
        }
        
        // Если нет в мапе, создаем имя из controller::action
        $controllerName = preg_replace('/([a-z])([A-Z])/', '$1 $2', $controller);
        $actionName = ucfirst($action);
        
        return $controllerName . ' - ' . $actionName;
    }
    
    /**
     * Получает текущий маршрут из REQUEST_URI
     * Очищает от BASE_URL и query string
     * 
     * @return string Текущий маршрут (например 'post/123' или 'home')
     */
    public static function getCurrentRoute() {
        if (isset($_SERVER['REQUEST_URI'])) {
            $uri = $_SERVER['REQUEST_URI'];
            
            // Убираем BASE_URL если есть
            if (defined('BASE_URL') && !empty(BASE_URL) && strpos($uri, BASE_URL) === 0) {
                $uri = substr($uri, strlen(BASE_URL));
            }
            
            // Убираем начальный и конечный слэш
            $uri = trim($uri, '/');
            
            // Убираем query string
            if (($pos = strpos($uri, '?')) !== false) {
                $uri = substr($uri, 0, $pos);
            }
            
            return $uri ?: 'home';
        }
        
        return 'home';
    }
    
    /**
     * Получает текущий контроллер и действие из роутера
     * 
     * @return array Массив с ключами 'controller' и 'action'
     */
    public static function getCurrentControllerAction() {
        $router = new Router();
        $currentUri = self::getCurrentRoute();
        
        $route = $router->match($currentUri);
        
        if ($route) {
            return [
                'controller' => $route['controller'] ?? 'Home',
                'action' => $route['action'] ?? 'index'
            ];
        }
        
        return [
            'controller' => 'Error',
            'action' => 'notFound'
        ];
    }
    
    /**
     * Получает маршрут по шаблону
     * 
     * @param string $pattern Шаблон маршрута
     * @return array|null Данные маршрута или null
     */
    public static function getRouteByPattern($pattern) {
        $allRoutes = self::getAllFrontendRoutes();
        
        foreach ($allRoutes as $route) {
            if ($route['route'] === $pattern) {
                return $route;
            }
        }
        
        return null;
    }
    
    /**
     * Получает список контроллеров с фронтенд роутами
     * Группирует маршруты по контроллерам
     * 
     * @return array Массив контроллеров с их маршрутами
     */
    public static function getControllersWithFrontendRoutes() {
        $allRoutes = self::getAllFrontendRoutes();
        $controllers = [];
        
        foreach ($allRoutes as $route) {
            if ($route['route'] === '*') {
                continue;
            }
            
            $controllerKey = $route['controller'];
            
            if (!isset($controllers[$controllerKey])) {
                $controllers[$controllerKey] = [
                    'name' => $route['controller'],
                    'display_name' => preg_replace('/([a-z])([A-Z])/', '$1 $2', $route['controller']),
                    'routes' => []
                ];
            }
            
            $controllers[$controllerKey]['routes'][] = $route;
        }
        
        return $controllers;
    }
}