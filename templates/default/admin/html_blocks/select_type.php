<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">
            <i class="bi bi-plus-square me-2"></i>
            Выберите тип контент-блока
        </h4>
        <a href="<?= ADMIN_URL ?>/html-blocks" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Назад к контент-блокам
        </a>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <label class="form-label">Фильтр по шаблону:</label>
                    <select class="form-select" id="template-filter">
                        <?php foreach($availableTemplates as $templateValue => $templateName): ?>
                        <option value="<?= $templateValue ?>">
                            <?= html($templateName) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <div class="text-muted small">
                        <i class="bi bi-info-circle me-1"></i>
                        Отображаются все блоки независимо от шаблона
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row" id="blocks-container">
        <?php foreach($blockTypes as $systemName => $type): ?>
        <div class="col-md-6 col-lg-4 mb-4 block-item" 
             data-template="<?= html($type['template'] ?? 'all') ?>">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-start mb-3">
                        <div class="bg-primary p-3 rounded me-3">
                            <i class="<?= $type['icon'] ?? 'bi bi-box' ?> text-white" style="font-size: 1.5rem;"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h5 class="card-title mb-1"><?= html($type['name']) ?></h5>
                            <p class="text-muted small mb-0"><?= html($type['short_description'] ?? $type['description']) ?></p>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <small class="text-muted"><?= html($type['description']) ?></small>
                    </div>
                    
                    <?php if(!empty($type['template']) && $type['template'] !== 'all'): ?>
                        <div class="mb-2">
                            <span class="badge bg-info">
                                <i class="bi bi-palette me-1"></i>
                                Шаблон: <?= html($type['template']) ?>
                            </span>
                        </div>
                    <?php endif; ?>
                    
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted small">
                            <div>Автор: <?= html($type['author'] ?? 'BloggyCMS') ?></div>
                            <div>Версия: <?= html($type['version'] ?? '1.0.0') ?></div>
                            <?php if(!empty($type['author_website'])): ?>
                            <div>
                                <a href="<?= html($type['author_website']) ?>" target="_blank" class="text-muted">
                                    <?= html($type['author_website']) ?>
                                </a>
                            </div>
                            <?php endif; ?>
                        </div>
                        <a href="<?= ADMIN_URL ?>/html-blocks/create?type=<?= $systemName ?>" 
                           class="btn btn-primary">
                            <i class="bi bi-plus-lg me-1"></i> Создать
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<?php ob_start(); ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const templateFilter = document.getElementById('template-filter');
    const blocksContainer = document.getElementById('blocks-container');
    const blockItems = document.querySelectorAll('.block-item');

    templateFilter.addEventListener('change', function() {
        const selectedTemplate = this.value;
        
        blockItems.forEach(function(item) {
            const blockTemplate = item.getAttribute('data-template');
            
            if (selectedTemplate === 'all' || blockTemplate === selectedTemplate) {
                item.style.display = 'block';
            } else {
                item.style.display = 'none';
            }
        });
    });
});
</script>
<?php admin_bottom_js(ob_get_clean()); ?>