<?php

namespace forms\actions;

/**
 * Действие обработки формы (публичное)
 */
class ProcessForm extends FormAction {
    
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
        
        try {
            // Получаем данные формы
            $postData = $_POST;
            $filesData = $_FILES;
            
            // Удаляем служебные поля
            unset($postData['csrf_token']);
            
            // Проверка CSRF токена
            $settings = $form['settings'] ?? [];
            $csrfEnabled = $settings['csrf_protection'] ?? true;
            
            if ($csrfEnabled) {
                $token = $_POST['csrf_token'] ?? '';
                if (!$this->verifyCsrfToken($token, $slug)) {
                    throw new \Exception('Неверный токен безопасности. Пожалуйста, обновите страницу и попробуйте снова.');
                }
            }
            
            // Проверка капчи если включена
            $captchaEnabled = $settings['captcha_enabled'] ?? false;
            if ($captchaEnabled) {
                if (!$this->verifyCaptcha($settings)) {
                    throw new \Exception('Проверка капчи не пройдена');
                }
            }
            
            // Проверка лимитов отправок
            if (!empty($settings['limit_submissions'])) {
                if (!$this->checkSubmissionLimits($form['id'], $settings)) {
                    throw new \Exception('Превышен лимит отправок. Попробуйте позже.');
                }
            }
            
            // Валидируем данные
            $errors = $this->validateSubmission($form, $postData, $filesData);
            
            if (!empty($errors)) {
                $errorMessage = is_array($errors) ? implode("\n", array_values($errors)) : $errors;
                throw new \Exception($errorMessage);
            }
            
            $submissionId = null;
            
            // Сохраняем отправку если включено
            $storeSubmissions = $settings['store_submissions'] ?? true;
            if ($storeSubmissions) {
                $submissionId = $this->formModel->saveSubmission($form['id'], $postData, $filesData);
            }
            
            // Отправляем уведомления если есть
            if (!empty($form['notifications'])) {
                $this->sendNotifications($form, $postData, $submissionId);
            }
            
            // Выполняем действия
            if (!empty($form['actions'])) {
                $this->executeActions($form, $postData, $submissionId);
            }
            
            $successMessage = $form['success_message'] ?? 'Форма успешно отправлена!';
            
            // Определяем URL редиректа
            $redirectUrl = null;
            foreach ($form['actions'] ?? [] as $action) {
                if ($action['enabled'] && $action['type'] === 'redirect') {
                    $redirectUrl = $action['url'] ?? null;
                    break;
                }
            }
            
            // Если запрос AJAX или включена AJAX отправка
            $isAjaxRequest = $this->isAjaxRequest();
            $ajaxEnabled = $settings['ajax_enabled'] ?? true;
            
            if ($isAjaxRequest || $ajaxEnabled) {
                $this->jsonResponse([
                    'success' => true,
                    'message' => $successMessage,
                    'submission_id' => $submissionId,
                    'redirect' => $redirectUrl
                ]);
                return;
            }
            
            // Обычная отправка (не AJAX)
            \Notification::success($successMessage);
            
            if ($redirectUrl) {
                $this->redirect($redirectUrl);
            } else {
                // Редирект на страницу формы с сообщением об успехе
                $_SESSION['form_success_' . $form['id']] = $successMessage;
                $this->redirect(BASE_URL . '/form/' . $slug);
            }
            
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            
            $isAjaxRequest = $this->isAjaxRequest();
            $ajaxEnabled = $form['settings']['ajax_enabled'] ?? true;
            
            if ($isAjaxRequest || $ajaxEnabled) {
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
    
    private function verifyCsrfToken($token, $formSlug) {
        // Начинаем сессию если не начата
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $formName = 'form_' . $formSlug;
        
        if (!isset($_SESSION['csrf_tokens'][$formName])) {
            return false;
        }
        
        $storedToken = $_SESSION['csrf_tokens'][$formName];
        
        // Проверяем время жизни токена (1 час)
        if (time() - $storedToken['created_at'] > 3600) {
            unset($_SESSION['csrf_tokens'][$formName]);
            return false;
        }
        
        // Сравниваем токены
        if (!hash_equals($storedToken['token'], $token)) {
            return false;
        }
        
        // Удаляем токен после успешной проверки
        unset($_SESSION['csrf_tokens'][$formName]);
        
        return true;
    }
    
    private function verifyCaptcha($settings) {
        $captchaAnswer = $_POST['captcha_answer'] ?? '';
        $captchaHash = $_POST['captcha_hash'] ?? '';
        
        if (empty($captchaAnswer) || empty($captchaHash)) {
            return false;
        }
        
        $secretKey = $settings['captcha_secret'] ?? 'bloggy_cms_captcha';
        $decrypted = openssl_decrypt(
            $captchaHash,
            'AES-128-ECB',
            $secretKey,
            0
        );
        
        if ($decrypted === false) {
            return false;
        }
        
        // Для текстовых капч сравниваем как строки
        if ($settings['captcha_type'] === 'text' || $settings['captcha_type'] === 'logic') {
            return strtolower(trim($captchaAnswer)) === strtolower(trim($decrypted));
        }
        
        // Для математических капч сравниваем как числа
        return intval($captchaAnswer) === intval($decrypted);
    }
    
    private function checkSubmissionLimits($formId, $settings) {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        
        // Проверка лимита в день
        $maxPerDay = intval($settings['max_submissions_per_day'] ?? 0);
        if ($maxPerDay > 0) {
            $countToday = $this->formModel->getSubmissionsCountToday($formId, $ip);
            if ($countToday >= $maxPerDay) {
                return false;
            }
        }
        
        // Проверка лимита с одного IP
        $maxPerIp = intval($settings['max_submissions_per_ip'] ?? 0);
        if ($maxPerIp > 0) {
            $countByIp = $this->formModel->getSubmissionsCountByIp($formId, $ip);
            if ($countByIp >= $maxPerIp) {
                return false;
            }
        }
        
        return true;
    }
    
    private function validateSubmission($form, $data, $files) {
        $errors = [];
        $structure = $form['structure'] ?? [];
        
        foreach ($structure as $field) {
            $fieldName = $field['name'] ?? '';
            $fieldType = $field['type'] ?? '';
            $fieldLabel = $field['label'] ?? $fieldName;
            $required = !empty($field['required']);
            
            if ($fieldType === 'submit' || $fieldType === 'hidden') {
                continue;
            }
            
            $value = $data[$fieldName] ?? '';
            $file = $files[$fieldName] ?? null;
            
            if ($required) {
                if ($fieldType === 'file') {
                    if (!$file || $file['error'] === UPLOAD_ERR_NO_FILE) {
                        $errors[$fieldName] = "Поле '{$fieldLabel}' обязательно для заполнения";
                        continue;
                    }
                } elseif (empty($value) && $value !== '0') {
                    $errors[$fieldName] = "Поле '{$fieldLabel}' обязательно для заполнения";
                    continue;
                }
            }
            
            // Если поле не обязательно и пустое - пропускаем остальные проверки
            if (!$required && empty($value) && $value !== '0' && (!$file || $file['error'] === UPLOAD_ERR_NO_FILE)) {
                continue;
            }
            
            // Валидация по типу поля
            if (!empty($value)) {
                switch ($fieldType) {
                    case 'email':
                        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                            $errors[$fieldName] = "Поле '{$fieldLabel}' должно содержать корректный email";
                        }
                        break;
                    case 'number':
                        if (!is_numeric($value)) {
                            $errors[$fieldName] = "Поле '{$fieldLabel}' должно содержать число";
                        }
                        break;
                    case 'tel':
                        if (!preg_match('/^[\d\s\-\+\(\)]+$/', $value)) {
                            $errors[$fieldName] = "Поле '{$fieldLabel}' должно содержать корректный номер телефона";
                        }
                        break;
                }
            }
            
            // Валидация файлов
            if ($fieldType === 'file' && $file && $file['error'] === UPLOAD_ERR_OK) {
                $maxSize = 5 * 1024 * 1024; // 5MB
                if ($file['size'] > $maxSize) {
                    $errors[$fieldName] = "Файл слишком большой. Максимальный размер: 5MB";
                }
                
                // Проверка типа файла
                $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];
                if (!in_array($file['type'], $allowedTypes)) {
                    $errors[$fieldName] = "Разрешены только JPEG, PNG, GIF и PDF файлы";
                }
            }
        }
        
        return $errors;
    }
    
