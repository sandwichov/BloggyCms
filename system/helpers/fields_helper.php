<?php

/**
 * Возвращает человекочитаемое название типа поля
 * 
 * @param string $type Тип поля
 * @return string Название типа поля
 */
function get_field_type_name($type) {
    $types = [
        'text' => 'Текст',
        'textarea' => 'Текстовая область',
        'number' => 'Число',
        'select' => 'Выпадающий список',
        'checkbox' => 'Чекбокс',
        'file' => 'Файл',
        'date' => 'Дата',
        'color' => 'Цвет',
        'email' => 'Email',
        'url' => 'URL',
        'string' => 'Текст',
        'text' => 'Текстовая область',
        'flag' => 'Флаг (Да/Нет)',
        'multi_select' => 'Множественный выбор',
        'html' => 'HTML редактор',
        'image' => 'Изображение',
        'link' => 'Ссылка'
    ];
    return $types[$type] ?? $type;
}

/**
 * Возвращает человекочитаемое название типа сущности
 * 
 * @param string $entityType Тип сущности
 * @return string Название типа сущности
 */
function get_entity_name($entityType) {
    $names = [
        'post' => 'Запись',
        'page' => 'Страница',
        'category' => 'Категория',
        'user' => 'Пользователь'
    ];
    return $names[$entityType] ?? $entityType;
}

if (!function_exists('get_field')) {
    /**
     * Получает значение поля для использования в шаблонах
     * Автоматически определяет контекст, если не указаны entityType и entityId
     * 
     * @param string $fieldName Системное имя поля
     * @param string|null $entityType Тип сущности
     * @param int|null $entityId ID сущности
     * @return string Значение поля
     */
    function get_field($fieldName, $entityType = null, $entityId = null) {
        global $db;
        
        if (!$db) {
            return '';
        }
        
        // Определение контекста, если не передан
        if ($entityType === null || $entityId === null) {
            $context = get_field_context();
            $entityType = $entityType ?? $context['entity_type'];
            $entityId = $entityId ?? $context['entity_id'];
        }
        
        if (!$entityType || !$entityId) {
            return '';
        }
        
        try {
            $fieldModel = new FieldModel($db);
            return $fieldModel->getFieldValue($entityType, $entityId, $fieldName);
        } catch (Exception $e) {
            return '';
        }
    }
}

if (!function_exists('the_field')) {
    /**
     * Выводит значение поля (экранированное)
     * 
     * @param string $fieldName Системное имя поля
     * @param string|null $entityType Тип сущности
     * @param int|null $entityId ID сущности
     */
    function the_field($fieldName, $entityType = null, $entityId = null) {
        echo htmlspecialchars(get_field($fieldName, $entityType, $entityId));
    }
}

if (!function_exists('get_field_display')) {
    /**
     * Получает отрендеренное значение поля через FieldManager
     * Возвращает HTML для отображения в зависимости от типа поля
     * 
     * @param string $fieldName Системное имя поля
     * @param string|null $entityType Тип сущности
     * @param int|null $entityId ID сущности
     * @return string HTML-код для отображения
     */
    function get_field_display($fieldName, $entityType = null, $entityId = null) {
        global $db;
        
        if (!$db) {
            return '';
        }
        
        // Определение контекста, если не передан
        if ($entityType === null || $entityId === null) {
            $context = get_field_context();
            $entityType = $entityType ?? $context['entity_type'];
            $entityId = $entityId ?? $context['entity_id'];
        }
        
        if (!$entityType || !$entityId) {
            return '';
        }
        
        try {
            $fieldModel = new FieldModel($db);
            $fieldManager = new FieldManager($db);
            
            // Получение поля из базы
            $field = $fieldModel->getFieldBySystemName($fieldName, $entityType);
            
            if (!$field) {
                return '';
            }
            
            // Получение значения
            $value = $fieldModel->getFieldValue($entityType, $entityId, $fieldName);
            
            if ($value === null || $value === '') {
                return '';
            }
            
            // Рендеринг через FieldManager
            $config = json_decode($field['config'] ?? '{}', true);
            return $fieldManager->renderFieldDisplay(
                $field['type'],
                $value,
                $config,
                $entityType,
                $entityId
            );
            
        } catch (Exception $e) {
            return '';
        }
    }
}

if (!function_exists('the_field_display')) {
    /**
     * Выводит отрендеренное значение поля
     * 
     * @param string $fieldName Системное имя поля
     * @param string|null $entityType Тип сущности
     * @param int|null $entityId ID сущности
     */
    function the_field_display($fieldName, $entityType = null, $entityId = null) {
        echo get_field_display($fieldName, $entityType, $entityId);
    }
}

if (!function_exists('get_field_context')) {
    /**
     * Определяет контекст для полей (автоматически определяет тип сущности и ID)
     * Проверяет глобальные переменные $post, $page, $category, $user
     * 
     * @return array Массив с ключами 'entity_type' и 'entity_id'
     */
    function get_field_context() {
        global $post, $page, $category, $user;
        
        // Проверка глобально установленных переменных
        if (isset($GLOBALS['entity_type']) && isset($GLOBALS['entity_id'])) {
            return [
                'entity_type' => $GLOBALS['entity_type'],
                'entity_id' => $GLOBALS['entity_id']
            ];
        }
        
        if (isset($post) && is_array($post) && isset($post['id'])) {
            return ['entity_type' => 'post', 'entity_id' => $post['id']];
        }
        
        if (isset($page) && is_array($page) && isset($page['id'])) {
            return ['entity_type' => 'page', 'entity_id' => $page['id']];
        }
        
        if (isset($category) && is_array($category) && isset($category['id'])) {
            return ['entity_type' => 'category', 'entity_id' => $category['id']];
        }
        
        if (isset($user) && is_array($user) && isset($user['id'])) {
            return ['entity_type' => 'user', 'entity_id' => $user['id']];
        }
        
        return ['entity_type' => null, 'entity_id' => null];
    }
}

