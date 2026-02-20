<?php
add_admin_css('templates/default/admin/assets/css/controllers/docs.css');
add_admin_js('templates/default/admin/assets/js/controllers/docs.js');
?>

<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">
            <i class="bi bi-journal-text me-2"></i>
            Документация
        </h4>
        <div class="d-flex gap-2">
            <a href="<?= ADMIN_URL ?>/docs/categories" class="btn btn-outline-secondary">
                <i class="bi bi-folder me-2"></i>Категории
            </a>
            <a href="<?= ADMIN_URL ?>/settings?tab=components&controller=docs" class="btn btn-outline-secondary">
                <i class="bi bi-gear me-2"></i>Настройки
            </a>
            <a href="<?= ADMIN_URL ?>/docs/create" class="btn btn-primary">
                <i class="bi bi-plus-lg me-2"></i>Создать статью
            </a>
        </div>
    </div>

    <?php if ($settings['admin_show_stats'] ?? true): ?>
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body py-3">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h4 class="mb-0"><?= $stats['total'] ?></h4>
                            <small>Всего статей</small>
                        </div>
                        <i class="bi bi-journal-text fs-2"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body py-3">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h4 class="mb-0"><?= $stats['published'] ?></h4>
                            <small>Опубликовано</small>
                        </div>
                        <i class="bi bi-check-circle fs-2"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-warning text-dark">
                <div class="card-body py-3">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h4 class="mb-0"><?= $stats['drafts'] ?></h4>
                            <small>Черновиков</small>
                        </div>
                        <i class="bi bi-pencil fs-2"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body py-3">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h4 class="mb-0"><?= $stats['views'] ?></h4>
                            <small>Просмотров</small>
                        </div>
                        <i class="bi bi-eye fs-2"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($settings['admin_show_search'] ?? true): ?>
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="get" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Категория</label>
                    <select name="category" class="form-select">
                        <option value="">Все категории</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= ($filters['category_id'] ?? '') == $cat['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Статус</label>
                    <select name="status" class="form-select">
                        <option value="">Все статусы</option>
                        <option value="published" <?= ($filters['status'] ?? '') == 'published' ? 'selected' : '' ?>>Опубликованные</option>
                        <option value="draft" <?= ($filters['status'] ?? '') == 'draft' ? 'selected' : '' ?>>Черновики</option>
                        <option value="archived" <?= ($filters['status'] ?? '') == 'archived' ? 'selected' : '' ?>>В архиве</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Поиск</label>
                    <input type="text" name="search" class="form-control" 
                           value="<?= htmlspecialchars($filters['search'] ?? '') ?>" 
                           placeholder="Поиск по статьям...">
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-funnel me-2"></i>Применить фильтр
                    </button>
                </div>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($settings['admin_show_tips'] ?? false): ?>
    <div class="alert alert-info d-flex align-items-center mb-3">
        <i class="bi bi-info-circle me-2"></i>
        <span>Совет: Используйте короткое и понятное описание для каждой статьи документации</span>
    </div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <?php if (empty($articles)): ?>
                <div class="text-center py-5">
                    <div class="mb-3">
                        <i class="bi bi-journal-text text-muted" style="font-size: 3rem;"></i>
                    </div>
                    <h5 class="text-muted">Статьи не найдены</h5>
                    <p class="text-muted">Попробуйте изменить параметры фильтра или создать новую статью</p>
                    <a href="<?= ADMIN_URL ?>/docs/create" class="btn btn-primary">
                        <i class="bi bi-plus-lg me-2"></i>Создать статью
                    </a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th width="50">#</th>
                                <th>Заголовок</th>
                                <th>Категория</th>
                                <th>Статус</th>
                                <th>Просмотры</th>
                                <th>Дата создания</th>
                                <th class="text-end">Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($articles as $index => $article): ?>
                            <tr>
                                <td>
                                    <span class="badge bg-secondary"><?= $article['sort_order'] ?></span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-start">
                                        <div>
                                            <strong><?= htmlspecialchars($article['title']) ?></strong>
                                            <?php if ($article['is_featured']): ?>
                                                <span class="badge bg-warning ms-2">
                                                    <i class="bi bi-star"></i> Избранная
                                                </span>
                                            <?php endif; ?>
                                            <?php if (!empty($article['excerpt'])): ?>
                                                <div class="text-muted small mt-1">
                                                    <?= htmlspecialchars(mb_substr($article['excerpt'], 0, 100)) ?>
                                                    <?= mb_strlen($article['excerpt']) > 100 ? '...' : '' ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <?php if ($article['category_name']): ?>
                                        <span class="badge bg-light text-dark">
                                            <?= htmlspecialchars($article['category_name']) ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="text-muted">Без категории</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-<?= 
                                        $article['status'] === 'published' ? 'success' : 
                                        ($article['status'] === 'draft' ? 'warning' : 'secondary')
                                    ?>">
                                        <?= 
                                            $article['status'] === 'published' ? 'Опубликована' : 
                                            ($article['status'] === 'draft' ? 'Черновик' : 'Архив')
                                        ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-1">
                                        <i class="bi bi-eye text-muted"></i>
                                        <span class="fw-medium"><?= $article['views'] ?? 0 ?></span>
                                    </div>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        <?= date('d.m.Y H:i', strtotime($article['created_at'])) ?>
                                    </small>
                                </td>
                                <td>
                                    <div class="d-flex justify-content-end gap-1">
                                        <a href="<?= BASE_URL ?>/docs/<?= $article['slug'] ?>" 
                                           class="btn btn-sm btn-outline-secondary" 
                                           target="_blank"
                                           title="Просмотр"
                                           data-bs-toggle="tooltip">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <?php if ($article['status'] !== 'published'): ?>
                                            <a href="<?= ADMIN_URL ?>/docs/toggle-status/<?= $article['id'] ?>" 
                                               class="btn btn-sm btn-outline-success"
                                               title="Опубликовать"
                                               data-bs-toggle="tooltip"
                                               onclick="return confirm('Опубликовать статью?')">
                                                <i class="bi bi-check-lg"></i>
                                            </a>
                                        <?php else: ?>
                                            <a href="<?= ADMIN_URL ?>/docs/toggle-status/<?= $article['id'] ?>" 
                                               class="btn btn-sm btn-outline-warning"
                                               title="В черновики"
                                               data-bs-toggle="tooltip"
                                               onclick="return confirm('Переместить статью в черновики?')">
                                                <i class="bi bi-archive"></i>
                                            </a>
                                        <?php endif; ?>
                                        <a href="<?= ADMIN_URL ?>/docs/edit/<?= $article['id'] ?>" 
                                           class="btn btn-sm btn-outline-primary"
                                           title="Редактировать"
                                           data-bs-toggle="tooltip">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <a href="<?= ADMIN_URL ?>/docs/delete/<?= $article['id'] ?>" 
                                           class="btn btn-sm btn-outline-danger"
                                           onclick="return confirm('Вы уверены, что хотите удалить эту статью?')"
                                           title="Удалить"
                                           data-bs-toggle="tooltip">
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