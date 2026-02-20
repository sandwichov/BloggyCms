<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><?php echo bloggy_icon('bs', 'people', '24', '#000', 'me-2 controller-svg'); ?> Пользователи</h4>
        <div class="d-flex gap-2">
            <a href="<?= ADMIN_URL ?>/user-groups" class="btn btn-outline-secondary"><?php echo bloggy_icon('bs', 'diagram-3', '20'); ?>Группы</a>
            <a href="<?= ADMIN_URL ?>/user-achievements" class="btn btn-outline-secondary"><?php echo bloggy_icon('bs', 'trophy', '20'); ?>Ачивки</a>
            <a href="<?= ADMIN_URL ?>/settings?tab=components&controller=users" class="btn btn-outline-secondary"><?php echo bloggy_icon('bs', 'gear-fill', '20'); ?>Настройки</a>
            <a href="<?= ADMIN_URL ?>/users/create" class="btn btn-primary"><?php echo bloggy_icon('bs', 'plus-lg', '20', '#fff', 'me-2'); ?>Добавить пользователя</a>
        </div>
    </div>

    <?php if(SettingsHelper::get('controller_users', 'show_filter') == true) { ?>
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <form method="get" class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label">Группа</label>
                        <select name="group" class="form-select">
                            <option value="">Все группы</option>
                            <?php foreach($allGroups as $group) { ?>
                                <option value="<?= $group['id'] ?>" <?= ($_GET['group'] ?? '') == $group['id'] ? 'selected' : '' ?>>
                                    <?php echo html($group['name']) ?>
                                </option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Статус</label>
                        <select name="status" class="form-select">
                            <option value="">Все статусы</option>
                            <option value="active" <?= ($_GET['status'] ?? '') == 'active' ? 'selected' : '' ?>>Активные</option>
                            <option value="banned" <?= ($_GET['status'] ?? '') == 'banned' ? 'selected' : '' ?>>Заблокированные</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Поиск</label>
                        <input type="text" name="search" class="form-control" placeholder="Имя или email..." 
                            value="<?php echo html($_GET['search'] ?? '') ?>">
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary w-100"><?php echo bloggy_icon('bs', 'funnel', '18'); ?>Применить фильтр</button>
                    </div>
                </form>
            </div>
        </div>
    <?php } ?>

    <?php if(SettingsHelper::get('controller_users', 'show_info') == true) { ?>
        <div class="alert alert-info d-flex align-items-center mb-3">
            <?php echo bloggy_icon('bs', 'info-circle', '16', '#5AAFC9', 'me-2'); ?>
            <span><?php echo html($randomHint); ?></span>
        </div>
    <?php } ?>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <?php if(empty($users)) { ?>
                <div class="text-center py-5">
                    <div class="mb-3">
                        <?php echo bloggy_icon('bs', 'people', '32', '#838383', 'me-2 controller-svg'); ?>
                    </div>
                    <h5 class="text-muted">Пользователи не найдены</h5>
                    <p class="text-muted">Попробуйте изменить параметры фильтра</p>
                    <a href="<?= ADMIN_URL ?>/users/create" class="btn btn-primary">
                        <?php echo bloggy_icon('bs', 'plus-lg', '20', '#fff', 'me-2'); ?> Добавить пользователя
                    </a>
                </div>
            <?php } else { ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Пользователь</th>
                                <th>Email</th>
                                <th>Группы</th>
                                <th>Статус</th>
                                <th>Дата регистрации</th>
                                <th class="text-end">Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($users as $user) { ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <?php if($user['avatar']): ?>
                                                <img src="<?= BASE_URL ?>/uploads/avatars/<?= $user['avatar'] ?>" 
                                                    class="rounded-circle me-2" 
                                                    style="width: 40px; height: 40px; object-fit: cover;"
                                                    alt="<?php echo html($user['username']) ?>">
                                            <?php else: ?>
                                                <div class="rounded-circle me-2 d-flex align-items-center justify-content-center bg-light" 
                                                    style="width: 40px; height: 40px;">
                                                    <i class="bi bi-person text-muted"></i>
                                                </div>
                                            <?php endif; ?>
                                            <div>
                                                <strong><?php echo html($user['username']) ?></strong>
                                                <?php if($user['id'] == $_SESSION['user_id']): ?>
                                                    <span class="badge bg-info ms-2">Вы</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?php echo html($user['email']) ?></td>
                                    <td>
                                        <?php if(!empty($user['groups'])) { ?>
                                            <div class="d-flex flex-wrap gap-1">
                                                <?php foreach($user['groups'] as $group): ?>
                                                    <span class="badge bg-secondary">
                                                        <?php echo html($group['name']) ?>
                                                    </span>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php } else { ?>
                                            <span class="text-muted">Нет групп</span>
                                        <?php } ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?= $user['status'] === 'active' ? 'success' : 'danger' ?>">
                                            <?= $user['status'] === 'active' ? 'Активен' : 'Заблокирован' ?>
                                        </span>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            <?= date('d.m.Y H:i', strtotime($user['created_at'])) ?>
                                        </small>
                                    </td>
                                    <td>
                                        <div class="d-flex justify-content-end gap-1">
                                            <?php if($user['status'] === 'active' && $user['id'] != $_SESSION['user_id']): ?>
                                                <a href="<?= ADMIN_URL ?>/users/toggle-status/<?= $user['id'] ?>" 
                                                class="btn btn-sm btn-outline-warning"
                                                title="Заблокировать"
                                                onclick="return confirm('Заблокировать пользователя?')">
                                                    <i class="bi bi-lock"></i>
                                                </a>
                                            <?php elseif($user['status'] === 'banned' && $user['id'] != $_SESSION['user_id']): ?>
                                                <a href="<?= ADMIN_URL ?>/users/toggle-status/<?= $user['id'] ?>" 
                                                class="btn btn-sm btn-outline-success"
                                                title="Разблокировать"
                                                onclick="return confirm('Разблокировать пользователя?')">
                                                    <i class="bi bi-unlock"></i>
                                                </a>
                                            <?php endif; ?>
                                            
                                            <a href="<?= ADMIN_URL ?>/users/edit/<?= $user['id'] ?>" 
                                                class="btn btn-sm btn-outline-primary"
                                                title="Редактировать">
                                                <i class="bi bi-pencil"></i>
                                            </a>

                                            <a href="<?= ADMIN_URL ?>/users/quick-assign-achievement/<?= $user['id'] ?>" 
                                                class="btn btn-sm btn-outline-info"
                                                title="Назначить ачивку">
                                                <?php echo bloggy_icon('bs', 'trophy', '16'); ?>
                                            </a>

                                            <?php if($user['id'] != $_SESSION['user_id']): ?>
                                            <a href="<?= ADMIN_URL ?>/users/delete/<?= $user['id'] ?>" 
                                                class="btn btn-sm btn-outline-danger"
                                                onclick="return confirm('Вы уверены, что хотите удалить этого пользователя?')"
                                                title="Удалить">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                            <?php endif; ?>
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