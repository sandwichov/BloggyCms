<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0 text-dark">
            <span class="badge bg-primary-subtle text-primary me-2 p-2">
                <?php echo bloggy_icon('bs', 'plug-fill', '16', '#0d6efd'); ?>
            </span>
            Управление плагинами
        </h4>
    </div>

    <?php if (!empty($plugins)) { ?>
        <div class="row">
            <?php foreach ($plugins as $pluginName => $pluginData) { ?>
                <?php $isActive = in_array($pluginName, $activePlugins); ?>
                <div class="col-12 col-md-6 col-xl-4 mb-4">
                    <div class="card h-100 border-0 plugin-card <?php echo $isActive ? 'active' : 'inactive'; ?>">
                        
                        <div class="card-status-bar"></div>
                        
                        <div class="card-body d-flex flex-column p-4">
                            
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <h5 class="card-title mb-0 d-flex align-items-center">
                                    <span class="plugin-icon me-2">
                                        <?php echo bloggy_icon('bs', 'puzzle-fill', '20', '#000'); ?>
                                    </span>
                                    <?php echo html($pluginData['name']); ?>
                                </h5>
                                <?php if ($isActive) { ?>
                                    <div class="status-indicator active" data-bs-toggle="tooltip" title="Активен">
                                        <?php echo bloggy_icon('bs', 'check-circle-fill', '20', '#198754'); ?>
                                    </div>
                                <?php } else { ?>
                                    <div class="status-indicator inactive" data-bs-toggle="tooltip" title="Неактивен">
                                        <?php echo bloggy_icon('bs', 'dash-circle-fill', '20', '#6c757d'); ?>
                                    </div>
                                <?php } ?>
                            </div>

                            
                            <div class="plugin-meta mb-3">
                                <span class="meta-item">
                                    <?php echo bloggy_icon('bs', 'person', '14', '#6c757d', 'me-1'); ?>
                                    <?php echo html($pluginData['author'] ?? 'Неизвестно'); ?>
                                </span>
                                <span class="meta-divider"></span>
                                <span class="meta-item">
                                    <?php echo bloggy_icon('bs', 'code-square', '14', '#6c757d', 'me-1'); ?>
                                    v<?php echo html($pluginData['version']); ?>
                                </span>
                            </div>

                            
                            <?php if (!empty($pluginData['description'])) { ?>
                                <p class="card-text text-muted flex-grow-1">
                                    <?php echo html($pluginData['description']); ?>
                                </p>
                            <?php } ?>

                            <div class="plugin-actions mt-3 pt-3">
                                <?php if ($isActive) { ?>
                                    <div class="d-flex gap-2 flex-wrap">
                                        <?php if (!empty($pluginData['has_settings'])) { ?>
                                            <a href="<?php echo ADMIN_URL; ?>/plugins/settings/<?php echo $pluginName; ?>" 
                                               class="btn btn-light btn-sm">
                                                <?php echo bloggy_icon('bs', 'sliders', '14', '#000', 'me-1'); ?>
                                                <span class="ms-1">Настройки</span>
                                            </a>
                                        <?php } ?>
                                        
                                        <div class="ms-auto">
                                            <a href="<?php echo ADMIN_URL; ?>/plugins/deactivate/<?php echo $pluginName; ?>" 
                                               class="btn btn-light btn-sm"
                                               onclick="return confirm('Вы уверены, что хотите деактивировать этот плагин?')">
                                                <?php echo bloggy_icon('bs', 'power', '14', '#000'); ?>
                                            </a>
                                            
                                            <a href="<?php echo ADMIN_URL; ?>/plugins/uninstall/<?php echo $pluginName; ?>" 
                                               class="btn btn-light btn-sm text-danger"
                                               onclick="return confirm('Вы уверены, что хотите удалить этот плагин? Все данные плагина будут удалены.')">
                                                <?php echo bloggy_icon('bs', 'trash3', '14', '#dc3545'); ?>
                                            </a>
                                        </div>
                                    </div>
                                <?php } else { ?>
                                    <a href="<?php echo ADMIN_URL; ?>/plugins/activate/<?php echo $pluginName; ?>"
                                       class="btn btn-primary btn-sm w-100">
                                        <?php echo bloggy_icon('bs', 'check-lg', '14', '#fff', 'me-1'); ?>
                                        Активировать
                                    </a>
                                <?php } ?>
                            </div>

                        </div>
                    </div>
                </div>
            <?php } ?>
        </div>
    <?php } else { ?>
        <div class="card border-0 bg-light">
            <div class="card-body text-center py-5">
                <div class="empty-state">
                    <div class="empty-state-icon mb-3">
                        <?php echo bloggy_icon('bs', 'plug', '48', '#6C6C6C'); ?>
                    </div>
                    <h5>Плагины не найдены</h5>
                    <p class="text-muted">
                        Поместите плагины в директорию system/plugins для их отображения здесь.
                    </p>
                </div>
            </div>
        </div>
    <?php } ?>
</div>