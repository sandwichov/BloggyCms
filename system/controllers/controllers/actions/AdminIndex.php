<?php

namespace controllers\actions;

/**
 * Действие отображения списка контроллеров в админ-панели
 * Предоставляет обзор всех контроллеров системы с их характеристиками и возможностями
 * Позволяет администратору видеть структуру контроллеров, их настройки и маршрутизацию
 * 
 * @package controllers\actions
 * @extends ControllersAction
 */
class AdminIndex extends ControllersAction {
    
    /**
     * Метод выполнения отображения списка контроллеров
     * Собирает информацию о всех контроллерах системы и отображает их в структурированном виде
     * 
     * @return mixed Результат рендеринга шаблона
     */
    public function execute() {
        try {
            // Получение списка всех контроллеров системы
            $controllers = $this->getAllControllers();
            
            // Сортировка контроллеров: системные сначала, затем остальные по алфавиту
            usort($controllers, function($a, $b) {
                if ($a['is_system'] === $b['is_system']) {
                    return strcmp($a['name'], $b['name']);
                }
                return $a['is_system'] ? -1 : 1;
            });
            
            // Коллекция подсказок для пользователя
            $hints = [
                "Системные контроллеры отмечены синим бейджем",
                "Настройки доступны только у контроллеров с файлом Settings.php",
                "Роутинг указывает на наличие файла routes.php",
                "Кликните на иконку информации для подробных сведений",
                "Вы можете перейти к настройкам контроллера кликнув на иконку настроек",
                "Контроллеры без роутинга обычно используются как внутренние модули",
                "Версия контроллера указана в его описании",
            ];
            
            // Выбор случайной подсказки для отображения
            $randomHint = $hints[array_rand($hints)];
            
            /**
             * Рендеринг шаблона админ-панели контроллеров
             * 
             * @param string $template Путь к шаблону (admin/controllers/index)
             * @param array $data Данные для шаблона:
             *                    - controllers: массив информации о контроллерах
             *                    - randomHint: случайная текстовая подсказка
             *                    - pageTitle: заголовок страницы
             */
            return $this->render('admin/controllers/index', [
                'controllers' => $controllers,
                'randomHint' => $randomHint,
                'pageTitle' => 'Управление контроллерами'
            ]);
            
        } catch (\Exception $e) {
            // Обработка ошибок при загрузке контроллеров
            \Notification::error('Ошибка при загрузке контроллеров: ' . $e->getMessage());
            $this->redirect(\ADMIN_URL);
        }
    }
    
    /**
     * Получение всех контроллеров системы
     * Сканирует директорию контроллеров и собирает информацию о каждом из них
     *
     * @return array Массив информации о контроллерах
     */
    private function getAllControllers() {
        $controllers = [];
        
        // Определение пути к директории контроллеров
        $basePath = $this->getBasePath();
        $controllersDir = $basePath . '/system/controllers';
        
        // Проверка существования директории контроллеров
        if (!is_dir($controllersDir)) {
            // В случае отсутствия директории возвращается пустой массив
            return $controllers;
        }
        
        // Сканирование содержимого директории контроллеров
        $items = scandir($controllersDir);
        
        // Обработка каждой найденной директории
        foreach ($items as $item) {
            // Пропуск служебных директорий
            if ($item === '.' || $item === '..' || $item === 'controllers') continue;
            
            $controllerPath = $controllersDir . '/' . $item;
            
            // Проверка, является ли элемент директорией
            if (is_dir($controllerPath)) {
                $controllerInfo = $this->getControllerInfo($item, $controllerPath);
                if ($controllerInfo) {
                    $controllers[] = $controllerInfo;
                }
            }
        }
        
        return $controllers;
    }
    
