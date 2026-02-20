<?php

namespace forms\actions;

/**
 * Действие редактирования существующей формы
 */
class AdminEdit extends FormAction {
    
    public function execute() {
        $id = $this->params['id'] ?? null;
        if (!$id) {
            \Notification::error('ID формы не указан');
            $this->redirect(ADMIN_URL . '/forms');
            return;
        }
        
        // Получаем форму из базы данных
        $form = $this->formModel->getById($id);
        if (!$form) {
            \Notification::error('Форма не найдена');
            $this->redirect(ADMIN_URL . '/forms');
            return;
        }
        
        // Получаем доступные шаблоны и текущую тему
        $templates = $this->controller->getAvailableTemplates();
        $currentTheme = $this->controller->getCurrentTheme();
        
        // Декодируем данные из JSON
        $formStructure = $form['structure'] ?? [];
        $settings = $form['settings'] ?? $this->getFormSettings();
        $notifications = $form['notifications'] ?? [];
        $actions = $form['actions'] ?? [];
        
        // Обработка POST-запроса
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                // Валидация обязательных полей
                if (empty(trim($_POST['name']))) {
                    throw new \Exception('Название формы обязательно');
                }
                
                // Декодирование и валидация структуры формы
                $formStructure = json_decode($_POST['form_structure'] ?? '[]', true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    throw new \Exception('Ошибка при разборе структуры формы: ' . json_last_error_msg());
                }
                
                list($isValid, $validationErrors) = $this->formModel->validateFormStructure($formStructure);
                
                if (!$isValid) {
                    throw new \Exception('Ошибки в структуре формы: ' . implode(', ', $validationErrors));
                }
                
                // Проверяем уникальность имен полей
                $fieldNames = [];
                foreach ($formStructure as $field) {
                    if (!empty($field['name']) && $field['type'] !== 'submit') {
                        if (in_array($field['name'], $fieldNames)) {
                            throw new \Exception('Имя поля "' . $field['name'] . '" используется несколько раз');
                        }
                        $fieldNames[] = $field['name'];
                    }
                }
                
                // Подготавливаем структуру формы с дополнительными данными
                $processedStructure = $this->processFormStructure($formStructure, $_POST);
                
                // Подготавливаем настройки (сохраняем ВСЕ настройки)
                $settings = $this->prepareSettings($_POST, $settings);
                
                // Подготавливаем уведомления
                $notifications = $this->prepareNotifications($_POST, $notifications);
                
                // Подготавливаем действия
                $actions = $this->prepareActions($_POST, $actions);
                
                // Подготовка данных для обновления
                $formData = [
                    'name' => trim($_POST['name']),
                    'description' => trim($_POST['description'] ?? ''),
                    'template' => $_POST['template'] ?? 'default',
                    'structure' => $processedStructure,
                    'settings' => $settings,
                    'success_message' => trim($_POST['success_message'] ?? 'Форма успешно отправлена!'),
                    'error_message' => trim($_POST['error_message'] ?? 'Произошла ошибка при отправке формы.'),
                    'status' => $_POST['status'] ?? 'active',
                    'notifications' => $notifications,
                    'actions' => $actions,
                    'updated_at' => date('Y-m-d H:i:s')
                ];
                
                // Обновление формы
                $success = $this->formModel->update($id, $formData);
                
                if ($success) {
                    \Notification::success('Форма успешно обновлена');
                    
                    // Обновляем данные формы из базы данных
                    $form = $this->formModel->getById($id);
                    
                    // Перенаправляем на страницу редактирования с обновленными данными
                    $this->redirect(ADMIN_URL . '/forms/edit/' . $id);
                } else {
                    throw new \Exception('Не удалось обновить форму');
                }
                
            } catch (\Exception $e) {
                // Обработка ошибок
                \Notification::error($e->getMessage());
                
                // Обновляем данные формы из POST для повторного отображения
                $form = array_merge($form, [
                    'name' => $_POST['name'] ?? '',
                    'description' => $_POST['description'] ?? '',
                    'template' => $_POST['template'] ?? 'default',
                    'status' => $_POST['status'] ?? 'active',
                    'success_message' => $_POST['success_message'] ?? '',
                    'error_message' => $_POST['error_message'] ?? ''
                ]);
                
                // Сохраняем настройки, уведомления и действия из POST
                $settings = $this->prepareSettings($_POST, $settings);
                $notifications = $this->prepareNotifications($_POST, $notifications);
                $actions = $this->prepareActions($_POST, $actions);
            }
        }
        
        // Рендеринг формы редактирования
        $this->render('admin/forms/form', [
            'form' => $form,
            'formStructure' => $formStructure,
            'fieldTypes' => $this->controller->getAvailableFieldTypes(),
            'validationTypes' => $this->controller->getValidationTypes(),
            'templates' => $templates,
            'currentTheme' => $currentTheme,
            'pageTitle' => 'Редактирование формы: ' . htmlspecialchars($form['name']),
            'isEdit' => true,
            'settings' => $settings,
            'notifications' => $notifications,
            'actions' => $actions,
            'formModel' => $this->formModel
        ]);
    }
    
    /**
     * Обработка структуры формы - добавление дополнительных данных
     */
    private function processFormStructure($structure, $postData) {
        $processed = [];
        
        foreach ($structure as $field) {
            $processedField = $field;
            
            // Добавляем CSS классы из формы, если они есть
            if (!empty($postData['field_css_' . ($field['name'] ?? '')])) {
                $processedField['class'] = trim($postData['field_css_' . $field['name']]);
            }
            
            // Для кнопки отправки добавляем текст
            if ($field['type'] === 'submit' && !empty($postData['submit_text'])) {
                $processedField['label'] = trim($postData['submit_text']);
            }
            
            $processed[] = $processedField;
        }
        
        return $processed;
    }
    
    /**
     * Подготовка настроек формы (сохраняем ВСЕ настройки)
     */
    private function prepareSettings($postData, $currentSettings = []) {
        // Определяем, какие чекбоксы отмечены
        $checkboxSettings = [
            'ajax_enabled' => 'ajax_enabled',
            'show_labels' => 'show_labels',
            'show_descriptions' => 'show_descriptions',
            'store_submissions' => 'store_submissions',
            'redirect_after_submit' => 'redirect_after_submit',
            'captcha_enabled' => 'captcha_enabled',
            'csrf_protection' => 'csrf_protection',
            'limit_submissions' => 'limit_submissions',
            'spam_protection' => 'spam_protection',
            'email_validation' => 'email_validation'
        ];
        
        $settings = [];
        
        // Обрабатываем чекбоксы
        foreach ($checkboxSettings as $key => $postKey) {
            $settings[$key] = !empty($postData[$postKey]);
        }
        
        // Обрабатываем текстовые поля
        $textSettings = [
            'redirect_url' => 'redirect_url',
            'captcha_type' => 'captcha_type',
            'captcha_question' => 'captcha_question',
            'captcha_secret' => 'captcha_secret',
            'spam_keywords' => 'spam_keywords'
        ];
        
        foreach ($textSettings as $key => $postKey) {
            $settings[$key] = trim($postData[$postKey] ?? '');
        }
        
        // Обрабатываем числовые поля
        $numericSettings = [
            'max_submissions_per_day' => 'max_submissions_per_day',
            'max_submissions_per_ip' => 'max_submissions_per_ip'
        ];
        
        foreach ($numericSettings as $key => $postKey) {
            $settings[$key] = intval($postData[$postKey] ?? 0);
        }
        
        // Объединяем с текущими настройками (чтобы не потерять системные)
        foreach ($currentSettings as $key => $value) {
            if (!isset($settings[$key])) {
                $settings[$key] = $value;
            }
        }
        
        // Устанавливаем значения по умолчанию, если они пустые
        if (empty($settings['captcha_type'])) {
            $settings['captcha_type'] = 'math';
        }
        
        if (empty($settings['captcha_question'])) {
            $settings['captcha_question'] = 'Сколько будет 2 + 2?';
        }
        
        if (empty($settings['captcha_secret'])) {
            $settings['captcha_secret'] = 'bloggy_cms_captcha';
        }
        
        return $settings;
    }
    
    /**
     * Подготовка уведомлений
     */
    private function prepareNotifications($postData, $currentNotifications = []) {
        $notifications = [];
        
        // Уведомление администратору
        $adminNotification = [
            'enabled' => !empty($postData['notify_admin_enabled']),
            'type' => 'admin',
            'to' => trim($postData['admin_email'] ?? ''),
            'from' => trim($postData['admin_from'] ?? ''),
            'subject' => trim($postData['admin_subject'] ?? 'Новая отправка формы'),
            'message' => trim($postData['admin_message'] ?? 'Поступила новая отправка формы.')
        ];
        
        // Уведомление пользователю
        $userNotification = [
            'enabled' => !empty($postData['notify_user_enabled']),
            'type' => 'user',
            'to_field' => trim($postData['user_email_field'] ?? '{email}'),
            'from' => trim($postData['user_from'] ?? ''),
            'subject' => trim($postData['user_subject'] ?? 'Ваша форма отправлена'),
            'message' => trim($postData['user_message'] ?? 'Спасибо за вашу заявку!')
        ];
        
        $notifications = [$adminNotification, $userNotification];
        
        return $notifications;
    }
    
    /**
     * Подготовка действий
     */
    private function prepareActions($postData, $currentActions = []) {
        $actions = [];
        
        // Действие по умолчанию - сохранение в БД
        $actions[] = [
            'enabled' => !empty($postData['store_submissions']),
            'type' => 'save_to_db',
            'name' => 'Сохранить в базу данных'
        ];
        
        // Редирект после отправки
        if (!empty($postData['redirect_enabled']) || !empty($postData['redirect_url'])) {
            $actions[] = [
                'enabled' => !empty($postData['redirect_enabled']),
                'type' => 'redirect',
                'name' => 'Редирект после отправки',
                'url' => trim($postData['redirect_url'] ?? '')
            ];
        }
        
        // Вебхук
        if (!empty($postData['webhook_enabled']) || !empty($postData['webhook_url'])) {
            $headers = [];
            $headersText = $postData['webhook_headers'] ?? '';
            if (!empty($headersText)) {
                $lines = explode("\n", $headersText);
                foreach ($lines as $line) {
                    $line = trim($line);
                    if (!empty($line) && strpos($line, ':') !== false) {
                        list($key, $value) = explode(':', $line, 2);
                        $headers[trim($key)] = trim($value);
                    }
                }
            }
            
            $actions[] = [
                'enabled' => !empty($postData['webhook_enabled']),
                'type' => 'webhook',
                'name' => 'Отправить на вебхук',
                'url' => trim($postData['webhook_url'] ?? ''),
                'method' => $postData['webhook_method'] ?? 'POST',
                'headers' => $headers
            ];
        }
        
        return $actions;
    }
}