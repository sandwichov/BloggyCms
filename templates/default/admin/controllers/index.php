<div class="table-responsive">
    <table class="table table-hover align-middle">
        <thead class="table-light">
            <tr>
                <th width="30%">Контроллер</th>
                <th width="35%">Информация</th>
                <th width="25%">Статус</th>
                <th width="10%" class="text-end">Действия</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($controllers as $controller) { ?>
                <tr class="controller-row">
                    <td>
                        <div class="d-flex align-items-center">
                            <?php if($controller['is_system']) { ?>
                                <span class="badge bg-info text-info me-2" title="Системный контроллер" style="min-width: 32px; text-align: center;" data-bs-toggle="tooltip">
                                    <?php echo bloggy_icon('bs', 'shield-fill-check', '16', '#0dcaf0'); ?>
                                </span>
                            <?php } else { ?>
                                <span class="badge bg-secondary text-secondary me-2" style="min-width: 32px; text-align: center;">
                                    <?php echo bloggy_icon('bs', 'box', '16', '#6c757d'); ?>
                                </span>
                            <?php } ?>
                            <div>
                                <div class="controller-name">
                                    <strong><?php echo html($controller['name']) ?></strong>
                                </div>
                                <div class="controller-path">
                                    <code class="text-muted"><?php echo html($controller['path']) ?></code>
                                </div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="controller-info">
                            <?php if(!empty($controller['description'])) { ?>
                                <div class="mb-2 text-primary small">
                                    <em><?php echo html($controller['description']) ?></em>
                                </div>
                            <?php } ?>
                            <div class="d-flex flex-wrap align-items-center gap-2">
                                <span class="badge bg-light text-dark border" data-bs-toggle="tooltip" title = "Разработчик">
                                    <?php echo bloggy_icon('bs', 'person', '12', '#6c757d', 'me-1'); ?>
                                    <?php echo html($controller['author']) ?>
                                </span>
                                <span class="badge bg-light text-dark border" data-bs-toggle="tooltip" title = "Версия">
                                    <?php echo bloggy_icon('bs', 'tag', '12', '#6c757d', 'me-1'); ?>
                                    v<?php echo html($controller['version']) ?>
                                </span>
                                <?php if($controller['actions_count'] > 0) { ?>
                                    <span class="badge bg-light text-dark border" data-bs-toggle="tooltip" title = "Количество экшенов">
                                        <?php echo bloggy_icon('bs', 'lightning', '12', '#6c757d', 'me-1'); ?>
                                        <?php echo $controller['actions_count'] ?>
                                    </span>
                                <?php } ?>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="d-flex flex-wrap gap-2">
                            <?php if($controller['has_settings']) { ?>
                                <span class="badge bg-success text-success border" title="Есть настройки">
                                    <?php echo bloggy_icon('bs', 'gear-fill', '14', '#0a4a2cff', 'me-1'); ?>
                                    Настройки
                                </span>
                            <?php } ?>
                            
                            <?php if($controller['has_routing']) { ?>
                                <span class="badge bg-primary text-primary border" title="Есть маршрутизация">
                                    <?php echo bloggy_icon('bs', 'signpost-split', '14', '#afcbf5ff', 'me-1'); ?>
                                    Роутинг
                                </span>
                            <?php } ?>
                            
                            <?php if($controller['is_system']) { ?>
                                <span class="badge bg-info text-info border" title="Системный контроллер">
                                    <?php echo bloggy_icon('bs', 'shield-fill-check', '14', '#0b7c92ff', 'me-1'); ?>
                                    Системный
                                </span>
                            <?php } ?>
                        </div>
                    </td>
                    <td>
                        <div class="d-flex justify-content-end gap-1">
                            <?php if($controller['has_settings']) { ?>
                                <a href="<?= ADMIN_URL ?>/settings?tab=components&controller=<?= $controller['path'] ?>"
                                   class="btn btn-sm btn-outline-primary border"
                                   title="Настройки контроллера"
                                   data-bs-toggle="tooltip">
                                    <?php echo bloggy_icon('bs', 'gear-fill', '16'); ?>
                                </a>
                            <?php } ?>
                            
                            <button type="button" 
                                    class="btn btn-sm btn-outline-secondary border controller-info-btn"
                                    title="Подробная информация"
                                    data-bs-toggle="modal"
                                    data-bs-target="#controllerInfoModal"
                                    data-controller='<?= html(json_encode([
                                        'name' => $controller['name'],
                                        'path' => $controller['path'],
                                        'author' => $controller['author'],
                                        'version' => $controller['version'],
                                        'description' => $controller['description'],
                                        'is_system' => $controller['is_system'],
                                        'has_settings' => $controller['has_settings'],
                                        'has_routing' => $controller['has_routing'],
                                        'actions_count' => $controller['actions_count']
                                    ]), ENT_QUOTES) ?>'>
                                <?php echo bloggy_icon('bs', 'info-circle', '16'); ?>
                            </button>
                        </div>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

