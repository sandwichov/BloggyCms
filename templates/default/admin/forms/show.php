<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">
            <?php echo bloggy_icon('bs', 'envelope', '24', '#000', 'me-2'); ?>
            Отправки формы: <?php echo html($form['name']); ?>
        </h4>
        <div>
            <a href="<?php echo ADMIN_URL; ?>/forms" class="btn btn-outline-secondary me-2">
                <?php echo bloggy_icon('bs', 'arrow-left', '16', '#000', 'me-2'); ?>
                Назад
            </a>
            <a href="<?php echo ADMIN_URL; ?>/forms/edit/<?php echo $form['id']; ?>" class="btn btn-outline-primary me-2">
                <?php echo bloggy_icon('bs', 'pencil', '16', '#000', 'me-2'); ?>
                Редактировать
            </a>
            <button type="button" class="btn btn-success" onclick="exportToCSV()">
                <?php echo bloggy_icon('bs', 'download', '16', '#fff', 'me-2'); ?>
                Экспорт в CSV
            </button>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <?php echo bloggy_icon('bs', 'envelope', '32', '#fff'); ?>
                        </div>
                        <div>
                            <h3 class="mb-0"><?php echo $submissionsCount; ?></h3>
                            <small>Всего отправок</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <?php echo bloggy_icon('bs', 'envelope-open', '32', '#fff'); ?>
                        </div>
                        <div>
                            <h3 class="mb-0"><?php echo $newCount; ?></h3>
                            <small>Новых отправок</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <?php echo bloggy_icon('bs', 'check-circle', '32', '#fff'); ?>
                        </div>
                        <div>
                            <h3 class="mb-0"><?php echo $processedCount; ?></h3>
                            <small>Обработано</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <?php echo bloggy_icon('bs', 'shield-slash', '32', '#fff'); ?>
                        </div>
                        <div>
                            <h3 class="mb-0"><?php echo $spamCount; ?></h3>
                            <small>Спам</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if (empty($submissions)) { ?>
    <div class="card border-0 shadow-sm">
        <div class="card-body text-center py-5">
            <?php echo bloggy_icon('bs', 'inbox', '48', '#6C6C6C', 'mb-3'); ?>
            <h5 class="text-muted">Отправок нет</h5>
            <p class="text-muted mb-4">Еще никто не отправил эту форму</p>
            <a href="<?php echo ADMIN_URL; ?>/forms/preview/<?php echo $form['id']; ?>" class="btn btn-primary">
                <?php echo bloggy_icon('bs', 'eye', '16', '#fff', 'me-2'); ?>
                Предпросмотр формы
            </a>
        </div>
    </div>
    <?php } else { ?>
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">
                <?php echo bloggy_icon('bs', 'list-ul', '20', '#000', 'me-2'); ?>
                Список отправок
            </h5>
            <div class="btn-group">
                <button type="button" class="btn btn-outline-danger btn-sm" onclick="deleteAllSubmissions()">
                    <?php echo bloggy_icon('bs', 'trash', '16', '#000', 'me-1'); ?>
                    Удалить все
                </button>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Дата</th>
                            <th>IP адрес</th>
                            <th>Данные</th>
                            <th>Статус</th>
                            <th class="end">Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($submissions as $submission) { 
                            $dataPreview = array();
                            foreach ($submission['data'] as $key => $value) {
                                if ($key === 'files' && is_array($value)) {
                                    foreach ($value as $fieldName => $fileInfo) {
                                        $dataPreview[] = '<strong>Файл ' . html($fieldName) . ':</strong> ' . 
                                                        html($fileInfo['name']);
                                    }
                                    continue;
                                }
                                
                                if (is_array($value)) {
                                    $value = implode(', ', $value);
                                }
                                if (mb_strlen($value) > 20) {
                                    $value = mb_substr($value, 0, 20) . '...';
                                }
                                $dataPreview[] = '<strong>' . html($key) . ':</strong> ' . html($value);
                            }
                        ?>
                        <tr>
                            <td>
                                <strong>#<?php echo html($submission['id']); ?></strong>
                            </td>
                            <td>
                                <small class="text-muted">
                                    <?php echo date('d.m.Y H:i', strtotime($submission['created_at'])); ?>
                                </small>
                            </td>
                            <td>
                                <code><?php echo html($submission['ip_address']); ?></code>
                            </td>
                            <td>
                                <small><?php echo implode('<br>', $dataPreview); ?></small>
                            </td>
                            <td>
                                <select class="form-select form-select-sm status-select" 
                                        data-id="<?php echo $submission['id']; ?>"
                                        data-original-value="<?php echo $submission['status']; ?>"
                                        style="width: 120px;">
                                    <option value="new" <?php echo $submission['status'] === 'new' ? 'selected' : ''; ?>>Новый</option>
                                    <option value="read" <?php echo $submission['status'] === 'read' ? 'selected' : ''; ?>>Прочитан</option>
                                    <option value="processed" <?php echo $submission['status'] === 'processed' ? 'selected' : ''; ?>>Обработан</option>
                                    <option value="spam" <?php echo $submission['status'] === 'spam' ? 'selected' : ''; ?>>Спам</option>
                                </select>
                            </td>
                            <td class="end">
                                <div class="btn-group btn-group-sm">
                                    <button type="button" 
                                            class="btn btn-outline-primary view-submission"
                                            data-id="<?php echo $submission['id']; ?>"
                                            data-bs-toggle="modal" 
                                            data-bs-target="#viewSubmissionModal">
                                        <?php echo bloggy_icon('bs', 'eye', '16', '#000'); ?>
                                    </button>
                                    <button type="button" 
                                            class="btn btn-outline-danger delete-submission"
                                            data-id="<?php echo $submission['id']; ?>">
                                        <?php echo bloggy_icon('bs', 'trash', '16', '#000'); ?>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <?php if ($totalPages > 1) { ?>
        <div class="card-footer bg-white border-0">
            <nav aria-label="Навигация по страницам">
                <ul class="pagination justify-content-center mb-0">
                    <?php for ($i = 1; $i <= $totalPages; $i++) { ?>
                        <li class="page-item <?php echo $i == $currentPage ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                        </li>
                    <?php } ?>
                </ul>
            </nav>
        </div>
        <?php } ?>
    </div>
    <?php } ?>
</div>

<div class="modal fade" id="viewSubmissionModal" tabindex="-1" aria-labelledby="viewSubmissionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewSubmissionModalLabel">
                    <?php echo bloggy_icon('bs', 'envelope-open', '20', '#000', 'me-2'); ?>
                    Просмотр отправки
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="submission-details">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Загрузка...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <?php echo bloggy_icon('bs', 'x-circle', '16', '#000', 'me-1'); ?>
                    Закрыть
                </button>
                <button type="button" class="btn btn-primary" onclick="printSubmission()">
                    <?php echo bloggy_icon('bs', 'printer', '16', '#fff', 'me-1'); ?>
                    Печать
                </button>
            </div>
        </div>
    </div>
</div>

<?php ob_start(); ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.status-select').forEach(select => {
            updateStatusSelectStyle(select);
            
            select.addEventListener('change', function() {
                const submissionId = this.dataset.id;
                const newStatus = this.value;
                const originalValue = this.dataset.originalValue;
                const originalColor = this.style.borderColor;
                this.style.borderColor = '#ffc107';
                fetch('<?php echo ADMIN_URL; ?>/forms/update-submission-status/' + submissionId + '?status=' + newStatus, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        updateStatusSelectStyle(this);
                        this.dataset.originalValue = newStatus;
                        showNotification('Статус обновлен', 'success');
                    } else {
                        this.value = originalValue;
                        updateStatusSelectStyle(this);
                        showNotification(data.message || 'Ошибка при обновлении статуса', 'error');
                    }
                })
                .catch(error => {
                    this.value = originalValue;
                    updateStatusSelectStyle(this);
                    showNotification('Ошибка сети: ' + error.message, 'error');
                })
                .finally(() => {
                    this.style.borderColor = originalColor;
                });
            });
        });
    });
    
    function updateStatusSelectStyle(select) {
        const statusColors = {
            'new': 'warning',
            'read': 'info',
            'processed': 'success',
            'spam': 'danger'
        };
        
        const color = statusColors[select.value] || 'secondary';
        select.className = `form-select form-select-sm status-select border-${color}`;
    }
    
    document.querySelectorAll('.view-submission').forEach(btn => {
        btn.addEventListener('click', function() {
            const submissionId = this.dataset.id;
            const modalBody = document.getElementById('submission-details');
            modalBody.innerHTML = `
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Загрузка...</span>
                    </div>
                </div>
            `;
            
            fetch('<?php echo ADMIN_URL; ?>/forms/get-submission/' + submissionId, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok: ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                if (data.success && data.submission) {
                    const submission = data.submission;
                    let html = `
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <strong>ID:</strong> #${submission.id}
                                </div>
                                <div class="mb-3">
                                    <strong>Дата отправки:</strong> ${submission.created_at}
                                </div>
                                <div class="mb-3">
                                    <strong>IP адрес:</strong> ${submission.ip_address || 'Не указан'}
                                </div>
                                <div class="mb-3">
                                    <strong>Статус:</strong> 
                                    <span class="badge bg-${getStatusColor(submission.status)}">
                                        ${getStatusText(submission.status)}
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <strong>User Agent:</strong>
                                    <div class="small text-muted">${submission.user_agent || 'Не указан'}</div>
                                </div>
                                <div class="mb-3">
                                    <strong>Referer:</strong>
                                    <div class="small text-muted">${submission.referer || 'Не указан'}</div>
                                </div>
                            </div>
                        </div>
                        
                        <hr>
                        
                        <h6 class="mb-3"><?php echo bloggy_icon('bs', 'card-text', '16', '#000', 'me-2'); ?>Данные формы:</h6>
                    `;
                    
                    if (submission.data && Object.keys(submission.data).length > 0) {
                        html += '<div class="table-responsive"><table class="table table-sm table-bordered">';
                        
                        for (const [field, value] of Object.entries(submission.data)) {
                            let displayValue = value;
                            if (Array.isArray(value)) {
                                displayValue = value.join(', ');
                            }
                            
                            html += `
                                <tr>
                                    <td style="width: 30%"><strong>${escapeHtml(field)}</strong></td>
                                    <td>${escapeHtml(displayValue)}</td>
                                </tr>
                            `;
                        }
                        
                        html += '</table></div>';
                    } else {
                        html += '<div class="alert alert-info">Нет данных</div>';
                    }
                    
                    if (submission.files && submission.files.length > 0) {
                        html += `
                            <hr>
                            <h6 class="mb-3"><?php echo bloggy_icon('bs', 'paperclip', '16', '#000', 'me-2'); ?>Прикрепленные файлы:</h6>
                            <div class="row">
                        `;
                        
                        submission.files.forEach(file => {
                            html += `
                                <div class="col-md-6 mb-2">
                                    <div class="card">
                                        <div class="card-body p-2">
                                            <div class="d-flex align-items-center">
                                                <div class="me-2">
                                                    <?php echo bloggy_icon('bs', 'file-earmark', '32', '#000'); ?>
                                                </div>
                                                <div>
                                                    <div class="small"><strong>${escapeHtml(file.file_name)}</strong></div>
                                                    <div class="small text-muted">${formatFileSize(file.file_size)}</div>
                                                    <div class="small">
                                                        <a href="<?php echo BASE_URL; ?>/${escapeHtml(file.file_path)}" target="_blank" class="text-decoration-none">
                                                            <?php echo bloggy_icon('bs', 'download', '16', '#000', 'me-1'); ?>Скачать
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            `;
                        });
                        
                        html += '</div>';
                    }
                    
                    modalBody.innerHTML = html;
                } else {
                    modalBody.innerHTML = `
                        <div class="alert alert-danger">
                            <?php echo bloggy_icon('bs', 'exclamation-triangle', '16', '#000', 'me-2'); ?>
                            ${data.message || 'Ошибка при загрузке данных'}
                        </div>
                    `;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                modalBody.innerHTML = `
                    <div class="alert alert-danger">
                        <?php echo bloggy_icon('bs', 'exclamation-triangle', '16', '#000', 'me-2'); ?>
                        Ошибка: ${error.message}
                        <div class="mt-2">
                            <button class="btn btn-sm btn-outline-secondary" onclick="location.reload()">
                                <?php echo bloggy_icon('bs', 'arrow-clockwise', '16', '#000', 'me-1'); ?>Обновить страницу
                            </button>
                        </div>
                    </div>
                `;
            });
        });
    });
    
    document.querySelectorAll('.delete-submission').forEach(btn => {
        btn.addEventListener('click', function() {
            const submissionId = this.dataset.id;
            
            if (confirm('Удалить эту отправку?')) {
                this.innerHTML = '<?php echo bloggy_icon('bs', 'hourglass-split', '16', '#000'); ?>';
                this.disabled = true;
                
                fetch('<?php echo ADMIN_URL; ?>/forms/delete-submission/' + submissionId, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        const row = this.closest('tr');
                        row.style.opacity = '0.5';
                        setTimeout(() => {
                            row.remove();
                            showNotification('Отправка удалена', 'success');
                        }, 300);
                    } else {
                        this.innerHTML = '<?php echo bloggy_icon('bs', 'trash', '16', '#000'); ?>';
                        this.disabled = false;
                        showNotification(data.message || 'Ошибка при удалении', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    this.innerHTML = '<?php echo bloggy_icon('bs', 'trash', '16', '#000'); ?>';
                    this.disabled = false;
                    showNotification('Ошибка сети: ' + error.message, 'error');
                });
            }
        });
    });
    
    function exportToCSV() {
        window.location.href = '<?php echo ADMIN_URL; ?>/forms/export/<?php echo $form['id']; ?>';
    }
    
    function deleteAllSubmissions() {
        if (confirm('Вы уверены, что хотите удалить ВСЕ отправки этой формы?')) {
            const button = event.target;
            const originalText = button.innerHTML;
            button.innerHTML = '<?php echo bloggy_icon('bs', 'hourglass-split', '16', '#000', 'me-1'); ?>Удаление...';
            button.disabled = true;
            
            fetch('<?php echo ADMIN_URL; ?>/forms/delete-all-submissions/<?php echo $form['id']; ?>', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    showNotification(`Удалено ${data.count || 0} отправок`, 'success');
                    setTimeout(() => {
                        location.reload();
                    }, 1000);
                } else {
                    button.innerHTML = originalText;
                    button.disabled = false;
                    showNotification(data.message || 'Ошибка при удалении', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                button.innerHTML = originalText;
                button.disabled = false;
                showNotification('Ошибка сети: ' + error.message, 'error');
            });
        }
    }
    
    function getStatusColor(status) {
        switch(status) {
            case 'new': return 'warning';
            case 'read': return 'info';
            case 'processed': return 'success';
            case 'spam': return 'danger';
            default: return 'secondary';
        }
    }
    
    function getStatusText(status) {
        switch(status) {
            case 'new': return 'Новый';
            case 'read': return 'Прочитан';
            case 'processed': return 'Обработан';
            case 'spam': return 'Спам';
            default: return status;
        }
    }
    
    function formatFileSize(bytes) {
        if (!bytes || bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }
    
    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    function printSubmission() {
        const printContent = document.getElementById('submission-details').innerHTML;
        const originalContent = document.body.innerHTML;
        
        document.body.innerHTML = `
            <html>
                <head>
                    <title>Отправка формы - <?php echo html($form['name']); ?></title>
                    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
                </head>
                <body>
                    <div class="container mt-4">
                        <h4>Отправка формы: <?php echo html($form['name']); ?></h4>
                        <hr>
                        ${printContent}
                    </div>
                </body>
            </html>
        `;
        
        window.print();
        document.body.innerHTML = originalContent;
        location.reload();
    }
    
    function showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
        notification.style.cssText = `
            top: 20px;
            right: 20px;
            z-index: 9999;
            min-width: 300px;
            max-width: 400px;
        `;
        notification.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        
        document.body.appendChild(notification);
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 5000);
    }
</script>
<?php admin_bottom_js(ob_get_clean()); ?>