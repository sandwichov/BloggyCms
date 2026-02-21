<?php
    add_admin_js('templates/default/admin/assets/js/controllers/image-upload.js');
    add_admin_js('templates/default/admin/assets/js/controllers/post-blocks.js');
    add_admin_css('templates/default/admin/assets/css/controllers/post-blocks.css');
?>

<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">
            <?php echo bloggy_icon('bs', 'file-earmark-text', '24', '#000', 'me-2'); ?>
            Создание страницы
        </h4>
        <a href="<?php echo ADMIN_URL; ?>/pages" class="btn btn-outline-secondary btn-sm">
            <?php echo bloggy_icon('bs', 'arrow-left', '16', '#000', 'me-1'); ?>
            Назад к страницам
        </a>
    </div>

    <form method="post" id="page-form" enctype="multipart/form-data">
        <input type="hidden" name="post_blocks" id="post_blocks_data" value="">

        <div class="row">
            <div class="col-lg-9">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <div class="mb-4">
                            <label class="form-label">Заголовок страницы</label>
                            <input type="text" 
                                   class="form-control form-control-lg" 
                                   name="title" 
                                   value="<?php echo isset($data['title']) ? html($data['title']) : ''; ?>" 
                                   placeholder="Введите заголовок страницы"
                                   required>
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
                                                <?php echo bloggy_icon('bs', 'x', '16', '#000'); ?>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body py-2">
                                <div id="post-block-buttons" class="d-flex flex-wrap gap-1"></div>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-body p-0">
                                <div id="post-blocks-container" class="min-h-100" style="min-height: 400px;">
                                    <div class="text-center text-muted py-5 empty-state">
                                        <?php echo bloggy_icon('bs', 'inbox', '48', '#6C6C6C', 'mb-3'); ?>
                                        <p class="mb-1">Нет добавленных блоков</p>
                                        <small class="text-muted">Добавьте блоки из панели выше для создания контента</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <div class="col-lg-3">
                <?php
                $fieldModel = new FieldModel($this->db);
                $customFields = $fieldModel->getActiveByEntityType('page');
                ?>

                <?php if (!empty($customFields)) { ?>
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0">
                        <h5 class="card-title mb-0">Дополнительные поля</h5>
                    </div>
                    <div class="card-body">
                        <?php foreach ($customFields as $field) { ?>
                            <div class="mb-3">
                                <label class="form-label small">
                                    <?php echo html($field['name']); ?>
                                    <?php if ($field['is_required']) { ?>
                                        <span class="text-danger">*</span>
                                    <?php } ?>
                                </label>
                                
                                <?php 
                                $config = json_decode($field['config'] ?? '{}', true);
                                $value = $config['default_value'] ?? '';
                                ?>
                                
                                <?php echo $fieldModel->renderFieldInput($field, $value, 'page', 0); ?>
                                
                                <?php if (!empty($field['description'])) { ?>
                                    <div class="form-text small"><?php echo html($field['description']); ?></div>
                                <?php } ?>
                            </div>
                        <?php } ?>
                    </div>
                </div>
                <?php } ?>

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0">
                        <h5 class="card-title mb-0">Настройки публикации</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-4">
                            <label class="form-label">Статус</label>
                            <select name="status" class="form-select" required>
                                <option value="draft" <?php echo (isset($data['status']) && $data['status'] == 'draft') ? 'selected' : ''; ?>>Черновик</option>
                                <option value="published" <?php echo (isset($data['status']) && $data['status'] == 'published') ? 'selected' : ''; ?>>Опубликовано</option>
                            </select>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <?php echo bloggy_icon('bs', 'check-lg', '16', '#fff', 'me-1'); ?>
                                Создать страницу
                            </button>
                            <a href="<?php echo ADMIN_URL; ?>/pages" class="btn btn-outline-secondary">
                                <?php echo bloggy_icon('bs', 'x-lg', '16', '#000', 'me-1'); ?>
                                Отмена
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
    const ADMIN_URL = '<?php echo ADMIN_URL; ?>';
    const BASE_URL = '<?php echo BASE_URL; ?>';
    window.availablePostBlocks = <?php echo json_encode($postBlockManager->getPostBlocksForJS('page')); ?>;
    window.initialPostBlocks = [];
</script>
<?php admin_bottom_js(ob_get_clean()); ?>