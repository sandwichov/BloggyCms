<?php

/**
 * Контроллер управления формами в админ-панели
 * Предоставляет интерфейс для создания, редактирования и управления формами сайта
 * 
 * @package controllers
 * @extends Controller
 */
class AdminFormController extends Controller {
    
    /**
     * @var FormModel Модель для работы с формами
     */
    private $formModel;
    
    /**
     * Конструктор контроллера форм
     * Инициализирует модель форм и проверяет права администратора
     *
     * @param Database $db Объект подключения к базе данных
     */
    public function __construct($db) {
        parent::__construct($db);
        $this->formModel = new FormModel($db);
        
        // Проверка авторизации пользователя
        if (!isset($_SESSION['user_id'])) {
            \Notification::error('Пожалуйста, авторизуйтесь для доступа к панели управления');
            $this->redirect(ADMIN_URL . '/login');
            return;
        }
        
        // Проверка административных прав
        if (!isset($_SESSION['is_admin']) || !$_SESSION['is_admin']) {
            \Notification::error('У вас нет прав доступа к панели управления');
            $this->redirect(BASE_URL);
            return;
        }
    }
    
    /**
     * Действие: Главная страница управления формами
     * Отображает список всех форм в системе
     * 
     * @return mixed
     */
    public function adminIndexAction() {
        $action = new \forms\actions\AdminIndex($this->db);
        $action->setController($this);
        return $action->execute();
    }
    
    /**
     * Действие: Создание новой формы
     * Отображает форму создания формы
     * 
     * @return mixed
     */
    public function createAction() {
        $action = new \forms\actions\AdminCreate($this->db);
        $action->setController($this);
        return $action->execute();
    }
    
    /**
     * Действие: Редактирование существующей формы
     * Отображает форму редактирования формы по ее ID
     * 
     * @param int $id ID редактируемой формы
     * @return mixed
     */
    public function editAction($id) {
        $action = new \forms\actions\AdminEdit($this->db, ['id' => $id]);
        $action->setController($this);
        return $action->execute();
    }
    
    /**
     * Действие: Удаление формы
     * Удаляет форму по ее ID
     * 
     * @param int $id ID удаляемой формы
     * @return mixed
     */
    public function deleteAction($id) {
        $action = new \forms\actions\AdminDelete($this->db, ['id' => $id]);
        $action->setController($this);
        return $action->execute();
    }
    
    /**
     * Действие: Получение структуры формы через AJAX
     * Возвращает JSON с полями формы
     * 
     * @param int $id ID формы
     * @return mixed JSON-ответ со структурой формы
     */
    public function getStructureAction($id) {
        $action = new \forms\actions\AdminGetStructure($this->db, ['id' => $id]);
        $action->setController($this);
        return $action->execute();
    }
    
    /**
     * Действие: Предварительный просмотр формы
     * Показывает как будет выглядеть форма на сайте
     * 
     * @param int $id ID формы для предпросмотра
     * @return mixed
     */
    public function previewAction($id) {
        $action = new \forms\actions\AdminPreview($this->db, ['id' => $id]);
        $action->setController($this);
        return $action->execute();
    }
    
    /**
     * Действие: Настройки формы
     * Отображает настройки формы (уведомления, действия и т.д.)
     * 
     * @param int $id ID формы
     * @return mixed
     */
    public function settingsAction($id) {
        $action = new \forms\actions\AdminSettings($this->db, ['id' => $id]);
        $action->setController($this);
        return $action->execute();
    }
    
    /**
     * Действие: Переключение статуса формы
     * Включает/выключает форму
     * 
     * @param int $id ID формы
     * @return mixed
     */
    public function toggleStatusAction($id) {
        $action = new \forms\actions\AdminToggleStatus($this->db, ['id' => $id]);
        $action->setController($this);
        return $action->execute();
    }
    
    /**
     * Действие: Просмотр отправок формы
     * Показывает список всех отправок формы
     * 
     * @param int $id ID формы
     * @return mixed
     */
    public function showAction($id) {
        $action = new \forms\actions\AdminShow($this->db, ['id' => $id]);
        $action->setController($this);
        return $action->execute();
    }
    
