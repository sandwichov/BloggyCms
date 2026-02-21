<div class="container-fluid p-0">
    <div class="d-flex btn-group justify-content-between align-items-center mb-4">
        <h4 class="mb-0">
            <?php echo bloggy_icon('bs', 'file-earmark-text', '24', '#000', 'me-2'); ?>
            Страницы
        </h4>
        <div class="d-flex gap-2">
            <a href="<?php echo ADMIN_URL; ?>/post-blocks" class="btn btn-outline-secondary">
                <?php echo bloggy_icon('bs', 'bricks', '20', '#353434', 'me-2'); ?>
                Пост-блоки
            </a>
            <a href="<?php echo ADMIN_URL; ?>/fields/entity/page" class="btn btn-outline-secondary">
                <?php echo bloggy_icon('bs', 'input-cursor-text', '20', '#353434', 'me-2'); ?>
                Дополнительные поля
            </a>
            <a href="<?php echo ADMIN_URL; ?>/pages/create" class="btn btn-primary">
                <?php echo bloggy_icon('bs', 'plus-lg', '16', '#fff', 'me-2'); ?>
                Создать страницу
            </a>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <?php if (empty($pages)) { ?>
                <div class="text-center py-5">
                    <div class="mb-3">
                        <?php echo bloggy_icon('bs', 'file-earmark-text', '48', '#6C6C6C'); ?>
                    </div>
                    <h5 class="text-muted">Страницы пока не созданы</h5>
                    <p class="text-muted">Создайте первую страницу для вашего сайта</p>
                    <a href="<?php echo ADMIN_URL; ?>/pages/create" class="btn btn-primary">
                        <?php echo bloggy_icon('bs', 'plus-lg', '16', '#fff', 'me-2'); ?>
                        Создать страницу
                    </a>
                </div>
            <?php } else { ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Заголовок</th>
                                <th>URL</th>
                                <th>Статус</th>
                                <th>Дата создания</th>
                                <th class="text-end">Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pages as $page) { ?>
                            <tr>
                                <td>
                                    <strong><?php echo html($page['title']); ?></strong>
                                </td>
                                <td>
                                    <code class="text-muted"><?php echo html($page['slug']); ?></code>
                                </td>
                                <td>
                                    <span class="badge bg-<?php echo $page['status'] === 'published' ? 'success' : 'warning'; ?>">
                                        <?php echo $page['status'] === 'published' ? 'Опубликовано' : 'Черновик'; ?>
                                    </span>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        <?php echo date('d.m.Y', strtotime($page['created_at'])); ?>
                                    </small>
                                </td>
                                <td>
                                    <div class="d-flex justify-content-end gap-2">
                                        <a href="<?php echo BASE_URL; ?>/page/<?php echo $page['slug']; ?>" 
                                           class="btn btn-sm btn-outline-secondary" 
                                           target="_blank"
                                           title="Просмотр">
                                           <?php echo bloggy_icon('bs', 'eye', '16', '#000'); ?>
                                        </a>
                                        <a href="<?php echo ADMIN_URL; ?>/pages/edit/<?php echo $page['id']; ?>" 
                                           class="btn btn-sm btn-outline-primary"
                                           title="Редактировать">
                                           <?php echo bloggy_icon('bs', 'pencil', '16', '#000'); ?>
                                        </a>
                                        <a href="<?php echo ADMIN_URL; ?>/pages/delete/<?php echo $page['id']; ?>" 
                                           class="btn btn-sm btn-outline-danger"
                                           onclick="return confirm('Вы уверены, что хотите удалить эту страницу?')"
                                           title="Удалить">
                                            <?php echo bloggy_icon('bs', 'trash', '16', '#000'); ?>
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