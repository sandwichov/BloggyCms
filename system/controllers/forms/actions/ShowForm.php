<?php

namespace forms\actions;

/**
 * Действие показа формы (публичное)
 */
class ShowForm extends FormAction {
    
    public function execute() {
        $slug = $this->params['slug'] ?? null;
        if (!$slug) {
            \Notification::error('Форма не указана');
            $this->redirect(BASE_URL);
            return;
        }
        
        $form = $this->formModel->getBySlug($slug);
        if (!$form || $form['status'] !== 'active') {
            \Notification::error('Форма не найдена или неактивна');
            $this->redirect(BASE_URL);
            return;
        }
        
        $this->render('forms/view', [
            'form' => $form,
            'pageTitle' => htmlspecialchars($form['name'])
        ]);
    }
}