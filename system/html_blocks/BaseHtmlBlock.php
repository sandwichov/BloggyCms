<?php

/**
 * Абстрактный базовый класс для всех типов HTML-блоков
 * 
 * Предоставляет единый интерфейс для создания, настройки и рендеринга
 * HTML-блоков в системе. Все кастомные блоки должны наследовать этот класс.
 * 
 * @package core
 * @author BloggyCMS Team
 * @version 1.0.0
 */
abstract class BaseHtmlBlock {
    
    /**
     * Возвращает название блока для отображения в админ-панели
     * 
     * @return string Название блока
     */
    abstract public function getName(): string;

    /**
     * Возвращает системное имя блока
     * 
     * Должно совпадать с именем класса блока. Используется для
     * идентификации блока в системе и при поиске шаблонов.
     * 
     * @return string Системное имя блока
     */
    abstract public function getSystemName(): string;

    /**
     * Возвращает подробное описание блока
     * 
     * @return string Описание блока
     */
    abstract public function getDescription(): string;

    /**
     * Возвращает иконку блока
     * 
     * Может быть классом Bootstrap Icons (bi bi-box) или путем к изображению.
     * 
     * @return string Класс иконки или путь к изображению
     */
    public function getIcon(): string {
        return 'bi bi-box';
    }

    /**
     * Возвращает имя автора блока
     * 
     * @return string Имя автора
     */
    public function getAuthor(): string {
        return 'BloggyCMS Team';
    }

    /**
     * Возвращает версию блока
     * 
     * @return string Версия в формате x.y.z
     */
    public function getVersion(): string {
        return '1.0.0';
    }

    /**
     * Возвращает сайт автора блока
     * 
     * @return string URL сайта автора (может быть пустым)
     */
    public function getAuthorWebsite(): string {
        return '';
    }

    /**
     * Возвращает краткое описание блока для списка
     * 
     * По умолчанию использует основное описание. Может быть переопределено
     * для более лаконичного описания в интерфейсе выбора типа блока.
     * 
     * @return string Краткое описание
     */
    public function getShortDescription(): string {
        return $this->getDescription();
    }

    /**
     * Возвращает HTML-форму настроек блока
     * 
     * Используется в админ-панели для конфигурации блока.
     * Должна быть реализована в каждом конкретном блоке.
     * 
     * @param array $currentSettings Текущие настройки блока (если есть)
     * @return string HTML-код формы настроек
     */
    abstract public function getSettingsForm($currentSettings = []): string;

    /**
     * Рендерит блок на фронтенде
     * 
     * На основе настроек и выбранного шаблона генерирует HTML-код блока.
     * 
     * @param array $settings Настройки блока
     * @param string|null $templateName Имя шаблона (если null, берется из настроек или 'default')
     * @return string HTML-код блока
     */
    public function processFrontend($settings = [], $templateName = null): string {
        $template = $templateName ?? ($settings['template'] ?? 'default');
        return $this->renderFromTemplate($settings, $template);
    }

    /**
     * Рендерит блок из файла шаблона
     * 
     * Ищет подходящий файл шаблона, подключает его и возвращает результат.
     * Если указанный шаблон не найден, пробует использовать 'default'.
     * 
     * @param array $settings Настройки блока
     * @param string $templateName Имя шаблона
     * @return string HTML-код блока
     */
    protected function renderFromTemplate($settings = [], $templateName = 'default'): string {
        $templatePath = $this->findTemplatePath($templateName);
        
        if ($templatePath && file_exists($templatePath)) {
            extract([
                'settings' => $settings, 
                'block' => $this,
                'templateName' => $templateName
            ], EXTR_SKIP);
            
            ob_start();
            include $templatePath;
            return ob_get_clean();
        }
        
        if ($templateName !== 'default') {
            return $this->renderFromTemplate($settings, 'default');
        }
        
        return $this->getFallbackContent($settings);
    }
    
