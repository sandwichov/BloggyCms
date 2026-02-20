<?php
add_admin_js('templates/default/admin/assets/js/controllers/tags-autocomplete.js');
add_admin_js('templates/default/admin/assets/js/controllers/posts-management.js');
add_admin_js('templates/default/admin/assets/js/controllers/image-upload.js');
add_admin_js('templates/default/admin/assets/js/controllers/post-blocks.js');
add_admin_css('templates/default/admin/assets/css/controllers/post-blocks.css');
?>
<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">
            <i class="bi bi-pencil-square me-2"></i>
            Редактирование поста
        </h4>
        <a href="<?= ADMIN_URL ?>/posts" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Назад к постам
        </a>
    </div>

    <form method="post" id="post-form" enctype="multipart/form-data">
        <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
        <input type="hidden" name="uploaded_image_path" id="uploaded-image-path" value="">
        <input type="hidden" name="uploaded_image_url" id="uploaded-image-url" value="">
        <input type="hidden" name="post_blocks" id="post_blocks_data" value="<?= html(json_encode($preparedBlocks ?? []), ENT_QUOTES) ?>">
        
        <div class="row">
            <div class="col-lg-9">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <div class="mb-4">
                            <label class="form-label">Заголовок поста</label>
                            <input type="text" class="form-control form-control-lg" name="title" value="<?= html($post['title']) ?>" required>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Краткое описание</label>
                            <textarea class="form-control" name="short_description" rows="3"
                                    placeholder="Краткое описание, которое будет отображаться в списках и анонсах"><?= html($post['short_description'] ?? '') ?></textarea>
                            <div class="form-text">Необязательное поле. Используется в списках постов и SEO-описании.</div>
                        </div>
                        
                        <div class="card mb-4 sticky-top" style="top: 20px; z-index: 1000;">
                            <div class="card-header bg-white py-2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0 text-muted small">Доступные блоки</h6>
                                    <div class="d-flex align-items-center">
                                        <select class="form-select form-select-sm me-2" id="block-category-filter" style="width: auto;">
                                            <option value="all">Все категории</option>
                                            <option value="text">🖊️ Текст</option>
                                            <option value="media">🎞️ Медиа</option>
                                            <option value="layout">🔩 Компоновка</option>
                                            <option value="advanced">🧲 Расширенные</option>
                                            <option value="basic">✔️ Основные</option>
                                        </select>
                                        
                                        <div class="input-group input-group-sm" style="width: 200px;">
                                            <input type="text" class="form-control" id="block-search" placeholder="Поиск блоков...">
                                            <button class="btn btn-outline-secondary" type="button" id="clear-search">
                                                <i class="bi bi-x"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body py-2">
                                <div id="post-block-buttons" class="d-flex flex-wrap gap-1">
                                </div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-body p-0">
                                <div id="post-blocks-container" class="min-h-100" style="min-height: 400px;">
                                    <div class="text-center text-muted py-5 empty-state">
                                        <i class="bi bi-inbox display-4 d-block mb-3 opacity-50"></i>
                                        <p class="mb-1">Нет добавленных блоков</p>
                                        <small class="text-muted">Добавьте блоки из панели выше для создания контента</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0">
                        <h5 class="card-title mb-0">Главное изображение</h5>
                    </div>
                    <div class="card-body">
                        <div class="image-upload-area border-2 border-dashed rounded-3 p-5 text-center position-relative"
                            id="imageUploadArea"
                            style="border-color: #dee2e6; border-style: dashed; background: #f8f9fa; transition: all 0.3s ease;">
                            
                            <?php if ($post['featured_image']): ?>
                                <div class="upload-preview" id="uploadPreview">
                                    <div class="position-relative d-inline-block">
                                        <img src="<?= BASE_URL ?>/uploads/images/<?= $post['featured_image'] ?>" 
                                             alt="Текущее изображение" 
                                             class="rounded shadow-sm" 
                                             style="max-height: 200px; max-width: 100%;" 
                                             id="imagePreview">
                                        <button type="button" 
                                                class="btn btn-danger btn-sm position-absolute top-0 end-0 m-1" 
                                                onclick="removeImage()" 
                                                style="border-radius: 50%; width: 30px; height: 30px;"
                                                title="Удалить изображение">
                                            <i class="bi bi-x"></i>
                                        </button>
                                    </div>
                                    <div class="mt-3">
                                        <small class="text-muted" id="fileName">Текущее изображение</small>
                                    </div>
                                    <div class="mt-2">
                                        <button type="button" 
                                                class="btn btn-outline-primary btn-sm" 
                                                onclick="document.getElementById('featured-image-input').click()">
                                            <i class="bi bi-arrow-repeat me-1"></i>Заменить изображение
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="upload-default d-none" id="uploadDefault">
                                    <div class="mb-3">
                                        <i class="bi bi-cloud-arrow-up display-4 text-muted"></i>
                                    </div>
                                    <h5 class="text-muted mb-2">Перетащите изображение сюда</h5>
                                    <p class="text-muted small mb-3">или</p>
                                    <button type="button" class="btn btn-primary btn-sm" onclick="document.getElementById('featured-image-input').click()">
                                        Выберите файл
                                    </button>
                                    <div class="mt-2">
                                        <small class="text-muted">PNG, JPG, GIF до 5MB</small>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="upload-default" id="uploadDefault">
                                    <div class="mb-3">
                                        <i class="bi bi-cloud-arrow-up display-4 text-muted"></i>
                                    </div>
                                    <h5 class="text-muted mb-2">Перетащите изображение сюда</h5>
                                    <p class="text-muted small mb-3">или</p>
                                    <button type="button" class="btn btn-primary btn-sm" onclick="document.getElementById('featured-image-input').click()">
                                        Выберите файл
                                    </button>
                                    <div class="mt-2">
                                        <small class="text-muted">PNG, JPG, GIF до 5MB</small>
                                    </div>
                                </div>

                                <div class="upload-preview d-none" id="uploadPreview">
                                    <div class="position-relative d-inline-block">
                                        <img src="" alt="Preview" class="rounded shadow-sm" 
                                            style="max-height: 200px; max-width: 100%;" id="imagePreview">
                                        <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 m-1" 
                                                onclick="removeImage()" style="border-radius: 50%; width: 30px; height: 30px;">
                                            <i class="bi bi-x"></i>
                                        </button>
                                    </div>
                                    <div class="mt-3">
                                        <small class="text-muted" id="fileName"></small>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <div class="upload-progress mt-3 d-none" id="uploadProgress">
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar progress-bar-striped progress-bar-animated" 
                                        role="progressbar" style="width: 0%" id="progressBar"></div>
                                </div>
                                <small class="text-muted mt-1" id="progressText">Загрузка...</small>
                            </div>

                            <input type="file" class="d-none" id="featured-image-input" name="featured_image" accept="image/*">
                            
                            <input type="hidden" name="remove_featured_image" id="removeFeaturedImage" value="0">
                        </div>
                    </div>
                </div>
                
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0">
                        <h5 class="card-title mb-0">SEO настройки</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">SEO Title</label>
                            <input type="text" class="form-control" name="seo_title" 
                                value="<?= html($post['seo_title'] ?? '') ?>"
                                placeholder="SEO заголовок (если отличается от основного)">
                            <div class="form-text">Если оставить пустым, будет использоваться заголовок поста.</div>
                        </div>
                        <div class="mb-0">
                            <label class="form-label">Meta Description</label>
                            <textarea class="form-control" name="meta_description" rows="2"><?= html($post['meta_description'] ?? '') ?></textarea>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-3">
                <?php
                    $fieldModel = new FieldModel($this->db);
                    $customFields = $fieldModel->getActiveByEntityType('post');
                ?>

                <?php if (!empty($customFields)): ?>
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white border-0">
                            <h5 class="card-title mb-0">Дополнительные поля</h5>
                        </div>
                        <div class="card-body">
                            <?php foreach ($customFields as $field): ?>
                                <div class="mb-3">
                                    <label class="form-label small">
                                        <?= html($field['name']) ?>
                                        <?php if ($field['is_required']): ?>
                                            <span class="text-danger">*</span>
                                        <?php endif; ?>
                                    </label>
                                    
                                    <?php 
                                    $config = json_decode($field['config'] ?? '{}', true);
                                    $value = $fieldModel->getFieldValue('post', $post['id'], $field['system_name']) ?? $config['default_value'] ?? '';
                                    ?>
                                    
                                    <?= $fieldModel->renderFieldInput($field, $value, 'post', $post['id']) ?>
                                    
                                    <?php if (!empty($field['description'])): ?>
                                        <div class="form-text small"><?= html($field['description']) ?></div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0">
                        <h5 class="card-title mb-0">Параметры публикации</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-4">
                            <label class="form-label">Статус поста</label>
                            <select class="form-select" name="status">
                                <option value="draft" <?= $post['status'] == 'draft' ? 'selected' : '' ?>>Черновик</option>
                                <option value="published" <?= $post['status'] == 'published' ? 'selected' : '' ?>>Опубликован</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       id="password_protected" 
                                       name="password_protected"
                                       <?= $post['password_protected'] ? 'checked' : '' ?>>
                                <label class="form-check-label" for="password_protected">
                                    Защитить паролем
                                </label>
                            </div>
                        </div>
                        
                        <div class="mb-4 password-field" style="display: <?= $post['password_protected'] ? 'block' : 'none' ?>;">
                            <label class="form-label">Пароль для доступа</label>
                            <input type="text" 
                                   class="form-control" 
                                   name="password" 
                                   value="<?= html($post['password'] ?? '') ?>">
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0">
                        <h5 class="card-title mb-0">Дата публикации</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="change_publish_date" name="change_publish_date" 
                                    <?= (isset($post['created_at']) && $post['created_at'] != date('Y-m-d H:i:s')) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="change_publish_date">
                                    Изменить дату публикации
                                </label>
                            </div>
                        </div>
                        
                        <div class="publish-date-field" style="display: <?= (isset($post['created_at']) && $post['created_at'] != date('Y-m-d H:i:s')) ? 'block' : 'none' ?>;">
                            <label class="form-label">Дата и время публикации</label>
                            <input type="datetime-local" class="form-control" name="publish_date" 
                                max="<?= date('Y-m-d\TH:i') ?>" 
                                value="<?= isset($post['created_at']) ? date('Y-m-d\TH:i', strtotime($post['created_at'])) : date('Y-m-d\TH:i') ?>">
                            <div class="form-text">
                                Не может быть будущей датой. Текущая дата: <?= date('d.m.Y H:i') ?>
                            </div>
                        </div>
                        
                        <div class="current-publish-date">
                            <small class="text-muted">
                                Текущая дата публикации: 
                                <strong>
                                    <?= isset($post['created_at']) ? date('d.m.Y H:i', strtotime($post['created_at'])) : date('d.m.Y H:i') ?>
                                </strong>
                            </small>
                        </div>
                    </div>
                </div>
                
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0">
                        <h5 class="card-title mb-0">Категория</h5>
                    </div>
                    <div class="card-body">
                        <select class="form-select" name="category_id" required>
                            <option value="">Выберите категорию</option>
                            <?php foreach ($categories as $category): ?>
                                <option value="<?= $category['id'] ?>" <?= $post['category_id'] == $category['id'] ? 'selected' : '' ?>>
                                    <?= html($category['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0">
                        <h5 class="card-title mb-0">Теги</h5>
                    </div>
                    <div class="card-body">
                        <div id="tags-container">
                            <?php foreach ($postTags as $tag): ?>
                                <span class="badge bg-primary me-2 mb-2 tag-badge" data-tag-id="<?= $tag['id'] ?>">
                                    <?= html($tag['name']) ?>
                                    <button type="button" class="btn-close btn-close-white ms-1" style="font-size: 0.7rem;" aria-label="Удалить"></button>
                                </span>
                            <?php endforeach; ?>
                        </div>
                        
                        <input type="text" 
                            class="form-control" 
                            id="tag-search" 
                            placeholder="Начните вводить название тега..."
                            autocomplete="off">
                        
                        <div class="form-text d-flex justify-content-between align-items-center mt-2">
                            <span>Введите название тега и выберите из списка. Можно добавить несколько тегов.</span>
                            <span class="badge bg-light text-dark" id="tags-counter">
                                <span id="current-tags-count"><?= count($postTags) ?></span> / 
                                <span id="max-tags-count"><?php echo \SettingsHelper::get('controller_tags', 'max_tags_per_post', 10); ?></span>
                            </span>
                        </div>
                        
                        <input type="hidden" name="tags_json" id="tags-json" value='<?= json_encode(array_column($postTags, 'id')) ?>'>
                        
                        <div class="dropdown">
                            <div class="dropdown-menu w-100" id="tags-suggestions" style="display: none; max-height: 200px; overflow-y: auto;"></div>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-shield-lock me-1"></i>Настройки видимости
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php
                        $userModel = new UserModel($this->db);
                        $groups = $userModel->getAllGroups();
                        $groups[] = [
                            'id' => 'guest',
                            'name' => 'Гость',
                            'description' => 'Неавторизованные пользователи'
                        ];
                        
                        $showToGroups = $post['show_to_groups'] ? json_decode($post['show_to_groups'], true) : [];
                        $hideFromGroups = $post['hide_from_groups'] ? json_decode($post['hide_from_groups'], true) : [];
                        ?>
                        
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label small">
                                    <i class="bi bi-eye me-1"></i>Показывать группам
                                </label>
                                <select class="form-select form-select-sm" name="show_to_groups[]" multiple size="4">
                                    <option value="">Все группы (если не выбрано)</option>
                                    <?php foreach ($groups as $group): ?>
                                        <option value="<?= $group['id'] ?>" <?= in_array($group['id'], $showToGroups) ? 'selected' : '' ?>>
                                            <?= html($group['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="form-text small">Оставьте пустым чтобы показывать всем. Если выбрать группы - пост будет виден только им.</div>
                            </div>
                            
                            <div class="col-md-12">
                                <label class="form-label small">
                                    <i class="bi bi-eye-slash me-1"></i>Не показывать группам
                                </label>
                                <select class="form-select form-select-sm" name="hide_from_groups[]" multiple size="4">
                                    <option value="">Никому не скрывать</option>
                                    <?php foreach ($groups as $group): ?>
                                        <option value="<?= $group['id'] ?>" <?= in_array($group['id'], $hideFromGroups) ? 'selected' : '' ?>>
                                            <?= html($group['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="form-text small">Выберите группы которым скрыть этот пост</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="allow_comments" name="allow_comments" 
                            value="1" <?= isset($post['allow_comments']) && $post['allow_comments'] == 1 ? 'checked' : 'checked' ?>>
                        <label class="form-check-label" for="allow_comments">
                            Разрешить комментарии
                        </label>
                    </div>
                </div>
                
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i> Обновить пост
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<?php ob_start(); ?>
<script>
    const ADMIN_URL = '<?= ADMIN_URL ?>';
    const BASE_URL = '<?= BASE_URL ?>';
    window.availablePostBlocks = <?= json_encode($postBlockManager->getPostBlocksForJS()) ?>;
    window.initialPostBlocks = <?= json_encode($preparedBlocks ?? []) ?>;
    window.isEditMode = true;
    window.MAX_TAGS_PER_POST = <?php echo \SettingsHelper::get('controller_tags', 'max_tags_per_post', 10); ?>;
</script>
<?php admin_bottom_js(ob_get_clean()); ?>