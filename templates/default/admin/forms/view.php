<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <h1 class="mb-4"><?= html($form['name']) ?></h1>
            
            <?php if (!empty($form['description'])): ?>
                <div class="alert alert-info mb-4">
                    <?= nl2br(html($form['description'])) ?>
                </div>
            <?php endif; ?>
            
            <?php 
            if (class_exists('FormRenderer')) {
                echo FormRenderer::render($form['slug']);
            } else {
                $this->renderForm($form['slug']);
            }
            ?>
        </div>
    </div>
</div>