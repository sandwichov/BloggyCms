<?php

namespace fields\actions;

/**
 * Действие создания нового дополнительного поля в админ-панели
 * Предоставляет интерфейс для создания пользовательских полей различных типов
 * с возможностью настройки конфигурации и поведения поля
 * 
 * @package fields\actions
 * @extends FieldAction
 */
class AdminCreate extends FieldAction {
    
    /**
     * Метод выполнения создания поля
     * Обрабатывает форму создания поля, валидирует данные и сохраняет поле в систему
     * 
     * @return void
     */
    public function execute() {
        // Получение типа сущности из параметров
        $entityType = $this->params['entityType'] ?? null;
        
        // Проверка наличия типа сущности
        if (!$entityType) {
            \Notification::error('Тип сущности не указан');
            $this->redirect(ADMIN_URL . '/fields');
            return;
        }
        
        // Обработка POST-запроса (отправка формы создания)
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                // Валидация обязательных полей формы
                if (empty($_POST['name']) || empty($_POST['system_name']) || empty($_POST['type'])) {
                    throw new \Exception('Заполните все обязательные поля');
                }
                
                // Обработка конфигурации поля через модель
                $config = $_POST['config'] ?? [];
                $config = $this->fieldModel->processFieldConfig($_POST['type'], $config);
                
                // Подготовка данных для сохранения
                $data = [
                    'name' => $_POST['name'],
                    'system_name' => $_POST['system_name'],
                    'type' => $_POST['type'],
                    'entity_type' => $entityType,
                    'description' => $_POST['description'] ?? '',
                    'is_required' => isset($_POST['is_required']) ? 1 : 0,
                    'is_active' => isset($_POST['is_active']) ? 1 : 1,
                    'sort_order' => $_POST['sort_order'] ?? 0,
                    'show_in_post' => isset($_POST['show_in_post']) ? 1 : 0,
                    'show_in_list' => isset($_POST['show_in_list']) ? 1 : 0,
                    'config' => json_encode($config)
                ];
                
                // Создание поля в базе данных
                $this->fieldModel->create($data);
                
                // Уведомление об успешном создании
                \Notification::success('Поле успешно создано');
                
                // Перенаправление на страницу полей для этой сущности
                $this->redirect(ADMIN_URL . "/fields/entity/{$entityType}");
                
            } catch (\Exception $e) {
                // Обработка ошибок создания поля
                \Notification::error('Ошибка при создании поля: ' . $e->getMessage());
                
                // Получение списка типов полей для повторного отображения формы
                $fieldTypes = $this->fieldModel->getFieldTypes();
                
                // Повторный рендеринг формы с сохраненными данными
                $this->render('admin/fields/form', [
                    'fieldTypes' => $fieldTypes,
                    'entityType' => $entityType,
                    'entityName' => $this->getEntityName($entityType),
                    'data' => $_POST,
                    'pageTitle' => 'Создание поля'
                ]);
            }
        } 
        // Обработка GET-запроса (отображение пустой формы)
        else {
            // Получение списка доступных типов полей
            $fieldTypes = $this->fieldModel->getFieldTypes();
            
            /**
             * Рендеринг формы создания поля
             * 
             * @param string $template Путь к шаблону (admin/fields/form)
             * @param array $data Данные для шаблона:
             * - fieldTypes: массив доступных типов полей
             * - entityType: тип сущности, для которой создается поле
             * - entityName: читаемое имя сущности
             * - pageTitle: заголовок страницы
             */
            $this->render('admin/fields/form', [
                'fieldTypes' => $fieldTypes,
                'entityType' => $entityType,
                'entityName' => $this->getEntityName($entityType),
                'pageTitle' => 'Создание поля'
            ]);
        }
    }
}