<?php

namespace forms\actions;

/**
 * Действие получения данных отправки через AJAX
 */
class AdminGetSubmission extends FormAction {
    
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
            // Используем существующее подключение
            $db = $this->db;
            $submission = $db->fetch(
                "SELECT * FROM form_submissions WHERE id = ?",
                [$submissionId]
            );
            
            if (!$submission) {
                throw new \Exception('Отправка не найдена');
            }
            
            // Декодируем данные
            $submission['data'] = json_decode($submission['data'], true) ?: [];
            
            // Получаем файлы
            $files = $db->fetchAll(
                "SELECT * FROM form_files WHERE submission_id = ?",
                [$submissionId]
            );
            $submission['files'] = $files;
            
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'submission' => $submission
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