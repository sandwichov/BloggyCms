<?php
    add_admin_js('templates/default/admin/assets/js/controllers/form-category.js');
?>

<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">
            <?php echo bloggy_icon('bs', 'folder-plus', '24 24', null, 'me-2'); ?>
            <?php echo isset($category) ? 'Редактирование категории' : 'Создание категории'; ?>
        </h4>
        <a href="<?php echo ADMIN_URL; ?>/categories" class="btn btn-outline-secondary btn-sm">
            <?php echo bloggy_icon('bs', 'arrow-left', '16 16', null, 'me-1'); ?>
            Назад к списку
        </a>
    </div>

    <form method="post" enctype="multipart/form-data">
        <div class="row">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0">
                        <h5 class="card-title mb-0">Основная информация</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-4">
                            <label class="form-label">Название категории <span class="text-danger">*</span></label>
                            <input type="text" 
                                   name="name" 
                                   class="form-control form-control-lg" 
                                   value="<?php echo isset($category) ? html($category['name']) : (isset($data['name']) ? html($data['name']) : ''); ?>" 
                                   placeholder="Введите название категории"
                                   required>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label">URL Slug</label>
                            <div class="input-group">
                                <span class="input-group-text"><?php echo BASE_URL; ?>/category/</span>
                                <input type="text" 
                                       name="slug" 
                                       class="form-control" 
                                       value="<?php echo isset($category) ? html($category['slug']) : (isset($data['slug']) ? html($data['slug']) : ''); ?>" 
                                       placeholder="автоматически-сгенерированный-url">
                            </div>
                            <div class="form-text">Уникальный идентификатор в URL. Если оставить пустым, сгенерируется автоматически</div>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label">Описание</label>
                            <textarea name="description" 
                                      class="form-control" 
                                      rows="4" 
                                      placeholder="Добавьте описание категории"><?php echo isset($category) ? html($category['description']) : (isset($data['description']) ? html($data['description']) : ''); ?></textarea>
                            <div class="form-text">Это описание будет отображаться на странице категории</div>
                        </div>

                        <?php
                        $fieldModel = new FieldModel($this->db);
                        $customFields = $fieldModel->getActiveByEntityType('category');
                        
                        if (!empty($customFields)) { 
                        ?>
                            <div class="mb-4">
                                <h6 class="card-title mb-3">Дополнительные поля</h6>
                                <?php 
                                $currentFieldValues = array();
                                if (isset($category['id'])) {
                                    foreach ($customFields as $field) {
                                        $currentFieldValues[$field['system_name']] = $fieldModel->getFieldValue('category', $category['id'], $field['system_name']);
                                    }
                                }
                                
                                foreach ($customFields as $field) { 
                                    $currentValue = isset($currentFieldValues[$field['system_name']]) ? $currentFieldValues[$field['system_name']] : '';
                                    $fieldManager = new FieldManager($this->db);
                                    $config = is_array($field['config']) ? $field['config'] : json_decode($field['config'] ?? '{}', true);
                                ?>
                                    <div class="mb-3">
                                        <label class="form-label"><?php echo html($field['name']); ?></label>
                                        <?php echo $fieldManager->renderFieldInput(
                                            $field['type'],
                                            $field['system_name'],
                                            $currentValue,
                                            $config,
                                            'category',
                                            isset($category['id']) ? $category['id'] : 0
                                        ); ?>
                                        <?php if (!empty($field['description'])) { ?>
                                            <div class="form-text"><?php echo html($field['description']); ?></div>
                                        <?php } ?>
                                    </div>
                                <?php } ?>
                            </div>
                        <?php } ?>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0">
                        <h5 class="card-title mb-0">SEO настройки</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">SEO Title</label>
                            <input type="text" 
                                   name="meta_title" 
                                   class="form-control" 
                                   value="<?php echo isset($category) ? html($category['meta_title']) : (isset($data['meta_title']) ? html($data['meta_title']) : ''); ?>" 
                                   placeholder="Если не указан, используется название категории">
                            <div class="form-text">До 60 символов</div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Meta Description</label>
                            <textarea name="meta_description" 
                                      class="form-control" 
                                      rows="3"
                                      placeholder="Краткое описание для поисковых систем"><?php echo isset($category) ? html($category['meta_description']) : (isset($data['meta_description']) ? html($data['meta_description']) : ''); ?></textarea>
                            <div class="form-text">До 160 символов</div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Canonical URL</label>
                            <input type="url" 
                                   name="canonical_url" 
                                   class="form-control" 
                                   value="<?php echo isset($category) ? html($category['canonical_url']) : (isset($data['canonical_url']) ? html($data['canonical_url']) : ''); ?>" 
                                   placeholder="https://example.com/category">
                        </div>
                        
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" 
                                   type="checkbox" 
                                   name="noindex" 
                                   id="noindex"
                                   <?php echo (isset($category) && $category['noindex']) || (isset($data['noindex']) && $data['noindex']) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="noindex">Noindex (запретить индексацию)</label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0">
                        <h5 class="card-title mb-0">Изображение категории</h5>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($category['image'])) { ?>
                            <div class="mb-3 text-center">
                                <img src="/uploads/images/<?php echo html($category['image']); ?>" 
                                    class="img-thumbnail mb-2" 
                                    style="max-height: 150px;"
                                    alt="Изображение категории">
                                <div class="form-text text-center">
                                    <a href="/uploads/images/<?php echo html($category['image']); ?>" 
                                    target="_blank" 
                                    class="text-decoration-none">
                                        <?php echo bloggy_icon('bs', 'eye', '16 16', null, 'me-1'); ?>
                                        Просмотреть оригинал
                                    </a>
                                </div>
                            </div>
                        <?php } ?>
                        
                        <div class="mb-3">
                            <label for="image" class="form-label">Загрузить изображение</label>
                            <input type="file" 
                                name="image" 
                                id="image"
                                class="form-control" 
                                accept="image/*">
                            <div class="form-text">
                                Рекомендуемый размер: 400x300px<br>
                                Форматы: JPG, PNG, GIF, WebP
                            </div>
                        </div>
                        
                        <?php if (!empty($category['image'])) { ?>
                            <div class="form-check">
                                <input class="form-check-input" 
                                    type="checkbox" 
                                    name="delete_image" 
                                    id="delete_image">
                                <label class="form-check-label text-danger" for="delete_image">
                                    <?php echo bloggy_icon('bs', 'trash', '16 16', null, 'me-1'); ?>
                                    Удалить текущее изображение
                                </label>
                            </div>
                        <?php } ?>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0">
                        <h5 class="card-title mb-0">Настройки сортировки</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Порядок сортировки</label>
                            <input type="number" 
                                   name="sort_order" 
                                   class="form-control" 
                                   value="<?php echo isset($category) ? (int)$category['sort_order'] : (isset($data['sort_order']) ? (int)$data['sort_order'] : 0); ?>" 
                                   min="0" 
                                   max="999">
                            <div class="form-text">Чем меньше число, тем выше в списке. 0 - по умолчанию</div>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0">
                        <h5 class="card-title mb-0">Настройки доступа</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       id="password_protected" 
                                       name="password_protected"
                                       <?php echo (isset($category) && $category['password_protected']) || (isset($data['password_protected']) && $data['password_protected']) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="password_protected">
                                    Защитить паролем
                                </label>
                            </div>
                        </div>
                        
                        <div class="password-field" 
                             style="display: <?php echo (isset($category) && $category['password_protected']) || (isset($data['password_protected']) && $data['password_protected']) ? 'block' : 'none'; ?>;">
                            <div class="mb-4">
                                <label for="password" class="form-label">Пароль для доступа</label>
                                <div class="input-group">
                                    <span class="input-group-text">
                                        <?php echo bloggy_icon('bs', 'lock', '16 16'); ?>
                                    </span>
                                    <input type="text" 
                                           class="form-control" 
                                           id="password" 
                                           name="password"
                                           placeholder="Введите пароль" 
                                           value="<?php echo isset($category) ? html($category['password'] ?? '') : (isset($data['password']) ? html($data['password']) : ''); ?>">
                                </div>
                                <div class="form-text text-muted">
                                    <?php echo bloggy_icon('bs', 'info-circle', '14 14', null, 'me-1'); ?>
                                    Все посты в этой категории будут защищены этим паролем
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-white border-0">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <?php echo bloggy_icon('bs', 'check-lg', '16 16', null, 'me-1'); ?>
                                <?php echo isset($category) ? 'Обновить категорию' : 'Создать категорию'; ?>
                            </button>
                            <a href="<?php echo ADMIN_URL; ?>/categories" class="btn btn-outline-secondary">
                                <?php echo bloggy_icon('bs', 'x-lg', '16 16', null, 'me-1'); ?>
                                Отмена
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<style>
.image-preview img {
    transition: opacity 0.3s ease;
}
</style>