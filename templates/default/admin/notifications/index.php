<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">
            <i class="bi bi-bell me-2"></i>
            Уведомления
        </h4>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-success" id="mark-all-read-btn">
                <i class="bi bi-check2-all me-1"></i> Отметить все как прочитанные
            </button>
            <button type="button" class="btn btn-outline-danger" id="clear-read-btn">
                <i class="bi bi-trash me-1"></i> Очистить прочитанные
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
                            <span class="badge bg-secondary"><?= $stats['total'] ?? 0 ?></span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span>Непрочитанные:</span>
                            <span class="badge bg-primary"><?= $stats['unread'] ?? 0 ?></span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span>Прочитанные:</span>
                            <span class="badge bg-success"><?= $stats['read_count'] ?? 0 ?></span>
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
    window.ADMIN_URL = '<?= ADMIN_URL ?>';
}
</script>