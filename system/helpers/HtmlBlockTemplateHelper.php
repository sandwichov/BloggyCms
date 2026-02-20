<?php

/**
 * Вспомогательный класс для работы с шаблонами HTML-блоков
 * Предоставляет методы для поиска и рендеринга шаблонов блоков
 * в различных темах оформления
 * 
 * @package Helpers
 */
class HtmlBlockTemplateHelper {
    
    /**
     * Получает список доступных шаблонов для блока
     * Ищет во всех установленных темах по структуре: /front/assets/html_blocks/BlockName/
     * 
     * @param string $blockSystemName Системное имя блока
     * @return array Массив шаблонов в формате [имя_файла => описание]
     */
    public static function getAvailableTemplates($blockSystemName): array {
        $templates = [];
        $templatesDir = BASE_PATH . '/templates';
        
        if (is_dir($templatesDir)) {
            $templateDirs = scandir($templatesDir);
            
            foreach ($templateDirs as $templateDir) {
                if ($templateDir === '.' || $templateDir === '..') continue;
                
                // Новый путь с вложенной структурой
                $blockDir = $templatesDir . '/' . $templateDir . '/front/assets/html_blocks/' . $blockSystemName;
                
                if (is_dir($blockDir)) {
                    // Поиск всех .php файлов в папке блока
                    $templateFiles = glob($blockDir . '/*.php');
                    foreach ($templateFiles as $file) {
                        $templateName = pathinfo($file, PATHINFO_FILENAME);
                        $templates[$templateName] = $templateDir . ' - ' . $templateName;
                    }
                }
            }
        }
        
        return $templates;
    }
    
    /**
     * Ищет шаблон блока в доступных темах
     * Приоритет поиска:
     * 1. Предпочтительный шаблон (если указан)
     * 2. Текущий активный шаблон
     * 3. Шаблон 'default'
     * 
     * @param string $blockSystemName Системное имя блока
     * @param string $templateName Имя файла шаблона (без расширения)
     * @param string|null $preferredTemplate Предпочтительная тема
     * @return string|null Полный путь к файлу шаблона или null
     */
    public static function findBlockTemplate($blockSystemName, $templateName = 'default', $preferredTemplate = null): ?string {
        
        // Сначала проверяем предпочтительный шаблон (если указан)
        if ($preferredTemplate) {
            $path = BASE_PATH . "/templates/{$preferredTemplate}/front/assets/html_blocks/{$blockSystemName}/{$templateName}.php";
            if (file_exists($path)) {
                return $path;
            }
        }
        
        // Ищем в текущем шаблоне
        $currentTemplate = get_current_template();
        $path = BASE_PATH . "/templates/{$currentTemplate}/front/assets/html_blocks/{$blockSystemName}/{$templateName}.php";
        if (file_exists($path)) {
            return $path;
        }
        
        // Ищем в дефолтном шаблоне
        $defaultPath = BASE_PATH . "/templates/default/front/assets/html_blocks/{$blockSystemName}/{$templateName}.php";
        if (file_exists($defaultPath)) {
            return $defaultPath;
        }
        
        return null;
    }
    
    /**
     * Рендерит блок из шаблона
     * Находит подходящий шаблон и подключает его, передавая настройки
     * 
     * @param string $blockSystemName Системное имя блока
     * @param array $settings Настройки блока
     * @param string $templateName Имя файла шаблона (по умолчанию 'default')
     * @return string HTML-код, сгенерированный шаблоном
     */
    public static function renderFromTemplate($blockSystemName, $settings = [], $templateName = 'default'): string {
        $templatePath = self::findBlockTemplate(
            $blockSystemName, 
            $templateName,
            $settings['preferred_template'] ?? null
        );
        
        if ($templatePath && file_exists($templatePath)) {
            // Извлекаем переменную $settings для использования в шаблоне
            extract(['settings' => $settings], EXTR_SKIP);
            
            ob_start();
            include $templatePath;
            return ob_get_clean();
        }
        
        return '<div class="alert alert-warning">Шаблон "' . htmlspecialchars($templateName) . '" для блока "' . htmlspecialchars($blockSystemName) . '" не найден.</div>';
    }
}