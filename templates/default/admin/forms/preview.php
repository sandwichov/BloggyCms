<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">
            <?php echo bloggy_icon('bs', 'eye', '24', '#000', 'me-2'); ?>
            Предпросмотр формы: <?php echo html($form['name']); ?>
        </h4>
        <a href="<?php echo ADMIN_URL; ?>/forms" class="btn btn-outline-secondary">
            <?php echo bloggy_icon('bs', 'arrow-left', '16', '#000', 'me-2'); ?>
            Назад к списку
        </a>
    </div>
    
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="row">
                <div class="col-md-12">
                    <h5 class="mb-3"><?php echo html($form['name']); ?></h5>
                    
                    <?php if (!empty($form['description'])) { ?>
                        <div class="alert alert-info mb-4">
                            <?php echo nl2br(html($form['description'])); ?>
                        </div>
                    <?php } ?>
                    
                    <?php echo $formHtml; ?>
                </div>
            </div>
        </div>
    </div>
</div>