    private function sendNotifications($form, $data, $submissionId) {
        $notifications = $form['notifications'] ?? [];
        
        foreach ($notifications as $notification) {
            if (empty($notification['enabled'])) {
                continue;
            }
            
            $to = $this->parseTemplate($notification['to'] ?? '', $data);
            $subject = $this->parseTemplate($notification['subject'] ?? 'Новая отправка формы', $data);
            $message = $this->parseTemplate($notification['message'] ?? '', $data);
            
            // Определяем отправителя
            $from = $notification['from'] ?? 'noreply@' . $_SERVER['HTTP_HOST'];
            
            // Отправляем email
            $headers = "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
            $headers .= "From: " . $from . "\r\n";
            $headers .= "Reply-To: " . $from . "\r\n";
            
            // Используем системную функцию отправки email если есть
            if (function_exists('send_email')) {
                send_email($to, $subject, $message, $headers);
            } else {
                mail($to, $subject, $message, $headers);
            }
        }
    }
    
    private function parseTemplate($template, $data) {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $value = implode(', ', $value);
            }
            $template = str_replace('{' . $key . '}', htmlspecialchars($value), $template);
        }
        
        // Системные переменные
        $template = str_replace('{date}', date('d.m.Y H:i'), $template);
        $template = str_replace('{time}', date('H:i'), $template);
        $template = str_replace('{ip}', $_SERVER['REMOTE_ADDR'] ?? '', $template);
        $template = str_replace('{base_url}', BASE_URL, $template);
        
        return $template;
    }
    
    private function executeActions($form, $data, $submissionId) {
        $actions = $form['actions'] ?? [];
        
        foreach ($actions as $action) {
            if (empty($action['enabled'])) {
                continue;
            }
            
            switch ($action['type']) {
                case 'redirect':
                    // Обрабатывается в основном потоке
                    break;
                    
                case 'webhook':
                    $this->executeWebhook($action, $form, $data, $submissionId);
                    break;
                    
                case 'save_to_db':
                    // Уже выполнено
                    break;
            }
        }
    }
    
    private function executeWebhook($action, $form, $data, $submissionId) {
        $webhookData = [
            'form_id' => $form['id'],
            'form_name' => $form['name'],
            'submission_id' => $submissionId,
            'data' => $data,
            'timestamp' => time(),
            'ip' => $_SERVER['REMOTE_ADDR'] ?? ''
        ];
        
        $ch = curl_init($action['url']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($webhookData));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'User-Agent: BloggyCMS/1.0'
        ]);
        
        if (!empty($action['headers'])) {
            $headers = [];
            foreach ($action['headers'] as $key => $value) {
                $headers[] = $key . ': ' . $value;
            }
            curl_setopt($ch, CURLOPT_HTTPHEADER, array_merge(
                ['Content-Type: application/json'],
                $headers
            ));
        }
        
        curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        // Логируем результат
        if ($httpCode >= 400) {
            error_log('Webhook error: HTTP ' . $httpCode . ' for form ' . $form['id']);
        }
    }
}