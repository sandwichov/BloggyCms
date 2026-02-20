<?php
add_admin_js('templates/default/admin/assets/js/controllers/docs-categories.js');
add_admin_css('templates/default/admin/assets/css/controllers/docs-categories.css');
?>

<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">
            <i class="bi bi-folder me-2"></i>
            Категории документации
        </h4>
        <div class="d-flex gap-2">
            <a href="<?= ADMIN_URL ?>/docs" class="btn btn-outline-secondary">
                <i class="bi bi-journal-text me-2"></i>Статьи
            </a>
            <a href="<?= ADMIN_URL ?>/docs/categories/create" class="btn btn-primary">
                <i class="bi bi-plus-lg me-2"></i>Создать категорию
            </a>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <?php if (empty($categories)): ?>
                <div class="text-center py-5">
                    <div class="mb-3">
                        <i class="bi bi-folder2-open text-muted" style="font-size: 3rem;"></i>
                    </div>
                    <h5 class="text-muted">Категории пока не созданы</h5>
                    <p class="text-muted">Создайте первую категорию для организации документации</p>
                    <a href="<?= ADMIN_URL ?>/docs/categories/create" class="btn btn-primary">
                        <i class="bi bi-plus-lg me-2"></i>Создать категорию
                    </a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle" id="docs-categories-table">
                        <thead class="table-light">
                            <tr>
                                <th width="50" class="text-center">#</th>
                                <th>Категория</th>
                                <th width="150" class="text-center">Статьи</th>
                                <th width="150" class="text-center">Порядок</th>
                                <th width="200" class="text-end">Действия</th>
                            </tr>
                        </thead>
                        <tbody id="sortable-categories">
                            <?php foreach ($categories as $category): ?>
                                <tr data-category-id="<?= $category['id'] ?>" class="sortable-item">
                                    <td class="text-center">
                                        <div class="drag-handle text-muted cursor-move" title="Перетащите для изменения порядка">
                                            <i class="bi bi-grip-vertical"></i>
                                        </div>
                                    </td>
                                    
                                    <td>
                                        <div class="d-flex align-items-start">
                                            <div class="flex-grow-1" style="padding-left: <?= ($category['level'] ?? 0) * 20 ?>px">
                                                <h6 class="mb-1">
                                                    <?php if ($category['level'] > 0): ?>
                                                        <i class="bi bi-arrow-return-right text-muted me-1"></i>
                                                    <?php endif; ?>
                                                    <?= htmlspecialchars($category['name']) ?>
                                                </h6>
                                                <div class="text-muted small">
                                                    <code title="URL категории">/docs/category/<?= htmlspecialchars($category['slug']) ?></code>
                                                </div>
                                                <?php if (!empty($category['description'])): ?>
                                                    <div class="text-muted small mt-1">
                                                        <?= htmlspecialchars(mb_substr($category['description'], 0, 100)) ?>
                                                        <?= mb_strlen($category['description']) > 100 ? '...' : '' ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    
                                    <td class="text-center">
                                        <span class="badge bg-secondary">
                                            <?= $category['articles_count'] ?? 0 ?>
                                        </span>
                                    </td>
                                    
                                    <td class="text-center">
                                        <span class="badge bg-light text-dark">
                                            <?= $category['sort_order'] ?>
                                        </span>
                                    </td>
                                    
                                    <td>
                                        <div class="d-flex justify-content-end gap-1">
                                            <a href="<?= BASE_URL ?>/docs/category/<?= $category['slug'] ?>" 
                                               class="btn btn-sm btn-outline-secondary" 
                                               target="_blank" 
                                               title="Просмотр на сайте"
                                               data-bs-toggle="tooltip">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <a href="<?= ADMIN_URL ?>/docs/categories/edit/<?= $category['id'] ?>" 
                                               class="btn btn-sm btn-outline-primary"
                                               title="Редактировать"
                                               data-bs-toggle="tooltip">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <a href="<?= ADMIN_URL ?>/docs/categories/delete/<?= $category['id'] ?>" 
                                               class="btn btn-sm btn-outline-danger"
                                               onclick="return confirm('Вы уверены, что хотите удалить категорию \"<?= addslashes($category['name']) ?>\"?')"
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

<?php ob_start(); ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Инициализация сортировки
    const sortable = new Sortable(document.getElementById('sortable-categories'), {
        handle: '.drag-handle',
        animation: 150,
        onEnd: function(evt) {
            const items = document.querySelectorAll('.sortable-item');
            const order = [];
            
            items.forEach((item, index) => {
                const categoryId = item.getAttribute('data-category-id');
                order.push({
                    id: parseInt(categoryId),
                    order: index + 1
                });
            });
            
            // Отправляем новый порядок на сервер
            fetch('<?= ADMIN_URL ?>/docs/categories/reorder', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(order)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Обновляем порядок в таблице
                    items.forEach((item, index) => {
                        const badge = item.querySelector('.badge.bg-light');
                        if (badge) {
                            badge.textContent = index + 1;
                        }
                    });
                } else {
                    alert('Ошибка при сохранении порядка: ' + (data.message || 'Неизвестная ошибка'));
                    location.reload();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Ошибка при сохранении порядка');
                location.reload();
            });
        }
    });
});
</script>
<?php admin_bottom_js(ob_get_clean()); ?>