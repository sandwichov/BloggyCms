<?php

/**
 * Поле типа "иконка" для системы полей
 * Позволяет выбирать иконки из библиотеки через модальное окно
 * Поддерживает различные наборы иконок, предпросмотр, поиск и очистку
 * 
 * @package Fields
 * @extends Field
 */
class FieldIcon extends Field {
    
    /**
     * Рендерит HTML-код поля для выбора иконки
     * Создает комплексный интерфейс с предпросмотром, кнопками выбора/очистки
     * и модальным окном для поиска и выбора иконок
     * 
     * @param mixed $currentValue Текущее значение поля (формат: "набор:имя_иконки")
     * @return string HTML-код поля
     */
    public function render($currentValue = null) {
        $value = $currentValue !== null ? $currentValue : $this->options['default'];
        
        // Разбор значения на набор и имя иконки
        $iconParts = $this->parseIconValue($value);
        $iconSet = $iconParts['set'] ?? '';
        $iconName = $iconParts['name'] ?? '';
        
        // Генерация превью иконки
        $previewHtml = $this->getIconPreview($iconSet, $iconName);
        
        // URL для загрузки страницы выбора иконок
        $iconsPageUrl = $this->options['icons_page_url'] ?? BASE_URL . '/admin/icons';
        
        // Уникальный ID для модального окна (на случай нескольких полей на странице)
        $modalId = 'iconPickerModal_' . $this->name . '_' . uniqid();
        
        ob_start();
        ?>
        <div class="icon-field-wrapper" 
             data-field-name="<?= $this->name ?>"
             data-icons-page-url="<?= htmlspecialchars($iconsPageUrl) ?>"
             data-modal-id="<?= $modalId ?>">
            
            <!-- Скрытое поле для хранения значения -->
            <input type="hidden" 
                   name="settings[<?= $this->name ?>]" 
                   value="<?= htmlspecialchars($value) ?>" 
                   class="icon-hidden-input">
            
            <!-- Контейнер для превью иконки -->
            <div class="icon-preview-container mb-2">
                <?php if (!empty($previewHtml)): ?>
                <div class="current-icon-preview">
                    <div class="icon-preview-large" style="font-size: 2rem;">
                        <?= $previewHtml ?>
                    </div>
                    <div class="mt-1">
                        <small class="text-muted icon-code">
                            <?= htmlspecialchars($iconSet . ':' . $iconName) ?>
                        </small>
                    </div>
                </div>
                <?php else: ?>
                <div class="empty-icon-placeholder text-muted text-center py-3">
                    <i class="bi bi-question-circle fs-3"></i>
                    <div class="mt-1">
                        <small>Иконка не выбрана</small>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Кнопки управления -->
            <div class="d-flex gap-2">
                <button type="button" 
                        class="btn btn-outline-primary btn-sm icon-select-btn"
                        data-bs-toggle="modal" 
                        data-bs-target="#<?= $modalId ?>">
                    <i class="bi bi-images me-1"></i>
                    Выбрать иконку
                </button>
                
                <?php if (!empty($value)): ?>
                <button type="button" 
                        class="btn btn-outline-danger btn-sm icon-clear-btn">
                    <i class="bi bi-x-circle me-1"></i>
                    Очистить
                </button>
                <?php endif; ?>
            </div>
            
            <!-- Модальное окно выбора иконок -->
            <div class="modal fade icon-picker-modal" 
                 id="<?= $modalId ?>" 
                 tabindex="-1" 
                 aria-hidden="true">
                <div class="modal-dialog modal-xl modal-dialog-scrollable">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">
                                <i class="bi bi-emoji-smile me-2"></i>
                                Выбор иконки
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-0">
                            <!-- Поле поиска -->
                            <div class="p-3 border-bottom">
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <i class="bi bi-search"></i>
                                    </span>
                                    <input type="text" 
                                           class="form-control icon-search-input" 
                                           placeholder="Поиск иконок..."
                                           data-modal-id="<?= $modalId ?>">
                                </div>
                            </div>
                            
                            <!-- Контейнер для контента страницы иконок (загружается через AJAX) -->
                            <div class="icon-modal-content" 
                                 data-field-name="<?= $this->name ?>"
                                 data-modal-id="<?= $modalId ?>">
                                <div class="text-center py-5">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Загрузка...</span>
                                    </div>
                                    <p class="mt-2">Загрузка иконок...</p>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                            <button type="button" class="btn btn-primary icon-select-confirm-btn" disabled>
                                Выбрать
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Стили для компонента -->
        <style>
        .icon-field-wrapper { min-height: 60px; }
        .icon-preview-container {
            min-height: 100px;
            border: 1px dashed #dee2e6;
            border-radius: 0.375rem;
            padding: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #f8f9fa;
        }
        .current-icon-preview { text-align: center; }
        .icon-preview-large { font-size: 2rem; }
        .icon-preview-large svg { width: 48px; height: 48px; }
        .empty-icon-placeholder { text-align: center; opacity: 0.5; }
        .icon-item.selected {
            border-color: #0d6efd !important;
            box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        }
        .icon-item:hover {
            border-color: #0d6efd !important;
            transform: translateY(-2px);
            transition: all 0.2s;
        }
        .icon-item { cursor: pointer; transition: all 0.2s; }
        .icon-modal-content .card { height: 100%; }
        .icon-search-input { border: none; box-shadow: none; }
        .icon-search-input:focus { box-shadow: none; }
        </style>
        <?php
        
        $fieldHtml = ob_get_clean();
        return $this->renderFieldGroup($fieldHtml);
    }
    
    /**
     * Разбирает значение иконки на набор и имя
     * Формат значения: "набор:имя_иконки" или просто "имя_иконки"
     * 
     * @param string $value Значение поля
     * @return array Ассоциативный массив с ключами 'set' и 'name'
     */
    private function parseIconValue($value) {
        if (empty($value) || !is_string($value)) {
            return ['set' => '', 'name' => ''];
        }
        
        $parts = explode(':', $value, 2);
        if (count($parts) === 2) {
            return ['set' => $parts[0], 'name' => $parts[1]];
        }
        
        return ['set' => '', 'name' => $value];
    }
    
    /**
     * Генерирует HTML для превью иконки
     * Использует функцию bloggy_icon() если доступна
     * 
     * @param string $set Набор иконок
     * @param string $name Имя иконки
     * @return string HTML-код для отображения иконки
     */
    private function getIconPreview($set, $name) {
        if (empty($set) || empty($name)) {
            return '';
        }
        
        // Использование системной функции для рендеринга иконок
        if (function_exists('bloggy_icon')) {
            return bloggy_icon($set, $name, '48 48', 'currentColor', 'icon-preview');
        }
        
        // Запасной вариант
        return '<div class="alert alert-info p-2 m-0">' . 
               htmlspecialchars($set . ':' . $name) . 
               '</div>';
    }
    
    /**
     * Переопределяет метод getAttributes для правильного рендеринга
     * Для FieldIcon стандартные атрибуты не используются
     * 
     * @return string Пустая строка
     */
    protected function getAttributes() {
        return '';
    }
}