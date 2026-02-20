<?php
class QuickActionsHelper {
    /**
     * Проверяет, есть ли активные быстрые действия
     */
    public static function hasQuickActions() {
        $actions = [
            'add_post',
            'add_page', 
            'add_category',
            'add_tag',
            'add_user',
            'add_content_block',
            'add_field',
            'add_form'
        ];
        
        foreach ($actions as $action) {
            if (SettingsHelper::get('controller_admin', $action, false)) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Получает классы позиции для кнопки
     */
    private static function getButtonPositionClass() {
        $position = SettingsHelper::get('controller_admin', 'position_btn', 'bottom-right');
        
        switch ($position) {
            case 'bottom-right-center':
                return 'bottom-0 start-50 translate-middle-x p-4';
            case 'bottom-right':
            default:
                return 'bottom-0 end-0 p-4';
        }
    }
    
    /**
     * Получает HTML для плавающей кнопки и модального окна
     */
    public static function renderQuickActions() {
        if (!self::hasQuickActions()) {
            return '';
        }
        
        $positionClass = self::getButtonPositionClass();
        
        ob_start();
        ?>
        <div class="position-fixed <?= $positionClass ?>" style="z-index: 1050;">
            <button type="button" 
                    class="btn btn-<?php echo SettingsHelper::get('controller_admin', 'color_btn', 'primary'); ?> btn-lg rounded-pill shadow-lg pulse-animation"
                    data-bs-toggle="modal" 
                    data-bs-target="#quickActionsModal"
                    style="width: 60px; height: 60px;">
                <?php echo bloggy_icon('bs', 'lightning-charge-fill', '24', 'white'); ?>
            </button>
        </div>

        <div class="modal fade" id="quickActionsModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title">
                            <?php echo bloggy_icon('bs', 'lightning-charge-fill', '20', '#0d6efd', 'me-2'); ?>
                            Быстрые действия
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <?php if(SettingsHelper::get('controller_admin', 'add_post') == true): ?>
                            <div class="col-6">
                                <a href="<?= ADMIN_URL ?>/posts/create" class="btn btn-outline-secondary w-100 h-100 p-3 text-start quick-action-btn">
                                    <div class="d-flex align-items-center">
                                        <div class="p-2 rounded me-3">
                                            <?php echo bloggy_icon('bs', 'file-earmark-plus', '32', '#0d6efd'); ?>
                                        </div>
                                        <div>
                                            <div class="fw-semibold">Создать пост</div>
                                            <small class="text-muted">Новая статья</small>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            <?php endif; ?>

                            <?php if(SettingsHelper::get('controller_admin', 'add_page') == true): ?>
                            <div class="col-6">
                                <a href="<?= ADMIN_URL ?>/pages/create" class="btn btn-outline-secondary w-100 h-100 p-3 text-start quick-action-btn">
                                    <div class="d-flex align-items-center">
                                        <div class="p-2 rounded me-3">
                                            <?php echo bloggy_icon('bs', 'file-text', '32', '#6c757d'); ?>
                                        </div>
                                        <div>
                                            <div class="fw-semibold">Создать страницу</div>
                                            <small class="text-muted">Статичная страница</small>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            <?php endif; ?>

                            <?php if(SettingsHelper::get('controller_admin', 'add_category') == true): ?>
                            <div class="col-6">
                                <a href="<?= ADMIN_URL ?>/categories/create" class="btn btn-outline-secondary w-100 h-100 p-3 text-start quick-action-btn">
                                    <div class="d-flex align-items-center">
                                        <div class="p-2 rounded me-3">
                                            <?php echo bloggy_icon('bs', 'folder-plus', '32', '#198754'); ?>
                                        </div>
                                        <div>
                                            <div class="fw-semibold">Создать категорию</div>
                                            <small class="text-muted">Группировка постов</small>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            <?php endif; ?>

                            <?php if(SettingsHelper::get('controller_admin', 'add_tag') == true): ?>
                            <div class="col-6">
                                <a href="<?= ADMIN_URL ?>/tags/create" class="btn btn-outline-secondary w-100 h-100 p-3 text-start quick-action-btn">
                                    <div class="d-flex align-items-center">
                                        <div class="p-2 rounded me-3">
                                            <?php echo bloggy_icon('bs', 'tag', '32', '#0dcaf0'); ?>
                                        </div>
                                        <div>
                                            <div class="fw-semibold">Создать тег</div>
                                            <small class="text-muted">Метка для постов</small>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            <?php endif; ?>

                            <?php if(SettingsHelper::get('controller_admin', 'add_user') == true): ?>
                            <div class="col-6">
                                <a href="<?= ADMIN_URL ?>/users/create" class="btn btn-outline-secondary w-100 h-100 p-3 text-start quick-action-btn">
                                    <div class="d-flex align-items-center">
                                        <div class="p-2 rounded me-3">
                                            <?php echo bloggy_icon('bs', 'person-plus', '32', '#ffc107'); ?>
                                        </div>
                                        <div>
                                            <div class="fw-semibold">Создать пользователя</div>
                                            <small class="text-muted">Новый аккаунт</small>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            <?php endif; ?>

                            <?php if(SettingsHelper::get('controller_admin', 'add_content_block') == true): ?>
                            <div class="col-6">
                                <a href="<?= ADMIN_URL ?>/html-blocks/create" class="btn btn-outline-secondary w-100 h-100 p-3 text-start quick-action-btn">
                                    <div class="d-flex align-items-center">
                                        <div class="p-2 rounded me-3">
                                            <?php echo bloggy_icon('bs', 'box', '32', '#dc3545'); ?>
                                        </div>
                                        <div>
                                            <div class="fw-semibold">Создать контент-блок</div>
                                            <small class="text-muted">HTML блок</small>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            <?php endif; ?>

                            <?php if(SettingsHelper::get('controller_admin', 'add_form') == true): ?>
                            <div class="col-6">
                                <a href="<?= ADMIN_URL ?>/forms/create" class="btn btn-outline-secondary w-100 h-100 p-3 text-start quick-action-btn">
                                    <div class="d-flex align-items-center">
                                        <div class="p-2 rounded me-3">
                                            <?php echo bloggy_icon('bs', 'mailbox', '32', '#b07a1dff'); ?>
                                        </div>
                                        <div>
                                            <div class="fw-semibold">Создать форму</div>
                                            <small class="text-muted">С различными полями</small>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            <?php endif; ?>

                            <?php if(SettingsHelper::get('controller_admin', 'add_field') == true): ?>
                            <div class="col-6">
                                <a href="<?= ADMIN_URL ?>/forms/create" class="btn btn-outline-secondary w-100 h-100 p-3 text-start quick-action-btn">
                                    <div class="d-flex align-items-center">
                                        <div class="p-2 rounded me-3">
                                            <?php echo bloggy_icon('bs', 'input-cursor-text', '32', '#2148d5ff'); ?>
                                        </div>
                                        <div>
                                            <div class="fw-semibold">Создать поле</div>
                                            <small class="text-muted">Для любого контроллера</small>
                                        </div>
                                    </div>
                                </a>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}