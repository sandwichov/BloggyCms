<?php

namespace fields\actions;

/**
 * Действие удаления дополнительного поля в админ-панели
 * Удаляет пользовательское поле из системы вместе со всеми сохраненными значениями
 * 
 * @package fields\actions
 * @extends FieldAction
 */
class AdminDelete extends FieldAction {
    
    /**
     * Метод выполнения удаления поля
     * Удаляет поле по ID и перенаправляет обратно на соответствующую страницу
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
            // Получение данных поля для определения типа сущности
            $field = $this->fieldModel->getById($id);
            
            // Выполнение удаления поля через модель
            $this->fieldModel->delete($id);
            
            // Уведомление об успешном удалении
            \Notification::success('Поле успешно удалено');
            
        } catch (\Exception $e) {
            // Обработка исключений при удалении
            \Notification::error('Ошибка при удалении поля');
        }
        
        // Перенаправление на соответствующую страницу
        if (isset($field['entity_type'])) {
            // Если известен тип сущности - возвращаем на страницу полей этой сущности
            $this->redirect(ADMIN_URL . "/fields/entity/{$field['entity_type']}");
        } else {
            // Иначе - на главную страницу управления полями
            $this->redirect(ADMIN_URL . '/fields');
        }
    }
}