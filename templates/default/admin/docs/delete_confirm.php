<?php
$article = $data['article'] ?? [];
$category = $data['category'] ?? [];
?>

<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">
            <i class="bi bi-exclamation-triangle text-warning me-2"></i>
            Подтверждение удаления
        </h4>
        <a href="<?= ADMIN_URL ?>/docs" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Назад
        </a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <strong>Внимание!</strong> Это действие нельзя отменить. Все связанные данные будут удалены.
            </div>
            
            <div class="row mb-4">
                <div class="col-md-12">
                    <div class="card bg-light">
                        <div class="card-body">
                            <h5 class="card-title">
                                <?= htmlspecialchars($article['title'] ?? $category['name'] ?? '') ?>
                            </h5>
                            
                            <?php if (isset($article)): ?>
                                <div class="mb-2">
                                    <span class="badge bg-<?= 
                                        $article['status'] === 'published' ? 'success' : 
                                        ($article['status'] === 'draft' ? 'warning' : 'secondary')
                                    ?>">
                                        <?= 
                                            $article['status'] === 'published' ? 'Опубликована' : 
                                            ($article['status'] === 'draft' ? 'Черновик' : 'Архив')
                                        ?>
                                    </span>
                                    
                                    <?php if (!empty($article['category_name'])): ?>
                                        <span class="badge bg-light text-dark ms-1">
                                            <?= htmlspecialchars($article['category_name']) ?>
                                        </span>
                                    <?php endif; ?>
                                    
                                    <?php if ($article['is_featured'] ?? false): ?>
                                        <span class="badge bg-warning ms-1">
                                            <i class="bi bi-star"></i> Избранная
                                        </span>
                                    <?php endif; ?>
                                </div>
                                
                                <?php if (!empty($article['excerpt'])): ?>
                                    <p class="card-text">
                                        <?= htmlspecialchars(mb_substr($article['excerpt'], 0, 200)) ?>
                                        <?= mb_strlen($article['excerpt']) > 200 ? '...' : '' ?>
                                    </p>
                                <?php endif; ?>
                                
                                <div class="text-muted small">
                                    <div>
                                        <i class="bi bi-eye"></i>
                                        <?= $article['views'] ?? 0 ?> просмотров
                                    </div>
                                    <div>
                                        <i class="bi bi-calendar"></i>
                                        Создано: <?= date('d.m.Y H:i', strtotime($article['created_at'] ?? 'now')) ?>
                                    </div>
                                </div>
                                
                            <?php elseif (isset($category)): ?>
                                <div class="mb-2">
                                    <span class="badge bg-info">
                                        <i class="bi bi-folder"></i>
                                        Категория
                                    </span>
                                    
                                    <?php if ($category['parent_id'] > 0): ?>
                                        <span class="badge bg-light text-dark ms-1">
                                            Подкатегория
                                        </span>
                                    <?php endif; ?>
                                </div>
                                
                                <?php if (!empty($category['description'])): ?>
                                    <p class="card-text">
                                        <?= htmlspecialchars(mb_substr($category['description'], 0, 200)) ?>
                                        <?= mb_strlen($category['description']) > 200 ? '...' : '' ?>
                                    </p>
                                <?php endif; ?>
                                
                                <div class="text-muted small">
                                    <div>
                                        <i class="bi bi-hash"></i>
                                        Slug: <?= htmlspecialchars($category['slug'] ?? '') ?>
                                    </div>
                                    <div>
                                        <i class="bi bi-calendar"></i>
                                        Создано: <?= date('d.m.Y H:i', strtotime($category['created_at'] ?? 'now')) ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <form method="post">
                <div class="d-flex justify-content-end gap-2">
                    <a href="<?= ADMIN_URL ?>/docs<?= isset($category) ? '/categories' : '' ?>" 
                       class="btn btn-outline-secondary">
                        Отмена
                    </a>
                    <button type="submit" class="btn btn-danger">
                        <i class="bi bi-trash me-2"></i>Удалить
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php ob_start(); ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Предотвращаем отправку формы двойным кликом
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function() {
            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="bi bi-trash me-2"></i>Удаление...';
            }
        });
    }
});
</script>
<?php admin_bottom_js(ob_get_clean()); ?>