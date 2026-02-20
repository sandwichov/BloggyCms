<?php

namespace forms\actions;

/**
 * Действие просмотра отправок формы
 */
class AdminShow extends FormAction {
    
    public function execute() {
        $id = $this->params['id'] ?? null;
        if (!$id) {
            \Notification::error('ID формы не указан');
            $this->redirect(ADMIN_URL . '/forms');
            return;
        }
        
        $form = $this->formModel->getById($id);
        if (!$form) {
            \Notification::error('Форма не найдена');
            $this->redirect(ADMIN_URL . '/forms');
            return;
        }
        
        // Получаем параметры пагинации
        $page = $_GET['page'] ?? 1;
        $perPage = 20;
        $offset = ($page - 1) * $perPage;
        
        // Получаем отправки
        $submissions = $this->formModel->getSubmissions($id, $perPage, $offset);
        $totalSubmissions = $this->formModel->getSubmissionsCount($id);
        $totalPages = ceil($totalSubmissions / $perPage);
        
        // Статистика по статусам
        $statusStats = [
            'new' => 0,
            'read' => 0,
            'processed' => 0,
            'spam' => 0
        ];
        
        // В реальном проекте нужно сделать отдельный запрос для статистики
        foreach ($submissions as $submission) {
            if (isset($statusStats[$submission['status']])) {
                $statusStats[$submission['status']]++;
            }
        }
        
        $this->render('admin/forms/show', [
            'form' => $form,
            'submissions' => $submissions,
            'submissionsCount' => $totalSubmissions,
            'newCount' => $statusStats['new'],
            'processedCount' => $statusStats['processed'],
            'spamCount' => $statusStats['spam'],
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'pageTitle' => 'Отправки формы: ' . htmlspecialchars($form['name']),
            'formModel' => $this->formModel
        ]);
    }
}