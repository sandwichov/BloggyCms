<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">
            <?php echo bloggy_icon('bs', 'bell', '24', '#000', 'me-2'); ?>
            Уведомления
        </h4>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-success" id="mark-all-read-btn">
                <?php echo bloggy_icon('bs', 'check2-all', '16', '#000', 'me-1'); ?>
                Отметить все как прочитанные
            </button>
            <button type="button" class="btn btn-outline-danger" id="clear-read-btn">
                <?php echo bloggy_icon('bs', 'trash', '16', '#000', 'me-1'); ?>
                Очистить прочитанные
            </button>
        </div>
    </div>

    <div class="row">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h6 class="card-title text-muted mb-3">Статистика</h6>
                    <div class="d-flex flex-column gap-3">
                        <?php 
                        $stats = $notificationModel->getStats(Auth::getUserId());
                        ?>
                        <div class="d-flex justify-content-between align-items-center">
                            <span>Всего:</span>
                            <span class="badge bg-secondary"><?php echo $stats['total'] ?? 0; ?></span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span>Непрочитанные:</span>
                            <span class="badge bg-primary"><?php echo $stats['unread'] ?? 0; ?></span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span>Прочитанные:</span>
                            <span class="badge bg-success"><?php echo $stats['read_count'] ?? 0; ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-9">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div id="notifications-list"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
if (typeof ADMIN_URL === 'undefined') {
    window.ADMIN_URL = '<?php echo ADMIN_URL; ?>';
}
</script>