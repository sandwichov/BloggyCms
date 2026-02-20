<?php

/**
 * Класс для рендеринга и обработки HTML-форм
 * Предоставляет методы для отображения форм по ID или слагу, валидации,
 * отправки уведомлений и выполнения действий после отправки
 * 
 * @package Forms
 */
class FormRenderer {
    
    /**
     * Проверяет, существует ли класс Database, и возвращает подключение
     * 
     * @return object|null Подключение к базе данных
     */
    private static function getDatabase() {
        if (class_exists('Database') && method_exists('Database', 'getInstance')) {
            return Database::getInstance();
        }
        // Альтернативный способ получения подключения к БД
        global $db;
        return $db;
    }
    
    /**
     * Рендерит форму по её ID
     * 
     * @param int $formId ID формы
     * @param array $options Опции рендеринга
     * @return string HTML-код формы
     */
    public static function renderById($formId, $options = []) {
        $db = self::getDatabase();
        $formModel = new FormModel($db);
        
        $form = $formModel->getById($formId);
        if (!$form || $form['status'] !== 'active') {
            return '<!-- Form not found or inactive -->';
        }
        
        return self::renderForm($form, $options);
    }
    
    /**
     * Рендерит форму по её слагу с учетом шаблона
     * 
     * @param string $formSlug Слаг формы
     * @param array $options Опции рендеринга
     * @return string HTML-код формы
     */
    public static function render($formSlug, $options = []) {
        $db = self::getDatabase();
        $formModel = new FormModel($db);
        
        $form = $formModel->getBySlug($formSlug);
        if (!$form) {
            return '<!-- Form not found: ' . $formSlug . ' -->';
        }
        
        // Определяем шаблон
        $template = $form['template'] ?? 'default';
        $currentTheme = defined('DEFAULT_TEMPLATE') ? DEFAULT_TEMPLATE : 'default';
        $templateFile = ROOT_PATH . '/templates/' . $currentTheme . '/front/assets/forms/' . $template . '.php';
        
        // Если есть кастомный шаблон и он существует - используем его
        if ($template !== 'default' && file_exists($templateFile)) {
            return self::renderCustomTemplate($form, $templateFile, $options);
        }
        
        // Иначе используем стандартный рендеринг
        return self::renderForm($form, $options);
    }
    
    /**
     * Рендерит форму с использованием кастомного шаблона
     * 
     * @param array $form Данные формы
     * @param string $templateFile Путь к файлу шаблона
     * @param array $options Опции рендеринга
     * @return string HTML-код формы
     */
    private static function renderCustomTemplate($form, $templateFile, $options) {
        // Извлекаем переменные для использования в шаблоне
        $structure = $form['structure'] ?? [];
        $settings = $form['settings'] ?? [];
        $formId = $form['id'];
        $formSlug = $form['slug'];
        $formName = $form['name'];
        $formDescription = $form['description'] ?? '';
        $formTemplate = $form['template'] ?? 'default';
        
        // Генерируем CSRF токен если защита включена
        $csrfToken = '';
        if ($settings['csrf_protection'] ?? true) {
            $csrfToken = self::generateCsrfToken($formSlug);
        }
        
        // Захватываем вывод шаблона
        ob_start();
        
        // Включаем шаблон с доступом к нужным переменным
        include $templateFile;
        
        $output = ob_get_clean();
        
        return $output;
    }
    
