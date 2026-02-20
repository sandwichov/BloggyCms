<?php

namespace forms\actions;

/**
 * Действие обновления статуса отправки
 */
class AdminUpdateSubmissionStatus extends FormAction {
    
    public function execute() {
        $submissionId = $this->params['id'] ?? null;
        $status = $_GET['status'] ?? null;
        
        if (!$submissionId || !$status) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Не указаны необходимые параметры'
            ]);
            return;
        }
        
        $validStatuses = ['new', 'read', 'processed', 'spam'];
        if (!in_array($status, $validStatuses)) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Неверный статус'
            ]);
            return;
        }
        
        try {
            $success = $this->formModel->updateSubmissionStatus($submissionId, $status);
            
            if ($success) {
                $statusTexts = [
                    'new' => 'Новый',
                    'read' => 'Прочитан',
                    'processed' => 'Обработан',
                    'spam' => 'Спам'
                ];
                
                // Используем системные уведомления
                \Notification::success('Статус изменен на: ' . $statusTexts[$status]);
                
                $this->jsonResponse([
                    'success' => true,
                    'message' => 'Статус обновлен',
                    'status_text' => $statusTexts[$status]
                ]);
            } else {
                throw new \Exception('Не удалось обновить статус');
            }
            
        } catch (\Exception $e) {
            \Notification::error('Ошибка: ' . $e->getMessage());
            $this->jsonResponse([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        }
    }
}