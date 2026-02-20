<?php

namespace icons\actions;

/**
 * Действие получения данных об иконках через AJAX
 * Возвращает JSON с информацией об иконках для использования в интерфейсе
 * 
 * @package icons\actions
 */
class AdminIconsData {
    
    /**
     * @var object|null Контроллер, управляющий действием
     */
    protected $controller;
    
    /**
     * Установка контроллера для действия
     * Связывает действие с контроллером для доступа к его методам
     *
     * @param object $controller Объект контроллера
     * @return void
     */
    public function setController($controller) {
        $this->controller = $controller;
    }
    
    /**
     * Метод выполнения получения данных об иконках
     * Собирает информацию о всех доступных иконках и возвращает в формате JSON
     * 
     * @return void
     */
    public function execute() {
        try {
            // Установка заголовка для JSON-ответа
            header('Content-Type: application/json');
            
            // Получение всех иконок
            $icons = $this->getAllIcons();
            
            // Проверка, найдены ли иконки
            if (empty($icons)) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Иконки не найдены',
                    'data' => []
                ]);
                exit;
            }
            
            // Успешный ответ с данными
            echo json_encode([
                'success' => true,
                'message' => 'Иконки успешно загружены',
                'data' => $icons
            ]);
            
        } catch (\Exception $e) {
            // Логирование ошибки и отправка JSON с ошибкой
            \Logger::error('Ошибка при загрузке иконок: ' . $e->getMessage());
            
            echo json_encode([
                'success' => false,
                'message' => 'Ошибка при загрузке иконок: ' . $e->getMessage(),
                'data' => []
            ]);
        }
        exit;
    }
    
    /**
     * Получение всех иконок из директорий шаблона
     * Сканирует директории иконок и извлекает информацию о наборах иконок
     *
     * @return array Структурированный массив с информацией об иконках
     */
    private function getAllIcons() {
        $icons = [];
        
        // Получаем список доступных шаблонов
        $templates = $this->getAvailableTemplates();
        
        foreach ($templates as $template) {
            $iconsDir = TEMPLATES_PATH . '/' . $template . '/admin/icons/';
            
            // Проверяем существование директории
            if (!is_dir($iconsDir)) {
                continue;
            }
            
            // Поиск всех SVG файлов в директории иконок
            $files = glob($iconsDir . '*.svg');
            
            // Обработка каждого найденного файла
            foreach ($files as $file) {
                $set = basename($file, '.svg');
                $content = file_get_contents($file);
                
                // Извлечение всех ID символов (иконок) из SVG файла
                preg_match_all('/<symbol\s+id="([^"]+)"/', $content, $matches);
                
                // Если в файле найдены иконки, добавляем их в результат
                if (!empty($matches[1])) {
                    $icons[$template][$set] = [
                        'name' => $set,
                        'template' => $template,
                        'path' => '/templates/' . $template . '/admin/icons/' . $set . '.svg',
                        'icons' => array_map(function($id) use ($set, $template) {
                            return [
                                'id' => $id,
                                'preview' => $this->getIconPreviewHtml($set, $id, $template)
                            ];
                        }, $matches[1])
                    ];
                }
            }
        }
        
        // Если иконок нет ни в одном шаблоне, выбрасываем исключение
        if (empty($icons)) {
            throw new \Exception('В системе не найдено ни одного файла с иконками');
        }
        
        return $icons;
    }
    
    /**
     * Получение списка доступных шаблонов
     *
     * @return array Массив с названиями шаблонов
     */
    private function getAvailableTemplates() {
        $templates = ['default']; // По умолчанию как минимум есть default
        
        $templatesDir = TEMPLATES_PATH;
        if (is_dir($templatesDir)) {
            $items = scandir($templatesDir);
            foreach ($items as $item) {
                if ($item !== '.' && $item !== '..' && is_dir($templatesDir . '/' . $item)) {
                    $templates[] = $item;
                }
            }
        }
        
        return array_unique($templates);
    }
    
    /**
     * Генерация HTML для предварительного просмотра иконки
     * Создает SVG элемент для отображения иконки в интерфейсе
     *
     * @param string $set Набор иконок (имя файла без расширения)
     * @param string $iconId ID иконки в SVG файле
     * @param string $template Название шаблона
     * @return string HTML-код для отображения иконки
     */
    private function getIconPreviewHtml($set, $iconId, $template = 'default') {
        // Формирование HTML для превью иконки с использованием символа из SVG спрайта
        return sprintf(
            '<svg width="24" height="24" style="fill: currentColor"><use href="%s#%s"/></svg>',
            BASE_URL . '/templates/' . $template . '/admin/icons/' . $set . '.svg',
            htmlspecialchars($iconId)
        );
    }
}