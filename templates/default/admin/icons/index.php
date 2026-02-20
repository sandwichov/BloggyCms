<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">
            <i class="bi bi-emoji-smile me-2"></i>
            Иконки
        </h4>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="input-group">
                <span class="input-group-text border-0 bg-light">
                    <i class="bi bi-search"></i>
                </span>
                <input type="text" 
                       id="iconSearch" 
                       class="form-control border-0 bg-light" 
                       placeholder="Поиск иконок..."
                       oninput="filterIcons(this.value)">
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <ul class="nav nav-tabs nav-tabs-custom" id="iconTabs" role="tablist">
                <?php $isFirst = true; foreach($icons as $set): ?>
                <li class="nav-item" role="presentation">
                    <button class="nav-link <?= $isFirst ? 'active' : '' ?>" 
                            id="<?= $set['name'] ?>-tab" 
                            data-bs-toggle="tab" 
                            data-bs-target="#<?= $set['name'] ?>-content" 
                            type="button" 
                            role="tab" 
                            aria-controls="<?= $set['name'] ?>-content" 
                            aria-selected="<?= $isFirst ? 'true' : 'false' ?>">
                        <?= html(ucfirst($set['name'])) ?>
                    </button>
                </li>
                <?php $isFirst = false; endforeach; ?>
            </ul>

            <div class="tab-content pt-4" id="iconTabsContent">
                <?php $isFirst = true; foreach($icons as $set): ?>
                <div class="tab-pane fade <?= $isFirst ? 'show active' : '' ?>" 
                     id="<?= $set['name'] ?>-content" 
                     role="tabpanel" 
                     aria-labelledby="<?= $set['name'] ?>-tab">
                    
                    <div class="row g-3">
                        <?php foreach($set['icons'] as $icon): ?>
                        <div class="col-md-3 col-lg-2 icon-item" data-icon-id="<?= html($icon['id']) ?>">
                            <div class="card border-0 shadow-sm h-100">
                                <div class="card-body text-center p-3">
                                    <div class="mb-2" style="font-size: 2rem;">
                                        <?= $icon['preview'] ?>
                                    </div>
                                    <div class="small text-muted">
                                        <code><?= html($icon['id']) ?></code>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php $isFirst = false; endforeach; ?>
            </div>
        </div>
    </div>
</div>

<?php
    add_admin_js('templates/default/admin/assets/js/controllers/icons.js');
?>