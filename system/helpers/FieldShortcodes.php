<?php

/**
 * Класс для обработки шорткодов пользовательских полей в контенте
 * Поддерживает простые шорткоды {field_name} и парные {field_name}...{/field_name}
 * Обрабатывает различные типы полей: select, multiselect, repeater, gallery
 * 
 * @package Fields
 */
class FieldShortcodes {
    
    /** @var object Подключение к базе данных */
    private $db;
    
    /** @var FieldModel Модель для работы с полями */
    private $fieldModel;
    
    /** @var FieldManager Менеджер для рендеринга полей */
    private $fieldManager;
    
    /** @var self|null Экземпляр класса (синглтон) */
    private static $instance = null;
    
    /**
     * Получает экземпляр класса (синглтон)
     * 
     * @param object|null $db Подключение к базе данных (обязательно при первом вызове)
     * @return self Экземпляр класса
     */
    public static function getInstance($db = null) {
        if (self::$instance === null && $db) {
            self::$instance = new self($db);
        }
        return self::$instance;
    }
    
    /**
     * Приватный конструктор (синглтон)
     * 
     * @param object $db Подключение к базе данных
     */
    private function __construct($db) {
        $this->db = $db;
        $this->fieldModel = new FieldModel($db);
        $this->fieldManager = new FieldManager($db);
    }
    
    /**
     * Обрабатывает все шорткоды полей в тексте
     * Поддерживает два формата:
     * - {field_name} - простой шорткод
     * - {field_name}content{/field_name} - парный шорткод
     * 
     * @param string $content Текст с шорткодами
     * @param string $entityType Тип сущности (post, user, category и т.д.)
     * @param int $entityId ID сущности
     * @return string Текст с обработанными шорткодами
     */
    public function process($content, $entityType, $entityId) {
        // Регулярное выражение для поиска простых шорткодов {field-name}
        $pattern = '/\{(\w+)\}/';
        
        // Замена всех простых шорткодов
        $content = preg_replace_callback($pattern, function($matches) use ($entityType, $entityId) {
            $fieldName = $matches[1];
            return $this->getFieldValue($fieldName, $entityType, $entityId);
        }, $content);
        
        // Регулярное выражение для поиска парных шорткодов {field-name}content{/field-name}
        $pairedPattern = '/\{(\w+)\}(.*?)\{\/\1\}/s';
        
        // Замена парных шорткодов
        $content = preg_replace_callback($pairedPattern, function($matches) use ($entityType, $entityId) {
            $fieldName = $matches[1];
            $innerContent = $matches[2];
            return $this->processPairedShortcode($fieldName, $innerContent, $entityType, $entityId);
        }, $content);
        
        return $content;
    }
    
    /**
     * Получает значение поля и рендерит его для отображения
     * 
     * @param string $fieldName Системное имя поля
     * @param string $entityType Тип сущности
     * @param int $entityId ID сущности
     * @return string Отформатированное значение поля
     */
    private function getFieldValue($fieldName, $entityType, $entityId) {
        $value = $this->fieldModel->getFieldValue($entityType, $entityId, $fieldName);
        
        if ($value === null) {
            return '';
        }
        
        // Получение информации о поле для рендеринга
        $field = $this->fieldModel->getFieldBySystemName($fieldName, $entityType);
        
        if ($field) {
            // Рендеринг через FieldManager
            return $this->fieldManager->renderFieldDisplay(
                $field['type'],
                $value,
                json_decode($field['config'] ?? '{}', true),
                $entityType,
                $entityId
            );
        }
        
        return htmlspecialchars($value);
    }
    
    /**
     * Обрабатывает парные шорткоды для различных типов полей
     * Поддерживает:
     * - select (одна замена)
     * - multiselect (повтор для каждого выбранного)
     * - repeater (повтор для каждого элемента)
     * - gallery (повтор для каждого изображения)
     * 
     * @param string $fieldName Системное имя поля
     * @param string $template Шаблон для замены
     * @param string $entityType Тип сущности
     * @param int $entityId ID сущности
     * @return string HTML-код результата
     */
    private function processPairedShortcode($fieldName, $template, $entityType, $entityId) {
        $value = $this->fieldModel->getFieldValue($entityType, $entityId, $fieldName);
        
        if ($value === null || empty($value)) {
            return '';
        }
        
        $field = $this->fieldModel->getFieldBySystemName($fieldName, $entityType);
        
        if (!$field) {
            return '';
        }
        
        // Обработка в зависимости от типа поля
        switch ($field['type']) {
            case 'select':
            case 'multiselect':
                return $this->processSelectShortcode($field, $value, $template);
                
            case 'repeater':
                return $this->processRepeaterShortcode($value, $template);
                
            case 'gallery':
                return $this->processGalleryShortcode($value, $template);
                
            default:
                // По умолчанию просто возвращаем значение
                return $this->getFieldValue($fieldName, $entityType, $entityId);
        }
    }

