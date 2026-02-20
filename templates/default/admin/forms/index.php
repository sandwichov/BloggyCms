<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">
            <i class="bi bi-ui-checks me-2"></i>
            Управление формами
        </h4>
        <a href="<?= ADMIN_URL ?>/forms/create" class="btn btn-primary">
            <i class="bi bi-plus-circle me-2"></i>Создать форму
        </a>
    </div>

    <?php
    $stats = $formModel->getStatistics();
    ?>
    
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <i class="bi bi-ui-checks display-6"></i>
                        </div>
                        <div>
                            <h3 class="mb-0"><?= $stats['total_forms'] ?></h3>
                            <small>Всего форм</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <i class="bi bi-check-circle display-6"></i>
                        </div>
                        <div>
                            <h3 class="mb-0"><?= $stats['active_forms'] ?></h3>
                            <small>Активных форм</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <i class="bi bi-send display-6"></i>
                        </div>
                        <div>
                            <h3 class="mb-0"><?= $stats['total_submissions'] ?></h3>
                            <small>Всего отправок</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <i class="bi bi-envelope display-6"></i>
                        </div>
                        <div>
                            <h3 class="mb-0"><?= $stats['unread_submissions'] ?></h3>
                            <small>Новых отправок</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if (empty($forms)): ?>
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5">
            <i class="bi bi-ui-checks display-4 text-muted mb-3"></i>
            <h5 class="text-muted">Формы не созданы</h5>
            <p class="text-muted mb-4">Создайте вашу первую форму для сайта</p>
            <a href="<?= ADMIN_URL ?>/forms/create" class="btn btn-primary">
                <i class="bi bi-plus-circle me-2"></i>Создать форму
            </a>
        </div>
    </div>
    <?php else: ?>
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Название</th>
                            <th>Слаг</th>
                            <th>Поля</th>
                            <th>Отправки</th>
                            <th>Статус</th>
                            <th>Дата создания</th>
                            <th class="end">Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($forms as $form): 
                            $fieldsCount = count($form['structure'] ?? []);
                            $submissionsCount = $formModel->getSubmissionsCount($form['id']);
                        ?>
                        <tr>
                            <td>
                                <strong>#<?= html($form['id']) ?></strong>
                            </td>
                            <td>
                                <strong><?= html($form['name']) ?></strong>
                                <?php if (!empty($form['description'])): ?>
                                    <small class="d-block text-muted"><?= html(mb_substr($form['description'], 0, 50)) ?>...</small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <code><?= html($form['slug']) ?></code>
                            </td>
                            <td>
                                <span class="badge bg-secondary"><?= $fieldsCount ?> полей</span>
                            </td>
                            <td>
                                <a href="<?= ADMIN_URL ?>/forms/show/<?= $form['id'] ?>" 
                                   class="badge bg-info text-decoration-none">
                                    <?= $submissionsCount ?> отправок
                                </a>
                            </td>
                            <td>
                                <form method="POST" action="<?= ADMIN_URL ?>/forms/toggle-status/<?= $form['id'] ?>" 
                                      class="d-inline">
                                    <button type="submit" class="btn btn-sm btn-<?= $form['status'] === 'active' ? 'success' : 'secondary' ?>">
                                        <?= $form['status'] === 'active' ? 'Активна' : 'Неактивна' ?>
                                    </button>
                                </form>
                            </td>
                            <td>
                                <small class="text-muted">
                                    <?= date('d.m.Y', strtotime($form['created_at'])) ?>
                                </small>
                            </td>
                            <td class="end">
                                <div class="btn-group btn-group-sm">
                                    <a href="<?= ADMIN_URL ?>/forms/preview/<?= $form['id'] ?>" 
                                       class="btn btn-outline-secondary" 
                                       title="Предпросмотр">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="<?= ADMIN_URL ?>/forms/edit/<?= $form['id'] ?>" 
                                       class="btn btn-outline-primary" 
                                       title="Редактировать">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <a href="<?= ADMIN_URL ?>/forms/settings/<?= $form['id'] ?>" 
                                       class="btn btn-outline-info" 
                                       title="Настройки">
                                        <i class="bi bi-gear"></i>
                                    </a>
                                    <button type="button" 
                                            class="btn btn-outline-danger" 
                                            title="Удалить"
                                            onclick="confirmDelete(<?= $form['id'] ?>, '<?= html($form['name']) ?>')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php ob_start(); ?>
<script>
    function confirmDelete(formId, formName) {
        if (confirm('Вы уверены, что хотите удалить форму "' + formName + '"?')) {
            window.location.href = '<?= ADMIN_URL ?>/forms/delete/' + formId;
        }
    }
</script>
<?php admin_bottom_js(ob_get_clean()); ?>