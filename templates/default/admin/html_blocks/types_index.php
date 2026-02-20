<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">
            <i class="bi bi-boxes me-2"></i>
            Типы контент-блоков
        </h4>
        <div>
            <a href="<?= ADMIN_URL ?>/html-blocks" class="btn btn-outline-secondary me-2">
                <i class="bi bi-arrow-left"></i> К блокам
            </a>
            <a href="<?= ADMIN_URL ?>/html-blocks/select-type" class="btn btn-primary">
                <i class="bi bi-plus-lg me-2"></i>Создать блок
            </a>
        </div>
    </div>

    <div class="alert alert-info">
        <div class="d-flex">
            <i class="bi bi-info-circle-fill me-2 mt-1"></i>
            <div>
                <strong>Управление типами блоков</strong><br>
                Здесь вы можете включать/отключать и удалять типы HTML-блоков. 
                Отключенные типы не будут отображаться при создании новых блоков, но их можно снова включить.
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <?php if(empty($blockTypes)) { ?>
                <div class="text-center py-5">
                    <div class="mb-3">
                        <i class="bi bi-boxes text-muted" style="font-size: 3rem;"></i>
                    </div>
                    <h5 class="text-muted">Типы блоков не найдены</h5>
                    <p class="text-muted">Добавьте файлы блоков в папку system/html_blocks</p>
                </div>
            <?php } else { ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Название</th>
                                <th>Системное имя</th>
                                <th>Шаблон</th>
                                <th>Автор</th>
                                <th>Версия</th>
                                <th>Статус</th>
                                <th class="text-end">Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($blockTypes as $systemName => $type) { ?>
                                <tr class="<?= (!$type['is_active'] && $systemName !== 'DefaultBlock') ? 'table-warning' : '' ?>">
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div>
                                                <strong><?php echo html($type['name']) ?></strong>
                                                <div class="text-muted small">
                                                    <?php echo html($type['description']) ?>
                                                </div>
                                                <?php if(!$type['is_visible_in_creation'] && $systemName !== 'DefaultBlock') { ?>
                                                    <div class="text-warning small mt-1">
                                                        <i class="bi bi-eye-slash"></i> Скрыт при создании блоков
                                                    </div>
                                                <?php } ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <code class="text-muted"><?php echo html($systemName) ?></code>
                                    </td>
                                    <td>
                                        <?php if(!empty($type['template']) && $type['template'] !== 'all') { ?>
                                            <span class="badge bg-info"><?php echo html($type['template']) ?></span>
                                        <?php } else { ?>
                                            <span class="badge bg-light text-dark">all</span>
                                        <?php } ?>
                                    </td>
                                    <td>
                                        <small><?php echo html($type['author'] ?? 'BloggyCMS') ?></small>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary"><?php echo html($type['version'] ?? '1.0.0') ?></span>
                                    </td>
                                    <td>
                                        <?php if($systemName === 'DefaultBlock') { ?>
                                            <span class="badge bg-success">Активен</span>
                                        <?php } else { ?>
                                            <?php if($type['is_active']) { ?>
                                                <span class="badge bg-success">Активен</span>
                                            <?php } else { ?>
                                                <span class="badge bg-warning">Отключен</span>
                                            <?php } ?>
                                        <?php } ?>
                                    </td>
                                    <td>
                                        <div class="d-flex justify-content-end gap-2">
                                            <?php if($systemName !== 'DefaultBlock') { ?>
                                                <?php $isActive = $type['is_active'] ?? true; ?>
                                                <a href="<?= ADMIN_URL ?>/html-blocks/types/toggle/<?= $systemName ?>" 
                                                class="btn btn-sm <?= $isActive ? 'btn-warning' : 'btn-success' ?>"
                                                title="<?= $isActive ? 'Отключить' : 'Включить' ?>">
                                                    <i class="bi bi-power"></i>
                                                    <?= $isActive ? '' : 'Вкл' ?>
                                                </a>
                                                <a href="<?= ADMIN_URL ?>/html-blocks/types/delete/<?= $systemName ?>" 
                                                    class="btn btn-sm btn-outline-danger"
                                                    onclick="return confirm('Внимание! Будет удален файл <?= $systemName ?>.php из папки system/html_blocks/ и запись из базы. Продолжить?')"
                                                    title="Удалить файл и запись">
                                                    <i class="bi bi-trash"></i> Удалить
                                                </a>
                                            <?php } else { ?>
                                                <span class="text-muted small">Системный</span>
                                            <?php } ?>
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