<div class="modal fade" id="controllerInfoModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="controllerInfoModalLabel">Детальная информация</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="controllerInfoContent"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Закрыть</button>
            </div>
        </div>
    </div>
</div>

<?php ob_start(); ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    const controllerInfoModal = document.getElementById('controllerInfoModal');
    if (controllerInfoModal) {
        controllerInfoModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const controllerData = JSON.parse(button.getAttribute('data-controller'));
            const modalBody = document.getElementById('controllerInfoContent');
            
            let html = `
                <div class="row">
                    <div class="col-12">
                        <h6 class="border-bottom pb-2 mb-3">${controllerData.name}</h6>
                    </div>
                </div>
                
                <div class="row mb-3">
                    <div class="col-6">
                        <div class="mb-2">
                            <small class="text-muted d-block">Путь:</small>
                            <code>/controllers/${controllerData.path}</code>
                        </div>
                        <div class="mb-2">
                            <small class="text-muted d-block">Автор:</small>
                            <div>${controllerData.author}</div>
                        </div>
                        <div class="mb-2">
                            <small class="text-muted d-block">Версия:</small>
                            <div>${controllerData.version}</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="mb-2">
                            <small class="text-muted d-block">Тип:</small>
                            <div>
                                ${controllerData.is_system ? 
                                    '<span class="badge bg-info text-white">Системный</span>' : 
                                    '<span class="badge bg-secondary text-white">Пользовательский</span>'}
                            </div>
                        </div>
                        <div class="mb-2">
                            <small class="text-muted d-block">Настройки:</small>
                            <div>
                                ${controllerData.has_settings ? 
                                    '<span class="badge bg-success text-white">Доступны</span>' : 
                                    '<span class="badge bg-secondary text-white">Не доступны</span>'}
                            </div>
                        </div>
                        <div class="mb-2">
                            <small class="text-muted d-block">Роутинг:</small>
                            <div>
                                ${controllerData.has_routing ? 
                                    '<span class="badge bg-primary text-white">Настроен</span>' : 
                                    '<span class="badge bg-secondary text-white">Не настроен</span>'}
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            if (controllerData.description) {
                html += `
                    <div class="row">
                        <div class="col-12">
                            <div class="mb-2">
                                <small class="text-muted d-block">Описание:</small>
                                <div class="alert alert-light bg-light border">
                                    ${controllerData.description}
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            }
            
            if (controllerData.actions_count > 0) {
                html += `
                    <div class="row">
                        <div class="col-12">
                            <div class="mb-2">
                                <small class="text-muted d-block">Количество экшенов:</small>
                                <div class="d-flex align-items-center">
                                    <span class="badge bg-light text-dark border">
                                        ${controllerData.actions_count} экшенов
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            }
            
            modalBody.innerHTML = html;
        });
    }
    
    const tableHeaders = document.querySelectorAll('th[data-sortable]');
    tableHeaders.forEach(header => {
        header.style.cursor = 'pointer';
        header.addEventListener('click', function() {
            sortTable(this.cellIndex);
        });
    });
});

function sortTable(columnIndex) {
    const table = document.querySelector('table');
    const tbody = table.querySelector('tbody');
    const rows = Array.from(tbody.querySelectorAll('tr'));
    
    const isAscending = tbody.getAttribute('data-sort-direction') !== 'asc';
    
    rows.sort((a, b) => {
        const aCell = a.cells[columnIndex];
        const bCell = b.cells[columnIndex];
        
        let aValue = aCell.textContent.trim();
        let bValue = bCell.textContent.trim();
        
        if (columnIndex === 2) {
            const aMatch = aValue.match(/\d+/);
            const bMatch = bValue.match(/\d+/);
            aValue = aMatch ? parseInt(aMatch[0]) : 0;
            bValue = bMatch ? parseInt(bMatch[0]) : 0;
        }
        
        if (aValue < bValue) return isAscending ? -1 : 1;
        if (aValue > bValue) return isAscending ? 1 : -1;
        return 0;
    });
    
    rows.forEach(row => tbody.appendChild(row));
    tbody.setAttribute('data-sort-direction', isAscending ? 'asc' : 'desc');
    updateSortIcons(table, columnIndex, isAscending);
}

function updateSortIcons(table, columnIndex, isAscending) {
    const headers = table.querySelectorAll('th');
    headers.forEach((header, index) => {
        header.classList.remove('sort-asc', 'sort-desc');
        if (index === columnIndex) {
            header.classList.add(isAscending ? 'sort-asc' : 'sort-desc');
        }
    });
}
</script>
<?php admin_bottom_js(ob_get_clean()); ?>