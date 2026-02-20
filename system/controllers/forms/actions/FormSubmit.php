<?php

namespace forms\actions;

/**
 * Действие отправки формы (публичное)
 */
class FormSubmit extends FormAction {
    
    public function execute() {
        $slug = $this->params['slug'] ?? null;
        if (!$slug) {
            if ($this->isAjaxRequest()) {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Форма не указана'
                ]);
            } else {
                \Notification::error('Форма не указана');
                $this->redirect(BASE_URL);
            }
            return;
        }
        
        // Получаем форму
        $form = $this->formModel->getBySlug($slug);
        if (!$form || $form['status'] !== 'active') {
            if ($this->isAjaxRequest()) {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Форма не найдена или неактивна'
                ]);
            } else {
                \Notification::error('Форма не найдена или неактивна');
                $this->redirect(BASE_URL);
            }
            return;
        }
        
        // Проверяем метод запроса
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            if ($this->isAjaxRequest()) {
                $this->jsonResponse([
                    'success' => false,
                    'message' => 'Неверный метод запроса'
                ]);
            } else {
                \Notification::error('Неверный метод запроса');
                $this->redirect(BASE_URL . '/form/' . $slug);
            }
            return;
        }
        
        // Проверяем CSRF токен если включена AJAX отправка
        if (($form['settings']['ajax_enabled'] ?? true) && !$this->isAjaxRequest()) {
            $csrfToken = $_POST['csrf_token'] ?? '';
            if (!\FormRenderer::verifyToken($csrfToken)) {
                if ($this->isAjaxRequest()) {
                    $this->jsonResponse([
                        'success' => false,
                        'message' => 'Неверный токен безопасности'
                    ]);
                } else {
                    \Notification::error('Неверный токен безопасности');
                    $this->redirect(BASE_URL . '/form/' . $slug);
                }
                return;
            }
        }
        
        try {
            // Получаем данные формы
            $postData = $_POST;
            $filesData = $_FILES;
            
            // Удаляем служебные поля
            unset($postData['form_id'], $postData['form_slug'], $postData['csrf_token']);
            
            // Валидируем данные
            $errors = \FormRenderer::validateSubmission($form, $postData, $filesData);
            
            if (!empty($errors)) {
                $errorMessage = is_array($errors) ? implode("\n", $errors) : $errors;
                throw new \Exception($errorMessage);
            }
            
            $submissionId = null;
            
            // Сохраняем отправку если включено
            if (!empty($form['settings']['store_submissions'])) {
                $submissionId = $this->formModel->saveSubmission($form['id'], $postData, $filesData);
            }
            
            // Отправляем уведомления если есть
            if (!empty($form['notifications'])) {
                \FormRenderer::sendNotifications($form, $postData, $submissionId);
            }
            
            // Выполняем действия
            if (!empty($form['actions'])) {
                \FormRenderer::executeActions($form, $postData, $submissionId);
            }
            
            // Определяем сообщение об успехе
            $successMessage = $form['success_message'] ?? 'Форма успешно отправлена!';
            
            // Определяем URL редиректа
            $redirectUrl = null;
            foreach ($form['actions'] ?? [] as $action) {
                if ($action['enabled'] && $action['type'] === 'redirect') {
                    $redirectUrl = $action['url'] ?? null;
                    break;
                }
            }
            
            // Если запрос AJAX, возвращаем JSON
            if ($this->isAjaxRequest() || ($form['settings']['ajax_enabled'] ?? true)) {
                $this->jsonResponse([
                    'success' => true,
                    'message' => $successMessage,
                    'submission_id' => $submissionId,
                    'redirect' => $redirectUrl
                ]);
                return;
            }
            
            // Иначе показываем сообщение и редиректим
            \Notification::success($successMessage);
            
            if ($redirectUrl) {
                $this->redirect($redirectUrl);
            } else {
                $this->redirect(BASE_URL . '/form/' . $slug);
            }
            
        } catch (\Exception $e) {
            // Обработка ошибок
            $errorMessage = $e->getMessage();
            
            if ($this->isAjaxRequest() || ($form['settings']['ajax_enabled'] ?? true)) {
                $this->jsonResponse([
                    'success' => false,
                    'message' => $errorMessage
                ]);
            } else {
                \Notification::error($errorMessage);
                $this->redirect(BASE_URL . '/form/' . $slug);
            }
        }
    }
    
}