    /**
     * Действие: Обработка отправки формы (публичная)
     * Принимает данные формы от пользователей
     * 
     * @param string $slug Слаг формы
     * @return mixed
     */
    public function submitAction($slug) {
        $action = new \forms\actions\FormSubmit($this->db, ['slug' => $slug]);
        $action->setController($this);
        return $action->execute();
    }
    
    /**
     * Действие: Просмотр формы (публичная)
     * Отображает форму на сайте
     * 
     * @param string $slug Слаг формы
     * @return mixed
     */
    public function viewAction($slug) {
        $action = new \forms\actions\FormView($this->db, ['slug' => $slug]);
        $action->setController($this);
        return $action->execute();
    }

    /**
     * Действие: Экспорт отправок в CSV
     * 
     * @param int $id ID формы
     * @return mixed
     */
    public function exportAction($id) {
        $action = new \forms\actions\AdminExport($this->db, ['id' => $id]);
        $action->setController($this);
        return $action->execute();
    }
    
    /**
     * Действие: Получение отправки через AJAX
     * 
     * @param int $id ID отправки
     * @return mixed
     */
    public function getSubmissionAction($id) {
        $action = new \forms\actions\AdminGetSubmission($this->db, ['id' => $id]);
        $action->setController($this);
        return $action->execute();
    }
    
    /**
     * Действие: Удаление отправки
     * 
     * @param int $id ID отправки
     * @return mixed
     */
    public function deleteSubmissionAction($id) {
        $action = new \forms\actions\AdminDeleteSubmission($this->db, ['id' => $id]);
        $action->setController($this);
        return $action->execute();
    }
    
    /**
     * Действие: Обновление статуса отправки
     * 
     * @param int $id ID отправки
     * @return mixed
     */
    public function updateSubmissionStatusAction($id) {
        $action = new \forms\actions\AdminUpdateSubmissionStatus($this->db, ['id' => $id]);
        $action->setController($this);
        return $action->execute();
    }
    
    /**
     * Действие: Удаление всех отправок формы
     * 
     * @param int $id ID формы
     * @return mixed
     */
    public function deleteAllSubmissionsAction($id) {
        $action = new \forms\actions\AdminDeleteAllSubmissions($this->db, ['id' => $id]);
        $action->setController($this);
        return $action->execute();
    }

    /**
     * Получение доступных шаблонов форм
     */
    public function getAvailableTemplates() {
        return $this->formModel->getAvailableTemplates();
    }

    /**
     * Получение текущей темы
     */
    public function getCurrentTheme() {
        // Используем константу DEFAULT_TEMPLATE
        if (defined('DEFAULT_TEMPLATE')) {
            return DEFAULT_TEMPLATE;
        }
        
        // Если константа не определена, пробуем получить из настроек
        if (class_exists('SettingsHelper') && method_exists('SettingsHelper', 'get')) {
            try {
                $theme = SettingsHelper::get('site', 'site_template');
                if (empty($theme)) {
                    $theme = SettingsHelper::get('site', 'theme');
                }
                return !empty($theme) ? $theme : 'default';
            } catch (Exception $e) {
                return 'default';
            }
        }
        
        return 'default';
    }
    
