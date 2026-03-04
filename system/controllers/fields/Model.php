<?php

/**
 * Модель дополнительных полей
 * Управляет созданием, хранением и отображением пользовательских полей для различных сущностей системы
 * Обеспечивает интеграцию с FieldManager для обработки различных типов полей
 * 
 * @package models
 */
class FieldModel implements ModelAPI {

    use APIAware;

    protected $allowedAPIMethods = [
        'getByEntityType',
        'getActiveByEntityType',
        'getFieldBySystemName',
        'getFieldValue',
        'getFieldTypes',
        'renderFieldDisplay',
        'renderFieldList'
    ];
    
    /**
     * @var Database Объект подключения к базе данных
     */
    private $db;
    
    /**
     * @var FieldManager Менеджер полей для обработки типов и значений
     */
    private $fieldManager;
    
    /**
     * Конструктор модели полей
     * Инициализирует подключение к БД и создает экземпляр менеджера полей
     *
     * @param Database $db Объект подключения к базе данных
     */
    public function __construct($db) {
        $this->db = $db;
        $this->fieldManager = new FieldManager($db);
    }
    
    /**
     * Получение всех полей системы с количеством использования
     * Возвращает полный список всех пользовательских полей с информацией о частоте использования
     *
     * @return array Массив полей с данными об использовании
     */
    public function getAll() {
        return $this->db->fetchAll("
            SELECT f.*, 
                   COUNT(fv.id) as usage_count 
            FROM fields f 
            LEFT JOIN field_values fv ON f.id = fv.field_id 
            GROUP BY f.id 
            ORDER BY f.entity_type, f.sort_order, f.name
        ");
    }

    /**
     * Получение поля по системному имени и типу сущности
     * Используется для доступа к полю по его уникальному идентификатору в рамках сущности
     *
     * @param string $systemName Уникальное системное имя поля
     * @param string $entityType Тип сущности (post, category, user и т.д.)
     * @return array|null Данные поля или null если не найдено
     */
    public function getFieldBySystemName($systemName, $entityType) {
        return $this->db->fetch(
            "SELECT * FROM fields WHERE system_name = ? AND entity_type = ?",
            [$systemName, $entityType]
        );
    }
    
    /**
     * Получение всех полей для указанного типа сущности
     * Возвращает все поля, связанные с конкретным типом сущности
     *
     * @param string $entityType Тип сущности
     * @return array Массив полей для сущности
     */
    public function getByEntityType($entityType) {
        return $this->db->fetchAll("
            SELECT * FROM fields 
            WHERE entity_type = ? 
            ORDER BY sort_order, name
        ", [$entityType]);
    }
    
    /**
     * Получение активных полей для указанного типа сущности
     * Возвращает только включенные поля для сущности
     *
     * @param string $entityType Тип сущности
     * @return array Массив активных полей
     */
    public function getActiveByEntityType($entityType) {
        return $this->db->fetchAll("
            SELECT * FROM fields 
            WHERE entity_type = ? AND is_active = 1 
            ORDER BY sort_order, name
        ", [$entityType]);
    }
    
    /**
     * Получение поля по ID
     * Возвращает данные поля по его идентификатору в базе данных
     *
     * @param int $id ID поля
     * @return array|null Данные поля или null если не найдено
     */
    public function getById($id) {
        return $this->db->fetch("SELECT * FROM fields WHERE id = ?", [$id]);
    }
    
    /**
     * Создание нового поля
     * Добавляет новое пользовательское поле в систему с проверкой уникальности
     *
     * @param array $data Массив данных поля:
     *                    - system_name: уникальное системное имя (обязательно)
     *                    - entity_type: тип сущности (обязательно)
     *                    - name: отображаемое имя
     *                    - type: тип поля (string, text, select и т.д.)
     *                    - description: описание поля
     *                    - is_required: обязательность заполнения
     *                    - is_active: активность поля
     *                    - sort_order: порядок сортировки
     *                    - config: конфигурация поля в JSON
     *                    - show_in_post: показывать в полном просмотре
     *                    - show_in_list: показывать в списках
     * @return int ID созданного поля
     * @throws Exception При ошибках валидации или дублировании системного имени
     */
    public function create($data) {
        // Проверка обязательных полей
        if (!isset($data['system_name']) || !isset($data['entity_type'])) {
            throw new Exception('Отсутствуют обязательные поля system_name или entity_type');
        }
        
        // Проверка уникальности системного имени
        $existing = $this->db->fetch(
            "SELECT COUNT(*) as count FROM fields WHERE system_name = ? AND entity_type = ?",
            [$data['system_name'], $data['entity_type']]
        );
        
        if ($existing['count'] > 0) {
            throw new Exception('Поле с таким системным именем уже существует для этого типа сущности');
        }
        
        $sql = "INSERT INTO fields (name, system_name, type, entity_type, description, is_required, is_active, sort_order, config, show_in_post, show_in_list) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $this->db->query($sql, [
            $data['name'] ?? '',
            $data['system_name'],
            $data['type'] ?? 'string',
            $data['entity_type'],
            $data['description'] ?? '',
            $data['is_required'] ?? 0,
            $data['is_active'] ?? 1,
            $data['sort_order'] ?? 0,
            $data['config'] ?? '{}',
            $data['show_in_post'] ?? 1,
            $data['show_in_list'] ?? 0 
        ]);
        
        return $this->db->lastInsertId();
    }
    
    /**
     * Обновление существующего поля
     * Изменяет данные поля с проверкой уникальности системного имени
     *
     * @param int $id ID обновляемого поля
     * @param array $data Массив данных для обновления (аналогично create)
     * @return bool Результат выполнения запроса
     * @throws Exception При ошибках валидации или дублировании системного имени
     */
    public function update($id, $data) {
        // Проверка наличия системного имени
        if (!isset($data['system_name'])) {
            throw new Exception('Отсутствует обязательное поле system_name');
        }
        
        // Получение текущих данных поля
        $currentField = $this->getById($id);
        if (!$currentField) {
            throw new Exception('Поле не найдено');
        }
        
        // Проверка уникальности системного имени
        $existing = $this->db->fetch(
            "SELECT COUNT(*) as count FROM fields WHERE system_name = ? AND entity_type = ? AND id != ?",
            [$data['system_name'], $currentField['entity_type'], $id]
        );
        
        if ($existing['count'] > 0) {
            throw new Exception('Поле с таким системным именем уже существует для этого типа сущности');
        }
        
        $sql = "UPDATE fields SET 
            name = ?, system_name = ?, type = ?, description = ?, 
            is_required = ?, is_active = ?, sort_order = ?, config = ?, 
            show_in_post = ?, show_in_list = ? 
            WHERE id = ?";
        
        return $this->db->query($sql, [
            $data['name'] ?? '',
            $data['system_name'],
            $data['type'] ?? 'string',
            $data['description'] ?? '',
            $data['is_required'] ?? 0,
            $data['is_active'] ?? 0,
            $data['sort_order'] ?? 0,
            $data['config'] ?? '{}',
            $data['show_in_post'] ?? 1,
            $data['show_in_list'] ?? 0,
            $id
        ]);
    }
    
    /**
     * Удаление поля
     * Удаляет поле и все его значения из системы
     *
     * @param int $id ID удаляемого поля
     * @return bool Результат выполнения запроса
     */
    public function delete($id) {
        // Сначала удаляем значения поля
        $this->db->query("DELETE FROM field_values WHERE field_id = ?", [$id]);
        // Затем само поле
        return $this->db->query("DELETE FROM fields WHERE id = ?", [$id]);
    }
    
    /**
     * Получение количества полей для типа сущности
     * Подсчитывает общее число полей для указанного типа сущности
     *
     * @param string $entityType Тип сущности
     * @return int Количество полей
     */
    public function getCountByEntityType($entityType) {
        $result = $this->db->fetch(
            "SELECT COUNT(*) as count FROM fields WHERE entity_type = ?",
            [$entityType]
        );
        return $result['count'];
    }
    
    /**
     * Получение доступных типов полей
     * Возвращает список всех поддерживаемых типов полей из FieldManager
     *
     * @return array Массив типов полей
     */
    public function getFieldTypes() {
        return $this->fieldManager->getAvailableFieldTypes();
    }
    
    /**
     * Генерация HTML-кода для ввода значения поля
     * Создает форму ввода для конкретного поля с учетом его типа и конфигурации
     *
     * @param array $field Данные поля
     * @param mixed $value Текущее значение поля
     * @param string $entityType Тип сущности
     * @param int $entityId ID сущности
     * @return string HTML-код поля ввода
     */
    public function renderFieldInput($field, $value, $entityType, $entityId): string {
        $config = json_decode($field['config'] ?? '{}', true);
        return $this->fieldManager->renderFieldInput(
            $field['type'],
            $field['system_name'],
            $value,
            $config,
            $entityType,
            $entityId
        );
    }
    
    /**
     * Генерация HTML-кода для отображения значения поля
     * Форматирует значение поля для отображения на странице
     *
     * @param array $field Данные поля
     * @param mixed $value Значение поля
     * @param string $entityType Тип сущности
     * @param int $entityId ID сущности
     * @return string HTML-код отображения значения
     */
    public function renderFieldDisplay($field, $value, $entityType, $entityId): string {
        $config = json_decode($field['config'] ?? '{}', true);
        return $this->fieldManager->renderFieldDisplay(
            $field['type'],
            $value,
            $config,
            $entityType,
            $entityId
        );
    }
    
    /**
     * Генерация HTML-кода для отображения значения поля в списке
     * Создает компактное представление значения для использования в таблицах и списках
     *
     * @param array $field Данные поля
     * @param mixed $value Значение поля
     * @param string $entityType Тип сущности
     * @param int $entityId ID сущности
     * @return string HTML-код отображения в списке
     */
    public function renderFieldList($field, $value, $entityType, $entityId): string {
        $config = json_decode($field['config'] ?? '{}', true);
        return $this->fieldManager->renderFieldList(
            $field['type'],
            $value,
            $config,
            $entityType,
            $entityId
        );
    }
    
    /**
     * Получение значения поля для конкретной сущности
     * Извлекает сохраненное значение поля по системному имени и идентификаторам сущности
     *
     * @param string $entityType Тип сущности
     * @param int $entityId ID сущности
     * @param string $fieldSystemName Системное имя поля
     * @return mixed Значение поля или null если не найдено
     */
    public function getFieldValue($entityType, $entityId, $fieldSystemName) {
        $result = $this->db->fetch("
            SELECT fv.value 
            FROM field_values fv 
            JOIN fields f ON fv.field_id = f.id 
            WHERE fv.entity_type = ? AND fv.entity_id = ? AND f.system_name = ?
        ", [$entityType, $entityId, $fieldSystemName]);
        
        return $result ? $result['value'] : null;
    }

    /**
     * Сохранение значения поля
     * Обрабатывает и сохраняет значение поля через FieldManager
     * Поддерживает обновление существующих значений и создание новых
     *
     * @param int $fieldId ID поля
     * @param string $entityType Тип сущности
     * @param int $entityId ID сущности
     * @param mixed $value Сохраняемое значение
     * @param string|null $fieldType Тип поля (для обработки значения)
     * @param array $config Конфигурация поля
     * @return bool Результат выполнения запроса
     */
    public function saveFieldValue($fieldId, $entityType, $entityId, $value, $fieldType = null, $config = []) {

        $existing = $this->db->fetch(
            "SELECT id FROM field_values WHERE field_id = ? AND entity_type = ? AND entity_id = ?",
            [$fieldId, $entityType, $entityId]
        );
        
        if ($value === null) {

            if ($existing) {
                $result = $this->db->query(
                    "DELETE FROM field_values WHERE field_id = ? AND entity_type = ? AND entity_id = ?",
                    [$fieldId, $entityType, $entityId]
                );
                return $result;
            }
            return true;
        }
        
        if ($existing) {
            $result = $this->db->query(
                "UPDATE field_values SET value = ?, updated_at = CURRENT_TIMESTAMP 
                WHERE field_id = ? AND entity_type = ? AND entity_id = ?",
                [$value, $fieldId, $entityType, $entityId]
            );
            return $result;
        } else {
            $result = $this->db->query(
                "INSERT INTO field_values (field_id, entity_type, entity_id, value) 
                VALUES (?, ?, ?, ?)",
                [$fieldId, $entityType, $entityId, $value]
            );
            return $result;
        }
    }

    /**
     * Обработка конфигурации поля
     * Валидирует и форматирует конфигурацию поля через FieldManager
     *
     * @param string $fieldType Тип поля
     * @param array $config Конфигурация поля
     * @return array Обработанная конфигурация
     */
    public function processFieldConfig($fieldType, $config) {
        return $this->fieldManager->processFieldConfig($fieldType, $config);
    }

    /**
     * Валидация значения поля
     * Проверяет корректность значения для конкретного типа поля
     *
     * @param string $fieldType Тип поля
     * @param mixed $value Проверяемое значение
     * @param array $config Конфигурация поля
     * @return bool Результат валидации
     */
    public function validateFieldValue($fieldType, $value, $config = []): bool {
        return $this->fieldManager->validateFieldValue($fieldType, $value, $config);
    }
}