<?php
    add_admin_js('templates/default/admin/assets/js/controllers/form-builder.js');
    add_admin_css('templates/default/admin/assets/css/form-builder.css');
?>

<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">
            <i class="bi bi-<?= $isEdit ? 'pencil' : 'plus-circle' ?> me-2"></i>
            <?= $isEdit ? 'Редактирование формы' : 'Создание формы' ?>
        </h4>
        <a href="<?= ADMIN_URL ?>/forms" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Назад к списку
        </a>
    </div>

    <form method="POST" id="form-builder-form">
        <div class="row">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-puzzle me-2"></i>Конструктор полей
                        </h5>
                        <div class="btn-group">
                            <button type="button" class="btn btn-outline-primary btn-sm" 
                                    data-bs-toggle="modal" data-bs-target="#addFieldModal">
                                <i class="bi bi-plus-circle me-1"></i>Добавить поле
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="form-builder">
                            <div id="form-fields-container" class="sortable-fields">
                                <?php if (!empty($formStructure)): ?>
                                    <?php foreach ($formStructure as $index => $field): ?>
                                        <?= $this->renderFormField($field, $index) ?>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div id="form-empty" class="text-center text-muted p-5">
                                        <div class="mb-3">
                                            <i class="bi bi-ui-checks display-4 opacity-50"></i>
                                        </div>
                                        <h5 class="text-muted">Форма пустая</h5>
                                        <p class="text-muted mb-3">Добавьте первое поле чтобы начать работу</p>
                                        <button type="button" class="btn btn-primary" 
                                                data-bs-toggle="modal" data-bs-target="#addFieldModal">
                                            <i class="bi bi-plus-circle me-1"></i>Добавить поле
                                        </button>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-gear me-2"></i>Основные настройки
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">
                                <i class="bi bi-tag me-1"></i>Название формы
                                <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control" 
                                   name="name" 
                                   value="<?= html($form['name'] ?? '') ?>" 
                                   required
                                   placeholder="Например: Контактная форма">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">
                                <i class="bi bi-card-text me-1"></i>Описание формы
                            </label>
                            <textarea class="form-control" 
                                      name="description" 
                                      rows="2"
                                      placeholder="Краткое описание формы"><?= html($form['description'] ?? '') ?></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">
                                <i class="bi bi-check-circle me-1"></i>Сообщение об успешной отправке
                            </label>
                            <textarea class="form-control" 
                                      name="success_message" 
                                      rows="2"
                                      placeholder="Форма успешно отправлена!"><?= html($form['success_message'] ?? 'Форма успешно отправлена!') ?></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">
                                <i class="bi bi-exclamation-triangle me-1"></i>Сообщение об ошибке
                            </label>
                            <textarea class="form-control" 
                                      name="error_message" 
                                      rows="2"
                                      placeholder="Произошла ошибка при отправке формы."><?= html($form['error_message'] ?? 'Произошла ошибка при отправке формы.') ?></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">
                                <i class="bi bi-power me-1"></i>Статус формы
                            </label>
                            <select class="form-select" name="status">
                                <option value="active" <?= ($form['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Активна</option>
                                <option value="inactive" <?= ($form['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Неактивна</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">
                                <i class="bi bi-layout-wtf me-1"></i>Шаблон формы
                            </label>
                            <select class="form-select" name="template">
                                <option value="">Стандартный шаблон</option>
                                <?php if (!empty($templates)): ?>
                                    <?php foreach ($templates as $templateKey => $templateName): ?>
                                        <option value="<?= $templateKey ?>" 
                                            <?= ($form['template'] ?? 'default') === $templateKey ? 'selected' : '' ?>>
                                            <?= html($templateName) ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                            <div class="form-text">
                                <i class="bi bi-folder me-1"></i>Шаблоны загружаются из: <code>templates/<?= html($currentTheme) ?>/front/assets/forms/</code>
                            </div>
                            <?php if (empty($templates) || count($templates) <= 1): ?>
                            <div class="alert alert-info mt-2 p-2 small">
                                <i class="bi bi-info-circle me-1"></i>
                                Создайте PHP файлы в указанной директории для добавления кастомных шаблонов.
                            </div>
                            <?php endif; ?>
                        </div>

                    </div>
                </div>
                
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-toggle-on me-2"></i>Дополнительные настройки
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="form-check mb-2">
                            <input class="form-check-input" 
                                   type="checkbox" 
                                   name="ajax_enabled" 
                                   id="ajax_enabled"
                                   <?= !empty($form['settings']['ajax_enabled'] ?? true) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="ajax_enabled">
                                AJAX отправка формы
                            </label>
                            <div class="form-text small">Отправка формы без перезагрузки страницы</div>
                        </div>
                        
                        <div class="form-check mb-2">
                            <input class="form-check-input" 
                                   type="checkbox" 
                                   name="show_labels" 
                                   id="show_labels"
                                   <?= !empty($form['settings']['show_labels'] ?? true) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="show_labels">
                                Показывать подписи полей
                            </label>
                        </div>
                        
                        <div class="form-check mb-2">
                            <input class="form-check-input" 
                                   type="checkbox" 
                                   name="show_descriptions" 
                                   id="show_descriptions"
                                   <?= !empty($form['settings']['show_descriptions'] ?? true) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="show_descriptions">
                                Показывать описания полей
                            </label>
                        </div>
                        
                    </div>
                </div>
                
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-lg me-2"></i>
                                <?= $isEdit ? 'Обновить форму' : 'Создать форму' ?>
                            </button>
                            
                            <?php if ($isEdit): ?>
                            <a href="<?= ADMIN_URL ?>/forms/preview/<?= $form['id'] ?>" 
                               class="btn btn-outline-secondary">
                                <i class="bi bi-eye me-2"></i>Предпросмотр
                            </a>
                            <a href="<?= ADMIN_URL ?>/forms/settings/<?= $form['id'] ?>" 
                               class="btn btn-outline-info">
                                <i class="bi bi-gear me-2"></i>Доп. настройки
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <?php if ($isEdit): ?>
                <div class="card border-0 shadow-sm mt-3">
                    <div class="card-header bg-white border-0">
                        <h6 class="card-title mb-0">
                            <i class="bi bi-graph-up me-2"></i>Статистика
                        </h6>
                    </div>
                    <div class="card-body">
                        <?php
                        $fieldsCount = count($formStructure);
                        $submissionsCount = $formModel->getSubmissionsCount($form['id']);
                        ?>
                        <div class="row text-center">
                            <div class="col-6">
                                <div class="border-end">
                                    <div class="h4 mb-0"><?= $fieldsCount ?></div>
                                    <small class="text-muted">Поля</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="h4 mb-0"><?= $submissionsCount ?></div>
                                <small class="text-muted">Отправок</small>
                            </div>
                        </div>
                        
                        <div class="mt-3">
                            <small class="text-muted">Слаг формы:</small>
                            <div class="input-group input-group-sm mt-1">
                                <input type="text" class="form-control" value="<?= html($form['slug']) ?>" readonly>
                                <button type="button" class="btn btn-outline-secondary" onclick="copySlug()">
                                    <i class="bi bi-copy"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <input type="hidden" name="form_structure" id="form-structure" value='<?= json_encode($formStructure ?? [], JSON_UNESCAPED_UNICODE) ?>'>
    </form>
</div>

<div class="modal fade" id="addFieldModal" tabindex="-1" aria-labelledby="addFieldModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addFieldModalLabel">
                    <i class="bi bi-plus-circle me-2"></i>Добавить поле
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-12">
                        <label class="form-label">
                            <i class="bi bi-list-ul me-1"></i>Тип поля
                            <span class="text-danger">*</span>
                        </label>
                        <div class="row g-2" id="field-type-selector">
                            <?php foreach ($fieldTypes as $type => $typeInfo): ?>
                                <div class="col-4 col-md-3">
                                    <div class="field-type-card card h-100" 
                                         data-type="<?= $type ?>"
                                         data-has-options="<?= $typeInfo['has_options'] ? '1' : '0' ?>"
                                         data-has-placeholder="<?= $typeInfo['has_placeholder'] ? '1' : '0' ?>">
                                        <div class="card-body text-center p-2">
                                            <div class="mb-2">
                                                <i class="bi bi-<?= $typeInfo['icon'] ?> display-6 text-primary"></i>
                                            </div>
                                            <div class="small"><?= html($typeInfo['label']) ?></div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <div class="col-md-12 mt-3" id="field-settings" style="display: none;">
                        <form id="field-settings-form">
                            <input type="hidden" id="field-type" name="type">
                            
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">
                                        <i class="bi bi-fonts me-1"></i>Заголовок поля
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="field-label" 
                                           name="label"
                                           placeholder="Например: Ваше имя"
                                           required>
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label">
                                        <i class="bi bi-code-slash me-1"></i>Имя поля (name)
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="field-name" 
                                           name="name"
                                           placeholder="Например: name"
                                           required
                                           pattern="[a-zA-Z0-9_]+">
                                    <div class="form-text small">Только латинские буквы, цифры и подчеркивание</div>
                                </div>
                                
                                <div class="col-md-12">
                                    <label class="form-label">
                                        <i class="bi bi-card-text me-1"></i>Описание поля
                                    </label>
                                    <textarea class="form-control" 
                                              id="field-description" 
                                              name="description"
                                              rows="2"
                                              placeholder="Пояснение для пользователя"></textarea>
                                </div>
                                
                                <div class="col-md-12">
                                    <label class="form-label">
                                        <i class="bi bi-palette me-1"></i>CSS классы поля
                                    </label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="field-class" 
                                           name="class"
                                           placeholder="например: form-control-lg custom-field">
                                    <div class="form-text small">Дополнительные CSS классы через пробел</div>
                                </div>
                                
                                <div id="dynamic-settings"></div>
                                
                                <div class="col-md-12 mt-3">
                                    <h6 class="border-bottom pb-2 mb-3">
                                        <i class="bi bi-shield-check me-2"></i>Настройки валидации
                                    </h6>
                                    
                                    <div class="row g-2">
                                        <div class="col-md-6">
                                            <div class="form-check">
                                                <input class="form-check-input" 
                                                       type="checkbox" 
                                                       id="field-required" 
                                                       name="required">
                                                <label class="form-check-label" for="field-required">
                                                    Обязательное поле
                                                </label>
                                            </div>
                                        </div>
                                        
                                        <div class="col-md-12 mt-2">
                                            <label class="form-label small">Дополнительные правила:</label>
                                            <div id="validation-rules"></div>
                                            <button type="button" class="btn btn-outline-secondary btn-sm mt-2" 
                                                    onclick="addValidationRule()">
                                                <i class="bi bi-plus-circle me-1"></i>Добавить правило
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1"></i>Отмена
                </button>
                <button type="button" class="btn btn-primary" id="save-field-btn" disabled>
                    <i class="bi bi-check-lg me-1"></i>Добавить поле
                </button>
            </div>
        </div>
    </div>
</div>

<?php ob_start(); ?>
<script>
    function copySlug() {
        const slugInput = document.querySelector('input[value="<?= html($form['slug'] ?? '') ?>"]');
        if (slugInput) {
            slugInput.select();
            document.execCommand('copy');
            alert('Слаг формы скопирован в буфер обмена');
        }
    }
</script>
<?php admin_bottom_js(ob_get_clean()); ?>