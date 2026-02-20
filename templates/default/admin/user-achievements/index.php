<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">
            <?php echo bloggy_icon('bs', 'trophy', '24', '#000', 'me-2'); ?>
            Управление ачивками
        </h4>
        <div class="d-flex gap-2">
            <a href="<?= ADMIN_URL ?>/user-achievements/create" class="btn btn-primary">
                <?php echo bloggy_icon('bs', 'plus-lg', '20', '#fff', 'me-2'); ?>
                Добавить ачивку
            </a>
        </div>
    </div>
    
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="get" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">Тип ачивки</label>
                    <select name="type" class="form-select">
                        <option value="">Все типы</option>
                        <option value="auto" <?= ($_GET['type'] ?? '') == 'auto' ? 'selected' : '' ?>>Автоматические</option>
                        <option value="manual" <?= ($_GET['type'] ?? '') == 'manual' ? 'selected' : '' ?>>Ручные</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Поиск</label>
                    <input type="text" name="search" class="form-control" placeholder="Название ачивки..." 
                        value="<?php echo html($_GET['search'] ?? '') ?>">
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary w-100">
                        <?php echo bloggy_icon('bs', 'funnel', '18'); ?>
                        Применить фильтр
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <?php if(empty($achievements)) { ?>
                <div class="text-center py-5">
                    <div class="mb-3">
                        <?php echo bloggy_icon('bs', 'trophy', '32', '#838383'); ?>
                    </div>
                    <h5 class="text-muted">Ачивки не найдены</h5>
                    <p class="text-muted">Создайте первую ачивку для пользователей</p>
                    <a href="<?= ADMIN_URL ?>/user-achievements/create" class="btn btn-primary">
                        <?php echo bloggy_icon('bs', 'plus-lg', '20', '#fff', 'me-2'); ?>
                        Создать ачивку
                    </a>
                </div>
            <?php } else { ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th width="50"></th>
                                <th>Ачивка</th>
                                <th>Условия</th>
                                <th>Получили</th>
                                <th>Статус</th>
                                <th class="text-end">Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($achievements as $achievement) { ?>
                                <tr>
                                    <td>
                                        <?php if($achievement['image']): ?>
                                            <img src="<?= BASE_URL ?>/uploads/achievements/<?= $achievement['image'] ?>" 
                                                class="rounded" 
                                                style="width: 40px; height: 40px; object-fit: cover;"
                                                alt="<?php echo html($achievement['name']) ?>">
                                        <?php else: ?>
                                            <div class="d-flex align-items-center justify-content-center rounded" 
                                                style="width: 40px; height: 40px; background: <?= $achievement['icon_color'] ?>;">
                                                <i class="bi bi-<?= $achievement['icon'] ?> text-white"></i>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div>
                                            <strong><?php echo html($achievement['name']) ?></strong>
                                            <?php if($achievement['type'] == 'manual'): ?>
                                                <span class="badge bg-warning ms-2">Ручная</span>
                                            <?php endif; ?>
                                        </div>
                                        <?php if($achievement['description']): ?>
                                            <small class="text-muted"><?php echo html($achievement['description']) ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if(!empty($achievement['conditions'])) { ?>
                                            <div class="d-flex flex-wrap gap-1">
                                                <?php foreach($achievement['conditions'] as $condition): ?>
                                                    <?php 
                                                    $conditionText = '';
                                                    switch($condition['condition_type']) {
                                                        case 'registration_days':
                                                            $conditionText = 'Дней с регистрации';
                                                            break;
                                                        case 'comments_count':
                                                            $conditionText = 'Комментариев';
                                                            break;
                                                        case 'posts_count':
                                                            $conditionText = 'Постов';
                                                            break;
                                                        case 'login_days':
                                                            $conditionText = 'Дней входа';
                                                            break;
                                                    }
                                                    ?>
                                                    <span class="badge bg-info">
                                                        <?php echo html($conditionText) ?> 
                                                        <?php echo html($condition['operator']) ?> 
                                                        <?php echo html($condition['value']) ?>
                                                    </span>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php } else { ?>
                                            <span class="text-muted">Без условий</span>
                                        <?php } ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">
                                            <?= $achievement['unlocked_count'] ?? 0 ?> пользователей
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= $achievement['is_active'] ? 'success' : 'secondary' ?>">
                                            <?= $achievement['is_active'] ? 'Активна' : 'Неактивна' ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex justify-content-end gap-1">
                                            <a href="<?= ADMIN_URL ?>/user-achievements/toggle/<?= $achievement['id'] ?>" 
                                            class="btn btn-sm btn-outline-<?= $achievement['is_active'] ? 'warning' : 'success' ?>"
                                            title="<?= $achievement['is_active'] ? 'Деактивировать' : 'Активировать' ?>">
                                                <i class="bi bi-<?= $achievement['is_active'] ? 'pause' : 'play' ?>"></i>
                                            </a>
                                            
                                            <a href="<?= ADMIN_URL ?>/user-achievements/edit/<?= $achievement['id'] ?>" 
                                            class="btn btn-sm btn-outline-primary"
                                            title="Редактировать">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            
                                            <a href="<?= ADMIN_URL ?>/user-achievements/delete/<?= $achievement['id'] ?>" 
                                            class="btn btn-sm btn-outline-danger"
                                            onclick="return confirm('Вы уверены, что хотите удалить эту ачивку?')"
                                            title="Удалить">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            <?php } ?>
        </div>
    </div>
</div>