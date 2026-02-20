<?php

namespace settings\actions;

/**
 * Действие отображения и обработки главной страницы настроек
 * Управляет тремя типами настроек: общие, сайта и компонентов (контроллеров)
 * Обрабатывает POST-запросы для сохранения, загрузку изображений и валидацию
 * 
 * @package settings\actions
 * @extends SettingsAction
 */
class AdminIndex extends SettingsAction {
    
    /**
     * Метод выполнения отображения и обработки настроек
     * В зависимости от активной вкладки загружает соответствующие настройки,
     * обрабатывает POST-запросы для сохранения, загрузку изображений и т.д.
     * 
     * @return void
     */
    public function execute() {
        // Проверка прав доступа администратора
        if (!$this->checkAdminAccess()) {
            \Notification::error('Пожалуйста, авторизуйтесь для доступа к настройкам');
            $this->redirect(ADMIN_URL . '/login');
            return;
        }
        
        try {
            $controllerManager = new \ControllerManager($this->db);
            
            // Получение параметров вкладки и контроллера
            $activeTab = $_GET['tab'] ?? 'general';
            $selectedController = $_GET['controller'] ?? '';
            
            $settings = [];
            
            // Обработка ОБЩИХ и САЙТ настроек
            if (in_array($activeTab, ['general', 'site'])) {
                $settings = $this->settingsModel->get($activeTab);
                
                // Получаем настройки по умолчанию
                $defaultSettings = $this->getDefaultSettings($activeTab);
                $settings = array_merge($defaultSettings, $settings);
                
                if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                    try {
                        if (class_exists('SettingsHelper')) {
                            \SettingsHelper::clearCache($activeTab);
                        }
                        
                        $postSettings = $_POST['settings'] ?? [];
                        
                        // Специфичная обработка для site
                        if ($activeTab === 'site') {
                            $postSettings = $this->handleBackupSettings($postSettings);
                            $postSettings = $this->handleFaviconUpload($postSettings);
                        }
                        
                        // Сохраняем настройки
                        $this->settingsModel->save($activeTab, $postSettings);
                        
                        if (class_exists('SettingsHelper')) {
                            \SettingsHelper::clearCache($activeTab);
                        }
                        
                        // Обновляем конфигурационные файлы если нужно
                        if ($activeTab === 'site') {
                            if (isset($postSettings['site_template'])) {
                                $this->updateConfigTemplate($postSettings['site_template']);
                            }
                            if (isset($postSettings['base_url'])) {
                                $this->updateConfigBaseUrl($postSettings['base_url']);
                            }
                        }
                        
                        \Notification::success('Настройки успешно сохранены');
                        $this->redirect(ADMIN_URL . '/settings?tab=' . $activeTab);
                        return;
                    } catch (\Exception $e) {
                        \Notification::error('Ошибка при сохранении настроек: ' . $e->getMessage());
                    }
                }
            }
            
            // Обработка КОМПОНЕНТОВ
            if ($activeTab === 'components') {
                if ($_SERVER['REQUEST_METHOD'] === 'POST' && $selectedController) {
                    try {
                        $postSettings = $_POST['settings'] ?? [];
                        
                        // Обрабатываем загрузку изображений для компонентов
                        $postSettings = $this->handleComponentImageUploads($postSettings, $selectedController);
                        
                        // Обрабатываем удаление изображений для компонентов
                        $postSettings = $this->handleComponentImageDeletes($postSettings);
                        
                        $controllerManager->saveControllerSettings($selectedController, $postSettings);
                        
                        \Notification::success('Настройки контроллера сохранены');
                        $this->redirect(ADMIN_URL . '/settings?tab=components&controller=' . $selectedController);
                        return;
                    } catch (\Exception $e) {
                        \Notification::error('Ошибка при сохранении настроек контроллера: ' . $e->getMessage());
                    }
                }
                
                // Загружаем настройки выбранного контроллера
                if ($selectedController) {
                    $controllerSettings = $controllerManager->getControllerSettings($selectedController);
                    $controller = $controllerManager->getController($selectedController);
                    
                    if ($controller) {
                        $controllerInstance = new $controller['class']($this->db);
                        $defaultControllerSettings = method_exists($controllerInstance, 'getDefaultSettings') 
                            ? $controllerInstance->getDefaultSettings() 
                            : [];
                        $settings = array_merge($defaultControllerSettings, $controllerSettings);
                    }
                }
            }
            
            // Отображение страницы настроек
            $this->render('admin/settings/index', [
                'settings' => $settings,
                'activeTab' => $activeTab,
                'selectedController' => $selectedController,
                'controllerManager' => $controllerManager,
                'pageTitle' => 'Настройки блога'
            ]);
            
        } catch (\Exception $e) {
            \Notification::error('Ошибка при загрузке настроек: ' . $e->getMessage());
            $this->redirect(ADMIN_URL);
        }
    }
    
    /**
     * Обрабатывает загрузку изображений для компонентов
     */
    private function handleComponentImageUploads($postSettings, $controllerName) {
        
        foreach ($_FILES as $fieldName => $file) {
            
            if (strpos($fieldName, '_file') !== false && $file['error'] === UPLOAD_ERR_OK) {
                $baseFieldName = str_replace('_file', '', $fieldName);
                
                try {
                    $imageName = $this->handleComponentImageUpload($file, $controllerName);
                    
                    $postSettings[$baseFieldName] = $imageName;
                    
                } catch (\Exception $e) {
                    \Notification::error('Ошибка загрузки изображения для поля ' . $baseFieldName . ': ' . $e->getMessage());
                }
            }
        }
        
        return $postSettings;
    }
    
    /**
     * Обрабатывает удаление изображений для компонентов
     */
    private function handleComponentImageDeletes($postSettings) {
        foreach ($_POST as $key => $value) {
            if (strpos($key, 'remove_') === 0 && $value == '1') {
                $fieldName = str_replace('remove_', '', $key);
                
                try {
                    if (!empty($postSettings[$fieldName])) {
                        $this->handleComponentImageDelete($postSettings[$fieldName]);
                    }
                    
                    $postSettings[$fieldName] = '';
                    
                } catch (\Exception $e) {
                    \Notification::error('Ошибка удаления изображения для поля ' . $fieldName . ': ' . $e->getMessage());
                }
            }
        }
        
        return $postSettings;
    }
    
    /**
     * Загружает изображение для компонента
     */
    private function handleComponentImageUpload($file, $controllerName) {
        $uploadDir = UPLOADS_PATH . '/settings/' . $controllerName . '/';
        
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $fileType = mime_content_type($file['tmp_name']);
        
        if (!in_array($fileType, $allowedTypes)) {
            throw new \Exception('Недопустимый тип файла. Разрешены: JPG, PNG, GIF, WebP');
        }
        
        if ($file['size'] > 5 * 1024 * 1024) {
            throw new \Exception('Размер файла не должен превышать 5MB');
        }
        
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $fileName = uniqid() . '_' . $this->generateSlug(pathinfo($file['name'], PATHINFO_FILENAME)) . '.' . $extension;
        $targetPath = $uploadDir . $fileName;
        
        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            throw new \Exception('Ошибка при загрузке файла');
        }
        
        return $fileName;
    }
    
    /**
     * Удаляет изображение компонента
     */
    private function handleComponentImageDelete($fileName) {
        if ($fileName) {
            $possiblePaths = [
                UPLOADS_PATH . '/settings/tags/' . $fileName,
                UPLOADS_PATH . '/settings/categories/' . $fileName,
                UPLOADS_PATH . '/settings/posts/' . $fileName,
            ];
            
            foreach ($possiblePaths as $filePath) {
                if (file_exists($filePath)) {
                    unlink($filePath);
                    break;
                }
            }
        }
    }
    
    /**
     * Генерирует slug для имени файла
     */
    private function generateSlug($string) {
        $converter = array(
            'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd',
            'е' => 'e', 'ё' => 'e', 'ж' => 'zh', 'з' => 'z', 'и' => 'i',
            'й' => 'y', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n',
            'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't',
            'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'c', 'ч' => 'ch',
            'ш' => 'sh', 'щ' => 'sch', 'ь' => '', 'ы' => 'y', 'ъ' => '',
            'э' => 'e', 'ю' => 'yu', 'я' => 'ya'
        );
        
        $string = strtr(mb_strtolower($string), $converter);
        $string = preg_replace('/[^a-z0-9-]/', '-', $string);
        $string = preg_replace('/-+/', '-', $string);
        $string = trim($string, '-');
        
        return $string;
    }

    /**
     * Обрабатывает загрузку favicon
     */
    private function handleFaviconUpload($postSettings) {
        // Проверяем, был ли загружен файл favicon
        if (isset($_FILES['favicon_file']) && $_FILES['favicon_file']['error'] === UPLOAD_ERR_OK) {
            try {
                $file = $_FILES['favicon_file'];
                
                // Проверяем формат файла
                $allowedTypes = ['ico', 'png', 'svg'];
                $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                
                if (!in_array($extension, $allowedTypes)) {
                    throw new \Exception('Недопустимый формат файла. Разрешены: ICO, PNG, SVG');
                }
                
                // Создаем директорию для favicon если её нет
                $uploadDir = UPLOADS_PATH . '/favicon/';
                if (!is_dir($uploadDir)) {
                    if (!mkdir($uploadDir, 0755, true)) {
                        throw new \Exception('Не удалось создать директорию для загрузки');
                    }
                }
                
                // Удаляем старый favicon если он существует
                if (!empty($postSettings['favicon'])) {
                    $oldFile = UPLOADS_PATH . '/' . ltrim($postSettings['favicon'], '/');
                    if (file_exists($oldFile)) {
                        unlink($oldFile);
                    }
                }
                
                // Генерируем имя файла
                $fileName = 'favicon_' . time() . '_' . uniqid() . '.' . $extension;
                $targetPath = $uploadDir . $fileName;
                
                // Перемещаем файл
                if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
                    throw new \Exception('Ошибка при сохранении файла');
                }
                
                // Сохраняем путь в настройках (относительный путь от корня сайта)
                $postSettings['favicon'] = 'uploads/favicon/' . $fileName;
                
            } catch (\Exception $e) {
                \Notification::error('Ошибка загрузки favicon: ' . $e->getMessage());
            }
        }
        
        // Проверяем, нужно ли удалить текущий favicon
        if (isset($_POST['remove_favicon']) && $_POST['remove_favicon'] == '1') {
            if (!empty($postSettings['favicon'])) {
                $oldFile = UPLOADS_PATH . '/' . ltrim($postSettings['favicon'], '/');
                if (file_exists($oldFile)) {
                    unlink($oldFile);
                }
                $postSettings['favicon'] = '';
            }
        }
        
        return $postSettings;
    }

}