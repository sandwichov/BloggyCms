<?php
add_admin_js('templates/default/admin/assets/js/controllers/form-settings.js');
?>

<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">
            <i class="bi bi-gear me-2"></i>
            Настройки формы: <?= html($form['name']) ?>
        </h4>
        <div>
            <a href="<?= ADMIN_URL ?>/forms" class="btn btn-outline-secondary me-2">
                <i class="bi bi-arrow-left me-2"></i>Назад
            </a>
            <a href="<?= ADMIN_URL ?>/forms/edit/<?= $form['id'] ?>" class="btn btn-outline-primary">
                <i class="bi bi-pencil me-2"></i>Редактировать форму
            </a>
        </div>
    </div>
    
    <form method="POST" id="form-settings-form">
        <div class="row">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-sliders me-2"></i>Основные настройки
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">
                                    <i class="bi bi-check-circle me-1"></i>Сообщение об успешной отправке
                                </label>
                                <textarea class="form-control" 
                                          name="success_message" 
                                          rows="2"><?= html($settings['success_message'] ?? 'Форма успешно отправлена!') ?></textarea>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">
                                    <i class="bi bi-exclamation-triangle me-1"></i>Сообщение об ошибке
                                </label>
                                <textarea class="form-control" 
                                          name="error_message" 
                                          rows="2"><?= html($settings['error_message'] ?? 'Произошла ошибка при отправке формы.') ?></textarea>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-check mb-2">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           name="ajax_enabled" 
                                           id="ajax_enabled"
                                           <?= !empty($settings['ajax_enabled']) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="ajax_enabled">
                                        AJAX отправка формы
                                    </label>
                                    <div class="form-text small">Отправка без перезагрузки страницы</div>
                                </div>
                                
                                <div class="form-check mb-2">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           name="show_labels" 
                                           id="show_labels"
                                           <?= !empty($settings['show_labels']) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="show_labels">
                                        Показывать подписи полей
                                    </label>
                                </div>
                                
                                <div class="form-check mb-2">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           name="show_descriptions" 
                                           id="show_descriptions"
                                           <?= !empty($settings['show_descriptions']) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="show_descriptions">
                                        Показывать описания полей
                                    </label>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-check mb-2">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           name="store_submissions" 
                                           id="store_submissions"
                                           <?= !empty($settings['store_submissions']) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="store_submissions">
                                        Сохранять отправки в БД
                                    </label>
                                    <div class="form-text small">Сохранять все данные отправок</div>
                                </div>
                                
                                <div class="form-check mb-2">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           name="csrf_protection" 
                                           id="csrf_protection"
                                           <?= !empty($settings['csrf_protection'] ?? true) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="csrf_protection">
                                        Защита от CSRF атак
                                    </label>
                                </div>
                                
                                <div class="form-check mb-2">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           name="email_validation" 
                                           id="email_validation"
                                           <?= !empty($settings['email_validation']) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="email_validation">
                                        Проверять email адреса
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-shield-check me-2"></i>Защита от спама
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="form-check mb-3">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           name="captcha_enabled" 
                                           id="captcha_enabled"
                                           <?= !empty($settings['captcha_enabled']) ? 'checked' : '' ?>
                                           onchange="toggleCaptchaSettings()">
                                    <label class="form-check-label" for="captcha_enabled">
                                        Включить капчу
                                    </label>
                                    <div class="form-text small">Защита от автоматических отправок</div>
                                </div>
                                
                                <div id="captcha_settings" style="<?= !empty($settings['captcha_enabled']) ? '' : 'display: none;' ?>">
                                    <div class="mb-3">
                                        <label class="form-label">
                                            <i class="bi bi-question-circle me-1"></i>Тип капчи
                                        </label>
                                        <select class="form-select" name="captcha_type" id="captcha_type" onchange="updateCaptchaExample()">
                                            <?php foreach ($captchaTypes as $type => $info): ?>
                                                <option value="<?= $type ?>" <?= ($settings['captcha_type'] ?? 'math') === $type ? 'selected' : '' ?>>
                                                    <?= html($info['name']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div class="form-text small" id="captcha_type_description">
                                            <?= $captchaTypes[$settings['captcha_type'] ?? 'math']['description'] ?>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">
                                            <i class="bi bi-chat-left-text me-1"></i>Вопрос капчи
                                        </label>
                                        <input type="text" 
                                               class="form-control" 
                                               name="captcha_question" 
                                               id="captcha_question"
                                               value="<?= html($settings['captcha_question'] ?? 'Сколько будет 2 + 2?') ?>"
                                               placeholder="Введите вопрос для капчи">
                                        <div class="form-text small">Или оставьте стандартный вопрос</div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">
                                            <i class="bi bi-key me-1"></i>Секретный ключ
                                        </label>
                                        <div class="input-group">
                                            <input type="text" 
                                                   class="form-control" 
                                                   name="captcha_secret" 
                                                   id="captcha_secret"
                                                   value="<?= html($settings['captcha_secret'] ?? 'bloggy_cms_captcha') ?>">
                                            <button type="button" class="btn btn-outline-secondary" onclick="generateCaptchaSecret()">
                                                <i class="bi bi-arrow-clockwise"></i>
                                            </button>
                                        </div>
                                        <div class="form-text small">Используется для шифрования ответов</div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <div class="card bg-light">
                                            <div class="card-body">
                                                <h6 class="card-title">
                                                    <i class="bi bi-eye me-2"></i>Пример капчи
                                                </h6>
                                                <div id="captcha_example">
                                                    <p><strong>Вопрос:</strong> <?= html($captchaExample['question']) ?></p>
                                                    <p><strong>Ответ:</strong> <?= html($captchaExample['answer']) ?></p>
                                                </div>
                                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="updateCaptchaExample()">
                                                    <i class="bi bi-arrow-clockwise me-1"></i>Сгенерировать новый пример
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <div class="form-check mb-3">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           name="spam_protection" 
                                           id="spam_protection"
                                           <?= !empty($settings['spam_protection']) ? 'checked' : '' ?>
                                           onchange="toggleSpamSettings()">
                                    <label class="form-check-label" for="spam_protection">
                                        Фильтр спам-слов
                                    </label>
                                </div>
                                
                                <div id="spam_settings" style="<?= !empty($settings['spam_protection']) ? '' : 'display: none;' ?>">
                                    <div class="mb-3">
                                        <label class="form-label">
                                            <i class="bi bi-ban me-1"></i>Спам-слова
                                        </label>
                                        <textarea class="form-control" 
                                                  name="spam_keywords" 
                                                  rows="4"
                                                  placeholder="Каждое слово с новой строки"><?= html($settings['spam_keywords'] ?? '') ?></textarea>
                                        <div class="form-text small">Отправки содержащие эти слова будут помечаться как спам</div>
                                    </div>
                                </div>
                                
                                <div class="form-check mb-3">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           name="limit_submissions" 
                                           id="limit_submissions"
                                           <?= !empty($settings['limit_submissions']) ? 'checked' : '' ?>
                                           onchange="toggleLimitSettings()">
                                    <label class="form-check-label" for="limit_submissions">
                                        Ограничить количество отправок
                                    </label>
                                </div>
                                
                                <div id="limit_settings" style="<?= !empty($settings['limit_submissions']) ? '' : 'display: none;' ?>">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">
                                                <i class="bi bi-calendar-day me-1"></i>Макс. в день
                                            </label>
                                            <input type="number" 
                                                   class="form-control" 
                                                   name="max_submissions_per_day" 
                                                   min="0"
                                                   value="<?= html($settings['max_submissions_per_day'] ?? 0) ?>">
                                            <div class="form-text small">0 = без ограничений</div>
                                        </div>
                                        
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">
                                                <i class="bi bi-pc-display me-1"></i>Макс. с одного IP
                                            </label>
                                            <input type="number" 
                                                   class="form-control" 
                                                   name="max_submissions_per_ip" 
                                                   min="0"
                                                   value="<?= html($settings['max_submissions_per_ip'] ?? 0) ?>">
                                            <div class="form-text small">0 = без ограничений</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-bell me-2"></i>Настройки уведомлений
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <div class="form-check mb-3">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           name="notify_admin_enabled" 
                                           id="notify_admin_enabled"
                                           <?= !empty($notifications[0]['enabled'] ?? false) ? 'checked' : '' ?>
                                           onchange="toggleAdminNotification()">
                                    <label class="form-check-label" for="notify_admin_enabled">
                                        Уведомлять администратора
                                    </label>
                                </div>
                                
                                <div id="admin_notification" style="<?= !empty($notifications[0]['enabled'] ?? false) ? '' : 'display: none;' ?>">
                                    <div class="mb-3">
                                        <label class="form-label">
                                            <i class="bi bi-envelope me-1"></i>Email администратора
                                        </label>
                                        <input type="email" 
                                               class="form-control" 
                                               name="admin_email" 
                                               value="<?= html($notifications[0]['to'] ?? '') ?>"
                                               placeholder="admin@example.com">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">
                                            <i class="bi bi-person-circle me-1"></i>Отправитель
                                        </label>
                                        <input type="text" 
                                               class="form-control" 
                                               name="admin_from" 
                                               value="<?= html($notifications[0]['from'] ?? '') ?>"
                                               placeholder="Имя <email@example.com>">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">
                                            <i class="bi bi-card-heading me-1"></i>Тема письма
                                        </label>
                                        <input type="text" 
                                               class="form-control" 
                                               name="admin_subject" 
                                               value="<?= html($notifications[0]['subject'] ?? 'Новая отправка формы') ?>">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">
                                            <i class="bi bi-card-text me-1"></i>Текст письма
                                        </label>
                                        <textarea class="form-control" 
                                                  name="admin_message" 
                                                  rows="4"><?= html($notifications[0]['message'] ?? 'Поступила новая отправка формы.') ?></textarea>
                                        <div class="form-text small">
                                            Используйте переменные: {имя_поля}, {date}, {time}, {ip}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6 mb-4">
                                <div class="form-check mb-3">
                                    <input class="form-check-input" 
                                           type="checkbox" 
                                           name="notify_user_enabled" 
                                           id="notify_user_enabled"
                                           <?= !empty($notifications[1]['enabled'] ?? false) ? 'checked' : '' ?>
                                           onchange="toggleUserNotification()">
                                    <label class="form-check-label" for="notify_user_enabled">
                                        Уведомлять пользователя
                                    </label>
                                </div>
                                
                                <div id="user_notification" style="<?= !empty($notifications[1]['enabled'] ?? false) ? '' : 'display: none;' ?>">
                                    <div class="mb-3">
                                        <label class="form-label">
                                            <i class="bi bi-input-cursor me-1"></i>Поле с email пользователя
                                        </label>
                                        <input type="text" 
                                               class="form-control" 
                                               name="user_email_field" 
                                               value="<?= html($notifications[1]['to_field'] ?? '{email}') ?>"
                                               placeholder="{email} или имя_поля">
                                        <div class="form-text small">Имя поля формы, содержащего email</div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">
                                            <i class="bi bi-person-circle me-1"></i>Отправитель
                                        </label>
                                        <input type="text" 
                                               class="form-control" 
                                               name="user_from" 
                                               value="<?= html($notifications[1]['from'] ?? '') ?>"
                                               placeholder="Имя <email@example.com>">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">
                                            <i class="bi bi-card-heading me-1"></i>Тема письма
                                        </label>
                                        <input type="text" 
                                               class="form-control" 
                                               name="user_subject" 
                                               value="<?= html($notifications[1]['subject'] ?? 'Ваша форма отправлена') ?>">
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">
                                            <i class="bi bi-card-text me-1"></i>Текст письма
                                        </label>
                                        <textarea class="form-control" 
                                                  name="user_message" 
                                                  rows="4"><?= html($notifications[1]['message'] ?? 'Спасибо за вашу заявку!') ?></textarea>
                                        <div class="form-text small">
                                            Используйте переменные: {имя_поля}, {date}, {time}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-lightning me-2"></i>Действия после отправки
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="form-check mb-2">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       name="redirect_enabled" 
                                       id="redirect_enabled"
                                       <?= !empty(array_filter($actions, function($a) { return $a['type'] === 'redirect' && !empty($a['enabled']); })) ? 'checked' : '' ?>
                                       onchange="toggleRedirectSettings()">
                                <label class="form-check-label" for="redirect_enabled">
                                    Перенаправление
                                </label>
                            </div>
                            
                            <div id="redirect_settings" style="<?= !empty(array_filter($actions, function($a) { return $a['type'] === 'redirect' && !empty($a['enabled']); })) ? '' : 'display: none;' ?>">
                                <label class="form-label small">URL для перенаправления:</label>
                                <input type="text" 
                                       class="form-control form-control-sm" 
                                       name="redirect_url" 
                                       value="<?= html(array_values(array_filter($actions, function($a) { return $a['type'] === 'redirect'; }))[0]['url'] ?? '') ?>"
                                       placeholder="https://example.com/thank-you">
                                <div class="form-text small">Оставьте пустым для перезагрузки страницы</div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-check mb-2">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       name="webhook_enabled" 
                                       id="webhook_enabled"
                                       <?= !empty(array_filter($actions, function($a) { return $a['type'] === 'webhook' && !empty($a['enabled']); })) ? 'checked' : '' ?>
                                       onchange="toggleWebhookSettings()">
                                <label class="form-check-label" for="webhook_enabled">
                                    Вебхук
                                </label>
                            </div>
                            
                            <div id="webhook_settings" style="<?= !empty(array_filter($actions, function($a) { return $a['type'] === 'webhook' && !empty($a['enabled']); })) ? '' : 'display: none;' ?>">
                                <div class="mb-2">
                                    <label class="form-label small">URL вебхука:</label>
                                    <input type="text" 
                                           class="form-control form-control-sm" 
                                           name="webhook_url" 
                                           value="<?= html(array_values(array_filter($actions, function($a) { return $a['type'] === 'webhook'; }))[0]['url'] ?? '') ?>"
                                           placeholder="https://example.com/webhook">
                                </div>
                                
                                <div class="mb-2">
                                    <label class="form-label small">Метод:</label>
                                    <select class="form-select form-select-sm" name="webhook_method">
                                        <option value="POST" <?= (array_values(array_filter($actions, function($a) { return $a['type'] === 'webhook'; }))[0]['method'] ?? 'POST') === 'POST' ? 'selected' : '' ?>>POST</option>
                                        <option value="GET" <?= (array_values(array_filter($actions, function($a) { return $a['type'] === 'webhook'; }))[0]['method'] ?? '') === 'GET' ? 'selected' : '' ?>>GET</option>
                                        <option value="PUT" <?= (array_values(array_filter($actions, function($a) { return $a['type'] === 'webhook'; }))[0]['method'] ?? '') === 'PUT' ? 'selected' : '' ?>>PUT</option>
                                    </select>
                                </div>
                                
                                <div class="mb-2">
                                    <label class="form-label small">Заголовки (необязательно):</label>
                                    <textarea class="form-control form-control-sm" 
                                              name="webhook_headers" 
                                              rows="3"
                                              placeholder="Content-Type: application/json&#10;Authorization: Bearer token"><?php 
                                        $webhookAction = array_values(array_filter($actions, function($a) { return $a['type'] === 'webhook'; }))[0] ?? [];
                                        if (!empty($webhookAction['headers'])) {
                                            foreach ($webhookAction['headers'] as $key => $value) {
                                                echo html("$key: $value\n");
                                            }
                                        }
                                    ?></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-lg me-2"></i>Сохранить настройки
                            </button>
                            <a href="<?= ADMIN_URL ?>/forms/preview/<?= $form['id'] ?>" 
                               class="btn btn-outline-secondary">
                                <i class="bi bi-eye me-2"></i>Предпросмотр
                            </a>
                            <a href="<?= ADMIN_URL ?>/forms" 
                               class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left me-2"></i>К списку форм
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<?php ob_start(); ?>
<script>
    function toggleCaptchaSettings() {
        const enabled = document.getElementById('captcha_enabled').checked;
        document.getElementById('captcha_settings').style.display = enabled ? 'block' : 'none';
    }
    
    function toggleSpamSettings() {
        const enabled = document.getElementById('spam_protection').checked;
        document.getElementById('spam_settings').style.display = enabled ? 'block' : 'none';
    }
    
    function toggleLimitSettings() {
        const enabled = document.getElementById('limit_submissions').checked;
        document.getElementById('limit_settings').style.display = enabled ? 'block' : 'none';
    }
    
    function toggleAdminNotification() {
        const enabled = document.getElementById('notify_admin_enabled').checked;
        document.getElementById('admin_notification').style.display = enabled ? 'block' : 'none';
    }
    
    function toggleUserNotification() {
        const enabled = document.getElementById('notify_user_enabled').checked;
        document.getElementById('user_notification').style.display = enabled ? 'block' : 'none';
    }
    
    function toggleRedirectSettings() {
        const enabled = document.getElementById('redirect_enabled').checked;
        document.getElementById('redirect_settings').style.display = enabled ? 'block' : 'none';
    }
    
    function toggleWebhookSettings() {
        const enabled = document.getElementById('webhook_enabled').checked;
        document.getElementById('webhook_settings').style.display = enabled ? 'block' : 'none';
    }
    
    function generateCaptchaSecret() {
        const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789!@#$%^&*()_+-=';
        let secret = '';
        for (let i = 0; i < 32; i++) {
            secret += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        document.getElementById('captcha_secret').value = secret;
    }
    
    function updateCaptchaExample() {
        const type = document.getElementById('captcha_type').value;
        const question = document.getElementById('captcha_question').value;
        const descriptions = {
            'math': 'Простой математический пример (2+2, 5*3 и т.д.)',
            'text': 'Вопрос с текстовым ответом',
            'logic': 'Простая логическая задача'
        };
        document.getElementById('captcha_type_description').textContent = descriptions[type] || '';
        
        fetch('<?= ADMIN_URL ?>/forms/generate-captcha-example', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `type=${encodeURIComponent(type)}&question=${encodeURIComponent(question)}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                document.getElementById('captcha_example').innerHTML = `
                    <p><strong>Вопрос:</strong> ${data.question}</p>
                    <p><strong>Ответ:</strong> ${data.answer || '(Пользовательский вопрос - ответ не генерируется)'}</p>
                `;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            const examples = {
                'math': { question: 'Сколько будет 7 + 3?', answer: '10' },
                'text': { question: 'Столица России?', answer: 'Москва' },
                'logic': { question: 'Что идет не двигаясь с места?', answer: 'время' }
            };
            
            const example = examples[type] || examples.math;
            document.getElementById('captcha_example').innerHTML = `
                <p><strong>Вопрос:</strong> ${example.question}</p>
                <p><strong>Ответ:</strong> ${example.answer}</p>
            `;
        });
    }
</script>
<?php admin_bottom_js(ob_get_clean()); ?>