    /**
     * Основной метод рендеринга формы
     * 
     * @param array $form Данные формы
     * @param array $options Опции рендеринга
     * @return string HTML-код формы
     */
    private static function renderForm($form, $options = []) {
        $structure = $form['structure'] ?? [];
        $settings = $form['settings'] ?? [];
        $formId = $form['id'];
        $formSlug = $form['slug'];
        
        // Объединяем настройки
        $options = array_merge([
            'class' => '',
            'style' => '',
            'show_labels' => true,
            'show_descriptions' => true,
            'submit_text' => $form['submit_text'] ?? 'Отправить',
            'submit_class' => 'btn btn-primary',
            'ajax' => false,
            'captcha' => false,
            'captcha_site_key' => '',
            'csrf_protection' => true
        ], $options);
        
        // Определяем URL для отправки
        $actionUrl = BASE_URL . '/form/' . $formSlug . '/submit';
        
        // Генерируем CSRF токен если защита включена
        $csrfToken = '';
        if ($options['csrf_protection']) {
            $csrfToken = self::generateCsrfToken($formSlug);
        }
        
        // Генерируем капчу если включена
        $captchaHtml = '';
        if ($options['captcha'] && !empty($settings['captcha_enabled'])) {
            $captchaHtml = self::renderCaptcha($settings);
        }
        
        // Начинаем вывод
        ob_start();
        ?>
        <form id="form-<?= $formSlug ?>" 
              class="form-builder-form <?= $options['class'] ?>" 
              style="<?= $options['style'] ?>" 
              method="POST" 
              action="<?= $actionUrl ?>"
              enctype="multipart/form-data"
              <?= $options['ajax'] ? 'data-ajax="true"' : '' ?>>
            
            <input type="hidden" name="form_id" value="<?= $formId ?>">
            <input type="hidden" name="form_slug" value="<?= $formSlug ?>">
            
            <?php if ($csrfToken): ?>
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
            <?php endif; ?>
            
            <div class="form-fields">
                <?php foreach ($structure as $field): ?>
                    <?= self::renderField($field, $options) ?>
                <?php endforeach; ?>
            </div>
            
            <?php if ($captchaHtml): ?>
                <?= $captchaHtml ?>
            <?php endif; ?>
            
            <?php if (!empty($form['description'])): ?>
                <div class="form-description mb-3">
                    <small class="text-muted"><?= htmlspecialchars($form['description']) ?></small>
                </div>
            <?php endif; ?>
        </form>
        
        <?php if ($options['ajax']): ?>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('form-<?= $formSlug ?>');
            if (!form) return;
            
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(form);
                const submitBtn = form.querySelector('button[type="submit"]');
                const originalText = submitBtn ? submitBtn.innerHTML : 'Отправить';
                
                // Показываем индикатор загрузки
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Отправка...';
                }
                
