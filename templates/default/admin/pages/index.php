<div class="container-fluid p-0">
    <div class="d-flex btn-group justify-content-between align-items-center mb-4">
        <h4 class="mb-0">
            <i class="bi bi-file-earmark-text me-2"></i>
            Страницы
        </h4>
        <div class="d-flex gap-2">
            <a href="<?= ADMIN_URL ?>/post-blocks" class="btn btn-outline-secondary">
                <svg class="icon icon-bricks" width="20" height="20" style="fill: #353434ff; margin-right:10px"><use href="http://bloggy2/templates/default/admin/icons/bs.svg#bricks"></use></svg>
                Пост-блоки
            </a>
            <a href="<?= ADMIN_URL ?>/fields/entity/page" class="btn btn-outline-secondary">
                <svg class="icon icon-input-cursor-text" width="20" height="20" style="fill: #353434ff; margin-right:10px"><use href="http://bloggy2/templates/default/admin/icons/bs.svg#input-cursor-text"></use></svg>
                Дополнительные поля
            </a>
            <a href="<?= ADMIN_URL ?>/pages/create" class="btn btn-primary">
                <i class="bi bi-plus-lg me-2"></i>Создать страницу
            </a>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <?php if(empty($pages)): ?>
                <div class="text-center py-5">
                    <div class="mb-3">
                        <i class="bi bi-file-earmark-text text-muted" style="font-size: 3rem;"></i>
                    </div>
                    <h5 class="text-muted">Страницы пока не созданы</h5>
                    <p class="text-muted">Создайте первую страницу для вашего сайта</p>
                    <a href="<?= ADMIN_URL ?>/pages/create" class="btn btn-primary">
                        <i class="bi bi-plus-lg me-2"></i>Создать страницу
                    </a>
                </div>
            <?php else: ?>
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
                            <?php foreach($pages as $page): ?>
                            <tr>
                                <td>
                                    <strong><?= html($page['title']) ?></strong>
                                </td>
                                <td>
                                    <code class="text-muted"><?= html($page['slug']) ?></code>
                                </td>
                                <td>
                                    <span class="badge bg-<?= $page['status'] === 'published' ? 'success' : 'warning' ?>">
                                        <?= $page['status'] === 'published' ? 'Опубликовано' : 'Черновик' ?>
                                    </span>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        <?= date('d.m.Y', strtotime($page['created_at'])) ?>
                                    </small>
                                </td>
                                <td>
                                    <div class="d-flex justify-content-end gap-2">
                                        <a href="<?= BASE_URL ?>/page/<?= $page['slug'] ?>" 
                                           class="btn btn-sm btn-outline-secondary" 
                                           target="_blank"
                                           title="Просмотр">
                                           <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="<?= ADMIN_URL ?>/pages/edit/<?= $page['id'] ?>" 
                                           class="btn btn-sm btn-outline-primary"
                                           title="Редактировать">
                                           <i class="bi bi-pencil"></i>
                                        </a>
                                        <a href="<?= ADMIN_URL ?>/pages/delete/<?= $page['id'] ?>" 
                                           class="btn btn-sm btn-outline-danger"
                                           onclick="return confirm('Вы уверены, что хотите удалить эту страницу?')"
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