<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">
            <i class="bi bi-file-text me-2"></i>
            Посты
        </h4>
        <div class="d-flex gap-2">
            <a href="<?= ADMIN_URL ?>/post-blocks" class="btn btn-outline-secondary">
                <svg class="icon icon-bricks" width="20" height="20" style="fill: #2c2c2cff; margin-right:10px"><use href="http://bloggy2/templates/default/admin/icons/bs.svg#bricks"></use></svg>
                Пост-блоки
            </a>
            <a href="<?= ADMIN_URL ?>/fields/entity/post" class="btn btn-outline-secondary">
                <svg class="icon icon-input-cursor-text" width="20" height="20" style="fill: #2c2c2cff; margin-right:10px"><use href="http://bloggy2/templates/default/admin/icons/bs.svg#input-cursor-text"></use></svg>
                Дополнительные поля
            </a>
            <a href="<?= ADMIN_URL ?>/categories" class="btn btn-outline-secondary">
                <svg class="icon icon-folder" width="20" height="20" style="fill: #2c2c2cff; margin-right:10px"><use href="http://bloggy2/templates/default/admin/icons/bs.svg#folder"></use></svg>
                Категории
            </a>
            <a href="<?= ADMIN_URL ?>/posts/create" class="btn btn-primary">
                <i class="bi bi-plus-lg me-2"></i>Создать пост
            </a>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="get" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label">Категория</label>
                    <select name="category" class="form-select">
                        <option value="">Все категории</option>
                        <?php 
                        $categoryModel = new CategoryModel($this->db);
                        $allCategories = $categoryModel->getAll();
                        $selectedCategory = $_GET['category'] ?? '';
                        foreach($allCategories as $cat): 
                        ?>
                            <option value="<?= $cat['id'] ?>" <?= $selectedCategory == $cat['id'] ? 'selected' : '' ?>>
                                <?= html($cat['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Статус</label>
                    <select name="status" class="form-select">
                        <option value="">Все статусы</option>
                        <option value="published" <?= ($_GET['status'] ?? '') == 'published' ? 'selected' : '' ?>>Опубликованные</option>
                        <option value="draft" <?= ($_GET['status'] ?? '') == 'draft' ? 'selected' : '' ?>>Черновики</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-funnel me-2"></i>Применить фильтр
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <?php if(empty($posts)): ?>
                <div class="text-center py-5">
                    <div class="mb-3">
                        <i class="bi bi-file-text text-muted" style="font-size: 3rem;"></i>
                    </div>
                    <h5 class="text-muted">Посты не найдены</h5>
                    <p class="text-muted">Попробуйте изменить параметры фильтра</p>
                    <a href="<?= ADMIN_URL ?>/posts/create" class="btn btn-primary">
                        <i class="bi bi-plus-lg me-2"></i>Создать пост
                    </a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Заголовок</th>
                                <th>Категория</th>
                                <th>Статус</th>
                                <th>Просмотры</th>
                                <th>Рейтинг</th>
                                <th>Дата создания</th>
                                <th class="text-end">Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($posts as $post): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <?php if($post['featured_image']): ?>
                                            <img src="<?= BASE_URL ?>/uploads/images/<?= $post['featured_image'] ?>" 
                                                 class="rounded me-2" 
                                                 style="width: 40px; height: 40px; object-fit: cover;"
                                                 alt="<?= html($post['title']) ?>">
                                        <?php else: ?>
                                            <div class="rounded me-2 d-flex align-items-center justify-content-center bg-light" 
                                                 style="width: 40px; height: 40px;">
                                                <i class="bi bi-image text-muted"></i>
                                            </div>
                                        <?php endif; ?>
                                        <div>
                                            <strong><?= html($post['title']) ?></strong>
                                            <?php if($post['password_protected']): ?>
                                                <span class="badge bg-warning ms-2" title="Защищено паролем">
                                                    <i class="bi bi-lock"></i>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark">
                                        <?= html($post['category_name']) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-<?= $post['status'] === 'published' ? 'success' : 'warning' ?>">
                                        <?= $post['status'] === 'published' ? 'Опубликован' : 'Черновик' ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-1">
                                        <i class="bi bi-eye text-muted"></i>
                                        <span class="fw-medium"><?= $post['views'] ?? 0 ?></span>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-1">
                                        <?php if(($post['rating'] ?? 0) > 0): ?>
                                            <i class="bi bi-arrow-up-circle text-success"></i>
                                            <span class="fw-medium text-success">+<?= $post['rating'] ?? 0 ?></span>
                                        <?php elseif(($post['rating'] ?? 0) < 0): ?>
                                            <i class="bi bi-arrow-down-circle text-danger"></i>
                                            <span class="fw-medium text-danger"><?= $post['rating'] ?? 0 ?></span>
                                        <?php else: ?>
                                            <i class="bi bi-dash-circle text-muted"></i>
                                            <span class="fw-medium text-muted">0</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        <?= date('d.m.Y H:i', strtotime($post['created_at'])) ?>
                                    </small>
                                </td>
                                <td>
                                    <div class="d-flex justify-content-end gap-1">
                                        <a href="<?= BASE_URL ?>/post/<?= $post['slug'] ?>" 
                                           class="btn btn-sm btn-outline-secondary" 
                                           target="_blank"
                                           title="Просмотр">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <?php if($post['status'] === 'published'): ?>
                                            <a href="<?= ADMIN_URL ?>/posts/toggle-status/<?= $post['id'] ?>" 
                                               class="btn btn-sm btn-outline-warning"
                                               title="Переместить в черновики"
                                               onclick="return confirm('Переместить пост в черновики?')">
                                                <i class="bi bi-archive"></i>
                                            </a>
                                        <?php else: ?>
                                            <a href="<?= ADMIN_URL ?>/posts/toggle-status/<?= $post['id'] ?>" 
                                               class="btn btn-sm btn-outline-success"
                                               title="Опубликовать"
                                               onclick="return confirm('Опубликовать пост?')">
                                                <i class="bi bi-check-lg"></i>
                                            </a>
                                        <?php endif; ?>
                                        <a href="<?= ADMIN_URL ?>/posts/edit/<?= $post['id'] ?>" 
                                           class="btn btn-sm btn-outline-primary"
                                           title="Редактировать">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <a href="<?= ADMIN_URL ?>/posts/delete/<?= $post['id'] ?>" 
                                           class="btn btn-sm btn-outline-danger"
                                           onclick="return confirm('Вы уверены, что хотите удалить этот пост?')"
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