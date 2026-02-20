<?php

namespace forms\actions;

/**
 * Действие переключения статуса формы
 */
class AdminToggleStatus extends FormAction {
    
    public function execute() {
        $id = $this->params['id'] ?? null;
        if (!$id) {
            \Notification::error('ID формы не указан');
            $this->redirect(ADMIN_URL . '/forms');
            return;
        }
        
        try {
            $form = $this->formModel->getById($id);
            if (!$form) {
                throw new \Exception('Форма не найдена');
            }
            
            // Определяем новый статус
            $newStatus = ($form['status'] === 'active') ? 'inactive' : 'active';
            
            // Обновляем статус
            $success = $this->formModel->update($id, [
                'status' => $newStatus,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            
            if ($success) {
                $statusText = $newStatus === 'active' ? 'активна' : 'неактивна';
                \Notification::success('Форма теперь ' . $statusText);
            } else {
                throw new \Exception('Не удалось изменить статус формы');
            }
            
        } catch (\Exception $e) {
            \Notification::error('Ошибка: ' . $e->getMessage());
        }
        
        // Перенаправление на страницу управления формами
        $this->redirect(ADMIN_URL . '/forms');
    }
}