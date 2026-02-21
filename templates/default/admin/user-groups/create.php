<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">
            <?php echo bloggy_icon('bs', 'diagram-3', '24', '#000', 'me-2'); ?>
            <?php echo $pageTitle; ?>
        </h4>
        <a href="<?php echo ADMIN_URL; ?>/user-groups" class="btn btn-outline-secondary btn-sm">
            <?php echo bloggy_icon('bs', 'arrow-left', '16', '#000', 'me-1'); ?> Назад к группам
        </a>
    </div>

    <form method="post">
        <div class="row">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">
                                Название группы
                                <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" name="name" 
                                value="<?php echo html($group['name'] ?? ''); ?>" 
                                required maxlength="100">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Описание</label>
                            <textarea class="form-control" name="description" rows="3" 
                                maxlength="500"><?php echo html($group['description'] ?? ''); ?></textarea>
                            <div class="form-text">Краткое описание назначения группы</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_default" 
                                       id="is_default" value="1" 
                                       <?php echo ($group['is_default'] ?? 0) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="is_default">
                                    Группа по умолчанию
                                </label>
                            </div>
                            <div class="form-text small">
                                Новые пользователи будут автоматически добавляться в эту группу
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary">
                        <?php echo bloggy_icon('bs', 'check-lg', '18', '#fff', 'me-1'); ?>
                        <?php echo isset($group) ? 'Обновить группу' : 'Создать группу'; ?>
                    </button>
                    <a href="<?php echo ADMIN_URL; ?>/user-groups" class="btn btn-outline-secondary">
                        Отмена
                    </a>
                </div>
            </div>
        </div>
    </form>
</div>

<?php ob_start(); ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    form.addEventListener('submit', function(e) {
        const submitBtn = form.querySelector('[type="submit"]');
        const originalBtnHtml = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Сохранение...';
        
        setTimeout(() => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnHtml;
        }, 5000);
    });
});
</script>
<?php admin_bottom_js(ob_get_clean()); ?>