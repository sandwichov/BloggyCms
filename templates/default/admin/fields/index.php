<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">
            <?php echo bloggy_icon('bs', 'input-cursor-text', '24', '#000', 'me-2'); ?>
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
                        <a href="<?php echo ADMIN_URL; ?>/fields/entity/post" 
                           class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <span>
                                <?php echo bloggy_icon('bs', 'file-text', '16', '#000', 'me-2'); ?>
                                Записи
                            </span>
                            <span class="badge bg-primary rounded-pill">
                                <?php echo $fieldModel->getCountByEntityType('post'); ?>
                            </span>
                        </a>
                        <a href="<?php echo ADMIN_URL; ?>/fields/entity/page" 
                           class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <span>
                                <?php echo bloggy_icon('bs', 'file-earmark', '16', '#000', 'me-2'); ?>
                                Страницы
                            </span>
                            <span class="badge bg-primary rounded-pill">
                                <?php echo $fieldModel->getCountByEntityType('page'); ?>
                            </span>
                        </a>
                        <a href="<?php echo ADMIN_URL; ?>/fields/entity/category" 
                           class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <span>
                                <?php echo bloggy_icon('bs', 'folder', '16', '#000', 'me-2'); ?>
                                Категории
                            </span>
                            <span class="badge bg-primary rounded-pill">
                                <?php echo $fieldModel->getCountByEntityType('category'); ?>
                            </span>
                        </a>
                        <a href="<?php echo ADMIN_URL; ?>/fields/entity/user" 
                           class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                            <span>
                                <?php echo bloggy_icon('bs', 'person', '16', '#000', 'me-2'); ?>
                                Пользователи
                            </span>
                            <span class="badge bg-primary rounded-pill">
                                <?php echo $fieldModel->getCountByEntityType('user'); ?>
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
                    <?php if(empty($fields)) { ?>
                        <div class="text-center py-5">
                            <div class="mb-3">
                                <?php echo bloggy_icon('bs', 'input-cursor-text', '48', '#6C6C6C'); ?>
                            </div>
                            <h5 class="text-muted">Поля не созданы</h5>
                            <p class="text-muted">Создайте первое поле для вашего сайта</p>
                        </div>
                    <?php } else { ?>
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
                                    <?php foreach($fields as $field) { ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo html($field['name']); ?></strong>
                                            <?php if(!empty($field['description'])) { ?>
                                                <br><small class="text-muted"><?php echo html($field['description']); ?></small>
                                            <?php } ?>
                                        </td>
                                        <td>
                                            <code class="text-muted"><?php echo html($field['system_name']); ?></code>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary"><?php echo get_field_type_name($field['type']); ?></span>
                                        </td>
                                        <td>
                                            <?php echo get_entity_name($field['entity_type']); ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?php echo $field['is_active'] ? 'success' : 'danger'; ?>">
                                                <?php echo $field['is_active'] ? 'Включено' : 'Отключено'; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="d-flex justify-content-end gap-2">
                                                <a href="<?php echo ADMIN_URL; ?>/fields/entity/<?php echo $field['entity_type']; ?>" 
                                                   class="btn btn-sm btn-outline-secondary"
                                                   title="К сущности">
                                                    <?php echo bloggy_icon('bs', 'arrow-right', '16', '#000'); ?>
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
    </div>
</div>