    /**
     * Ищет путь к файлу шаблона
     * 
     * Поиск осуществляется в следующем порядке приоритета:
     * 1. Предпочтительный шаблон (из getTemplate) с новой структурой (/assets/html_blocks/BlockName/)
     * 2. Текущий активный шаблон с новой структурой
     * 3. Дефолтный шаблон с новой структурой
     * 4. Предпочтительный шаблон со старой структурой (/html_blocks/BlockName.php)
     * 5. Текущий шаблон со старой структурой
     * 6. Дефолтный шаблон со старой структурой
     * 
     * @param string $templateName Имя шаблона (файла)
     * @return string|null Путь к файлу шаблона или null если не найден
     */
    protected function findTemplatePath($templateName = 'default'): ?string {
        $systemName = $this->getSystemName();
        $currentTemplate = get_current_template();
        $preferredTemplate = $this->getTemplate();
        
        // Новая структура: /front/assets/html_blocks/BlockName/template.php
        if ($preferredTemplate && $preferredTemplate !== 'all') {
            $path = BASE_PATH . "/templates/{$preferredTemplate}/front/assets/html_blocks/{$systemName}/{$templateName}.php";
            if (file_exists($path)) {
                return $path;
            }
        }
        
        $path = BASE_PATH . "/templates/{$currentTemplate}/front/assets/html_blocks/{$systemName}/{$templateName}.php";
        if (file_exists($path)) {
            return $path;
        }
        
        $defaultPath = BASE_PATH . "/templates/default/front/assets/html_blocks/{$systemName}/{$templateName}.php";
        if (file_exists($defaultPath)) {
            return $defaultPath;
        }
        
        // Старая структура для обратной совместимости: /front/html_blocks/BlockName.php
        if ($preferredTemplate && $preferredTemplate !== 'all') {
            $legacyPath = BASE_PATH . "/templates/{$preferredTemplate}/front/html_blocks/{$systemName}.php";
            if (file_exists($legacyPath)) {
                return $legacyPath;
            }
        }
        
        $legacyPath = BASE_PATH . "/templates/{$currentTemplate}/front/html_blocks/{$systemName}.php";
        if (file_exists($legacyPath)) {
            return $legacyPath;
        }
        
        $legacyDefaultPath = BASE_PATH . "/templates/default/front/html_blocks/{$systemName}.php";
        if (file_exists($legacyDefaultPath)) {
            return $legacyDefaultPath;
        }
        
        return null;
    }
    
    /**
     * Возвращает заглушку, когда шаблон не найден
     * 
     * @param array $settings Настройки блока
     * @return string HTML-код сообщения об ошибке
     */
    protected function getFallbackContent($settings): string {
        return '<div class="alert alert-warning">Шаблон для блока "' . $this->getName() . '" не найден.</div>';
    }

    /**
     * Возвращает массив CSS файлов для админ-панели
     * 
     * @return array Массив путей к CSS файлам
     */
    public function getAdminCss(): array {
        return [];
    }

    /**
     * Возвращает массив JavaScript файлов для админ-панели
     * 
     * @return array Массив путей к JS файлам
     */
    public function getAdminJs(): array {
        return [];
    }

    /**
     * Возвращает массив CSS файлов для фронтенда
     * 
     * @return array Массив путей к CSS файлам
     */
    public function getFrontendCss(): array {
        return [];
    }

    /**
     * Возвращает массив JavaScript файлов для фронтенда
     * 
     * @return array Массив путей к JS файлам
     */
    public function getFrontendJs(): array {
        return [];
    }

    /**
     * Возвращает инлайн CSS код для фронтенда
     * 
     * @return string CSS код
     */
    public function getFrontendInlineCss(): string {
        return '';
    }

    /**
     * Возвращает инлайн JavaScript код для фронтенда
     * 
     * @return string JavaScript код
     */
    public function getFrontendInlineJs(): string {
        return '';
    }

    /**
     * Валидирует настройки блока перед сохранением
     * 
     * @param array $settings Настройки для валидации
     * @return array Массив [bool $isValid, array $errors]
     */
    public function validateSettings($settings): array {
        return [true, []];
    }

    /**
     * Подготавливает настройки перед сохранением в базу данных
     * 
     * @param array $settings Исходные настройки из формы
     * @return array Подготовленные настройки
     */
    public function prepareSettings($settings): array {
        return $settings;
    }

    /**
     * Возвращает название шаблона темы, для которой предназначен блок
     * 
     * Если возвращает 'all' или пустую строку - блок доступен для всех шаблонов.
     * Используется при поиске предпочтительного шаблона для рендеринга.
     * 
     * @return string Название шаблона темы или 'all'
     */
    public function getTemplate(): string {
        return 'all';
    }

