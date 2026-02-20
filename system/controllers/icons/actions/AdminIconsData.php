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
        // Установка заголовка для JSON-ответа
        header('Content-Type: application/json');
        
        // Получение всех иконок и вывод в формате JSON
        $icons = $this->getAllIcons();
        echo json_encode($icons);
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
        $iconsDir = TEMPLATES_PATH . '/default/admin/icons/';
        
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
                $icons[$set] = [
                    'name' => $set,
                    'icons' => array_map(function($id) use ($set) {
                        return [
                            'id' => $id,
                            'preview' => $this->getIconPreviewHtml($set, $id)
                        ];
                    }, $matches[1])
                ];
            }
        }
        
        return $icons;
    }
    
    /**
     * Генерация HTML для предварительного просмотра иконки
     * Создает SVG элемент для отображения иконки в интерфейсе
     *
     * @param string $set Набор иконок (имя файла без расширения)
     * @param string $iconId ID иконки в SVG файле
     * @return string HTML-код для отображения иконки
     */
    private function getIconPreviewHtml($set, $iconId) {
        // Формирование HTML для превью иконки с использованием символа из SVG спрайта
        return sprintf(
            '<svg width="24" height="24" style="fill: currentColor"><use href="%s#%s"/></svg>',
            BASE_URL . '/templates/default/admin/icons/' . $set . '.svg',
            htmlspecialchars($iconId)
        );
    }
}