<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">
            <i class="bi bi-eye me-2"></i>
            Предпросмотр формы: <?= html($form['name']) ?>
        </h4>
        <a href="<?= ADMIN_URL ?>/forms" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Назад к списку
        </a>
    </div>
    
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="row">
                <div class="col-md-12">
                    <h5 class="mb-3"><?= html($form['name']) ?></h5>
                    
                    <?php if (!empty($form['description'])): ?>
                        <div class="alert alert-info mb-4">
                            <?= nl2br(html($form['description'])) ?>
                        </div>
                    <?php endif; ?>
                    
                    <?= $formHtml ?>
                </div>
            </div>
        </div>
    </div>
</div>