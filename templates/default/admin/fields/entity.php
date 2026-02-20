<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">
            <i class="bi bi-input-cursor-text me-2"></i>
            Поля для <?= html($entityName) ?>
        </h4>
        <a href="<?= ADMIN_URL ?>/fields/create/<?= $entityType ?>" class="btn btn-primary">
            <i class="bi bi-plus-lg me-2"></i>Добавить поле
        </a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <?php if(empty($fields)): ?>
                <div class="text-center py-5">
                    <div class="mb-3">
                        <i class="bi bi-input-cursor-text text-muted" style="font-size: 3rem;"></i>
                    </div>
                    <h5 class="text-muted">Поля не созданы</h5>
                    <p class="text-muted">Создайте первое поле для <?= html(mb_strtolower($entityName)) ?></p>
                    <a href="<?= ADMIN_URL ?>/fields/create/<?= $entityType ?>" class="btn btn-primary">
                        <i class="bi bi-plus-lg me-2"></i>Добавить поле
                    </a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th width="5%">#</th>
                                <th>Название</th>
                                <th>Системное имя</th>
                                <th>Тип</th>
                                <th>Обязательное</th>
                                <th>Статус</th>
                                <th class="text-end">Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($fields as $field): ?>
                            <tr>
                                <td><?= $field['sort_order'] ?></td>
                                <td>
                                    <strong><?= html($field['name']) ?></strong>
                                    <?php if(!empty($field['description'])): ?>
                                        <br><small class="text-muted"><?= html($field['description']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <code class="text-muted"><?= html($field['system_name']) ?></code>
                                </td>
                                <td>
                                    <span class="badge bg-secondary"><?= get_field_type_name($field['type']) ?></span>
                                </td>
                                <td>
                                    <?php if($field['is_required']): ?>
                                        <span class="badge bg-danger">Да</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Нет</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-<?= $field['is_active'] ? 'success' : 'danger' ?>">
                                        <?= $field['is_active'] ? 'Включено' : 'Отключено' ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex justify-content-end gap-2">
                                        <a href="<?= ADMIN_URL ?>/fields/toggle/<?= $field['id'] ?>" 
                                           class="btn btn-sm btn-<?= $field['is_active'] ? 'warning' : 'success' ?>"
                                           title="<?= $field['is_active'] ? 'Отключить' : 'Включить' ?>">
                                            <i class="bi bi-power"></i>
                                        </a>
                                        <a href="<?= ADMIN_URL ?>/fields/edit/<?= $field['id'] ?>" 
                                           class="btn btn-sm btn-outline-primary"
                                           title="Редактировать">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <a href="<?= ADMIN_URL ?>/fields/delete/<?= $field['id'] ?>" 
                                           class="btn btn-sm btn-outline-danger"
                                           onclick="return confirm('Вы уверены, что хотите удалить это поле?')"
                                           title="Удалить">
                                            <i class="bi bi-trash"></i>
                                        </a>
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