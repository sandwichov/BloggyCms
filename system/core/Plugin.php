<?php

/**
 * Абстрактный базовый класс для всех плагинов
 */
abstract class Plugin {
    /**
     * @var Database Подключение к базе данных
     */
    protected $db;
    
    /**
     * @var string Название плагина
     */
    protected $name = '';
    
    /**
     * @var string Версия плагина
     */
    protected $version;
    
    /**
     * @var string Автор плагина
     */
    protected $author;
    
    /**
     * @var string Описание плагина
     */
    protected $description;
    
    /**
     * @var array Настройки плагина
     */
    protected $settings;
    
    /**
     * Конструктор Plugin
     *
     * @param Database $db Подключение к базе данных
     */
    public function __construct(Database $db) {
        $this->db = $db;
        $this->init();
    }
    
    /**
     * Инициализация плагина
     */
    abstract protected function init();
    
    /**
     * Регистрация ассетов плагина
     */
    public function registerAssets(): void {
        $this->init();
    }
    
    /**
     * Получение маршрутов плагина
     *
     * @return array Массив маршрутов
     */
    abstract public function getRoutes(): array;
    
    /**
     * Получение системного имени плагина
     *
     * @return string Системное имя плагина
     */
    public function getSystemName(): string {
        return strtolower(str_replace(' ', '', $this->name));
    }

    /**
     * Получение названия плагина
     *
     * @return string Название плагина
     */
    public function getName(): string {
        return $this->name;
    }
    
    /**
     * Получение информации о плагине
     *
     * @return array Информация о плагине
     */
    public function getInfo(): array {
        return [
            'name' => $this->name,
            'system_name' => $this->getSystemName(),
            'version' => $this->version,
            'author' => $this->author,
            'description' => $this->description
        ];
    }
    
    /**
     * Получение настроек плагина
     *
     * @return array Настройки плагина
     */
    public function getSettings(): array {
        if ($this->settings === null) {
            $settingsModel = new SettingsModel($this->db);
            $this->settings = $settingsModel->get('plugin_' . $this->getSystemName()) ?? [];
        }
        return $this->settings;
    }
    
    /**
     * Сохранение настроек плагина
     *
     * @param array $settings Настройки для сохранения
     * @return bool Результат сохранения
     */
    public function saveSettings(array $settings): bool {
        $settingsModel = new SettingsModel($this->db);
        $this->settings = $settings;
        return $settingsModel->save('plugin_' . $this->getSystemName(), $settings);
    }
    
    /**
     * Активация плагина
     */
    public function activate() {}
    
    /**
     * Деактивация плагина
     */
    public function deactivate() {}
    
    /**
     * Удаление плагина
     */
    public function uninstall() {}
    
    /**
     * Обработка запроса в админке
     *
     * @param string $action Действие
     * @param array $params Параметры
     * @return mixed Результат обработки
     */
    public function handleAdminRequest(string $action, array $params = []): mixed {
        return null;
    }
    
    /**
     * Обработка запроса на фронтенде
     *
     * @param string $action Действие
     * @param array $params Параметры
     * @return mixed Результат обработки
     */
    public function handleFrontRequest(string $action, array $params = []): mixed {
        return null;
    }
    
    /**
     * Рендеринг админской страницы настроек
     *
     * @return string HTML код страницы
     */
    public function renderAdminPage(): string {
        $settingsFile = PLUGINS_PATH . '/' . $this->getSystemName() . '/admin/settings.php';
        
        if (file_exists($settingsFile)) {
            $settings = $this->getSettings();
            
            ob_start();
            include $settingsFile;
            return ob_get_clean();
        }
        
        return '<div class="alert alert-info">У этого плагина нет настроек</div>';
    }

    /**
     * Обработка шорткодов
     *
     * @param array $params Параметры шорткода
     * @return string Результат обработки
     */
    public function processShortcode(array $params = []): string {
        return '';
    }

    /**
     * Добавление CSS файла для фронтенда
     *
     * @param string $file Имя CSS файла
     */
    protected function addCss(string $file): void {
        AssetManager::getInstance()->addFrontendCss('/system/plugins/' . $this->getSystemName() . '/front/assets/css/' . $file . '.css');
    }
    
    /**
     * Добавление JS файла для фронтенда
     *
     * @param string $file Имя JS файла
     */
    protected function addJs(string $file): void {
        AssetManager::getInstance()->addFrontendJs('/system/plugins/' . $this->getSystemName() . '/front/assets/js/' . $file . '.js');
    }
    
    /**
     * Добавление CSS файла для админки
     *
     * @param string $file Имя CSS файла
     */
    protected function addAdminCss(string $file): void {
        AssetManager::getInstance()->addAdminCss('/system/plugins/' . $this->getSystemName() . '/admin/assets/css/' . $file . '.css');
    }
    
    /**
     * Добавление JS файла для админки
     *
     * @param string $file Имя JS файла
     */
    protected function addAdminJs(string $file): void {
        AssetManager::getInstance()->addAdminJs('/system/plugins/' . $this->getSystemName() . '/admin/assets/js/' . $file . '.js');
    }
    
    /**
     * Добавление инлайн JS кода
     *
     * @param string $code JS код
     */
    protected function addInlineJs(string $code): void {
        AssetManager::getInstance()->addInlineJs($code);
    }
    
    /**
     * Рендеринг фронтенд части плагина
     *
     * @param array $params Параметры рендеринга
     * @return string HTML код
     */
    public function renderFront(array $params = []): string {
        return '';
    }
}