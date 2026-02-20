<?php

/**
 * Вспомогательный класс для генерации пагинации
 * Предоставляет методы для создания HTML-кода навигации по страницам
 * с поддержкой кастомных шаблонов и fallback-рендеринга
 * 
 * @package Helpers
 */
class PaginationHelper {
    
    /**
     * Генерирует HTML пагинации на основе количества элементов
     * 
     * @param int $currentPage Текущая страница
     * @param int $totalItems Общее количество элементов
     * @param int $perPage Элементов на странице
     * @param string $baseUrl Базовый URL для ссылок
     * @param array $options Дополнительные опции
     * @return string HTML-код пагинации
     */
    public static function render($currentPage, $totalItems, $perPage, $baseUrl, $options = []) {
        $totalPages = ceil($totalItems / $perPage);
        $baseUrl = self::normalizeBaseUrl($baseUrl);
        
        $paginationData = [
            'current_page' => (int)$currentPage,
            'total_pages' => (int)$totalPages,
            'total_items' => (int)$totalItems,
            'per_page' => (int)$perPage
        ];
        
        return self::renderTemplate($paginationData, $baseUrl, $options);
    }
    
    /**
     * Быстрый метод для стандартных случаев, когда известно только количество страниц
     * 
     * @param int $currentPage Текущая страница
     * @param int $totalPages Всего страниц
     * @param string $baseUrl Базовый URL для ссылок
     * @param array $options Дополнительные опции
     * @return string HTML-код пагинации
     */
    public static function simple($currentPage, $totalPages, $baseUrl, $options = []) {
        $baseUrl = self::normalizeBaseUrl($baseUrl);
        
        $paginationData = [
            'current_page' => (int)$currentPage,
            'total_pages' => (int)$totalPages
        ];
        
        return self::renderTemplate($paginationData, $baseUrl, $options);
    }
    
    /**
     * Рендерит шаблон пагинации
     * Ищет шаблон в теме, при отсутствии использует fallback
     * 
     * @param array $paginationData Данные пагинации
     * @param string $baseUrl Базовый URL
     * @param array $options Опции
     * @return string HTML-код пагинации
     */
    private static function renderTemplate($paginationData, $baseUrl, $options = []) {
        $templatePath = self::getTemplatePath();
        
        if (!file_exists($templatePath)) {
            return self::generateFallbackPagination($paginationData, $baseUrl);
        }
        
        ob_start();
        include $templatePath;
        return ob_get_clean();
    }
    
    /**
     * Получает путь к шаблону пагинации
     * Ищет в разных возможных директориях темы
     * 
     * @return string Путь к файлу шаблона
     */
    private static function getTemplatePath() {
        $template = defined('DEFAULT_TEMPLATE') ? DEFAULT_TEMPLATE : 'default';
        
        $possiblePaths = [
            BASE_PATH . '/templates/' . $template . '/front/assets/ui/pagination.php',
        ];
        
        foreach ($possiblePaths as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }
        
        return $possiblePaths[0]; // Первый путь для создания файла
    }
    
    /**
     * Базовая пагинация на случай если шаблон не найден
     * Генерирует простую навигацию с Bootstrap классами
     * 
     * @param array $paginationData Данные пагинации
     * @param string $baseUrl Базовый URL
     * @return string HTML-код пагинации
     */
    private static function generateFallbackPagination($paginationData, $baseUrl) {
        $currentPage = $paginationData['current_page'] ?? 1;
        $totalPages = $paginationData['total_pages'] ?? 1;
        
        if ($totalPages <= 1) {
            return '';
        }
        
        ob_start();
        ?>
        <div class="col-12">
            <nav class="text-center mt-5">
                <ul class="pagination justify-content-center">
                    <li class="page-item <?= $currentPage <= 1 ? 'disabled' : '' ?>">
                        <a class="page-link" href="<?= $currentPage > 1 ? $baseUrl . ($currentPage - 1) : '#' ?>">
                            Назад
                        </a>
                    </li>
                    
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?= $i == $currentPage ? 'active' : '' ?>">
                            <a class="page-link" href="<?= $baseUrl . $i ?>"><?= $i ?></a>
                        </li>
                    <?php endfor; ?>
                    
                    <li class="page-item <?= $currentPage >= $totalPages ? 'disabled' : '' ?>">
                        <a class="page-link" href="<?= $currentPage < $totalPages ? $baseUrl . ($currentPage + 1) : '#' ?>">
                            Вперед
                        </a>
                    </li>
                </ul>
            </nav>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Нормализует базовый URL для пагинации
     * Добавляет правильный разделитель (? или &) для параметра page
     * 
     * @param string $baseUrl Исходный URL
     * @return string Нормализованный URL с разделителем
     */
    private static function normalizeBaseUrl($baseUrl) {
        $baseUrl = rtrim($baseUrl, '/');
        
        if (strpos($baseUrl, '?') !== false) {
            return $baseUrl . '&page=';
        } else {
            return $baseUrl . '?page=';
        }
    }
}