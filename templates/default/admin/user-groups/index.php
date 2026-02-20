<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">
            <i class="bi bi-diagram-3 me-2"></i>
            Группы пользователей
        </h4>
        <a href="<?= ADMIN_URL ?>/user-groups/create" class="btn btn-primary">
            <i class="bi bi-plus-lg me-2"></i>Добавить группу
        </a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <?php if(empty($groups)): ?>
                <div class="text-center py-5">
                    <div class="mb-3">
                        <i class="bi bi-diagram-3 text-muted" style="font-size: 3rem;"></i>
                    </div>
                    <h5 class="text-muted">Группы не найдены</h5>
                    <p class="text-muted">Создайте первую группу пользователей</p>
                    <a href="<?= ADMIN_URL ?>/user-groups/create" class="btn btn-primary">
                        <i class="bi bi-plus-lg me-2"></i>Добавить группу
                    </a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Название</th>
                                <th>Описание</th>
                                <th>Пользователей</th>
                                <th>По умолчанию</th>
                                <th class="text-end">Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($groups as $group): ?>
                            <tr>
                                <td>
                                    <strong><?= html($group['name']) ?></strong>
                                </td>
                                <td>
                                    <?= html($group['description'] ?? '') ?>
                                </td>
                                <td>
                                    <?php
                                        $userCount = $db->fetchValue(
                                            "SELECT COUNT(*) FROM users_groups WHERE group_id = ?", 
                                            [$group['id']]
                                        );
                                    ?>
                                    <span class="badge bg-secondary"><?= $userCount ?></span>
                                </td>
                                <td>
                                    <?php if($group['is_default']): ?>
                                        <span class="badge bg-success">Да</span>
                                    <?php else: ?>
                                        <span class="badge bg-light text-dark">Нет</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="d-flex justify-content-end gap-1">
                                        <a href="<?= ADMIN_URL ?>/user-groups/permissions/<?= $group['id'] ?>" 
                                           class="btn btn-sm btn-outline-warning"
                                           title="Права доступа">
                                            <i class="bi bi-shield-lock"></i>
                                        </a>
                                        <a href="<?= ADMIN_URL ?>/user-groups/edit/<?= $group['id'] ?>" 
                                           class="btn btn-sm btn-outline-primary"
                                           title="Редактировать">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <?php if(!$group['is_default']): ?>
                                        <a href="<?= ADMIN_URL ?>/user-groups/delete/<?= $group['id'] ?>" 
                                           class="btn btn-sm btn-outline-danger"
                                           onclick="return confirm('Вы уверены, что хотите удалить эту группу?')"
                                           title="Удалить">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>