<?php

namespace forms\actions;

/**
 * Действие главной страницы управления формами
 */
class AdminIndex extends FormAction {
    
    public function execute() {
        $forms = $this->formModel->getAll();
        $statistics = $this->formModel->getStatistics();
        
        $this->render('admin/forms/index', [
            'forms' => $forms,
            'statistics' => $statistics,
            'pageTitle' => 'Управление формами',
            'formModel' => $this->formModel
        ]);
    }
}