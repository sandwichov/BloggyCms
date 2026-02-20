<?php

namespace forms\actions;

/**
 * Действие удаления формы
 */
class AdminDelete extends FormAction {
    
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
            
            // Удаляем форму (включая все связанные данные)
            $this->deleteFormWithRelations($id);
            
            \Notification::success('Форма успешно удалена');
            
        } catch (\Exception $e) {
            \Notification::error('Ошибка при удалении формы: ' . $e->getMessage());
        }
        
        $this->redirect(ADMIN_URL . '/forms');
    }
    
    /**
     * Удаляет форму со всеми связанными данными
     */
    private function deleteFormWithRelations($formId) {
        $db = $this->db;
        
        // Начинаем транзакцию
        $db->beginTransaction();
        
        try {
            // 1. Удаляем файлы отправок
            $submissions = $db->fetchAll(
                "SELECT id FROM form_submissions WHERE form_id = ?",
                [$formId]
            );
            
            foreach ($submissions as $submission) {
                // Удаляем файлы
                $files = $db->fetchAll(
                    "SELECT * FROM form_files WHERE submission_id = ?",
                    [$submission['id']]
                );
                
                foreach ($files as $file) {
                    if (file_exists(ROOT_PATH . '/' . $file['file_path'])) {
                        unlink(ROOT_PATH . '/' . $file['file_path']);
                    }
                }
                
                // Удаляем записи о файлах
                $db->delete('form_files', ['submission_id' => $submission['id']]);
                
                // Удаляем отправку
                $db->delete('form_submissions', ['id' => $submission['id']]);
            }
            
            // 2. Удаляем саму форму
            $result = $db->delete('forms', ['id' => $formId]);
            
            if ($result === false) {
                throw new \Exception('Ошибка базы данных при удалении формы');
            }
            
            // Фиксируем транзакцию
            $db->commit();
            
        } catch (\Exception $e) {
            // Откатываем транзакцию при ошибке
            $db->rollBack();
            throw $e;
        }
    }
}