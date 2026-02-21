<?php defined('BASE_PATH') || exit('No direct script access allowed'); ?>

<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">
            <?php echo bloggy_icon('bs', 'gear', '24', '#000', 'me-2'); ?>
            Настройки плагина: <?php echo html($plugin->getName()); ?>
        </h4>
        <a href="<?php echo ADMIN_URL; ?>/plugins" class="btn btn-outline-secondary">
            <?php echo bloggy_icon('bs', 'arrow-left', '16', '#000', 'me-2'); ?>
            Назад к списку
        </a>
    </div>

    <?php echo $plugin->renderAdminPage(); ?>
</div>