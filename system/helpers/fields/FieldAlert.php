<?php

/**
 * Поле-уведомление для системы полей
 * Отображает информационное сообщение в стиле Bootstrap Alert
 * Используется для вывода подсказок, предупреждений и уведомлений в формах
 * 
 * @package Fields
 * @extends Field
 */
class FieldAlert extends Field {
    
    /**
     * Рендерит HTML-код уведомления
     * 
     * @param mixed $currentValue Текущее значение поля (не используется)
     * @return string HTML-код уведомления
     */
    public function render($currentValue = null) {
        // Получение параметров из конфигурации
        $type = $this->options['type'] ?? 'info';
        $icon = $this->options['icon'] ?? null;
        $dismissible = $this->options['dismissible'] ?? false;
        $fullWidth = $this->options['full_width'] ?? true;
        
        // Формирование класса уведомления
        $alertClass = "alert alert-{$type}";
        if ($dismissible) { 
            $alertClass .= ' alert-dismissible fade show'; 
        }
        
        // Иконка (если указана)
        $iconHtml = '';
        if ($icon) { 
            $iconHtml = bloggy_icon('bs', $icon, '20', 'currentColor', 'me-2') . ' '; 
        }
        
        // Кнопка закрытия (если включена)
        $dismissButton = '';
        if ($dismissible) { 
            $dismissButton = '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>'; 
        }
        
        // Основной контент уведомления
        $content = "
        <div class=\"{$alertClass}\" role=\"alert\">
            <div class=\"d-flex align-items-center\">
                <div class=\"flex-grow-1\">
                    {$iconHtml}{$this->options['title']}
                </div>
                {$dismissButton}
            </div>
            " . ($this->options['hint'] ? '<div class="mt-2">' . htmlspecialchars($this->options['hint']) . '</div>' : '') . "
        </div>";
        
        // Обертка на всю ширину (если требуется)
        if ($fullWidth) { 
            $content = "<div class=\"col-12\">{$content}</div>"; 
        }
        
        return $content;
    }
}