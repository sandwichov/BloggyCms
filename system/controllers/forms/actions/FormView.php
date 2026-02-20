<?php

namespace forms\actions;

/**
 * Действие просмотра формы (публичное)
 */
class FormView extends FormAction {
    
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
        
        // Генерируем CSRF токен
        $csrfToken = \FormRenderer::generateToken();
        
        // Получаем настройки формы
        $settings = $form['settings'] ?? [];
        $ajaxEnabled = $settings['ajax_enabled'] ?? true;
        $showLabels = $settings['show_labels'] ?? true;
        $showDescriptions = $settings['show_descriptions'] ?? true;
        $recaptchaSiteKey = $settings['recaptcha_site_key'] ?? '';
        
        // Рендерим форму
        $formHtml = \FormRenderer::render($slug, [
            'class' => 'form-view',
            'ajax' => $ajaxEnabled,
            'show_labels' => $showLabels,
            'show_descriptions' => $showDescriptions,
            'recaptcha' => $recaptchaEnabled,
            'recaptcha_site_key' => $recaptchaSiteKey
        ]);
        
        // Если включена reCAPTCHA, добавляем скрипт
        $additionalScripts = '';
        if ($recaptchaEnabled && $recaptchaSiteKey) {
            $additionalScripts = '
                <script src="https://www.google.com/recaptcha/api.js" async defer></script>
            ';
        }
        
        $this->render('forms/view', [
            'form' => $form,
            'formHtml' => $formHtml,
            'csrfToken' => $csrfToken,
            'ajaxEnabled' => $ajaxEnabled,
            'additionalScripts' => $additionalScripts,
            'pageTitle' => htmlspecialchars($form['name'])
        ]);
    }
}