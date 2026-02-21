<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">
            <?php echo bloggy_icon('bs', 'diagram-3', '24', '#000', 'me-2'); ?>
            Группы пользователей
        </h4>
        <a href="<?php echo ADMIN_URL; ?>/user-groups/create" class="btn btn-primary">
            <?php echo bloggy_icon('bs', 'plus-lg', '16', '#fff', 'me-2'); ?>
            Добавить группу
        </a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <?php if (empty($groups)) { ?>
                <div class="text-center py-5">
                    <div class="mb-3">
                        <?php echo bloggy_icon('bs', 'diagram-3', '48', '#6C6C6C'); ?>
                    </div>
                    <h5 class="text-muted">Группы не найдены</h5>
                    <p class="text-muted">Создайте первую группу пользователей</p>
                    <a href="<?php echo ADMIN_URL; ?>/user-groups/create" class="btn btn-primary">
                        <?php echo bloggy_icon('bs', 'plus-lg', '16', '#fff', 'me-2'); ?>
                        Добавить группу
                    </a>
                </div>
            <?php } else { ?>
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
                            <?php foreach ($groups as $group) { ?>
                            <tr>
                                <td>
                                    <strong><?php echo html($group['name']); ?></strong>
                                </td>
                                <td>
                                    <?php echo html($group['description'] ?? ''); ?>
                                </td>
                                <td>
                                    <?php
                                        $userCount = $db->fetchValue(
                                            "SELECT COUNT(*) FROM users_groups WHERE group_id = ?", 
                                            array($group['id'])
                                        );
                                    ?>
                                    <span class="badge bg-secondary"><?php echo $userCount; ?></span>
                                </td>
                                <td>
                                    <?php if ($group['is_default']) { ?>
                                        <span class="badge bg-success">Да</span>
                                    <?php } else { ?>
                                        <span class="badge bg-light text-dark">Нет</span>
                                    <?php } ?>
                                </td>
                                <td>
                                    <div class="d-flex justify-content-end gap-1">
                                        <a href="<?php echo ADMIN_URL; ?>/user-groups/permissions/<?php echo $group['id']; ?>" 
                                           class="btn btn-sm btn-outline-warning"
                                           title="Права доступа">
                                            <?php echo bloggy_icon('bs', 'shield-lock', '16', '#000'); ?>
                                        </a>
                                        <a href="<?php echo ADMIN_URL; ?>/user-groups/edit/<?php echo $group['id']; ?>" 
                                           class="btn btn-sm btn-outline-primary"
                                           title="Редактировать">
                                            <?php echo bloggy_icon('bs', 'pencil', '16', '#000'); ?>
                                        </a>
                                        <?php if (!$group['is_default']) { ?>
                                        <a href="<?php echo ADMIN_URL; ?>/user-groups/delete/<?php echo $group['id']; ?>" 
                                           class="btn btn-sm btn-outline-danger"
                                           onclick="return confirm('Вы уверены, что хотите удалить эту группу?')"
                                           title="Удалить">
                                            <?php echo bloggy_icon('bs', 'trash', '16', '#000'); ?>
                                        </a>
                                        <?php } ?>
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