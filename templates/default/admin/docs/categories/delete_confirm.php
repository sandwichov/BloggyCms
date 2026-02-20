<?php
$category = $data['category'] ?? [];
$articleCount = $data['article_count'] ?? 0;
$subcategoryCount = $data['subcategory_count'] ?? 0;
?>

<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">
            <i class="bi bi-exclamation-triangle text-warning me-2"></i>
            Подтверждение удаления категории
        </h4>
        <a href="<?= ADMIN_URL ?>/docs/categories" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Назад к категориям
        </a>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <strong>Внимание!</strong> Это действие нельзя отменить. Все связанные данные будут удалены.
                    </div>
                    
                    <?php if ($articleCount > 0): ?>
                        <div class="alert alert-warning">
                            <i class="bi bi-journal-text me-2"></i>
                            <strong>Внимание!</strong> В этой категории содержится <strong><?= $articleCount ?></strong> статей.
                            При удалении категории эти статьи станут бескатегорийными.
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($subcategoryCount > 0): ?>
                        <div class="alert alert-danger">
                            <i class="bi bi-folder me-2"></i>
                            <strong>Невозможно удалить!</strong> В этой категории содержится <strong><?= $subcategoryCount ?></strong> подкатегорий.
                            Сначала удалите или переместите подкатегории.
                        </div>
                    <?php endif; ?>
                    
                    <div class="card bg-light mb-4">
                        <div class="card-body">
                            <h5 class="card-title d-flex align-items-center">
                                <i class="bi bi-folder text-primary me-2"></i>
                                <?= htmlspecialchars($category['name'] ?? '') ?>
                            </h5>
                            
                            <div class="mb-3">
                                <?php if ($category['parent_id'] > 0): ?>
                                    <span class="badge bg-info">
                                        <i class="bi bi-arrow-return-right"></i>
                                        Подкатегория
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-primary">
                                        <i class="bi bi-folder"></i>
                                        Основная категория
                                    </span>
                                <?php endif; ?>
                                
                                <span class="badge bg-light text-dark ms-2">
                                    <i class="bi bi-hash"></i>
                                    <?= htmlspecialchars($category['slug'] ?? '') ?>
                                </span>
                                
                                <span class="badge bg-secondary ms-2">
                                    <i class="bi bi-sort-numeric-down"></i>
                                    Порядок: <?= $category['sort_order'] ?? 0 ?>
                                </span>
                            </div>
                            
                            <?php if (!empty($category['description'])): ?>
                                <div class="mb-3">
                                    <h6 class="text-muted mb-2">Описание:</h6>
                                    <p class="card-text">
                                        <?= htmlspecialchars($category['description']) ?>
                                    </p>
                                </div>
                            <?php endif; ?>
                            
                            <div class="row text-muted small">
                                <div class="col-md-6">
                                    <div class="mb-1">
                                        <i class="bi bi-calendar-plus"></i>
                                        Создано: <?= date('d.m.Y H:i', strtotime($category['created_at'] ?? 'now')) ?>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-1">
                                        <i class="bi bi-calendar-check"></i>
                                        Обновлено: <?= date('d.m.Y H:i', strtotime($category['updated_at'] ?? 'now')) ?>
                                    </div>
                                </div>
                            </div>
                            
                            <?php if (!empty($category['meta_title'])): ?>
                                <div class="mt-3 border-top pt-3">
                                    <h6 class="text-muted mb-2">Мета-данные:</h6>
                                    <p class="mb-1"><strong>Title:</strong> <?= htmlspecialchars($category['meta_title']) ?></p>
                                    <?php if (!empty($category['meta_description'])): ?>
                                        <p class="mb-0"><strong>Description:</strong> <?= htmlspecialchars(mb_substr($category['meta_description'], 0, 100)) ?></p>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <?php if ($subcategoryCount == 0): ?>
                        <form method="post">
                            <div class="d-flex justify-content-end gap-2">
                                <a href="<?= ADMIN_URL ?>/docs/categories" 
                                   class="btn btn-outline-secondary">
                                    <i class="bi bi-x-circle me-2"></i>Отмена
                                </a>
                                <button type="submit" 
                                        class="btn btn-danger"
                                        <?= $articleCount > 0 ? 'data-bs-toggle="tooltip" data-bs-title="Статьи станут бескатегорийными"' : '' ?>>
                                    <i class="bi bi-trash me-2"></i>Удалить категорию
                                </button>
                            </div>
                        </form>
                    <?php else: ?>
                        <div class="d-flex justify-content-end gap-2">
                            <a href="<?= ADMIN_URL ?>/docs/categories" 
                               class="btn btn-primary">
                                <i class="bi bi-arrow-left me-2"></i>Вернуться к категориям
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="bi bi-info-circle me-2"></i>
                        Статистика категории
                    </h5>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <span>
                                <i class="bi bi-journal-text me-2"></i>
                                Статей в категории
                            </span>
                            <span class="badge bg-<?= $articleCount > 0 ? 'primary' : 'secondary' ?> rounded-pill">
                                <?= $articleCount ?>
                            </span>
                        </div>
                        <div class="list-group-item d-flex justify-content-between align-items-center">
                            <span>
                                <i class="bi bi-folder me-2"></i>
                                Подкатегорий
                            </span>
                            <span class="badge bg-<?= $subcategoryCount > 0 ? 'warning' : 'secondary' ?> rounded-pill">
                                <?= $subcategoryCount ?>
                            </span>
                        </div>
                        <div class="list-group-item">
                            <small class="text-muted">
                                <i class="bi bi-exclamation-circle me-1"></i>
                                <?php if ($subcategoryCount > 0): ?>
                                    Сначала удалите все подкатегории
                                <?php elseif ($articleCount > 0): ?>
                                    Статьи будут сохранены без категории
                                <?php else: ?>
                                    Категория пуста, можно безопасно удалить
                                <?php endif; ?>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="card border-0 shadow-sm mt-3">
                <div class="card-header bg-light">
                    <h5 class="mb-0">
                        <i class="bi bi-lightbulb me-2"></i>
                        Рекомендации
                    </h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <?php if ($articleCount > 0): ?>
                            <li class="mb-2">
                                <i class="bi bi-arrow-right-circle text-primary me-2"></i>
                                <small>Перед удалением перенесите статьи в другую категорию</small>
                            </li>
                        <?php endif; ?>
                        <?php if ($subcategoryCount > 0): ?>
                            <li class="mb-2">
                                <i class="bi bi-arrow-right-circle text-warning me-2"></i>
                                <small>Сначала удалите или переместите все подкатегории</small>
                            </li>
                        <?php endif; ?>
                        <li class="mb-2">
                            <i class="bi bi-arrow-right-circle text-info me-2"></i>
                            <small>Если категория используется, лучше её переименовать</small>
                        </li>
                        <li>
                            <i class="bi bi-arrow-right-circle text-success me-2"></i>
                            <small>Используйте архивацию вместо удаления</small>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php ob_start(); ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Инициализация тултипов
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Предотвращаем отправку формы двойным кликом
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function() {
            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="bi bi-trash me-2"></i>Удаление...';
                
                // Показываем подтверждение для категорий со статьями
                const articleCount = <?= $articleCount ?>;
                if (articleCount > 0) {
                    const confirmMessage = "В этой категории содержится " + articleCount + " статей.\n" +
                                         "После удаления категории статьи станут бескатегорийными.\n\n" +
                                         "Вы уверены, что хотите продолжить?";
                    
                    if (!confirm(confirmMessage)) {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = '<i class="bi bi-trash me-2"></i>Удалить категорию';
                        return false;
                    }
                }
            }
        });
    }
});
</script>
<?php admin_bottom_js(ob_get_clean()); ?>