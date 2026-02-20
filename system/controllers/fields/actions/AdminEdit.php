<?php

namespace fields\actions;

/**
 * Действие редактирования дополнительного поля в админ-панели
 * Предоставляет интерфейс для изменения параметров существующего пользовательского поля
 * Обеспечивает обработку конфигурации поля через FieldManager
 * 
 * @package fields\actions
 * @extends FieldAction
 */
class AdminEdit extends FieldAction {
    
    /**
     * Метод выполнения редактирования поля
     * Обрабатывает форму редактирования, обновляет данные поля и его конфигурацию
     * 
     * @return void
     */
    public function execute() {
        // Получение ID поля из параметров
        $id = $this->params['id'] ?? null;
        
        // Проверка наличия ID поля
        if (!$id) {
            \Notification::error('ID поля не указан');
            $this->redirect(ADMIN_URL . '/fields');
            return;
        }
        
        // Получение данных поля из базы данных
        $field = $this->fieldModel->getById($id);
        if (!$field) {
            \Notification::error('Поле не найдено');
            $this->redirect(ADMIN_URL . '/fields');
            return;
        }
        
        // Декодирование конфигурации из JSON
        $config = json_decode($field['config'] ?? '{}', true);
        
        // Обработка POST-запроса (отправка формы редактирования)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                // Получение конфигурации из формы
                $config = $_POST['config'] ?? [];
                
                // ВАЖНО: Обработка конфигурации через FieldManager
                // Это обеспечивает валидацию и форматирование конфигурации в соответствии с типом поля
                $config = $this->fieldModel->processFieldConfig($_POST['type'], $config);
                
                // Подготовка данных для обновления
                $data = [
                    'name' => $_POST['name'],
                    'system_name' => $_POST['system_name'],
                    'type' => $_POST['type'],
                    'description' => $_POST['description'] ?? '',
                    'is_required' => isset($_POST['is_required']) ? 1 : 0,
                    'is_active' => isset($_POST['is_active']) ? 1 : 0,
                    'sort_order' => $_POST['sort_order'] ?? 0,
                    'show_in_post' => isset($_POST['show_in_post']) ? 1 : 0,
                    'show_in_list' => isset($_POST['show_in_list']) ? 1 : 0,
                    'config' => json_encode($config)
                ];
                
                // Обновление поля в базе данных
                $this->fieldModel->update($id, $data);
                
                // Уведомление об успешном обновлении
                \Notification::success('Поле успешно обновлено');
                
                // Перенаправление на страницу полей для соответствующей сущности
                $this->redirect(ADMIN_URL . "/fields/entity/{$field['entity_type']}");
                
            } catch (\Exception $e) {
                // Обработка ошибок обновления
                \Notification::error('Ошибка при обновлении поля: ' . $e->getMessage());
            }
        }
        
        // Получение списка доступных типов полей
        $fieldTypes = $this->fieldModel->getFieldTypes();
        
        // Подготовка конфигурации для отображения в форме
        if (!empty($field['config'])) {
            $decodedConfig = json_decode($field['config'], true);
            
            // Использование FieldManager для подготовки конфигурации к отображению в форме
            $fieldManager = new \FieldManager($this->db);
            $fieldInstance = $fieldManager->getFieldInstance($field['type'], $decodedConfig);
            
            // Если у поля есть метод подготовки конфигурации для формы, используем его
            if ($fieldInstance && method_exists($fieldInstance, 'prepareConfigForForm')) {
                $decodedConfig = $fieldInstance->prepareConfigForForm($decodedConfig);
                $field['config'] = json_encode($decodedConfig);
            }
        }
        
        /**
         * Рендеринг формы редактирования поля
         * 
         * @param string $template Путь к шаблону (admin/fields/form)
         * @param array $data Данные для шаблона:
         * - field: данные редактируемого поля
         * - fieldTypes: массив доступных типов полей
         * - entityType: тип сущности, к которой относится поле
         * - entityName: читаемое имя сущности
         * - pageTitle: заголовок страницы
         */
        $this->render('admin/fields/form', [
            'field' => $field,
            'fieldTypes' => $fieldTypes,
            'entityType' => $field['entity_type'],
            'entityName' => $this->getEntityName($field['entity_type']),
            'pageTitle' => 'Редактирование поля'
        ]);
    }
}