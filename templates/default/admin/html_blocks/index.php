<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">
            <i class="bi bi-code-square me-2"></i>
            Контент-блоки
        </h4>
        <div>
            <a href="<?= ADMIN_URL ?>/html-blocks/types" class="btn btn-outline-secondary me-2">
                <i class="bi bi-boxes me-2"></i>Типы блоков
            </a>
            <a href="<?= ADMIN_URL ?>/html-blocks/select-type" class="btn btn-primary">
                <i class="bi bi-plus-lg me-2"></i>Создать блок
            </a>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <?php if(empty($blocks)): ?>
                <div class="text-center py-5">
                    <div class="mb-3">
                        <i class="bi bi-code-square text-muted" style="font-size: 3rem;"></i>
                    </div>
                    <h5 class="text-muted">Контент-блоки пока не созданы</h5>
                    <p class="text-muted">Создайте первый контент-блок для вашего сайта</p>
                    <a href="<?= ADMIN_URL ?>/html-blocks/select-type" class="btn btn-primary">
                        <i class="bi bi-plus-lg me-2"></i>Создать блок
                    </a>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Название</th>
                                <th>Тип</th>
                                <th>Шаблон</th>
                                <th>Идентификатор</th>
                                <th>Статус типа</th>
                                <th class="text-end">Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($blocks as $block): ?>
                            <?php 
                                $typeIsActive = $block['type_is_active'] ?? true;
                            ?>
                            <tr class="<?= (!$typeIsActive) ? 'table-warning' : '' ?>">
                                <td>
                                    <strong><?= html($block['name']) ?></strong>
                                    <?php if(!$typeIsActive): ?>
                                    <div class="text-warning small mt-1">
                                        <i class="bi bi-exclamation-triangle"></i> Тип блока отключен
                                    </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-secondary"><?= html($block['type_name'] ?? 'Дефолтный') ?></span>
                                </td>
                                <td>
                                    <?php if(!empty($block['template']) && $block['template'] !== 'all'): ?>
                                    <span class="badge bg-info"><?= html($block['template']) ?></span>
                                    <?php else: ?>
                                    <span class="badge bg-light text-dark">all</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <code class="text-muted"><?= html($block['slug']) ?></code>
                                </td>
                                <td>
                                    <?php if($typeIsActive): ?>
                                        <span class="badge bg-success">Активен</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning">Тип отключен</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="d-flex justify-content-end gap-2">
                                        <?php if($typeIsActive): ?>
                                            <a href="<?= ADMIN_URL ?>/html-blocks/edit/<?= $block['id'] ?>" 
                                            class="btn btn-sm btn-outline-primary"
                                            title="Редактировать">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                        <?php else: ?>
                                            <button class="btn btn-sm btn-outline-secondary" 
                                                    disabled
                                                    title="Сначала активируйте тип блока">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                        <?php endif; ?>
                                        
                                        <a href="<?= ADMIN_URL ?>/html-blocks/delete/<?= $block['id'] ?>" 
                                        class="btn btn-sm btn-outline-danger"
                                        onclick="return confirm('Вы уверены, что хотите удалить этот блок?')"
                                        title="Удалить">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>