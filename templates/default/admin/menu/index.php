<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">
            <?php echo bloggy_icon('bs', 'list-ul', '24', '#000', 'me-2'); ?>
            Управление меню
        </h4>
        <a href="<?php echo ADMIN_URL; ?>/menu/create" class="btn btn-primary">
            <?php echo bloggy_icon('bs', 'plus-circle', '16', '#fff', 'me-2'); ?>
            Создать меню
        </a>
    </div>

    <?php if (empty($menus)) { ?>
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5">
            <?php echo bloggy_icon('bs', 'list-ul', '48', '#6C6C6C', 'mb-3'); ?>
            <h5 class="text-muted">Меню не созданы</h5>
            <p class="text-muted mb-4">Создайте ваше первое меню для сайта</p>
            <a href="<?php echo ADMIN_URL; ?>/menu/create" class="btn btn-primary">
                <?php echo bloggy_icon('bs', 'plus-circle', '16', '#fff', 'me-2'); ?>
                Создать меню
            </a>
        </div>
    </div>
    <?php } else { ?>
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
                            <th class="end">Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($menus as $menu) { ?>
                        <tr>
                            <td>
                                <strong><?php echo html($menu['id']); ?></strong>
                            </td>
                            <td>
                                <strong><?php echo html($menu['name']); ?></strong>
                            </td>
                            <td>
                                <code><?php echo html($menu['template']); ?></code>
                            </td>
                            <td>
                                <span class="badge bg-<?php echo $menu['status'] === 'active' ? 'success' : 'secondary'; ?>">
                                    <?php echo $menu['status'] === 'active' ? 'Активно' : 'Неактивно'; ?>
                                </span>
                            </td>
                            <td>
                                <small class="text-muted">
                                    <?php echo date('d.m.Y H:i', strtotime($menu['created_at'])); ?>
                                </small>
                            </td>
                            <td class="end">
                                <div class="btn-group btn-group-sm">
                                    <a href="<?php echo ADMIN_URL; ?>/menu/preview/<?php echo $menu['id']; ?>" 
                                       class="btn btn-outline-secondary" 
                                       title="Предпросмотр">
                                        <?php echo bloggy_icon('bs', 'eye', '16', '#000'); ?>
                                    </a>
                                    <a href="<?php echo ADMIN_URL; ?>/menu/edit/<?php echo $menu['id']; ?>" 
                                       class="btn btn-outline-primary" 
                                       title="Редактировать">
                                        <?php echo bloggy_icon('bs', 'pencil', '16', '#000'); ?>
                                    </a>
                                    <button type="button" 
                                            class="btn btn-outline-danger" 
                                            title="Удалить"
                                            onclick="confirmDelete(<?php echo $menu['id']; ?>, '<?php echo html($menu['name']); ?>')">
                                        <?php echo bloggy_icon('bs', 'trash', '16', '#000'); ?>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php } ?>
</div>

<?php ob_start(); ?>
<script>
    function confirmDelete(menuId, menuName) {
        if (confirm('Вы уверены, что хотите удалить меню "' + menuName + '"?')) {
            window.location.href = '<?php echo ADMIN_URL; ?>/menu/delete/' + menuId;
        }
    }
</script>
<?php admin_bottom_js(ob_get_clean()); ?>