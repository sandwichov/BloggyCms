<?php
    add_admin_js('templates/default/admin/assets/js/controllers/fields-form.js');
    $config = isset($field) && !empty($field['config']) ? json_decode($field['config'], true) : [];
?>

<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">
            <i class="bi bi-plus-circle me-2"></i>
            <?= isset($field) ? 'Редактирование поля' : 'Создание поля' ?>
        </h4>
        <a href="<?= ADMIN_URL ?>/fields/entity/<?= $entityType ?>" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Назад к полям
        </a>
    </div>

    <form method="post" id="field-form">
        <div class="row">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0">
                        <h5 class="card-title mb-0">Основные настройки</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Название поля *</label>
                                    <input type="text" 
                                           class="form-control" 
                                           name="name" 
                                           value="<?= isset($field) ? html($field['name']) : (isset($data['name']) ? html($data['name']) : '') ?>" 
                                           placeholder="Например: Цена, Телефон, Email"
                                           required>
                                    <div class="form-text">Человеко-понятное название поля</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Системное имя *</label>
                                    <input type="text" 
                                           class="form-control" 
                                           name="system_name" 
                                           value="<?= isset($field) ? html($field['system_name']) : (isset($data['system_name']) ? html($data['system_name']) : '') ?>" 
                                           placeholder="Например: price, phone, email"
                                           pattern="[a-z0-9_]+"
                                           required>
                                    <div class="form-text">Только латинские буквы, цифры и подчеркивания</div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Тип поля *</label>
                            <select class="form-select" name="type" id="field-type" required>
                                <option value="">Выберите тип поля</option>
                                <?php foreach($fieldTypes as $type => $name): ?>
                                    <option value="<?= $type ?>" 
                                        <?= (isset($field) && $field['type'] == $type) ? 'selected' : ((isset($data['type']) && $data['type'] == $type) ? 'selected' : '') ?>>
                                        <?= html($name) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Описание</label>
                            <textarea class="form-control" 
                                      name="description" 
                                      rows="2" 
                                      placeholder="Необязательное описание поля"><?= isset($field) ? html($field['description']) : (isset($data['description']) ? html($data['description']) : '') ?></textarea>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4" id="field-settings">
                    <div class="card-header bg-white border-0">
                        <h5 class="card-title mb-0">Настройки поля</h5>
                    </div>
                    <div class="card-body" id="field-settings-content">
                        <?php if (isset($field) && !empty($field['type'])): ?>
                            <?php
                            $fieldManager = new FieldManager($this->db);
                            $fieldInstance = $fieldManager->getFieldInstance(
                                $field['type'], 
                                $config
                            );
                            if ($fieldInstance) {
                                echo $fieldInstance->getSettingsForm();
                            }
                            ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0">
                        <h5 class="card-title mb-0">Параметры поля</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Порядок сортировки</label>
                            <input type="number" 
                                class="form-control" 
                                name="sort_order" 
                                value="<?= isset($field) ? html($field['sort_order']) : (isset($data['sort_order']) ? html($data['sort_order']) : '0') ?>" 
                                min="0">
                            <div class="form-text">Чем меньше число, тем выше в списке</div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" 
                                    type="checkbox" 
                                    name="is_required" 
                                    id="is_required"
                                    value="1"
                                    <?= (isset($field) && $field['is_required']) ? 'checked' : ((isset($data['is_required']) && $data['is_required']) ? 'checked' : '') ?>>
                                <label class="form-check-label" for="is_required">
                                    Обязательное поле
                                </label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" 
                                    type="checkbox" 
                                    name="is_active" 
                                    id="is_active"
                                    value="1"
                                    <?= (!isset($field) || $field['is_active']) ? 'checked' : ((isset($data['is_active']) && $data['is_active']) ? 'checked' : '') ?>>
                                <label class="form-check-label" for="is_active">
                                    Активное поле
                                </label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" 
                                    type="checkbox" 
                                    name="show_in_post" 
                                    id="show_in_post"
                                    value="1"
                                    <?= (!isset($field) || ($field['show_in_post'] ?? true)) ? 'checked' : ((isset($data['show_in_post']) && $data['show_in_post']) ? 'checked' : '') ?>>
                                <label class="form-check-label" for="show_in_post">
                                    Показывать в записи
                                </label>
                                <div class="form-text">Отображать поле на странице поста</div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" 
                                    type="checkbox" 
                                    name="show_in_list" 
                                    id="show_in_list"
                                    value="1"
                                    <?= (isset($field) && $field['show_in_list']) ? 'checked' : ((isset($data['show_in_list']) && $data['show_in_list']) ? 'checked' : '') ?>>
                                <label class="form-check-label" for="show_in_list">
                                    Показывать в списке
                                </label>
                                <div class="form-text">Отображать поле в списке постов</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="bi bi-check-lg me-2"></i>
                        <?= isset($field) ? 'Обновить поле' : 'Создать поле' ?>
                    </button>
                </div>
            </div>
        </div>
        
        <?php if (!empty($config)): ?>
            <?php foreach ($config as $key => $value): ?>
                <?php if (is_array($value)): ?>
                    <?php foreach ($value as $subKey => $subValue): ?>
                        <input type="hidden" name="config[<?= html($key) ?>][<?= html($subKey) ?>]" value="<?= html($subValue) ?>">
                    <?php endforeach; ?>
                <?php else: ?>
                    <input type="hidden" name="config[<?= html($key) ?>]" value="<?= html($value) ?>">
                <?php endif; ?>
            <?php endforeach; ?>
        <?php endif; ?>
    </form>
</div>