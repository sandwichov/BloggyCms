<?php
$fieldModel = new FieldModel($this->db);
$customFields = $fieldModel->getActiveByEntityType('user');
?>

<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">
            <i class="bi bi-person-gear me-2"></i>
            Редактирование пользователя
        </h4>
        <a href="<?= ADMIN_URL ?>/users" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Назад к пользователям
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

                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="change_password" name="change_password">
                                <label class="form-check-label" for="change_password">
                                    Изменить пароль
                                </label>
                            </div>
                        </div>

                        <div class="row password-fields" style="display: none;">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Новый пароль</label>
                                    <input type="password" class="form-control" name="password">
                                    <div class="form-text">Минимум 6 символов</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Подтверждение пароля</label>
                                    <input type="password" class="form-control" name="password_confirm">
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
                    <div class="card-header bg-white border-0">
                        <h5 class="card-title mb-0">Аватар</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Загрузить новый аватар</label>
                            <input type="file" class="form-control" name="avatar" accept="image/*">
                            <div class="form-text">
                                Разрешены: JPG, PNG, GIF, WebP. Максимальный размер: 2MB
                            </div>
                        </div>
                        
                        <?php if (!empty($user['avatar']) && $user['avatar'] !== 'default.jpg'): ?>
                        <div class="mt-3">
                            <label class="form-label">Текущий аватар</label>
                            <div>
                                <img src="<?= BASE_URL ?>/uploads/avatars/<?= $user['avatar'] ?>" 
                                     class="rounded" style="max-width: 150px; max-height: 150px;">
                                <div class="mt-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="remove_avatar" name="remove_avatar">
                                        <label class="form-check-label" for="remove_avatar">
                                            Удалить аватар
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
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
                                    $currentValue = $fieldModel->getFieldValue('user', $user['id'], $field['system_name']);
                                    ?>
                                    
                                    <?= $fieldModel->renderFieldInput($field, $currentValue, 'user', $user['id']) ?>
                                    
                                    <?php if (!empty($field['description'])): ?>
                                        <div class="form-text small"><?= html($field['description']) ?></div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-body text-center text-muted">
                            <i class="bi bi-input-cursor-text" style="font-size: 2rem;"></i>
                            <p class="mt-2 mb-0">Нет дополнительных полей</p>
                            <small>Вы можете добавить их в разделе "Поля"</small>
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
                            $userGroups = $userModel->getUserGroups($user['id'] ?? 0);
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
                                            <?= in_array($group['id'], $userGroups) ? 'checked' : '' ?>>
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
                                    Измените группы пользователя
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
                    <div class="card-header bg-white border-0">
                        <h5 class="card-title mb-0">Настройки пользователя</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-4">
                            <label class="form-label">Роль</label>
                            <select class="form-select" name="role" required>
                                <option value="user" <?= ($user['role'] ?? 'user') === 'user' ? 'selected' : '' ?>>Пользователь</option>
                                <option value="admin" <?= ($user['role'] ?? 'user') === 'admin' ? 'selected' : '' ?>>Администратор</option>
                            </select>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label">Статус</label>
                            <select class="form-select" name="status" required>
                                <option value="active" <?= ($user['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Активен</option>
                                <option value="banned" <?= ($user['status'] ?? 'active') === 'banned' ? 'selected' : '' ?>>Заблокирован</option>
                            </select>
                        </div>

                        <div class="text-muted small">
                            <div><strong>ID:</strong> <?= $user['id'] ?></div>
                            <div><strong>Зарегистрирован:</strong> <?= date('d.m.Y H:i', strtotime($user['created_at'])) ?></div>
                        </div>
                    </div>
                </div>
                
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i> Сохранить изменения
                    </button>
                    <a href="<?= ADMIN_URL ?>/users" class="btn btn-outline-secondary">
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
    const changePasswordCheckbox = document.getElementById('change_password');
    const passwordFields = document.querySelector('.password-fields');
    
    if (changePasswordCheckbox && passwordFields) {
        changePasswordCheckbox.addEventListener('change', function() {
            passwordFields.style.display = this.checked ? 'block' : 'none';
        });
    }
    
    const passwordInput = document.querySelector('input[name="password"]');
    const confirmInput = document.querySelector('input[name="password_confirm"]');
    const form = document.querySelector('form');
    
    function validatePasswords() {
        if (passwordInput.value && passwordInput.value !== confirmInput.value) {
            confirmInput.setCustomValidity('Пароли не совпадают');
        } else {
            confirmInput.setCustomValidity('');
        }
    }
    
    if (passwordInput && confirmInput) {
        passwordInput.addEventListener('input', validatePasswords);
        confirmInput.addEventListener('input', validatePasswords);
        
        passwordInput.addEventListener('input', function() {
            if (this.value.length > 0 && this.value.length < 6) {
                this.setCustomValidity('Пароль должен содержать минимум 6 символов');
            } else {
                this.setCustomValidity('');
            }
        });
    }
    
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