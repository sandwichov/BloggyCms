<?php

namespace forms\actions;

/**
 * Действие предварительного просмотра формы
 */
class AdminPreview extends FormAction {
    
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
        
        // Получаем настройки формы
        $settings = $form['settings'] ?? [];
        
        // Рендерим форму для предпросмотра с использованием шаблона
        $formHtml = \FormRenderer::render($form['slug'], [
            'class' => 'form-preview',
            'ajax' => $settings['ajax_enabled'] ?? false,
            'show_labels' => $settings['show_labels'] ?? true,
            'show_descriptions' => $settings['show_descriptions'] ?? true,
            'captcha' => $settings['captcha_enabled'] ?? false,
            'csrf_protection' => $settings['csrf_protection'] ?? true
        ]);
        
        $this->render('admin/forms/preview', [
            'form' => $form,
            'formHtml' => $formHtml,
            'pageTitle' => 'Предпросмотр формы: ' . htmlspecialchars($form['name'])
        ]);
    }
}