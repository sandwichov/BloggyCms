<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">
            <i class="bi bi-list-ul me-2"></i>
            Управление меню
        </h4>
        <a href="<?= ADMIN_URL ?>/menu/create" class="btn btn-primary">
            <i class="bi bi-plus-circle me-2"></i>Создать меню
        </a>
    </div>

    <?php if (empty($menus)): ?>
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5">
            <i class="bi bi-list-ul display-4 text-muted mb-3"></i>
            <h5 class="text-muted">Меню не созданы</h5>
            <p class="text-muted mb-4">Создайте ваше первое меню для сайта</p>
            <a href="<?= ADMIN_URL ?>/menu/create" class="btn btn-primary">
                <i class="bi bi-plus-circle me-2"></i>Создать меню
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
                            <th>Шаблон</th>
                            <th>Статус</th>
                            <th>Дата создания</th>
                            <th class = "end">Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($menus as $menu): ?>
                        <tr>
                            <td>
                                <strong><?= html($menu['id']) ?></strong>
                            </td>
                            <td>
                                <strong><?= html($menu['name']) ?></strong>
                            </td>
                            <td>
                                <code><?= html($menu['template']) ?></code>
                            </td>
                            <td>
                                <span class="badge bg-<?= $menu['status'] === 'active' ? 'success' : 'secondary' ?>">
                                    <?= $menu['status'] === 'active' ? 'Активно' : 'Неактивно' ?>
                                </span>
                            </td>
                            <td>
                                <small class="text-muted">
                                    <?= date('d.m.Y H:i', strtotime($menu['created_at'])) ?>
                                </small>
                            </td>
                            <td class = "end">
                                <div class="btn-group btn-group-sm">
                                    <a href="<?= ADMIN_URL ?>/menu/preview/<?= $menu['id'] ?>" 
                                       class="btn btn-outline-secondary" 
                                       title="Предпросмотр">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="<?= ADMIN_URL ?>/menu/edit/<?= $menu['id'] ?>" 
                                       class="btn btn-outline-primary" 
                                       title="Редактировать">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <button type="button" 
                                            class="btn btn-outline-danger" 
                                            title="Удалить"
                                            onclick="confirmDelete(<?= $menu['id'] ?>, '<?= html($menu['name']) ?>')">
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
    function confirmDelete(menuId, menuName) {
        if (confirm('Вы уверены, что хотите удалить меню "' + menuName + '"?')) {
            window.location.href = '<?= ADMIN_URL ?>/menu/delete/' + menuId;
        }
    }
</script>
<?php admin_bottom_js(ob_get_clean()); ?>