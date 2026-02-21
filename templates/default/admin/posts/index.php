<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">
            <?php echo bloggy_icon('bs', 'file-text', '24', '#000', 'me-2'); ?>
            Посты
        </h4>
        <div class="d-flex gap-2">
            <a href="<?php echo ADMIN_URL; ?>/post-blocks" class="btn btn-outline-secondary">
                <?php echo bloggy_icon('bs', 'bricks', '20', '#2c2c2c', 'me-2'); ?>
                Пост-блоки
            </a>
            <a href="<?php echo ADMIN_URL; ?>/fields/entity/post" class="btn btn-outline-secondary">
                <?php echo bloggy_icon('bs', 'input-cursor-text', '20', '#2c2c2c', 'me-2'); ?>
                Дополнительные поля
            </a>
            <a href="<?php echo ADMIN_URL; ?>/categories" class="btn btn-outline-secondary">
                <?php echo bloggy_icon('bs', 'folder', '20', '#2c2c2c', 'me-2'); ?>
                Категории
            </a>
            <a href="<?php echo ADMIN_URL; ?>/posts/create" class="btn btn-primary">
                <?php echo bloggy_icon('bs', 'plus-lg', '16', '#fff', 'me-2'); ?>
                Создать пост
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
                        foreach ($allCategories as $cat) { 
                        ?>
                            <option value="<?php echo $cat['id']; ?>" <?php echo $selectedCategory == $cat['id'] ? 'selected' : ''; ?>>
                                <?php echo html($cat['name']); ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Статус</label>
                    <select name="status" class="form-select">
                        <option value="">Все статусы</option>
                        <option value="published" <?php echo ($_GET['status'] ?? '') == 'published' ? 'selected' : ''; ?>>Опубликованные</option>
                        <option value="draft" <?php echo ($_GET['status'] ?? '') == 'draft' ? 'selected' : ''; ?>>Черновики</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary w-100">
                        <?php echo bloggy_icon('bs', 'funnel', '16', '#fff', 'me-2'); ?>
                        Применить фильтр
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <?php if (empty($posts)) { ?>
                <div class="text-center py-5">
                    <div class="mb-3">
                        <?php echo bloggy_icon('bs', 'file-text', '48', '#6C6C6C'); ?>
                    </div>
                    <h5 class="text-muted">Посты не найдены</h5>
                    <p class="text-muted">Попробуйте изменить параметры фильтра</p>
                    <a href="<?php echo ADMIN_URL; ?>/posts/create" class="btn btn-primary">
                        <?php echo bloggy_icon('bs', 'plus-lg', '16', '#fff', 'me-2'); ?>
                        Создать пост
                    </a>
                </div>
            <?php } else { ?>
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
                            <?php foreach ($posts as $post) { ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <?php if ($post['featured_image']) { ?>
                                            <img src="<?php echo BASE_URL; ?>/uploads/images/<?php echo $post['featured_image']; ?>" 
                                                 class="rounded me-2" 
                                                 style="width: 40px; height: 40px; object-fit: cover;"
                                                 alt="<?php echo html($post['title']); ?>">
                                        <?php } else { ?>
                                            <div class="rounded me-2 d-flex align-items-center justify-content-center bg-light" 
                                                 style="width: 40px; height: 40px;">
                                                <?php echo bloggy_icon('bs', 'image', '20', '#6C6C6C'); ?>
                                            </div>
                                        <?php } ?>
                                        <div>
                                            <strong><?php echo html($post['title']); ?></strong>
                                            <?php if ($post['password_protected']) { ?>
                                                <span class="badge bg-warning ms-2" title="Защищено паролем">
                                                    <?php echo bloggy_icon('bs', 'lock', '12', '#000'); ?>
                                                </span>
                                            <?php } ?>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark">
                                        <?php echo html($post['category_name']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-<?php echo $post['status'] === 'published' ? 'success' : 'warning'; ?>">
                                        <?php echo $post['status'] === 'published' ? 'Опубликован' : 'Черновик'; ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-1">
                                        <?php echo bloggy_icon('bs', 'eye', '16', '#6C6C6C'); ?>
                                        <span class="fw-medium"><?php echo $post['views'] ?? 0; ?></span>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-1">
                                        <?php if (($post['rating'] ?? 0) > 0) { ?>
                                            <?php echo bloggy_icon('bs', 'arrow-up-circle', '16', '#198754'); ?>
                                            <span class="fw-medium text-success">+<?php echo $post['rating'] ?? 0; ?></span>
                                        <?php } elseif (($post['rating'] ?? 0) < 0) { ?>
                                            <?php echo bloggy_icon('bs', 'arrow-down-circle', '16', '#dc3545'); ?>
                                            <span class="fw-medium text-danger"><?php echo $post['rating'] ?? 0; ?></span>
                                        <?php } else { ?>
                                            <?php echo bloggy_icon('bs', 'dash-circle', '16', '#6C6C6C'); ?>
                                            <span class="fw-medium text-muted">0</span>
                                        <?php } ?>
                                    </div>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        <?php echo date('d.m.Y H:i', strtotime($post['created_at'])); ?>
                                    </small>
                                </td>
                                <td>
                                    <div class="d-flex justify-content-end gap-1">
                                        <a href="<?php echo BASE_URL; ?>/post/<?php echo $post['slug']; ?>" 
                                           class="btn btn-sm btn-outline-secondary" 
                                           target="_blank"
                                           title="Просмотр">
                                            <?php echo bloggy_icon('bs', 'eye', '14', '#000'); ?>
                                        </a>
                                        <?php if ($post['status'] === 'published') { ?>
                                            <a href="<?php echo ADMIN_URL; ?>/posts/toggle-status/<?php echo $post['id']; ?>" 
                                               class="btn btn-sm btn-outline-warning"
                                               title="Переместить в черновики"
                                               onclick="return confirm('Переместить пост в черновики?')">
                                                <?php echo bloggy_icon('bs', 'archive', '14', '#000'); ?>
                                            </a>
                                        <?php } else { ?>
                                            <a href="<?php echo ADMIN_URL; ?>/posts/toggle-status/<?php echo $post['id']; ?>" 
                                               class="btn btn-sm btn-outline-success"
                                               title="Опубликовать"
                                               onclick="return confirm('Опубликовать пост?')">
                                                <?php echo bloggy_icon('bs', 'check-lg', '14', '#000'); ?>
                                            </a>
                                        <?php } ?>
                                        <a href="<?php echo ADMIN_URL; ?>/posts/edit/<?php echo $post['id']; ?>" 
                                           class="btn btn-sm btn-outline-primary"
                                           title="Редактировать">
                                            <?php echo bloggy_icon('bs', 'pencil', '14', '#000'); ?>
                                        </a>
                                        <a href="<?php echo ADMIN_URL; ?>/posts/delete/<?php echo $post['id']; ?>" 
                                           class="btn btn-sm btn-outline-danger"
                                           onclick="return confirm('Вы уверены, что хотите удалить этот пост?')"
                                           title="Удалить">
                                            <?php echo bloggy_icon('bs', 'trash', '14', '#000'); ?>
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