<?php

namespace forms\actions;

/**
 * Действие удаления отправки
 */
class AdminDeleteSubmission extends FormAction {
    
    public function execute() {
        $submissionId = $this->params['id'] ?? null;
        if (!$submissionId) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'ID отправки не указан'
            ]);
            exit;
        }
        
        try {
            $success = $this->formModel->deleteSubmission($submissionId);
            
            header('Content-Type: application/json');
            if ($success) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Отправка успешно удалена'
                ]);
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Не удалось удалить отправку'
                ]);
            }
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