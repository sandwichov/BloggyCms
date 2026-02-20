<?php

namespace forms\actions;

/**
 * Действие экспорта отправок в CSV
 */
class AdminExport extends FormAction {
    
    public function execute() {
        $formId = $this->params['id'] ?? null;
        if (!$formId) {
            \Notification::error('ID формы не указан');
            $this->redirect(ADMIN_URL . '/forms');
            return;
        }
        
        $form = $this->formModel->getById($formId);
        if (!$form) {
            \Notification::error('Форма не найдена');
            $this->redirect(ADMIN_URL . '/forms');
            return;
        }
        
        // Генерируем CSV
        $csvContent = $this->formModel->exportSubmissionsToCSV($formId);
        
        if (empty($csvContent)) {
            \Notification::warning('Нет данных для экспорта');
            $this->redirect(ADMIN_URL . '/forms/show/' . $formId);
            return;
        }
        
        // Отправляем CSV файл с правильными заголовками
        $filename = 'form-submissions-' . $form['slug'] . '-' . date('Y-m-d') . '.csv';
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($csvContent));
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        echo $csvContent;
        exit;
    }
}