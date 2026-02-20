<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">
            <?php echo bloggy_icon('bs', 'trophy', '24', '#000', 'me-2'); ?>
            Создание ачивки
        </h4>
        <a href="<?= ADMIN_URL ?>/user-achievements" class="btn btn-outline-secondary btn-sm">
            <?php echo bloggy_icon('bs', 'arrow-left', '18'); ?> Назад
        </a>
    </div>
    
    <form method="post" enctype="multipart/form-data" id="achievementForm">
        <div class="row">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">
                                Название ачивки
                                <span class="text-danger">*</span>
                            </label>
                            <input type="text" class="form-control" name="name" 
                                value="<?= html($achievement['name'] ?? '') ?>" 
                                required maxlength="255">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Описание</label>
                            <textarea class="form-control" name="description" rows="3"
                                maxlength="500"><?= html($achievement['description'] ?? '') ?></textarea>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label fw-bold mb-3">
                                <?php echo bloggy_icon('bs', 'gear', '18', '#000', 'me-2'); ?>
                                Условия получения
                            </label>
                            
                            <div id="conditionsContainer">
                                <div class="condition-item card mb-3">
                                    <div class="card-body">
                                        <div class="row g-3">
                                            <div class="col-md-4">
                                                <label class="form-label small">Тип условия</label>
                                                <select class="form-select condition-type" name="conditions[0][type]">
                                                    <option value="">Выберите тип</option>
                                                    <option value="registration_days">Дней с регистрации</option>
                                                    <option value="comments_count">Количество комментариев</option>
                                                    <option value="likes_count">Количество лайков</option>
                                                    <option value="bookmarks_count">Количество закладок</option>
                                                    <option value="login_days">Дней входа</option>
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <label class="form-label small">Оператор</label>
                                                <select class="form-select condition-operator" name="conditions[0][operator]">
                                                    <option value=">">Больше</option>
                                                    <option value="<">Меньше</option>
                                                    <option value="=">Равно</option>
                                                    <option value=">=">Больше или равно</option>
                                                    <option value="<=">Меньше или равно</option>
                                                    <option value="!=">Не равно</option>
                                                </select>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label small">Значение</label>
                                                <input type="number" class="form-control condition-value" 
                                                    name="conditions[0][value]" min="0" value="1">
                                            </div>
                                            <div class="col-md-1 d-flex align-items-end">
                                                <button type="button" class="btn btn-sm btn-outline-danger remove-condition" 
                                                    style="margin-bottom: 8px;">
                                                    <?php echo bloggy_icon('bs', 'trash', '16'); ?>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="condition-description mt-2 small text-muted"></div>
                                    </div>
                                </div>
                            </div>
                            
                            <button type="button" class="btn btn-sm btn-outline-primary" id="addCondition">
                                <?php echo bloggy_icon('bs', 'plus', '16', '#000', 'me-1'); ?>
                                Добавить условие
                            </button>
                            
                            <div class="form-text mt-2">
                                Если не указаны условия, ачивку можно будет присваивать только вручную
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">
                                Изображение ачивки
                                <span class="text-danger">*</span>
                            </label>
                            <input type="file" class="form-control" name="image" accept="image/*" 
                                id="imageUpload" required>
                            <div class="form-text">Рекомендуемый размер: 128x128 пикселей</div>
                        </div>
                        
                        <div id="imagePreview" class="text-center mt-3" style="display: none;">
                            <img src="" alt="Preview" class="img-thumbnail" style="max-width: 128px;">
                        </div>
                    </div>
                </div>
                
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Тип ачивки</label>
                            <select class="form-select" name="type">
                                <option value="auto" <?= ($achievement['type'] ?? 'auto') == 'auto' ? 'selected' : '' ?>>
                                    Автоматическая (по условиям)
                                </option>
                                <option value="manual" <?= ($achievement['type'] ?? 'auto') == 'manual' ? 'selected' : '' ?>>
                                    Ручная (только администратор)
                                </option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Приоритет</label>
                            <input type="number" class="form-control" name="priority"
                                value="<?= html($achievement['priority'] ?? 0) ?>" min="0">
                            <div class="form-text">Чем выше число, тем выше приоритет в списке</div>
                        </div>
                        
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" name="is_active" 
                                id="isActive" <?= ($achievement['is_active'] ?? 1) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="isActive">
                                Ачивка активна
                            </label>
                        </div>
                    </div>
                </div>
                
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">
                        <?php echo bloggy_icon('bs', 'check-lg', '18', '#fff', 'me-1'); ?>
                        Создать ачивку
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<?php ob_start(); ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    let conditionIndex = 1;
    const conditionTemplates = {
        'registration_days': 'Дней с момента регистрации пользователя',
        'comments_count': 'Количество оставленных комментариев',
        'likes_count': 'Количество поставленных лайков',
        'bookmarks_count': 'Количество добавленных в закладки постов',
        'login_days': 'Количество дней с посещением сайта'
    };
    
    document.getElementById('addCondition').addEventListener('click', function() {
        const container = document.getElementById('conditionsContainer');
        const newCondition = document.createElement('div');
        newCondition.className = 'condition-item card mb-3';
        newCondition.innerHTML = `
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label small">Тип условия</label>
                        <select class="form-select condition-type" name="conditions[${conditionIndex}][type]">
                            <option value="">Выберите тип</option>
                            <option value="registration_days">Дней с регистрации</option>
                            <option value="comments_count">Количество комментариев</option>
                            <option value="likes_count">Количество лайков</option>
                            <option value="bookmarks_count">Количество закладок</option>
                            <option value="login_days">Дней входа</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">Оператор</label>
                        <select class="form-select condition-operator" name="conditions[${conditionIndex}][operator]">
                            <option value=">">Больше</option>
                            <option value="<">Меньше</option>
                            <option value="=">Равно</option>
                            <option value=">=">Больше или равно</option>
                            <option value="<=">Меньше или равно</option>
                            <option value="!=">Не равно</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small">Значение</label>
                        <input type="number" class="form-control condition-value" 
                            name="conditions[${conditionIndex}][value]" min="0" value="1">
                    </div>
                    <div class="col-md-1 d-flex align-items-end">
                        <button type="button" class="btn btn-sm btn-outline-danger remove-condition" 
                            style="margin-bottom: 8px;">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
                <div class="condition-description mt-2 small text-muted"></div>
            </div>
        `;
        container.appendChild(newCondition);
        conditionIndex++;
        addConditionHandlers(newCondition);
    });
    
    function addConditionHandlers(conditionElement) {
        const typeSelect = conditionElement.querySelector('.condition-type');
        const removeBtn = conditionElement.querySelector('.remove-condition');
        const descriptionDiv = conditionElement.querySelector('.condition-description');
        typeSelect.addEventListener('change', function() {
            const desc = conditionTemplates[this.value] || '';
            descriptionDiv.textContent = desc;
        });
        
        removeBtn.addEventListener('click', function() {
            if (document.querySelectorAll('.condition-item').length > 1) {
                conditionElement.remove();
            } else {
                alert('Должно быть хотя бы одно условие');
            }
        });
        
        if (typeSelect.value) {
            descriptionDiv.textContent = conditionTemplates[typeSelect.value] || '';
        }
    }
    
    document.querySelectorAll('.condition-item').forEach(addConditionHandlers);
    
    document.getElementById('imageUpload').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.getElementById('imagePreview');
                const img = preview.querySelector('img');
                img.src = e.target.result;
                preview.style.display = 'block';
            };
            reader.readAsDataURL(file);
        }
    });
    
    document.getElementById('achievementForm').addEventListener('submit', function(e) {
        const nameInput = this.querySelector('input[name="name"]');
        const imageInput = this.querySelector('input[name="image"]');
        
        if (!nameInput.value.trim()) {
            e.preventDefault();
            alert('Пожалуйста, введите название ачивки');
            nameInput.focus();
            return;
        }
        
        if (!imageInput.files || imageInput.files.length === 0) {
            e.preventDefault();
            alert('Пожалуйста, загрузите изображение для ачивки');
            imageInput.focus();
            return;
        }
        
        const typeSelects = this.querySelectorAll('.condition-type');
        let hasValidCondition = false;
        
        typeSelects.forEach(select => {
            if (select.value) {
                const operator = select.closest('.condition-item').querySelector('.condition-operator').value;
                const value = select.closest('.condition-item').querySelector('.condition-value').value;
                
                if (operator && value !== '') {
                    hasValidCondition = true;
                }
            }
        });
        
        if (!hasValidCondition) {
            if (!confirm('Вы не указали условия для автоматической ачивки. Ачивку можно будет присвоить только вручную. Продолжить?')) {
                e.preventDefault();
                return;
            }
        }
    });
});
</script>
<?php admin_bottom_js(ob_get_clean()); ?>