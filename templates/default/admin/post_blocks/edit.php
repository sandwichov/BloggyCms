<?php
    add_admin_js('templates/default/admin/assets/js/controllers/ace.js');
    add_admin_js('templates/default/admin/assets/js/controllers/mode-html.js');
    add_admin_js('templates/default/admin/assets/js/controllers/theme-monokai.js');
    add_admin_js('templates/default/admin/assets/js/controllers/postblocks.js');
?>

<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">
            <i class="bi bi-gear me-2"></i>
            Настройки: <?= html($postBlock['name']) ?>
        </h4>
        <a href="<?= ADMIN_URL ?>/post-blocks" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Назад
        </a>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Основные настройки</h5>
                </div>
                <div class="card-body">
                    <form method="POST" id="blockSettingsForm">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="enable_in_posts" 
                                           id="enable_in_posts" <?= $settings['enable_in_posts'] ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="enable_in_posts">
                                        Включить в постах
                                    </label>
                                </div>
                                <div class="form-text">
                                    Блок будет доступен при создании и редактировании постов
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" name="enable_in_pages" 
                                           id="enable_in_pages" <?= $settings['enable_in_pages'] ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="enable_in_pages">
                                        Включить в страницах
                                    </label>
                                </div>
                                <div class="form-text">
                                    Блок будет доступен при создании и редактировании страниц
                                </div>
                            </div>
                            
                            <div class="col-12">
                                <label class="form-label">Шаблон блока</label>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div>
                                        <small class="text-muted">Используйте шорткоды для динамического контента</small>
                                    </div>
                                    <button type="button" id="load-template" class="btn btn-sm btn-outline-secondary">
                                        <i class="bi bi-arrow-clockwise"></i> Загрузить стандартный шаблон
                                    </button>
                                </div>
                                <div id="template-editor" style="height: 400px; width: 100%; border: 1px solid #dee2e6; border-radius: 0.375rem;"></div>
                                <textarea name="template" id="template" style="display: none;"><?= html($settings['template']) ?></textarea>
                                <div class="form-text">
                                    Используйте шорткоды для динамического контента. Оставьте пустым для использования стандартного шаблона.
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-lg me-1"></i> Сохранить настройки
                            </button>
                        </div>
                    </form>

                    <div class="col-12 mt-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="mb-0">Пресеты блока</h5>
                            <button type="button" class="btn btn-sm btn-success" id="add-preset-btn">
                                <i class="bi bi-plus-circle me-1"></i> Добавить пресет
                            </button>
                        </div>
                        
                        <div class="card">
                            <div class="card-body">
                                <div id="presets-container">
                                    <div class="text-center text-muted py-4" id="no-presets-message">
                                        <?php echo bloggy_icon('bs', 'pencil', '32', '#000'); ?>
                                        <p class="mb-0">Пресеты не добавлены</p>
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
                <div class="card-header bg-white">
                    <h5 class="mb-0">Информация о блоке</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="post-block-icon me-3">
                            <i class="<?= $postBlock['icon'] ?>"></i>
                        </div>
                        <div>
                            <h6 class="mb-1"><?= html($postBlock['name']) ?></h6>
                            <p class="text-muted small mb-0"><?= html($postBlock['description']) ?></p>
                        </div>
                    </div>
                    
                    <div class="small">
                        <div class="mb-2">
                            <strong>Системное имя:</strong>
                            <code class="d-block mt-1"><?= $postBlock['system_name'] ?></code>
                        </div>
                        <div class="mb-2">
                            <strong>Категория:</strong>
                            <span class="badge bg-secondary"><?= $postBlock['category'] ?></span>
                        </div>
                        <div class="mb-2">
                            <strong>Версия:</strong> <?= $postBlock['version'] ?>
                        </div>
                        <div class="mb-2">
                            <strong>Автор:</strong> <?= $postBlock['author'] ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Доступные шорткоды</h5>
                </div>
                <div class="card-body">
                    <?php if(empty($shortcodes)): ?>
                        <p class="text-muted mb-0">Для этого блока нет специальных шорткодов.</p>
                    <?php else: ?>
                        <div class="shortcodes-list">
                            <?php foreach($shortcodes as $shortcode => $description): ?>
                                <div class="shortcode-item mb-3 p-3 border rounded">
                                    <code class="text-primary d-block mb-1 shortcode-insert" data-shortcode="<?= html($shortcode) ?>"><?= html($shortcode) ?></code>
                                    <div class="text-muted small"><?= html($description) ?></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="presetModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="presetModalLabel">Редактирование пресета</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="presetForm">
                    <input type="hidden" name="preset_id" id="preset_id">
                    <input type="hidden" name="system_name" id="preset_system_name" value="<?= $postBlock['system_name'] ?>">
                    
                    <div class="mb-3">
                        <label class="form-label">Имя пресета</label>
                        <input type="text" class="form-control" name="preset_name" id="preset_name" required>
                        <div class="form-text">Уникальное имя для этого пресета</div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Шаблон пресета</label>
                        <div id="preset-template-editor" style="height: 300px; width: 100%; border: 1px solid #dee2e6; border-radius: 0.375rem;"></div>
                        <textarea name="preset_template" id="preset_template" style="display: none;"></textarea>
                        <div class="form-text">
                            Используйте шорткоды для динамического контента
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                <button type="button" class="btn btn-danger" id="delete-preset-btn" style="display: none;">
                    <i class="bi bi-trash me-1"></i> Удалить
                </button>
                <button type="button" class="btn btn-primary" id="save-preset-btn">
                    <i class="bi bi-check-lg me-1"></i> Сохранить
                </button>
            </div>
        </div>
    </div>
</div>

<div id="postblock-data" 
     data-system-name="<?= $postBlock['system_name'] ?>"
     data-admin-url="<?= ADMIN_URL ?>"
     style="display: none;">
</div>