    /**
     * Рендеринг одного поля формы для конструктора
     * Генерирует HTML-структуру для редактирования поля
     *
     * @param array $field Данные поля
     * @param string $index Уникальный индекс поля в структуре
     * @return string HTML-код поля
     */
    public function renderFormField($field, $index) {
        $fieldType = $field['type'] ?? 'text';
        $fieldLabel = htmlspecialchars($field['label'] ?? '');
        $fieldName = htmlspecialchars($field['name'] ?? '');
        $fieldValue = htmlspecialchars($field['default_value'] ?? '');
        $placeholder = htmlspecialchars($field['placeholder'] ?? '');
        $required = !empty($field['required']);
        $description = htmlspecialchars($field['description'] ?? '');
        $options = $field['options'] ?? [];
        $validation = $field['validation'] ?? [];
        
        ob_start();
        ?>
        <div class="form-field card mb-2" data-index="<?= $index ?>" data-type="<?= $fieldType ?>">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <h6 class="card-title mb-0">
                        <i class="bi bi-<?= $this->getFieldTypeIcon($fieldType) ?> me-2"></i>
                        <?= $this->getFieldTypeLabel($fieldType) ?>
                    </h6>
                    <div class="btn-group btn-group-sm">
                        <button type="button" class="btn btn-outline-secondary form-field-handle">
                            <i class="bi bi-arrows-move"></i>
                        </button>
                        <button type="button" class="btn btn-outline-primary edit-form-field" 
                                data-bs-toggle="modal" 
                                data-bs-target="#fieldSettingsModal">
                            <i class="bi bi-gear"></i>
                        </button>
                        <button type="button" class="btn btn-outline-danger remove-form-field">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-12">
                        <div class="d-flex align-items-center mb-2">
                            <span class="badge bg-secondary me-2"><?= strtoupper($fieldType) ?></span>
                            <strong><?= $fieldLabel ?: 'Без названия' ?></strong>
                            <?php if ($required): ?>
                                <span class="badge bg-danger ms-2">Обязательное</span>
                            <?php endif; ?>
                            <?php if (!empty($fieldName)): ?>
                                <small class="text-muted ms-2">(name: <?= $fieldName ?>)</small>
                            <?php endif; ?>
                        </div>
                        
                        <?php if ($description): ?>
                            <p class="small text-muted mb-2"><?= $description ?></p>
                        <?php endif; ?>
                        
                        <!-- Превью поля -->
                        <div class="preview-field mt-2">
                            <?= $this->renderFieldPreview($field) ?>
                        </div>
                    </div>
                </div>
                
                <!-- Скрытые данные для редактирования -->
                <input type="hidden" class="field-type" value="<?= $fieldType ?>">
                <input type="hidden" class="field-label" value="<?= $fieldLabel ?>">
                <input type="hidden" class="field-name" value="<?= $fieldName ?>">
                <input type="hidden" class="field-default-value" value="<?= $fieldValue ?>">
                <input type="hidden" class="field-placeholder" value="<?= $placeholder ?>">
                <input type="hidden" class="field-required" value="<?= $required ? '1' : '0' ?>">
                <input type="hidden" class="field-description" value="<?= $description ?>">
                <input type="hidden" class="field-options" value="<?= htmlspecialchars(json_encode($options)) ?>">
                <input type="hidden" class="field-validation" value="<?= htmlspecialchars(json_encode($validation)) ?>">
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Получение иконки для типа поля
     */
    private function getFieldTypeIcon($type) {
        $icons = [
            'text' => 'input-cursor-text',
            'textarea' => 'textarea-t',
            'email' => 'envelope',
            'tel' => 'telephone',
            'number' => '123',
            'date' => 'calendar',
            'select' => 'menu-down',
            'checkbox' => 'check-square',
            'radio' => 'circle',
            'file' => 'paperclip',
            'password' => 'key',
            'hidden' => 'eye-slash',
            'submit' => 'send'
        ];
        
        return $icons[$type] ?? 'input-cursor';
    }
    
    /**
     * Получение читаемого названия типа поля
     */
    private function getFieldTypeLabel($type) {
        $labels = [
            'text' => 'Текстовое поле',
            'textarea' => 'Текстовая область',
            'email' => 'Email',
            'tel' => 'Телефон',
            'number' => 'Число',
            'date' => 'Дата',
            'select' => 'Выпадающий список',
            'checkbox' => 'Галочка',
            'radio' => 'Радио кнопки',
            'file' => 'Файл',
            'password' => 'Пароль',
            'hidden' => 'Скрытое поле',
            'submit' => 'Кнопка отправки'
        ];
        
        return $labels[$type] ?? 'Неизвестное поле';
    }
    
    /**
     * Рендеринг превью поля
     */
    private function renderFieldPreview($field) {
        $type = $field['type'] ?? 'text';
        $label = $field['label'] ?? '';
        $name = $field['name'] ?? '';
        $value = $field['default_value'] ?? '';
        $placeholder = $field['placeholder'] ?? '';
        $required = !empty($field['required']);
        $options = $field['options'] ?? [];
        
        ob_start();
        ?>
        <div class="form-preview">
            <?php switch ($type): 
                case 'text':
                case 'email':
                case 'tel':
                case 'number':
                case 'date':
                case 'password': ?>
                    <div class="mb-2">
                        <label class="form-label small"><?= htmlspecialchars($label) ?> <?= $required ? '<span class="text-danger">*</span>' : '' ?></label>
                        <input type="<?= $type ?>" 
                               class="form-control form-control-sm" 
                               value="<?= htmlspecialchars($value) ?>"
                               placeholder="<?= htmlspecialchars($placeholder) ?>"
                               disabled>
                    </div>
                    <?php break;
                
                case 'textarea': ?>
                    <div class="mb-2">
                        <label class="form-label small"><?= htmlspecialchars($label) ?> <?= $required ? '<span class="text-danger">*</span>' : '' ?></label>
                        <textarea class="form-control form-control-sm" 
                                  placeholder="<?= htmlspecialchars($placeholder) ?>"
                                  rows="3"
                                  disabled><?= htmlspecialchars($value) ?></textarea>
                    </div>
                    <?php break;
                
                case 'select': ?>
                    <div class="mb-2">
                        <label class="form-label small"><?= htmlspecialchars($label) ?> <?= $required ? '<span class="text-danger">*</span>' : '' ?></label>
                        <select class="form-select form-select-sm" disabled>
                            <option value=""><?= htmlspecialchars($placeholder) ?></option>
                            <?php foreach ($options as $option): ?>
                                <option value="<?= htmlspecialchars($option['value'] ?? '') ?>"
                                        <?= ($value === ($option['value'] ?? '')) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($option['label'] ?? '') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php break;
                
                case 'checkbox': ?>
                    <div class="mb-2">
                        <div class="form-check">
                            <input type="checkbox" 
                                   class="form-check-input" 
                                   <?= !empty($value) ? 'checked' : '' ?>
                                   disabled>
                            <label class="form-check-label small"><?= htmlspecialchars($label) ?> <?= $required ? '<span class="text-danger">*</span>' : '' ?></label>
                        </div>
                    </div>
                    <?php break;
                
                case 'radio': ?>
                    <div class="mb-2">
                        <label class="form-label small"><?= htmlspecialchars($label) ?> <?= $required ? '<span class="text-danger">*</span>' : '' ?></label>
                        <?php foreach ($options as $option): ?>
                            <div class="form-check">
                                <input type="radio" 
                                       class="form-check-input" 
                                       name="preview_<?= $name ?>"
                                       <?= ($value === ($option['value'] ?? '')) ? 'checked' : '' ?>
                                       disabled>
                                <label class="form-check-label small"><?= htmlspecialchars($option['label'] ?? '') ?></label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <?php break;
                
                case 'submit': ?>
                    <div class="mb-2">
                        <button type="button" class="btn btn-primary btn-sm" disabled>
                            <?= htmlspecialchars($label) ?>
                        </button>
                    </div>
                    <?php break;
                
                default: ?>
                    <div class="alert alert-warning small p-2">
                        Превью недоступно для типа "<?= $type ?>"
                    </div>
            <?php endswitch; ?>
        </div>
        <?php
        return ob_get_clean();
    }
    
    /**
     * Получение всех доступных типов полей
     */
    public function getAvailableFieldTypes() {
        return [
            'text' => [
                'label' => 'Текстовое поле',
                'icon' => 'input-cursor-text',
                'has_options' => false,
                'has_placeholder' => true
            ],
            'textarea' => [
                'label' => 'Текстовая область',
                'icon' => 'textarea-t',
                'has_options' => false,
                'has_placeholder' => true
            ],
            'email' => [
                'label' => 'Email',
                'icon' => 'envelope',
                'has_options' => false,
                'has_placeholder' => true
            ],
            'tel' => [
                'label' => 'Телефон',
                'icon' => 'telephone',
                'has_options' => false,
                'has_placeholder' => true
            ],
            'number' => [
                'label' => 'Число',
                'icon' => '123',
                'has_options' => false,
                'has_placeholder' => true
            ],
            'date' => [
                'label' => 'Дата',
                'icon' => 'calendar',
                'has_options' => false,
                'has_placeholder' => true
            ],
            'select' => [
                'label' => 'Выпадающий список',
                'icon' => 'menu-down',
                'has_options' => true,
                'has_placeholder' => true
            ],
            'checkbox' => [
                'label' => 'Галочка',
                'icon' => 'check-square',
                'has_options' => false,
                'has_placeholder' => false
            ],
            'radio' => [
                'label' => 'Радио кнопки',
                'icon' => 'circle',
                'has_options' => true,
                'has_placeholder' => false
            ],
            'file' => [
                'label' => 'Файл',
                'icon' => 'paperclip',
                'has_options' => false,
                'has_placeholder' => false
            ],
            'password' => [
                'label' => 'Пароль',
                'icon' => 'key',
                'has_options' => false,
                'has_placeholder' => true
            ],
            'hidden' => [
                'label' => 'Скрытое поле',
                'icon' => 'eye-slash',
                'has_options' => false,
                'has_placeholder' => false
            ],
            'submit' => [
                'label' => 'Кнопка отправки',
                'icon' => 'send',
                'has_options' => false,
                'has_placeholder' => false
            ]
        ];
    }
    
    /**
     * Получение типов валидации
     */
    public function getValidationTypes() {
        return [
            'required' => [
                'label' => 'Обязательное поле',
                'description' => 'Поле должно быть заполнено'
            ],
            'email' => [
                'label' => 'Email',
                'description' => 'Проверка формата email'
            ],
            'url' => [
                'label' => 'URL',
                'description' => 'Проверка формата URL'
            ],
            'numeric' => [
                'label' => 'Число',
                'description' => 'Допустимы только цифры'
            ],
            'min' => [
                'label' => 'Минимальное значение',
                'description' => 'Минимальное значение (для чисел) или длина (для текста)',
                'has_param' => true
            ],
            'max' => [
                'label' => 'Максимальное значение',
                'description' => 'Максимальное значение (для чисел) или длина (для текста)',
                'has_param' => true
            ],
            'regex' => [
                'label' => 'Регулярное выражение',
                'description' => 'Проверка по регулярному выражению',
                'has_param' => true
            ]
        ];
    }

    /**
     * Действие: Генерация примера капчи через AJAX
     * 
     * @return mixed JSON-ответ с примером капчи
     */
    public function generateCaptchaExampleAction() {
        header('Content-Type: application/json');
        
        $type = $_POST['type'] ?? 'math';
        $question = $_POST['question'] ?? '';
        
        $captchaExample = $this->generateCaptchaExample($type, $question);
        
        echo json_encode([
            'success' => true,
            'question' => $captchaExample['question'],
            'answer' => $captchaExample['answer']
        ]);
        exit;
    }

    /**
     * Генерация примера для капчи (для AJAX)
     */
    private function generateCaptchaExample($type = 'math', $customQuestion = '') {
        switch ($type) {
            case 'math':
                if (!empty($customQuestion) && preg_match('/(\d+)\s*([+\-*\/])\s*(\d+)/', $customQuestion, $matches)) {
                    // Используем пользовательский вопрос
                    $a = intval($matches[1]);
                    $b = intval($matches[3]);
                    $op = $matches[2];
                    
                    $question = $customQuestion;
                    $answer = eval("return $a $op $b;");
                } else {
                    $operations = ['+', '-', '*'];
                    $op = $operations[array_rand($operations)];
                    $a = rand(1, 10);
                    $b = rand(1, 10);
                    
                    if ($op === '-') {
                        $a = max($a, $b) + rand(0, 5);
                    }
                    
                    $question = "Сколько будет $a $op $b?";
                    $answer = eval("return $a $op $b;");
                }
                break;
                
            case 'text':
                if (!empty($customQuestion)) {
                    $question = $customQuestion;
                    $answer = ''; // Для пользовательских вопросов ответ не генерируем
                } else {
                    $questions = [
                        'Столица России?' => 'Москва',
                        'Сколько дней в неделе?' => '7',
                        'Какого цвета трава?' => 'Зеленый'
                    ];
                    $question = array_rand($questions);
                    $answer = $questions[$question];
                }
                break;
                
            case 'logic':
                if (!empty($customQuestion)) {
                    $question = $customQuestion;
                    $answer = '';
                } else {
                    $questions = [
                        'Что тяжелее: 1 кг пуха или 1 кг железа?' => 'одинаково',
                        'Что идет не двигаясь с места?' => 'время',
                        'Что можно увидеть с закрытыми глазами?' => 'сон'
                    ];
                    $question = array_rand($questions);
                    $answer = $questions[$question];
                }
                break;
                
            default:
                $question = 'Сколько будет 2 + 2?';
                $answer = '4';
        }
        
        return [
            'question' => $question,
            'answer' => $answer
        ];
    }

}