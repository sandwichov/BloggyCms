<?php
    add_admin_js('templates/default/admin/assets/js/controllers/settings.js');
    add_admin_js('templates/default/admin/assets/js/controllers/conditional-fields.js');
    add_admin_css('templates/default/admin/assets/css/controllers/settings.css');
?>

<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0"><?php echo bloggy_icon('bs', 'gear', '24', '#000', 'me-2 controller-svg'); ?> Настройки</h4>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 pb-0">
            <ul class="nav nav-tabs nav-tabs-custom">
                <li class="nav-item">
                    <a class="nav-link <?= $activeTab === 'general' ? 'active' : '' ?>" 
                       href="<?= ADMIN_URL ?>/settings?tab=general">
                       <?php echo bloggy_icon('bs', 'sliders', '14', '#000', 'me-1 controller-svg'); ?> Общее
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $activeTab === 'site' ? 'active' : '' ?>" 
                       href="<?= ADMIN_URL ?>/settings?tab=site">
                       <?php echo bloggy_icon('bs', 'globe', '14', '#000', 'me-1 controller-svg'); ?> Сайт
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= $activeTab === 'components' ? 'active' : '' ?>" 
                       href="<?= ADMIN_URL ?>/settings?tab=components">
                       <?php echo bloggy_icon('bs', 'puzzle', '14', '#000', 'me-1 controller-svg'); ?> Компоненты
                    </a>
                </li>
            </ul>
        </div>
        
        <div class="card-body">
            <?php if ($activeTab === 'general' || $activeTab === 'site') { ?>
                <form method="POST" enctype="multipart/form-data">
                    <?php
                    $tabFile = __DIR__ . '/tabs/' . $activeTab . '.php';
                    if (file_exists($tabFile)) {
                        include $tabFile;
                    } else {
                        echo '<div class="alert alert-warning">Вкладка настроек "' . html($activeTab) . '" не найдена</div>';
                    }
                    ?>
                    
                    <div class="d-flex justify-content-end mt-4">
                        <button type="submit" class="btn btn-primary">
                            <?php echo bloggy_icon('bs', 'check-lg', '20', '#fff', 'me-1'); ?> Сохранить настройки
                        </button>
                    </div>
                </form>
                
            <?php } elseif ($activeTab === 'components') { ?>
                <div class="row g-4">
                    <div class="col-md-3">
                        <div class="components-sidebar">
                            <h6 class="components-sidebar-title">Компоненты системы</h6>
                            
                            <?php $controllersWithSettings = $controllerManager->getControllersWithSettings(); ?>
                            
                            <?php if (!empty($controllersWithSettings)) { ?>
                                <div class="components-list">
                                    <?php foreach ($controllersWithSettings as $controller) { ?>
                                        <a href="<?= ADMIN_URL ?>/settings?tab=components&controller=<?= $controller['key'] ?>" 
                                        class="component-item <?= $selectedController === $controller['key'] ? 'active' : '' ?>">
                                            <div class="component-content">
                                                <div class="component-name"><?= $controller['name'] ?></div>
                                                <div class="component-meta">
                                                    <span class="component-author"><?= $controller['author'] ?></span>
                                                </div>
                                                <?php if (!empty($controller['description'])): ?>
                                                    <div class="component-description"><?= $controller['description'] ?></div>
                                                <?php endif; ?>
                                            </div>
                                        </a>
                                    <?php } ?>
                                </div>
                            <?php } else { ?>
                                <div class="components-empty">
                                    <?php echo bloggy_icon('bs', 'inboxes', '24', '#000'); ?>
                                    <p>Нет компонентов с настройками</p>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                    
                    <div class="col-md-9">
                        <?php if ($selectedController) { ?>
                            <?php 
                                $controller = $controllerManager->getController($selectedController);
                                $settingsForm = $controllerManager->getControllerSettingsForm($selectedController, $settings);
                            ?>
                            
                            <?php if ($controller && !empty($settingsForm)) { ?>
                                <form method="POST" class="component-settings" enctype="multipart/form-data">
                                    <div class="component-header">
                                        <div class="component-title-section">
                                            <h5 class="component-title"><?= $controller['name'] ?></h5>
                                            <div class="component-meta-large">
                                                <span class="component-author">Разработчик: <?= $controller['author'] ?></span>
                                                <span class="component-version">Версия <?= $controller['version'] ?></span>
                                            </div>
                                        </div>
                                        
                                        <?php if (!empty($controller['description'])) { ?>
                                            <div class="component-description-panel">
                                                <?= $controller['description'] ?>
                                            </div>
                                        <?php } ?>
                                    </div>
                                    
                                    <div class="component-settings-form">
                                        <?= $settingsForm ?>
                                    </div>
                                    
                                    <div class="component-footer">
                                        <button type="submit" class="btn btn-primary">
                                            <?php echo bloggy_icon('bs', 'check-lg', '20', '#fff', 'me-2'); ?>Сохранить настройки
                                        </button>
                                    </div>
                                </form>
                            <?php } else { ?>
                                <div class="component-not-found">
                                    <?php echo bloggy_icon('bs', 'gear', '20', '#000'); ?>
                                    <h5>Настройки не найдены</h5>
                                    <p>Для этого компонента не найдены настройки</p>
                                    <a href="<?= ADMIN_URL ?>/settings?tab=components" class="btn btn-outline-secondary">
                                        <?php echo bloggy_icon('bs', 'check-lg', '20', '#000', 'me-2'); ?>Назад к списку
                                    </a>
                                </div>
                            <?php } ?>
                            
                        <?php } else { ?>
                            <div class="component-welcome">
                                <?php echo bloggy_icon('bs', 'puzzle', '24', '#464343', 'my-3'); ?>
                                <h5>Выберите компонент</h5>
                                <p>Выберите компонент из списка слева для настройки его параметров</p>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>
</div>

<?php add_admin_js('templates/default/admin/assets/js/controllers/icon-field.js'); ?>