<?php defined('BASE_PATH') || exit('No direct script access allowed'); ?>

<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">
            <i class="bi bi-gear me-2"></i>
            Настройки плагина: <?= html($plugin->getName()) ?>
        </h4>
        <a href="<?= ADMIN_URL ?>/plugins" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Назад к списку
        </a>
    </div>

    <?= $plugin->renderAdminPage() ?>
</div>