    /**
     * Определение базового пути проекта
     * Поиск корневой директории проекта для корректного доступа к контроллерам
     *
     * @return string Абсолютный путь к корневой директории проекта
     */
    private function getBasePath() {
        // Метод 1: От текущего файла
        $currentFile = __FILE__;
        $basePath = dirname(dirname(dirname(dirname($currentFile))));
        
        // Проверка наличия директории контроллеров в найденном пути
        if (is_dir($basePath . '/system/controllers')) {
            return $basePath;
        }
        
        // Метод 2: Через DOCUMENT_ROOT
        if (isset($_SERVER['DOCUMENT_ROOT'])) {
            $docRoot = $_SERVER['DOCUMENT_ROOT'];
            if (is_dir($docRoot . '/system/controllers')) {
                return $docRoot;
            }
        }
        
        // Метод 3: Текущая рабочая директория
        return getcwd();
    }
    
    /**
     * Получение информации о конкретном контроллере
     * Анализирует структуру контроллера и извлекает метаданные
     *
     * @param string $dirName Имя директории контроллера
     * @param string $controllerPath Абсолютный путь к директории контроллера
     * @return array Информация о контроллере
     */
    private function getControllerInfo($dirName, $controllerPath) {
        // Базовая структура информации о контроллере
        $info = [
            'name' => $this->formatControllerName($dirName),
            'key' => $dirName,
            'has_settings' => false,
            'has_routing' => false,
            'is_system' => false,
            'version' => '1.0.0',
            'description' => '',
            'author' => 'BloggyCMS',
            'path' => $dirName,
            'actions_count' => 0
        ];
        
        // Определение системного статуса контроллера
        $systemControllers = [
            'admin', 'auth', 'settings', 'users', 'posts', 'categories', 
            'pages', 'menu', 'plugins', 'comments', 'profile', 'search', 
            'tags', 'fields', 'html_blocks', 'icons', 'postblocks', 
            'docs', 'addons', 'archive', 'forms', 'login_attempt', 'notifications'
        ];
        $info['is_system'] = in_array(strtolower($dirName), $systemControllers);
        
        // 1. Проверка наличия и загрузка файла manifest.php
        $manifestFile = $controllerPath . '/manifest.php';
        if (file_exists($manifestFile)) {
            $manifestData = $this->loadManifestFile($manifestFile);
            if ($manifestData) {
                $info = array_merge($info, $manifestData);
            }
        }
        
        // 2. Проверка наличия файла настроек Settings.php
        $settingsFile = $controllerPath . '/Settings.php';
        if (file_exists($settingsFile)) {
            $info['has_settings'] = true;
        }
        
        // 3. Проверка наличия файла маршрутизации routes.php
        $routesFile = $controllerPath . '/routes.php';
        if (file_exists($routesFile)) {
            $info['has_routing'] = true;
        }
        
        // 4. Подсчет количества экшенов в контроллере
        $actionsDir = $controllerPath . '/actions';
        if (is_dir($actionsDir)) {
            $phpFiles = glob($actionsDir . '/*.php');
            $info['actions_count'] = count($phpFiles);
        }
        
        return $info;
    }
    
    /**
     * Загрузка файла manifest.php контроллера
     * Извлекает метаданные контроллера из файла манифеста
     *
     * @param string $manifestFile Путь к файлу manifest.php
     * @return array|null Данные манифеста или null при ошибке
     */
    private function loadManifestFile($manifestFile) {
        try {
            $manifestData = include $manifestFile;
            if (is_array($manifestData)) {
                return $manifestData;
            }
        } catch (\Exception $e) {
            // Ошибки загрузки манифеста игнорируются
        }
        
        return null;
    }
    
    /**
     * Форматирование имени контроллера для отображения
     * Преобразует техническое имя директории в читаемое название
     *
     * @param string $dirName Исходное имя директории контроллера
     * @return string Отформатированное имя для отображения
     */
    private function formatControllerName($dirName) {
        // Замена разделителей на пробелы
        $name = str_replace(['_', '-'], ' ', $dirName);
        
        // Добавление пробелов между строчными и прописными буквами
        $name = preg_replace('/([a-z])([A-Z])/', '$1 $2', $name);
        
        // Преобразование первой буквы каждого слова в заглавную
        return ucwords($name);
    }
}