if (!function_exists('has_field')) {
    /**
     * Проверяет, есть ли значение у поля (не пустое после trim)
     * 
     * @param string $fieldName Системное имя поля
     * @param string|null $entityType Тип сущности
     * @param int|null $entityId ID сущности
     * @return bool true если значение не пустое
     */
    function has_field($fieldName, $entityType = null, $entityId = null) {
        $value = get_field($fieldName, $entityType, $entityId);
        return !empty(trim($value));
    }
}

// Шорткоды - упрощенная версия
if (!function_exists('do_shortcodes')) {
    /**
     * Обрабатывает шорткоды в тексте через класс Shortcodes
     * 
     * @param string $content Текст с шорткодами
     * @return string Текст с обработанными шорткодами
     */
    function do_shortcodes($content) {
        if (empty($content)) {
            return $content;
        }
        
        if (!class_exists('Shortcodes')) {
            return $content;
        }
        
        return Shortcodes::process($content);
    }
}

if (!function_exists('do_field_shortcodes')) {
    /**
     * Обрабатывает шорткоды полей в тексте в формате {field_name}
     * Заменяет {field_name} на значение поля
     * 
     * @param string $content Текст с шорткодами полей
     * @param string|null $entityType Тип сущности
     * @param int|null $entityId ID сущности
     * @return string Текст с замененными значениями
     */
    function do_field_shortcodes($content, $entityType = null, $entityId = null) {
        if (empty($content)) {
            return $content;
        }
        
        // Определение контекста, если не передан
        if ($entityType === null || $entityId === null) {
            $context = get_field_context();
            $entityType = $entityType ?? $context['entity_type'];
            $entityId = $entityId ?? $context['entity_id'];
        }
        
        if (!$entityType || !$entityId) {
            return $content;
        }
        
        // Замена {field_name} на значение поля
        return preg_replace_callback('/\{(\w+)\}/', function($matches) use ($entityType, $entityId) {
            $fieldName = $matches[1];
            return get_field_display($fieldName, $entityType, $entityId);
        }, $content);
    }
}

if (!function_exists('get_field_iterable')) {
    /**
     * Получает поле как итерируемый массив
     * Для полей, поддерживающих итерацию (multiselect, repeater и т.д.)
     * 
     * @param string $fieldName Системное имя поля
     * @param string|null $entityType Тип сущности
     * @param int|null $entityId ID сущности
     * @return array Массив элементов для итерации
     */
    function get_field_iterable($fieldName, $entityType = null, $entityId = null) {
        global $db;
        
        if (!$db) {
            return [];
        }
        
        // Определение контекста, если не передан
        if ($entityType === null || $entityId === null) {
            $context = get_field_context();
            $entityType = $entityType ?? $context['entity_type'];
            $entityId = $entityId ?? $context['entity_id'];
        }
        
        if (!$entityType || !$entityId) {
            return [];
        }
        
        try {
            $fieldModel = new FieldModel($db);
            $fieldManager = new FieldManager($db);
            
            // Получение поля из базы
            $field = $fieldModel->getFieldBySystemName($fieldName, $entityType);
            
            if (!$field) {
                return [];
            }
            
            // Получение значения
            $value = $fieldModel->getFieldValue($entityType, $entityId, $fieldName);
            
            if ($value === null || $value === '') {
                return [];
            }
            
            // Создание экземпляра поля
            $config = json_decode($field['config'] ?? '{}', true);
            $fieldInstance = $fieldManager->getFieldInstance($field['type'], $config);
            
            if (!$fieldInstance) {
                return [$value]; // Возвращаем как массив с одним элементом
            }
            
            $fieldInstance->setSystemName($fieldName);
            $fieldInstance->setEntityContext($entityType, $entityId);
            
            // Получение данных для итерации
            return $fieldInstance->getIterableData($value, $entityType, $entityId);
            
        } catch (Exception $e) {
            return [];
        }
    }
}

if (!function_exists('foreach_field')) {
    /**
     * Итерация по значениям поля
     * Удобная обертка для get_field_iterable для использования в циклах
     * 
     * @param string $fieldName Системное имя поля
     * @param string|null $entityType Тип сущности
     * @param int|null $entityId ID сущности
     * @return array Массив для итерации
     * 
     * @example <?php foreach_field('technologies', 'post', $post['id']) as $item): ?>
     */
    function foreach_field($fieldName, $entityType = null, $entityId = null) {
        $data = get_field_iterable($fieldName, $entityType, $entityId);
        return $data;
    }
}

if (!function_exists('field_loop')) {
    /**
     * Альтернативный синтаксис для итерации с дополнительной информацией
     * Возвращает генератор с элементами, обогащенными информацией о позиции
     * 
     * @param string $fieldName Системное имя поля
     * @param string|null $entityType Тип сущности
     * @param int|null $entityId ID сущности
     * @return Generator Генератор для итерации
     * 
     * @example <?php field_loop('technologies', 'post', $post['id']): ?>
     */
    function field_loop($fieldName, $entityType = null, $entityId = null) {
        $items = get_field_iterable($fieldName, $entityType, $entityId);
        $total = count($items);
        $current = 0;
        
        foreach ($items as $item) {
            $current++;
            yield array_merge($item, [
                'current' => $current,
                'total' => $total,
                'is_first' => $current === 1,
                'is_last' => $current === $total,
                'is_even' => $current % 2 === 0,
                'is_odd' => $current % 2 !== 0,
            ]);
        }
    }
}