    /**
     * Возвращает системные CSS файлы
     * 
     * Эти файлы подключаются автоматически и не могут быть удалены
     * через интерфейс управления ресурсами.
     * 
     * @return array Массив путей к CSS файлам
     */
    public function getSystemCss(): array {
        return [];
    }
    
    /**
     * Возвращает системные JavaScript файлы
     * 
     * Эти файлы подключаются автоматически и не могут быть удалены
     * через интерфейс управления ресурсами.
     * 
     * @return array Массив путей к JS файлам
     */
    public function getSystemJs(): array {
        return [];
    }
    
    /**
     * Возвращает системный инлайн CSS код
     * 
     * Этот код подключается автоматически и не может быть удален
     * через интерфейс управления ресурсами.
     * 
     * @return string CSS код
     */
    public function getSystemInlineCss(): string {
        return '';
    }
    
    /**
     * Возвращает системный инлайн JavaScript код
     * 
     * Этот код подключается автоматически и не может быть удален
     * через интерфейс управления ресурсами.
     * 
     * @return string JavaScript код
     */
    public function getSystemInlineJs(): string {
        return '';
    }

    /**
     * Возвращает список доступных шаблонов для этого блока
     * 
     * Ищет все PHP файлы в директориях шаблонов блока во всех установленных темах.
     * Формат возвращаемого массива: ['имя_шаблона' => 'Описание шаблона [тема]']
     * 
     * @return array Ассоциативный массив доступных шаблонов
     */
    public function getAvailableTemplates(): array {
        $templates = [];
        $systemName = $this->getSystemName();
        $templatesDir = BASE_PATH . '/templates';
        
        if (is_dir($templatesDir)) {
            $templateDirs = scandir($templatesDir);
            
            foreach ($templateDirs as $templateDir) {
                if ($templateDir === '.' || $templateDir === '..') continue;
                
                // Поиск в новой структуре
                $blockDir = $templatesDir . '/' . $templateDir . '/front/assets/html_blocks/' . $systemName;
                if (is_dir($blockDir)) {
                    $files = glob($blockDir . '/*.php');
                    foreach ($files as $file) {
                        $templateName = pathinfo($file, PATHINFO_FILENAME);
                        $description = $this->getTemplateDescription($templateName, $file);
                        $templates[$templateName] = $description . ' [' . $templateDir . ']';
                    }
                }
                
                // Поиск в старой структуре (для обратной совместимости)
                $legacyFile = $templatesDir . '/' . $templateDir . '/front/html_blocks/' . $systemName . '.php';
                if (file_exists($legacyFile)) {
                    $description = $this->getTemplateDescription('default', $legacyFile);
                    $templates['default'] = $description . ' [' . $templateDir . '] (legacy)';
                }
            }
        }
        
        if (empty($templates)) {
            $templates['default'] = 'Стандартный шаблон';
        }
        
        return $templates;
    }

    /**
     * Извлекает описание шаблона из PHPDoc комментария файла
     * 
     * @param string $templateName Имя шаблона
     * @param string $filePath Путь к файлу шаблона
     * @return string Описание шаблона или имя файла, если описание не найдено
     */
    protected function getTemplateDescription($templateName, $filePath): string {
        if (!file_exists($filePath)) {
            return $templateName;
        }
        
        $content = file_get_contents($filePath);
        if (preg_match('/\/\*\*\s*(.*?)\s*\*\//s', $content, $matches)) {
            $lines = explode("\n", $matches[1]);
            foreach ($lines as $line) {
                $line = trim($line, " *\t\r\n\0\x0B");
                if (strpos($line, '@') === false && !empty($line)) {
                    return $line;
                }
            }
        }
        
        return $templateName;
    }

    /**
     * Возвращает путь к директории с шаблонами блока
     * 
     * @deprecated 1.0.0 Используйте findTemplatePath() вместо этого метода
     * @return string Путь к директории (всегда пустая строка)
     */
    protected function getTemplatesDirectory(): string {
        return '';
    }

    /**
     * Возвращает путь к файлу шаблона
     * 
     * @deprecated 1.0.0 Используйте findTemplatePath() вместо этого метода
     * @param string $templateName Имя шаблона
     * @return string Путь к файлу или пустая строка
     */
    protected function getTemplatePath($templateName = 'default'): string {
        $path = $this->findTemplatePath($templateName);
        return $path ?: '';
    }
}