    /**
     * Обрабатывает шорткоды для select/multiselect полей
     * 
     * @param array $field Данные поля
     * @param mixed $value Значение поля
     * @param string $template Шаблон
     * @return string Обработанный шаблон
     */
    private function processSelectShortcode($field, $value, $template) {
        $config = json_decode($field['config'] ?? '{}', true);
        $options = $config['options'] ?? [];
        
        if ($field['type'] === 'multiselect') {
            $selectedValues = json_decode($value, true) ?? [];
            $result = '';
            foreach ($selectedValues as $val) {
                $label = $options[$val] ?? $val;
                $result .= str_replace('{value}', htmlspecialchars($label), $template);
            }
            return $result;
        } else {
            $label = $options[$value] ?? $value;
            return str_replace('{value}', htmlspecialchars($label), $template);
        }
    }

    /**
     * Обрабатывает шорткоды для repeater полей
     * 
     * @param string $value JSON-строка с данными repeater
     * @param string $template Шаблон
     * @return string Обработанный шаблон для каждого элемента
     */
    private function processRepeaterShortcode($value, $template) {
        $repeaterData = json_decode($value, true) ?? [];
        $result = '';
        
        foreach ($repeaterData as $index => $item) {
            $processedItem = $template;
            
            // Замена {field.subfield} на значения
            $processedItem = preg_replace_callback('/\{(\w+\.\w+)\}/', function($matches) use ($item) {
                $fieldPath = $matches[1];
                list($fieldName, $subField) = explode('.', $fieldPath, 2);
                return htmlspecialchars($item[$subField] ?? '');
            }, $processedItem);
            
            // Замена {index} на номер элемента
            $processedItem = str_replace('{index}', $index + 1, $processedItem);
            
            $result .= $processedItem;
        }
        
        return $result;
    }

    /**
     * Обрабатывает шорткоды для gallery полей
     * 
     * @param string $value JSON-строка с данными галереи
     * @param string $template Шаблон
     * @return string Обработанный шаблон для каждого изображения
     */
    private function processGalleryShortcode($value, $template) {
        $images = json_decode($value, true) ?? [];
        $result = '';
        
        foreach ($images as $index => $image) {
            $processed = $template;
            $processed = str_replace('{url}', htmlspecialchars($image['url'] ?? ''), $processed);
            $processed = str_replace('{alt}', htmlspecialchars($image['alt'] ?? ''), $processed);
            $processed = str_replace('{caption}', htmlspecialchars($image['caption'] ?? ''), $processed);
            $processed = str_replace('{index}', $index + 1, $processed);
            $result .= $processed;
        }
        
        return $result;
    }
    
    /**
     * Получает все поля сущности в виде массива для использования в шаблонах
     * Возвращает массив с ключами:
     * - value: исходное значение
     * - display: отрендеренное значение
     * - name: название поля
     * - type: тип поля
     * - config: конфигурация поля
     * 
     * @param string $entityType Тип сущности
     * @param int $entityId ID сущности
     * @return array Массив полей с данными
     */
    public function getAllFields($entityType, $entityId) {
        $fields = $this->fieldModel->getActiveByEntityType($entityType);
        $result = [];
        
        foreach ($fields as $field) {
            $value = $this->fieldModel->getFieldValue($entityType, $entityId, $field['system_name']);
            
            // Рендеринг отображения
            $displayValue = $this->fieldManager->renderFieldDisplay(
                $field['type'],
                $value,
                json_decode($field['config'] ?? '{}', true),
                $entityType,
                $entityId
            );
            
            $result[$field['system_name']] = [
                'value' => $value,
                'display' => $displayValue,
                'name' => $field['name'],
                'type' => $field['type'],
                'config' => json_decode($field['config'] ?? '{}', true)
            ];
        }
        
        return $result;
    }
}