<?php

namespace icons\actions;

/**
 * Действие отображения списка иконок в админ-панели
 * Показывает все доступные иконки системы с возможностью поиска и выбора
 * 
 * @package icons\actions
 */
class AdminIndex {
    
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
     * Метод выполнения отображения списка иконок
     * Загружает все иконки из системы и отображает их в интерфейсе выбора
     * 
     * @return void
     */
    public function execute() {
        // Получение всех иконок системы
        $icons = $this->getAllIcons();
        
        /**
         * Рендеринг страницы управления иконками
         * 
         * @param string $template Путь к шаблону (admin/icons/index)
         * @param array $data Данные для шаблона:
         * - icons: структурированный массив с информацией об иконках
         * - pageTitle: заголовок страницы
         */
        $this->render('admin/icons/index', [
            'icons' => $icons,
            'pageTitle' => 'Иконки блога'
        ]);
    }
    
    /**
     * Получение всех иконок из директорий шаблона
     * Сканирует директории иконок, извлекает SVG символы и формирует структурированные данные
     *
     * @return array Структурированный массив с информацией об иконках, сгруппированный по наборам
     */
    private function getAllIcons() {
        $icons = [];
        $iconsDir = TEMPLATES_PATH . '/default/admin/icons/';
        
        // Поиск всех SVG файлов в директории иконок
        $files = glob($iconsDir . '*.svg');
        
        // Обработка каждого найденного файла иконок
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
                            'preview' => bloggy_icon($set, $id, '24 24'),
                            'code' => "<?php echo bloggy_icon('{$set}', '{$id}'); ?>"
                        ];
                    }, $matches[1])
                ];
            }
        }
        
        return $icons;
    }
    
    /**
     * Рендеринг шаблона с данными
     * Передает управление методу рендеринга контроллера
     *
     * @param string $template Путь к файлу шаблона
     * @param array $data Массив данных для передачи в шаблон
     * @return void
     * @throws \Exception Если контроллер не установлен
     */
    protected function render($template, $data = []) {
        if ($this->controller) {
            $this->controller->render($template, $data);
        } else {
            throw new \Exception('Controller not set for Action');
        }
    }
}