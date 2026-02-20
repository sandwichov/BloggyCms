<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">
            <i class="bi bi-shield-lock me-2"></i>
            Права доступа: <?= html($group['name']) ?>
        </h4>
        <a href="<?= ADMIN_URL ?>/user-groups" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Назад к группам
        </a>
    </div>

    <form method="post">
        <div class="row">
            <div class="col-lg-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <?php if(empty($allPermissions)): ?>
                            <div class="text-center py-4">
                                <i class="bi bi-shield-slash text-muted" style="font-size: 3rem;"></i>
                                <h5 class="text-muted mt-3">Нет доступных прав</h5>
                                <p class="text-muted">Создайте файлы permissions.php в контроллерах</p>
                            </div>
                        <?php else: ?>
                            <?php foreach($allPermissions as $controller => $permissions): ?>
                            <div class="mb-4 pb-3 border-bottom">
                                <h6 class="mb-3 text-uppercase text-primary fw-bold">
                                    <i class="bi bi-folder me-2"></i>
                                    <?= html(ucfirst($controller)) ?>
                                </h6>
                                
                                <div class="row">
                                    <?php foreach($permissions as $key => $permission): ?>
                                    <div class="col-md-6 col-lg-4 mb-3">
                                        <div class="card border h-100">
                                            <div class="card-body p-3">
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" 
                                                           name="permissions[]" value="<?= $key ?>"
                                                           id="perm_<?= $key ?>"
                                                           <?= in_array($key, $groupPermissions) ? 'checked' : '' ?>>
                                                    <label class="form-check-label fw-bold" for="perm_<?= $key ?>">
                                                        <?= html($permission['title']) ?>
                                                    </label>
                                                </div>
                                                <?php if(!empty($permission['description'])): ?>
                                                <p class="small text-muted mb-0">
                                                    <?= html($permission['description']) ?>
                                                </p>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>

                        <div class="d-flex gap-2 mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-lg me-1"></i>Сохранить права
                            </button>
                            <a href="<?= ADMIN_URL ?>/user-groups" class="btn btn-outline-secondary">
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

    function setupSectionSelectors() {
        document.querySelectorAll('.permissions-section').forEach(section => {
            const checkboxes = section.querySelectorAll('input[type="checkbox"]');
            const selectAllBtn = section.querySelector('.select-all-btn');
            
            if (selectAllBtn) {
                selectAllBtn.addEventListener('click', function() {
                    const allChecked = Array.from(checkboxes).every(cb => cb.checked);
                    checkboxes.forEach(cb => cb.checked = !allChecked);
                });
            }
        });
    }
    
    setupSectionSelectors();
});
</script>
<?php admin_bottom_js(ob_get_clean()); ?>