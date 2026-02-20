<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0 text-dark">
            <span class="badge bg-primary-subtle text-primary me-2 p-2">
                <i class="bi bi-plug-fill"></i>
            </span>
            Управление плагинами
        </h4>
    </div>

    <?php if (!empty($plugins)): ?>
        <div class="row">
            <?php foreach ($plugins as $pluginName => $pluginData): ?>
                <?php $isActive = in_array($pluginName, $activePlugins); ?>
                <div class="col-12 col-md-6 col-xl-4 mb-4">
                    <div class="card h-100 border-0 plugin-card <?= $isActive ? 'active' : 'inactive' ?>">
                        
                        <div class="card-status-bar"></div>
                        
                        <div class="card-body d-flex flex-column p-4">
                            
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <h5 class="card-title mb-0 d-flex align-items-center">
                                    <span class="plugin-icon me-2">
                                        <i class="bi bi-puzzle-fill"></i>
                                    </span>
                                    <?= html($pluginData['name']) ?>
                                </h5>
                                <?php if ($isActive): ?>
                                    <div class="status-indicator active" data-bs-toggle="tooltip" title="Активен">
                                        <i class="bi bi-check-circle-fill"></i>
                                    </div>
                                <?php else: ?>
                                    <div class="status-indicator inactive" data-bs-toggle="tooltip" title="Неактивен">
                                        <i class="bi bi-dash-circle-fill"></i>
                                    </div>
                                <?php endif; ?>
                            </div>

                            
                            <div class="plugin-meta mb-3">
                                <span class="meta-item">
                                    <i class="bi bi-person"></i>
                                    <?= html($pluginData['author'] ?? 'Неизвестно') ?>
                                </span>
                                <span class="meta-divider"></span>
                                <span class="meta-item">
                                    <i class="bi bi-code-square"></i>
                                    v<?= html($pluginData['version']) ?>
                                </span>
                            </div>

                            
                            <?php if (!empty($pluginData['description'])): ?>
                                <p class="card-text text-muted flex-grow-1">
                                    <?= html($pluginData['description']) ?>
                                </p>
                            <?php endif; ?>

                            <div class="plugin-actions mt-3 pt-3">
                                <?php if ($isActive): ?>
                                    <div class="d-flex gap-2 flex-wrap">
                                        <?php if (!empty($pluginData['has_settings'])): ?>
                                            <a href="<?= ADMIN_URL ?>/plugins/settings/<?= $pluginName ?>" 
                                            class="btn btn-light btn-sm">
                                                <i class="bi bi-sliders"></i>
                                                <span class="ms-1">Настройки</span>
                                            </a>
                                        <?php endif; ?>
                                        
                                        <div class="ms-auto">
                                            <a href="<?= ADMIN_URL ?>/plugins/deactivate/<?= $pluginName ?>" 
                                            class="btn btn-light btn-sm"
                                            onclick="return confirm('Вы уверены, что хотите деактивировать этот плагин?')">
                                                <i class="bi bi-power"></i>
                                            </a>
                                            
                                            <a href="<?= ADMIN_URL ?>/plugins/uninstall/<?= $pluginName ?>" 
                                            class="btn btn-light btn-sm text-danger"
                                            onclick="return confirm('Вы уверены, что хотите удалить этот плагин? Все данные плагина будут удалены.')">
                                                <i class="bi bi-trash3"></i>
                                            </a>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <a href="<?= ADMIN_URL ?>/plugins/activate/<?= $pluginName ?>"
                                    class="btn btn-primary btn-sm w-100">
                                        <i class="bi bi-check-lg me-1"></i>
                                        Активировать
                                    </a>
                                <?php endif; ?>
                            </div>

                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="card border-0 bg-light">
            <div class="card-body text-center py-5">
                <div class="empty-state">
                    <div class="empty-state-icon mb-3">
                        <i class="bi bi-plug"></i>
                    </div>
                    <h5>Плагины не найдены</h5>
                    <p class="text-muted">
                        Поместите плагины в директорию system/plugins для их отображения здесь.
                    </p>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
