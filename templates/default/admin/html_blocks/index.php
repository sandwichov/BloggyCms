<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">
            <?php echo bloggy_icon('bs', 'code-square', '24', '#000', 'me-2'); ?>
            Контент-блоки
        </h4>
        <div>
            <a href="<?php echo ADMIN_URL; ?>/html-blocks/types" class="btn btn-outline-secondary me-2">
                <?php echo bloggy_icon('bs', 'boxes', '16', '#000', 'me-2'); ?>
                Типы блоков
            </a>
            <a href="<?php echo ADMIN_URL; ?>/html-blocks/select-type" class="btn btn-primary">
                <?php echo bloggy_icon('bs', 'plus-lg', '16', '#fff', 'me-2'); ?>
                Создать блок
            </a>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <?php if (empty($blocks)) { ?>
                <div class="text-center py-5">
                    <div class="mb-3">
                        <?php echo bloggy_icon('bs', 'code-square', '48', '#6C6C6C'); ?>
                    </div>
                    <h5 class="text-muted">Контент-блоки пока не созданы</h5>
                    <p class="text-muted">Создайте первый контент-блок для вашего сайта</p>
                    <a href="<?php echo ADMIN_URL; ?>/html-blocks/select-type" class="btn btn-primary">
                        <?php echo bloggy_icon('bs', 'plus-lg', '16', '#fff', 'me-2'); ?>
                        Создать блок
                    </a>
                </div>
            <?php } else { ?>
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
                            <?php foreach ($blocks as $block) { 
                                $typeIsActive = $block['type_is_active'] ?? true;
                            ?>
                            <tr class="<?php echo (!$typeIsActive) ? 'table-warning' : ''; ?>">
                                <td>
                                    <strong><?php echo html($block['name']); ?></strong>
                                    <?php if (!$typeIsActive) { ?>
                                    <div class="text-warning small mt-1">
                                        <?php echo bloggy_icon('bs', 'exclamation-triangle', '16', '#ffc107', 'me-1'); ?>
                                        Тип блока отключен
                                    </div>
                                    <?php } ?>
                                </td>
                                <td>
                                    <span class="badge bg-secondary"><?php echo html($block['type_name'] ?? 'Дефолтный'); ?></span>
                                </td>
                                <td>
                                    <?php if (!empty($block['template']) && $block['template'] !== 'all') { ?>
                                    <span class="badge bg-info"><?php echo html($block['template']); ?></span>
                                    <?php } else { ?>
                                    <span class="badge bg-light text-dark">all</span>
                                    <?php } ?>
                                </td>
                                <td>
                                    <code class="text-muted"><?php echo html($block['slug']); ?></code>
                                </td>
                                <td>
                                    <?php if ($typeIsActive) { ?>
                                        <span class="badge bg-success">Активен</span>
                                    <?php } else { ?>
                                        <span class="badge bg-warning">Тип отключен</span>
                                    <?php } ?>
                                </td>
                                <td>
                                    <div class="d-flex justify-content-end gap-2">
                                        <?php if ($typeIsActive) { ?>
                                            <a href="<?php echo ADMIN_URL; ?>/html-blocks/edit/<?php echo $block['id']; ?>" 
                                               class="btn btn-sm btn-outline-primary"
                                               title="Редактировать">
                                                <?php echo bloggy_icon('bs', 'pencil', '16', '#000'); ?>
                                            </a>
                                        <?php } else { ?>
                                            <button class="btn btn-sm btn-outline-secondary" 
                                                    disabled
                                                    title="Сначала активируйте тип блока">
                                                <?php echo bloggy_icon('bs', 'pencil', '16', '#6c757d'); ?>
                                            </button>
                                        <?php } ?>
                                        
                                        <a href="<?php echo ADMIN_URL; ?>/html-blocks/delete/<?php echo $block['id']; ?>" 
                                           class="btn btn-sm btn-outline-danger"
                                           onclick="return confirm('Вы уверены, что хотите удалить этот блок?')"
                                           title="Удалить">
                                            <?php echo bloggy_icon('bs', 'trash', '16', '#000'); ?>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            <?php } ?>
        </div>
    </div>
</div>