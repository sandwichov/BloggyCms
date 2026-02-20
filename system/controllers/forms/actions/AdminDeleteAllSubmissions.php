<?php

namespace forms\actions;

/**
 * Действие удаления всех отправок формы
 */
class AdminDeleteAllSubmissions extends FormAction {
    
    public function execute() {
        $formId = $this->params['id'] ?? null;
        if (!$formId) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'ID формы не указан'
            ]);
            exit;
        }
        
        try {
            // Получаем все отправки формы
            $submissions = $this->db->fetchAll(
                "SELECT id FROM form_submissions WHERE form_id = ?",
                [$formId]
            );
            
            $deletedCount = 0;
            
            // Удаляем каждую отправку
            foreach ($submissions as $submission) {
                if ($this->formModel->deleteSubmission($submission['id'])) {
                    $deletedCount++;
                }
            }
            
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'message' => 'Все отправки удалены',
                'count' => $deletedCount
            ]);
            exit;
            
        } catch (\Exception $e) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => $e->getMessage()
            ]);
            exit;
        }
    }
}