<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">
            <i class="bi bi-input-cursor-text me-2"></i>
            Управление полями
        </h4>
    </div>

    <div class="row">
        <div class="col-md-3 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0">
                    <h6 class="card-title mb-0">Типы сущностей</h6>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <a href="<?= ADMIN_URL ?>/fields/entity/post" 
                           class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <span>
                                <i class="bi bi-file-text me-2"></i>Записи
                            </span>
                            <span class="badge bg-primary rounded-pill">
                                <?= $fieldModel->getCountByEntityType('post') ?>
                            </span>
                        </a>
                        <a href="<?= ADMIN_URL ?>/fields/entity/page" 
                           class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <span>
                                <i class="bi bi-file-earmark me-2"></i>Страницы
                            </span>
                            <span class="badge bg-primary rounded-pill">
                                <?= $fieldModel->getCountByEntityType('page') ?>
                            </span>
                        </a>
                        <a href="<?= ADMIN_URL ?>/fields/entity/category" 
                           class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <span>
                                <i class="bi bi-folder me-2"></i>Категории
                            </span>
                            <span class="badge bg-primary rounded-pill">
                                <?= $fieldModel->getCountByEntityType('category') ?>
                            </span>
                        </a>
                        <a href="<?= ADMIN_URL ?>/fields/entity/user" 
                           class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <span>
                                <i class="bi bi-person me-2"></i>Пользователи
                            </span>
                            <span class="badge bg-primary rounded-pill">
                                <?= $fieldModel->getCountByEntityType('user') ?>
                            </span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-9">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                    <h6 class="card-title mb-0">Все поля системы</h6>
                </div>
                <div class="card-body">
                    <?php if(empty($fields)): ?>
                        <div class="text-center py-5">
                            <div class="mb-3">
                                <i class="bi bi-input-cursor-text text-muted" style="font-size: 3rem;"></i>
                            </div>
                            <h5 class="text-muted">Поля не созданы</h5>
                            <p class="text-muted">Создайте первое поле для вашего сайта</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Название</th>
                                        <th>Системное имя</th>
                                        <th>Тип</th>
                                        <th>Сущность</th>
                                        <th>Статус</th>
                                        <th class="text-end">Действия</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach($fields as $field): ?>
                                    <tr>
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
                                            <?= get_entity_name($field['entity_type']) ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?= $field['is_active'] ? 'success' : 'danger' ?>">
                                                <?= $field['is_active'] ? 'Включено' : 'Отключено' ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="d-flex justify-content-end gap-2">
                                                <a href="<?= ADMIN_URL ?>/fields/entity/<?= $field['entity_type'] ?>" 
                                                   class="btn btn-sm btn-outline-secondary"
                                                   title="К сущности">
                                                    <i class="bi bi-arrow-right"></i>
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
    </div>
</div>