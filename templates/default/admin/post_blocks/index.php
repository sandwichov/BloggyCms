<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">
            <i class="bi bi-grid-3x3-gap me-2"></i>
            Постблоки
        </h4>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <?php foreach($postBlocksByCategory as $category => $blocks): ?>
            <div class="border-bottom">
                <div class="p-4 bg-light">
                    <h5 class="mb-0 text-dark">
                        <?= html(ucfirst($category)) ?> 
                        <span class="badge bg-secondary ms-2"><?= count($blocks) ?></span>
                    </h5>
                </div>
                
                <div class="p-4">
                    <div class="row g-3">
                        <?php foreach($blocks as $block): ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="card h-100 border">
                                <div class="card-body">
                                    <div class="d-flex align-items-start mb-3">
                                        <div class="post-block-icon me-3">
                                            <i class="<?= $block['icon'] ?>"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="card-title mb-1"><?= html($block['name']) ?></h6>
                                            <p class="card-text text-muted small mb-2">
                                                <?= html($block['description']) ?>
                                            </p>
                                            <div class="d-flex gap-2 mb-2">
                                                <span class="badge bg-<?= $block['can_use_in_posts'] ? 'success' : 'danger' ?>">
                                                    Посты
                                                </span>
                                                <span class="badge bg-<?= $block['can_use_in_pages'] ? 'success' : 'danger' ?>">
                                                    Страницы
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="small text-muted mb-3">
                                        <div>Системное имя: <code><?= $block['system_name'] ?></code></div>
                                        <div>Версия: <?= $block['version'] ?></div>
                                        <div>Автор: <?= $block['author'] ?></div>
                                    </div>
                                </div>
                                <div class="card-footer bg-transparent">
                                    <a href="<?= ADMIN_URL ?>/post-blocks/edit?system_name=<?= $block['system_name'] ?>" 
                                       class="btn btn-sm btn-outline-primary w-100">
                                        <i class="bi bi-gear me-1"></i> Настройки
                                    </a>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>