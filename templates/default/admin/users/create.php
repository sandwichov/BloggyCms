<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">
            <i class="bi bi-person-plus me-2"></i>
            Создание пользователя
        </h4>
        <a href="<?= ADMIN_URL ?>/users" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Назад к пользователям
        </a>
    </div>

    <form method="post" enctype="multipart/form-data">
        <div class="row">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">
                                        Имя пользователя
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" class="form-control" name="username" 
                                        value="<?= html($user['username'] ?? '') ?>" 
                                        required maxlength="50">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">
                                        Email
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input type="email" class="form-control" name="email" 
                                        value="<?= html($user['email'] ?? '') ?>" 
                                        required maxlength="100">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">
                                        Пароль
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input type="password" class="form-control" name="password" required>
                                    <div class="form-text">Минимум 6 символов</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">
                                        Подтверждение пароля
                                        <span class="text-danger">*</span>
                                    </label>
                                    <input type="password" class="form-control" name="password_confirm" required>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Отображаемое имя</label>
                            <input type="text" class="form-control" name="display_name" 
                                value="<?= html($user['display_name'] ?? '') ?>" 
                                maxlength="100">
                            <div class="form-text">Если не указано, будет использоваться имя пользователя</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">О себе</label>
                            <textarea class="form-control" name="bio" rows="3" 
                                    maxlength="500"><?= html($user['bio'] ?? '') ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Веб-сайт</label>
                            <input type="url" class="form-control" name="website" 
                                value="<?= html($user['website'] ?? '') ?>" 
                                maxlength="255">
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Загрузить аватар</label>
                            <input type="file" class="form-control" name="avatar" accept="image/*">
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <?php
                    $fieldModel = new FieldModel($db);
                    $customFields = $fieldModel->getActiveByEntityType('user');
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
                                    $value = $config['default_value'] ?? '';
                                    ?>
                                    
                                    <?= $fieldModel->renderFieldInput($field, $value, 'user', 0) ?>
                                    
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
                        <h5 class="card-title mb-0">Группы пользователя</h5>
                    </div>
                    <div class="card-body">
                        <?php
                            $userModel = new UserModel($db);
                            $allGroups = $userModel->getAllGroups();
                        ?>
                        <?php if (!empty($allGroups)): ?>
                            <div class="mb-3">
                                <label class="form-label">Выберите группы</label>
                                <div class="border rounded p-3" style="max-height: 200px; overflow-y: auto;">
                                    <?php foreach($allGroups as $group): ?>
                                    <div class="form-check mb-2">
                                        <input class="form-check-input" type="checkbox" 
                                            name="groups[]" value="<?= $group['id'] ?>"
                                            id="group_<?= $group['id'] ?>"
                                            <?= ($group['is_default'] ?? 0) ? 'checked' : '' ?>>
                                        <label class="form-check-label" for="group_<?= $group['id'] ?>">
                                            <strong><?= html($group['name']) ?></strong>
                                            <?php if($group['is_default']): ?>
                                                <span class="badge bg-success ms-1">по умолчанию</span>
                                            <?php endif; ?>
                                            <?php if($group['description']): ?>
                                                <br>
                                                <small class="text-muted"><?= html($group['description']) ?></small>
                                            <?php endif; ?>
                                        </label>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                <div class="form-text">
                                    Пользователь будет добавлен в выбранные группы
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="text-center text-muted py-3">
                                <i class="bi bi-diagram-3" style="font-size: 2rem;"></i>
                                <p class="mt-2 mb-0">Группы не созданы</p>
                                <a href="<?= ADMIN_URL ?>/user-groups/create" class="btn btn-sm btn-outline-primary mt-2">
                                    Создать группу
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0">
                        <h5 class="card-title mb-0">
                            <?php echo bloggy_icon('bs', 'trophy', '20', '#000', 'me-2'); ?>
                            Ачивки пользователя
                        </h5>
                    </div>
                    <div class="card-body">
                        <?php
                            $userModel = new UserModel($db);
                            $allAchievements = $userModel->getAllAchievements(['active' => true]);
                            $userAchievements = [];
                            if (isset($user['id']) && $user['id']) {
                                $userAchievementIds = $userModel->getUserUnlockedAchievements($user['id']);
                                $userAchievements = array_column($userAchievementIds, 'id');
                            }
                        ?>
                        
                        <?php if (!empty($allAchievements)): ?>
                            <div class="mb-3">
                                <label class="form-label">Выберите ачивки для пользователя</label>
                                <div class="border rounded p-3" style="max-height: 300px; overflow-y: auto;">
                                    <?php foreach($allAchievements as $achievement): ?>
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="checkbox" 
                                                name="achievements[]" value="<?= $achievement['id'] ?>"
                                                id="achievement_<?= $achievement['id'] ?>"
                                                <?= in_array($achievement['id'], $userAchievements) ? 'checked' : '' ?>
                                                <?= $achievement['type'] == 'auto' ? 'disabled title="Автоматическая ачивка"' : '' ?>>
                                            <label class="form-check-label d-flex align-items-center" for="achievement_<?= $achievement['id'] ?>">
                                                <?php if($achievement['image']): ?>
                                                    <img src="<?= BASE_URL ?>/uploads/achievements/<?= $achievement['image'] ?>" 
                                                        class="rounded me-2" 
                                                        style="width: 24px; height: 24px; object-fit: cover;"
                                                        alt="<?= html($achievement['name']) ?>">
                                                <?php else: ?>
                                                    <div class="rounded me-2 d-flex align-items-center justify-content-center" 
                                                        style="width: 24px; height: 24px; background: <?= $achievement['icon_color'] ?>;">
                                                        <i class="bi bi-<?= $achievement['icon'] ?> text-white" style="font-size: 12px;"></i>
                                                    </div>
                                                <?php endif; ?>
                                                
                                                <div>
                                                    <strong><?= html($achievement['name']) ?></strong>
                                                    <?php if($achievement['type'] == 'auto'): ?>
                                                        <span class="badge bg-info ms-1 small">авто</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-warning ms-1 small">ручная</span>
                                                    <?php endif; ?>
                                                    <?php if($achievement['description']): ?>
                                                        <br>
                                                        <small class="text-muted"><?= html($achievement['description']) ?></small>
                                                    <?php endif; ?>
                                                </div>
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                <div class="form-text">
                                    Только ручные ачивки можно назначать вручную. Автоматические ачивки присваиваются при выполнении условий.
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="text-center text-muted py-3">
                                <i class="bi bi-trophy" style="font-size: 2rem;"></i>
                                <p class="mt-2 mb-0">Ачивки не созданы</p>
                                <a href="<?= ADMIN_URL ?>/user-achievements/create" class="btn btn-sm btn-outline-primary mt-2">
                                    Создать ачивку
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <div class="mb-4">
                            <label class="form-label">Роль</label>
                            <select class="form-select" name="role" required>
                                <option value="user">Пользователь</option>
                                <option value="admin">Администратор</option>
                            </select>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label">Статус</label>
                            <select class="form-select" name="status" required>
                                <option value="active">Активен</option>
                                <option value="banned">Заблокирован</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i> Создать пользователя
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<?php ob_start(); ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const passwordInput = document.querySelector('input[name="password"]');
    const confirmInput = document.querySelector('input[name="password_confirm"]');
    const form = document.querySelector('form');
    
    function validatePasswords() {
        if (passwordInput.value !== confirmInput.value) {
            confirmInput.setCustomValidity('Пароли не совпадают');
        } else {
            confirmInput.setCustomValidity('');
        }
    }
    
    passwordInput.addEventListener('input', validatePasswords);
    confirmInput.addEventListener('input', validatePasswords);
    passwordInput.addEventListener('input', function() {
        if (this.value.length > 0 && this.value.length < 6) {
            this.setCustomValidity('Пароль должен содержать минимум 6 символов');
        } else {
            this.setCustomValidity('');
        }
    });
    
    form.addEventListener('submit', function(e) {
        const submitBtn = form.querySelector('[type="submit"]');
        const originalBtnHtml = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Создание...';
        
        setTimeout(() => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnHtml;
        }, 5000);
    });
});
</script>
<?php admin_bottom_js(ob_get_clean()); ?>