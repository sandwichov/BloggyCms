<?php

namespace fields\actions;

/**
 * Действие переключения активности поля в админ-панели
 * Включает или отключает дополнительное поле без его удаления из системы
 * Позволяет временно скрыть поле из интерфейса без потери данных
 * 
 * @package fields\actions
 * @extends FieldAction
 */
class AdminToggle extends FieldAction {
    
    /**
     * Метод выполнения переключения активности поля
     * Изменяет статус активности поля и обновляет его в базе данных
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
        
        try {
            // Получение данных поля из базы данных
            $field = $this->fieldModel->getById($id);
            if (!$field) {
                throw new \Exception('Поле не найдено');
            }
            
            // Определение нового статуса активности (инвертирование текущего)
            $newStatus = $field['is_active'] ? 0 : 1;
            
            // Подготовка данных для обновления
            $data = [
                'system_name' => $field['system_name'],
                'name' => $field['name'],
                'type' => $field['type'],
                'description' => $field['description'],
                'is_required' => $field['is_required'],
                'is_active' => $newStatus,
                'sort_order' => $field['sort_order'],
                'config' => $field['config']
            ];
            
            // Обновление статуса активности в базе данных
            $result = $this->fieldModel->update($id, $data);
            
            // Проверка успешности обновления
            if ($result) {
                // Формирование текста уведомления в зависимости от нового статуса
                $statusText = $newStatus ? 'включено' : 'отключено';
                \Notification::success("Поле {$statusText}");
            } else {
                throw new \Exception('Не удалось обновить поле в базе данных');
            }
            
        } catch (\Exception $e) {
            // Обработка исключений при изменении статуса
            \Notification::error('Ошибка при изменении статуса поля: ' . $e->getMessage());
        }
        
        // Перенаправление на соответствующую страницу
        if (isset($field['entity_type'])) {
            // Возврат на страницу полей для соответствующей сущности
            $this->redirect(ADMIN_URL . "/fields/entity/{$field['entity_type']}");
        } else {
            // Возврат на главную страницу управления полями
            $this->redirect(ADMIN_URL . '/fields');
        }
    }
}