                fetch(form.action, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Показываем сообщение об успехе
                        const successHtml = `
                            <div class="alert alert-success">
                                <i class="bi bi-check-circle me-2"></i>
                                ${data.message}
                            </div>
                        `;
                        form.innerHTML = successHtml;
                        
                        // Редирект если указан
                        if (data.redirect) {
                            setTimeout(() => {
                                window.location.href = data.redirect;
                            }, 2000);
                        }
                    } else {
                        // Показываем ошибку
                        const formContent = form.innerHTML;
                        const errorHtml = `
                            <div class="alert alert-danger">
                                <i class="bi bi-exclamation-triangle me-2"></i>
                                ${data.message}
                            </div>
                        `;
                        form.innerHTML = errorHtml + formContent;
                        
                        // Восстанавливаем кнопку
                        if (submitBtn) {
                            submitBtn.disabled = false;
                            submitBtn.innerHTML = originalText;
                        }
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Произошла ошибка при отправке формы');
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalText;
                    }
                });
            });
        });
        </script>
        <?php endif; ?>
        
        <?php
        return ob_get_clean();
    }
    
    /**
     * Рендерит капчу для защиты от спама
     * 
     * @param array $settings Настройки формы
     * @return string HTML-код капчи
     */
    private static function renderCaptcha($settings) {
        $type = $settings['captcha_type'] ?? 'math';
        $question = $settings['captcha_question'] ?? 'Сколько будет 2 + 2?';
        $secret = $settings['captcha_secret'] ?? 'bloggy_cms_captcha';
        
        // Генерируем правильный ответ
        $answer = self::generateCaptchaAnswer($type, $question);
        
        // Шифруем ответ
        $encryptedAnswer = openssl_encrypt(
            $answer,
            'AES-128-ECB',
            $secret,
            0
        );
        
        ob_start();
        ?>
        <div class="form-group mb-3">
            <label class="form-label">
                <i class="bi bi-shield-check me-1"></i>Защита от спама
                <span class="text-danger">*</span>
            </label>
            
            <div class="card bg-light">
                <div class="card-body">
                    <h6 class="card-title"><?= htmlspecialchars($question) ?></h6>
                    <input type="hidden" name="captcha_hash" value="<?= htmlspecialchars($encryptedAnswer) ?>">
                    <input type="text" 
                           class="form-control" 
                           name="captcha_answer" 
                           placeholder="Введите ответ"
                           required>
                    <div class="form-text small">Пожалуйста, ответьте на вопрос для подтверждения, что вы не робот</div>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Генерирует ответ для капчи
     * 
     * @param string $type Тип капчи (math, text, logic)
     * @param string $question Вопрос
     * @return string Правильный ответ
     */
    private static function generateCaptchaAnswer($type, $question) {
        switch ($type) {
            case 'math':
                // Парсим математический пример из вопроса
                if (preg_match('/(\d+)\s*([+\-*\/])\s*(\d+)/', $question, $matches)) {
                    $a = intval($matches[1]);
                    $b = intval($matches[3]);
                    $op = $matches[2];
                    
                    switch ($op) {
                        case '+': return (string)($a + $b);
                        case '-': return (string)($a - $b);
                        case '*': return (string)($a * $b);
                        case '/': return $b != 0 ? (string)($a / $b) : '0';
                    }
                }
                // Дефолтный ответ
                return '4';
                
            case 'text':
                // Для текстовых вопросов возвращаем стандартные ответы
                $questions = [
                    'Столица России?' => 'Москва',
                    'Сколько дней в неделе?' => '7',
                    'Какого цвета трава?' => 'Зеленый'
                ];
                return $questions[$question] ?? 'ответ';
                
            case 'logic':
                $questions = [
                    'Что тяжелее: 1 кг пуха или 1 кг железа?' => 'одинаково',
                    'Что идет не двигаясь с места?' => 'время',
                    'Что можно увидеть с закрытыми глазами?' => 'сон'
                ];
                return $questions[$question] ?? 'ответ';
                
            default:
                return '4';
        }
    }
    
    /**
     * Генерирует CSRF токен для формы
     * 
     * @param string $formSlug Слаг формы
     * @return string Сгенерированный токен
     */
    public static function generateCsrfToken($formSlug) {
        // Начинаем сессию если не начата
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $formName = 'form_' . $formSlug;
        
        // Инициализируем массив токенов если не существует
        if (!isset($_SESSION['csrf_tokens'])) {
            $_SESSION['csrf_tokens'] = [];
        }
        
        // Генерируем новый токен
        $token = bin2hex(random_bytes(32));
        
        $_SESSION['csrf_tokens'][$formName] = [
            'token' => $token,
            'created_at' => time()
        ];
        
        // Очищаем старые токены (старше 1 часа)
        foreach ($_SESSION['csrf_tokens'] as $name => $tokenData) {
            if (time() - $tokenData['created_at'] > 3600) {
                unset($_SESSION['csrf_tokens'][$name]);
            }
        }
        
        return $token;
    }
    
    /**
     * Проверяет CSRF токен
     * 
     * @param string $token Токен для проверки
     * @param string $formSlug Слаг формы
     * @return bool true если токен валидный
     */
    public static function verifyCsrfToken($token, $formSlug) {
        // Начинаем сессию если не начата
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $formName = 'form_' . $formSlug;
        
        // Проверяем существование токена
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
        
        return true;
    }
    
    /**
     * Рендерит одно поле формы
     * 
     * @param array $field Данные поля
     * @param array $options Опции рендеринга
     * @return string HTML-код поля
     */
    private static function renderField($field, $options = []) {
        $type = $field['type'] ?? 'text';
        $name = $field['name'] ?? '';
        $label = $field['label'] ?? '';
        $value = $field['default_value'] ?? '';
        $placeholder = $field['placeholder'] ?? '';
        $required = !empty($field['required']);
        $description = $field['description'] ?? '';
        $validation = $field['validation'] ?? [];
        $fieldOptions = $field['options'] ?? [];
        $cssClass = $field['class'] ?? '';
        
        // Добавляем атрибуты валидации
        $validationAttrs = self::getValidationAttributes($validation);
        
        // Если это скрытое поле или кнопка отправки, рендерим особо
        if ($type === 'hidden') {
            return '<input type="hidden" name="' . htmlspecialchars($name) . '" value="' . htmlspecialchars($value) . '">';
        }
        
        if ($type === 'submit') {
            $label = $label ?: ($field['submit_text'] ?? 'Отправить');
            return '<button type="submit" class="' . ($field['class'] ?? $options['submit_class']) . '">' . htmlspecialchars($label) . '</button>';
        }
        
        // Для остальных полей рендерим обертку
        ob_start();
        ?>
        <div class="form-group mb-3 field-<?= $type ?> <?= $field['class'] ?? '' ?>">
            <?php if ($options['show_labels'] && $label && $type !== 'checkbox' && $type !== 'radio'): ?>
                <label for="field-<?= $name ?>" class="form-label">
                    <?= htmlspecialchars($label) ?>
                    <?php if ($required): ?>
                        <span class="text-danger">*</span>
                    <?php endif; ?>
                </label>
            <?php endif; ?>
            
            <?php switch ($type):
                case 'text':
                case 'email':
                case 'tel':
                case 'number':
                case 'date':
                case 'password': ?>
                    <input type="<?= $type ?>" 
                        id="field-<?= $name ?>" 
                        name="<?= htmlspecialchars($name) ?>" 
                        class="form-control <?= $cssClass ?> <?= $required ? 'required' : '' ?>" 
                        value="<?= htmlspecialchars($value) ?>"
                        placeholder="<?= htmlspecialchars($placeholder) ?>"
                        <?= $required ? 'required' : '' ?>
                        <?= $validationAttrs ?>>
                    <?php break;
                
                case 'textarea': ?>
                    <textarea id="field-<?= $name ?>" 
                              name="<?= htmlspecialchars($name) ?>" 
                              class="form-control <?= $cssClass ?> <?= $required ? 'required' : '' ?>" 
                              rows="<?= $field['rows'] ?? 3 ?>"
                              placeholder="<?= htmlspecialchars($placeholder) ?>"
                              <?= $required ? 'required' : '' ?>
                              <?= $validationAttrs ?>><?= htmlspecialchars($value) ?></textarea>
                    <?php break;
                
                case 'select': ?>
                    <select id="field-<?= $name ?>" 
                            name="<?= htmlspecialchars($name) . (!empty($field['multiple']) ? '[]' : '') ?>" 
                            class="form-select <?= $cssClass ?> <?= $required ? 'required' : '' ?>"
                            <?= $required ? 'required' : '' ?>
                            <?= !empty($field['multiple']) ? 'multiple' : '' ?>
                            <?= $validationAttrs ?>>
                        <?php if (!empty($placeholder)): ?>
                            <option value=""><?= htmlspecialchars($placeholder) ?></option>
                        <?php endif; ?>
                        <?php foreach ($fieldOptions as $option): ?>
                            <option value="<?= htmlspecialchars($option['value'] ?? '') ?>"
                                    <?= self::isOptionSelected($option['value'] ?? '', $value) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($option['label'] ?? '') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php break;
                
                case 'checkbox': ?>
                    <div class="form-check">
                        <input type="checkbox" 
                               id="field-<?= $name ?>" 
                               name="<?= htmlspecialchars($name) ?>" 
                               class="form-check-input <?= $cssClass ?> <?= $required ? 'required' : '' ?>" 
                               value="<?= htmlspecialchars($field['checkbox_value'] ?? '1') ?>"
                               <?= !empty($value) ? 'checked' : '' ?>
                               <?= $required ? 'required' : '' ?>
                               <?= $validationAttrs ?>>
                        <label for="field-<?= $name ?>" class="form-check-label">
                            <?= htmlspecialchars($label) ?>
                            <?php if ($required): ?>
                                <span class="text-danger">*</span>
                            <?php endif; ?>
                        </label>
                    </div>
                    <?php break;
                
                case 'radio': ?>
                    <div class="radio-group">
                        <?php foreach ($fieldOptions as $index => $option): ?>
                            <div class="form-check">
                                <input type="radio" 
                                       id="field-<?= $name ?>-<?= $index ?>" 
                                       name="<?= htmlspecialchars($name) ?>" 
                                       class="form-check-input <?= $cssClass ?> <?= $required ? 'required' : '' ?>" 
                                       value="<?= htmlspecialchars($option['value'] ?? '') ?>"
                                       <?= self::isOptionSelected($option['value'] ?? '', $value) ? 'checked' : '' ?>
                                       <?= $required ? 'required' : '' ?>
                                       <?= $validationAttrs ?>>
                                <label for="field-<?= $name ?>-<?= $index ?>" class="form-check-label">
                                    <?= htmlspecialchars($option['label'] ?? '') ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <?php break;
                
                case 'file': ?>
                    <input type="file" 
                           id="field-<?= $name ?>" 
                           name="<?= htmlspecialchars($name) . (!empty($field['multiple']) ? '[]' : '') ?>" 
                           class="form-control <?= $cssClass ?> <?= $required ? 'required' : '' ?>" 
                           <?= $required ? 'required' : '' ?>
                           <?= !empty($field['multiple']) ? 'multiple' : '' ?>
                           <?= !empty($field['accept']) ? 'accept="' . htmlspecialchars($field['accept']) . '"' : '' ?>
                           <?= $validationAttrs ?>>
                    <?php break;
                
                default: ?>
                    <div class="alert alert-warning">
                        Неизвестный тип поля: <?= htmlspecialchars($type) ?>
                    </div>
            <?php endswitch; ?>
            
            <?php if ($options['show_descriptions'] && $description): ?>
                <div class="form-text"><?= htmlspecialchars($description) ?></div>
            <?php endif; ?>
            
            <?php if (!empty($validation)): ?>
                <div class="invalid-feedback d-none"></div>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Проверяет, выбрана ли опция
     * 
     * @param mixed $optionValue Значение опции
     * @param mixed $fieldValue Значение поля
     * @return bool true если выбрана
     */
    private static function isOptionSelected($optionValue, $fieldValue) {
        if (is_array($fieldValue)) {
            return in_array($optionValue, $fieldValue);
        }
        return $optionValue == $fieldValue;
    }
    
    /**
     * Получает атрибуты валидации для поля
     * 
     * @param array $validation Массив правил валидации
     * @return string Строка с HTML-атрибутами
     */
    private static function getValidationAttributes($validation) {
        $attrs = [];
        
        foreach ($validation as $rule => $params) {
            switch ($rule) {
                case 'required':
                    $attrs[] = 'required';
                    break;
                case 'email':
                    $attrs[] = 'pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$"';
                    break;
                case 'url':
                    $attrs[] = 'pattern="https?://.+"';
                    break;
                case 'numeric':
                    $attrs[] = 'pattern="\d*"';
                    break;
                case 'min':
                    if (is_numeric($params)) {
                        $attrs[] = 'min="' . $params . '"';
                    } else {
                        $attrs[] = 'minlength="' . $params . '"';
                    }
                    break;
                case 'max':
                    if (is_numeric($params)) {
                        $attrs[] = 'max="' . $params . '"';
                    } else {
                        $attrs[] = 'maxlength="' . $params . '"';
                    }
                    break;
                case 'regex':
                    $attrs[] = 'pattern="' . htmlspecialchars($params) . '"';
                    break;
            }
        }
        
        return implode(' ', $attrs);
    }
    
    /**
     * Валидирует данные формы на стороне сервера
     * 
     * @param array $form Данные формы
     * @param array $data POST-данные
     * @param array $files FILES-данные
     * @return array Массив ошибок [поле => сообщение]
     */
    public static function validateSubmission($form, $data, $files = []) {
        $errors = [];
        $structure = $form['structure'] ?? [];
        
        foreach ($structure as $field) {
            $fieldName = $field['name'] ?? '';
            $fieldType = $field['type'] ?? '';
            $fieldLabel = $field['label'] ?? $fieldName;
            $required = !empty($field['required']);
            $validation = $field['validation'] ?? [];
            
            // Проверяем только обычные поля (не submit)
            if ($fieldType === 'submit' || $fieldType === 'hidden') {
                continue;
            }
            
            $value = $data[$fieldName] ?? '';
            $file = $files[$fieldName] ?? null;
            
            // Проверка обязательности
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
            
            // Проверка валидации
            foreach ($validation as $rule => $params) {
                $error = self::validateRule($rule, $params, $value, $fieldLabel, $fieldType);
                if ($error) {
                    $errors[$fieldName] = $error;
                    break;
                }
            }
            
            // Проверка типа поля
            $typeError = self::validateFieldType($fieldType, $value, $fieldLabel);
            if ($typeError) {
                $errors[$fieldName] = $typeError;
            }
            
            // Проверка файлов
            if ($fieldType === 'file' && $file && $file['error'] === UPLOAD_ERR_OK) {
                $fileError = self::validateFile($file, $field);
                if ($fileError) {
                    $errors[$fieldName] = $fileError;
                }
            }
        }
        
        return $errors;
    }
    
    /**
     * Проверяет правило валидации
     * 
     * @param string $rule Правило
     * @param mixed $params Параметры
     * @param mixed $value Значение
     * @param string $label Название поля
     * @param string $type Тип поля
     * @return string|null Сообщение об ошибке или null
     */
    private static function validateRule($rule, $params, $value, $label, $type) {
        switch ($rule) {
            case 'email':
                if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    return "Поле '{$label}' должно содержать корректный email адрес";
                }
                break;
            case 'url':
                if (!filter_var($value, FILTER_VALIDATE_URL)) {
                    return "Поле '{$label}' должно содержать корректный URL";
                }
                break;
            case 'numeric':
                if (!is_numeric($value)) {
                    return "Поле '{$label}' должно содержать только цифры";
                }
                break;
            case 'min':
                if ($type === 'number' || $type === 'date') {
                    if ($value < $params) {
                        return "Поле '{$label}' должно быть не меньше {$params}";
                    }
                } else {
                    if (strlen($value) < $params) {
                        return "Поле '{$label}' должно содержать не менее {$params} символов";
                    }
                }
                break;
            case 'max':
                if ($type === 'number' || $type === 'date') {
                    if ($value > $params) {
                        return "Поле '{$label}' должно быть не больше {$params}";
                    }
                } else {
                    if (strlen($value) > $params) {
                        return "Поле '{$label}' должно содержать не более {$params} символов";
                    }
                }
                break;
            case 'regex':
                if (!preg_match($params, $value)) {
                    return "Поле '{$label}' не соответствует требуемому формату";
                }
                break;
        }
        
        return null;
    }
    
    /**
     * Проверяет тип поля
     * 
     * @param string $type Тип поля
     * @param mixed $value Значение
     * @param string $label Название поля
     * @return string|null Сообщение об ошибке или null
     */
    private static function validateFieldType($type, $value, $label) {
        if (empty($value)) return null;
        
        switch ($type) {
            case 'email':
                if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    return "Поле '{$label}' должно содержать корректный email адрес";
                }
                break;
            case 'number':
                if (!is_numeric($value)) {
                    return "Поле '{$label}' должно содержать число";
                }
                break;
            case 'tel':
                if (!preg_match('/^[\d\s\-\+\(\)]+$/', $value)) {
                    return "Поле '{$label}' должно содержать корректный номер телефона";
                }
                break;
            case 'date':
                if (!strtotime($value)) {
                    return "Поле '{$label}' должно содержать корректную дату";
                }
                break;
        }
        
        return null;
    }
    
    /**
     * Проверяет файл
     * 
     * @param array $file Данные файла
     * @param array $field Данные поля
     * @return string|null Сообщение об ошибке или null
     */
    private static function validateFile($file, $field) {
        $maxSize = $field['max_size'] ?? 5242880; // 5MB по умолчанию
        $allowedTypes = $field['allowed_types'] ?? [];
        
        // Проверка размера
        if ($file['size'] > $maxSize) {
            $maxSizeMB = round($maxSize / 1024 / 1024, 1);
            return "Размер файла не должен превышать {$maxSizeMB}MB";
        }
        
        // Проверка типа файла
        if (!empty($allowedTypes)) {
            $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $fileMime = mime_content_type($file['tmp_name']);
            
            if (!in_array($fileExtension, $allowedTypes) && !in_array($fileMime, $allowedTypes)) {
                return "Разрешены только файлы типов: " . implode(', ', $allowedTypes);
            }
        }
        
        return null;
    }
    
    /**
     * Отправляет уведомления по email
     * 
     * @param array $form Данные формы
     * @param array $data Данные отправки
     * @param int $submissionId ID отправки
     * @return int Количество отправленных уведомлений
     */
    public static function sendNotifications($form, $data, $submissionId) {
        $notifications = $form['notifications'] ?? [];
        $sent = 0;
        
        foreach ($notifications as $notification) {
            if (empty($notification['enabled'])) continue;
            
            $to = self::parseEmailTemplate($notification['to'], $data);
            $subject = self::parseEmailTemplate($notification['subject'], $data);
            $message = self::parseEmailTemplate($notification['message'], $data);
            
            $headers = "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
            $headers .= "From: " . ($notification['from'] ?? 'noreply@' . $_SERVER['HTTP_HOST']) . "\r\n";
            
            if (mail($to, $subject, $message, $headers)) {
                $sent++;
            }
        }
        
        return $sent;
    }
    
    /**
     * Парсит шаблон email с подстановкой данных
     * 
     * @param string $template Шаблон
     * @param array $data Данные
     * @return string Обработанный шаблон
     */
    private static function parseEmailTemplate($template, $data) {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $value = implode(', ', $value);
            }
            $template = str_replace('{' . $key . '}', $value, $template);
        }
        
        // Добавляем системные переменные
        $template = str_replace('{date}', date('d.m.Y H:i'), $template);
        $template = str_replace('{ip}', $_SERVER['REMOTE_ADDR'] ?? '', $template);
        
        return $template;
    }
    
    /**
     * Выполняет действия после отправки формы
     * 
     * @param array $form Данные формы
     * @param array $data Данные отправки
     * @param int $submissionId ID отправки
     * @return int Количество выполненных действий
     */
    public static function executeActions($form, $data, $submissionId) {
        $actions = $form['actions'] ?? [];
        $executed = 0;
        
        foreach ($actions as $action) {
            if (empty($action['enabled'])) continue;
            
            switch ($action['type']) {
                case 'redirect':
                    // Редирект на указанную страницу
                    $_SESSION['form_redirect'] = $action['url'];
                    $executed++;
                    break;
                    
                case 'save_to_db':
                    // Уже сохранено в основном потоке
                    $executed++;
                    break;
                    
                case 'webhook':
                    // Отправка данных на вебхук
                    $webhookData = [
                        'form_id' => $form['id'],
                        'submission_id' => $submissionId,
                        'data' => $data,
                        'timestamp' => time()
                    ];
                    
                    $ch = curl_init($action['url']);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($webhookData));
                    curl_setopt($ch, CURLOPT_HTTPHEADER, [
                        'Content-Type: application/json'
                    ]);
                    curl_exec($ch);
                    curl_close($ch);
                    
                    $executed++;
                    break;
            }
        }
        
        return $executed;
    }
}