<?php
    add_admin_js('templates/default/admin/assets/js/controllers/menu-builder.js');
    add_admin_js('templates/default/admin/assets/js/controllers/menu-icons.js');
?>

<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">
            <?php echo bloggy_icon('bs', isset($menu['id']) ? 'pencil' : 'plus-circle', '24', '#000', 'me-2'); ?>
            <?php echo isset($menu['id']) ? 'Редактирование меню' : 'Создание меню'; ?>
        </h4>
        <a href="<?php echo ADMIN_URL; ?>/menu" class="btn btn-outline-secondary">
            <?php echo bloggy_icon('bs', 'arrow-left', '16', '#000', 'me-2'); ?>
            Назад к списку
        </a>
    </div>

    <div class="alert alert-info mb-4">
        <div class="d-flex align-items-center">
            <?php echo bloggy_icon('bs', 'info-circle', '16', '#000', 'me-2'); ?>
            <div>
                <strong>Текущая тема:</strong> <?php echo html($currentTheme); ?>
                <div class="small">Шаблоны меню загружаются из: <code>templates/<?php echo html($currentTheme); ?>/front/assets/menu/</code></div>
            </div>
        </div>
    </div>

    <?php if (empty($availableTemplates)) { ?>
    <div class="alert alert-warning">
        <div class="d-flex align-items-center">
            <?php echo bloggy_icon('bs', 'exclamation-triangle', '16', '#000', 'me-2'); ?>
            <div>
                <strong>Шаблоны меню не найдены!</strong>
                <div class="small">
                    Создайте PHP файлы в директории: <code>templates/<?php echo html($currentTheme); ?>/front/assets/menu/</code>
                    <br>Например: <code>main.php</code>, <code>footer.php</code>
                </div>
            </div>
        </div>
    </div>
    <?php } ?>

    <form method="POST" id="menu-form">
        <div class="row">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Структура меню</h5>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-outline-primary btn-sm" id="add-menu-item" data-bs-toggle="modal" data-bs-target="#menuItemModal">
                                <?php echo bloggy_icon('bs', 'plus-circle', '16', '#000', 'me-1'); ?>
                                Добавить пункт
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" id="expand-all">
                                <?php echo bloggy_icon('bs', 'arrows-expand', '16', '#000', 'me-1'); ?>
                                Развернуть все
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" id="collapse-all">
                                <?php echo bloggy_icon('bs', 'arrows-collapse', '16', '#000', 'me-1'); ?>
                                Свернуть все
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="menu-builder">
                            <div class="mb-3">
                                <div class="alert alert-light border">
                                    <div class="d-flex align-items-center">
                                        <?php echo bloggy_icon('bs', 'lightbulb', '16', '#ffc107', 'me-2'); ?>
                                        <small>Нажмите на пункт меню для редактирования. Перетаскивайте для изменения порядка.</small>
                                    </div>
                                </div>
                            </div>
                            
                            <div id="menu-items-container" class="sortable-menu menu-tree">
                                <?php if (!empty($menuStructure)) { ?>
                                    <?php 
                                    function renderMenuItem($item, $index, $level = 0) {
                                        $title = html($item['title'] ?? '');
                                        $url = html($item['url'] ?? '');
                                        $class = html($item['class'] ?? '');
                                        $target = $item['target'] ?? '_self';
                                        $children = $item['children'] ?? array();
                                        $hasChildren = !empty($children);
                                        $levelClass = 'level-' . min($level, 4);
                                        $itemData = html(json_encode(array(
                                            'title' => $item['title'] ?? '',
                                            'url' => $item['url'] ?? '',
                                            'class' => $item['class'] ?? '',
                                            'target' => $item['target'] ?? '_self'
                                        )));
                                        ?>
                                        <div class="menu-item-card card mb-2 <?php echo $levelClass; ?>" 
                                             data-index="<?php echo $index; ?>" 
                                             data-level="<?php echo $level; ?>"
                                             data-item="<?php echo $itemData; ?>">
                                            <div class="card-body p-3">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div class="d-flex align-items-center flex-grow-1">
                                                        <div class="menu-level-indicator me-3">
                                                            <?php for ($i = 0; $i < $level; $i++) { ?>
                                                                <span class="level-line"></span>
                                                            <?php } ?>
                                                            <span class="level-dot"></span>
                                                        </div>
                                                        <div class="flex-grow-1">
                                                            <div class="d-flex align-items-center">
                                                                <?php if ($hasChildren) { ?>
                                                                    <?php echo bloggy_icon('bs', 'folder-fill', '16', '#ffc107', 'me-2'); ?>
                                                                <?php } else { ?>
                                                                    <?php echo bloggy_icon('bs', 'link-45deg', '16', '#0d6efd', 'me-2'); ?>
                                                                <?php } ?>
                                                                <div>
                                                                    <h6 class="mb-1"><?php echo !empty($title) ? $title : 'Без названия'; ?></h6>
                                                                    <small class="text-muted"><?php echo $url; ?></small>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="btn-group btn-group-sm">
                                                        <button type="button" class="btn btn-outline-secondary menu-item-handle" title="Перетащить">
                                                            <?php echo bloggy_icon('bs', 'arrows-move', '16', '#000'); ?>
                                                        </button>
                                                        <button type="button" class="btn btn-outline-primary edit-menu-item" 
                                                                title="Редактировать"
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#menuItemModal">
                                                            <?php echo bloggy_icon('bs', 'pencil', '16', '#000'); ?>
                                                        </button>
                                                        <button type="button" class="btn btn-outline-success add-child-item" 
                                                                title="Добавить подпункт"
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#menuItemModal"
                                                                data-parent-index="<?php echo $index; ?>">
                                                            <?php echo bloggy_icon('bs', 'patch-plus', '16', '#000'); ?>
                                                        </button>
                                                        <button type="button" class="btn btn-outline-danger remove-menu-item" title="Удалить">
                                                            <?php echo bloggy_icon('bs', 'trash', '16', '#000'); ?>
                                                        </button>
                                                    </div>
                                                </div>
                                                
                                                <?php if ($hasChildren) { ?>
                                                    <div class="menu-children-container mt-3">
                                                        <div class="border-top pt-3">
                                                            <div class="menu-children sortable-menu">
                                                                <?php foreach ($children as $childIndex => $child) { ?>
                                                                    <?php renderMenuItem($child, $index . '_' . $childIndex, $level + 1); ?>
                                                                <?php } ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php } ?>
                                            </div>
                                        </div>
                                        <?php
                                    }
                                    
                                    foreach ($menuStructure as $index => $item) {
                                        renderMenuItem($item, $index, 0);
                                    }
                                    ?>
                                <?php } ?>
                            </div>
                            
                            <div id="menu-empty" class="text-center text-muted p-5 <?php echo !empty($menuStructure) ? 'd-none' : ''; ?>">
                                <div class="mb-3">
                                    <?php echo bloggy_icon('bs', 'list-ul', '48', '#6C6C6C'); ?>
                                </div>
                                <h5 class="text-muted">Меню пустое</h5>
                                <p class="text-muted mb-3">Добавьте первый пункт меню чтобы начать работу</p>
                                <button type="button" class="btn btn-primary" id="add-first-item" data-bs-toggle="modal" data-bs-target="#menuItemModal">
                                    <?php echo bloggy_icon('bs', 'plus-circle', '16', '#fff', 'me-1'); ?>
                                    Добавить первый пункт
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0">
                        <h5 class="card-title mb-0">
                            <?php echo bloggy_icon('bs', 'gear', '20', '#000', 'me-2'); ?>
                            Настройки меню
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">
                                <?php echo bloggy_icon('bs', 'tag', '16', '#000', 'me-1'); ?>
                                Название меню
                                <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control" 
                                   name="name" 
                                   value="<?php echo html($menu['name'] ?? ''); ?>" 
                                   required
                                   placeholder="Например: Главное меню">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">
                                <?php echo bloggy_icon('bs', 'layout-wtf', '16', '#000', 'me-1'); ?>
                                Шаблон меню
                                <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" name="template" required <?php echo empty($availableTemplates) ? 'disabled' : ''; ?>>
                                <option value="">Выберите шаблон</option>
                                <?php foreach ($availableTemplates as $templateKey => $templateName) { ?>
                                    <option value="<?php echo $templateKey; ?>" 
                                        <?php echo ($menu['template'] ?? '') === $templateKey ? 'selected' : ''; ?>>
                                        <?php echo html($templateName); ?>
                                    </option>
                                <?php } ?>
                            </select>
                            <div class="form-text">
                                <?php echo bloggy_icon('bs', 'folder', '16', '#000', 'me-1'); ?>
                                Путь: <code>templates/<?php echo html($currentTheme); ?>/front/assets/menu/</code>
                            </div>
                            <?php if (empty($availableTemplates)) { ?>
                            <div class="alert alert-warning mt-2 p-2 small">
                                <?php echo bloggy_icon('bs', 'exclamation-triangle', '16', '#000', 'me-1'); ?>
                                Нет доступных шаблонов. Создайте файлы в указанной директории.
                            </div>
                            <?php } ?>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">
                                <?php echo bloggy_icon('bs', 'power', '16', '#000', 'me-1'); ?>
                                Статус
                            </label>
                            <select class="form-select" name="status">
                                <option value="active" <?php echo ($menu['status'] ?? 'active') === 'active' ? 'selected' : ''; ?>>Активно</option>
                                <option value="inactive" <?php echo ($menu['status'] ?? '') === 'inactive' ? 'selected' : ''; ?>>Неактивно</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary" <?php echo empty($availableTemplates) ? 'disabled' : ''; ?>>
                                <?php echo bloggy_icon('bs', 'check-lg', '20', '#fff', 'me-2'); ?>
                                <?php echo isset($menu['id']) ? 'Обновить меню' : 'Создать меню'; ?>
                            </button>
                            
                            <?php if (isset($menu['id'])) { ?>
                            <a href="<?php echo ADMIN_URL; ?>/menu/preview/<?php echo $menu['id']; ?>" 
                               class="btn btn-outline-secondary">
                                <?php echo bloggy_icon('bs', 'eye', '16', '#000', 'me-2'); ?>
                                Предпросмотр
                            </a>
                            <?php } ?>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mt-3">
                    <div class="card-header bg-white border-0">
                        <h6 class="card-title mb-0">
                            <?php echo bloggy_icon('bs', 'graph-up', '16', '#000', 'me-2'); ?>
                            Статистика
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-6">
                                <div class="border-end">
                                    <div class="h4 mb-0" id="total-items">0</div>
                                    <small class="text-muted">Всего пунктов</small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="h4 mb-0" id="nested-items">0</div>
                                <small class="text-muted">Вложенных</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <input type="hidden" name="menu_structure" id="menu-structure" value='<?php echo json_encode($menuStructure ?? array(), JSON_UNESCAPED_UNICODE); ?>'>
    </form>
</div>

<div class="modal fade" id="menuItemModal" tabindex="-1" aria-labelledby="menuItemModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="menuItemModalLabel">
                    <?php echo bloggy_icon('bs', 'plus-circle', '20', '#000', 'me-2'); ?>
                    Добавить пункт меню
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="menu-item-form">
                    <input type="hidden" id="edit-item-index">
                    <input type="hidden" id="parent-item-index">
                    
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label">
                                <?php echo bloggy_icon('bs', 'fonts', '16', '#000', 'me-1'); ?>
                                Название пункта
                                <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control" 
                                   id="item-title" 
                                   placeholder="Введите название пункта меню" 
                                   maxlength="100"
                                   required>
                            <div class="form-text">Отображаемое название в меню</div>
                        </div>
                        
                        <div class="col-md-12">
                            <label class="form-label">
                                <?php echo bloggy_icon('bs', 'link', '16', '#000', 'me-1'); ?>
                                URL адрес
                                <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <input type="text" 
                                       class="form-control" 
                                       id="item-url" 
                                       placeholder="/page или http://..." 
                                       maxlength="255"
                                       required>
                            </div>
                            <div class="form-text">Ссылка, на которую ведет пункт меню</div>
                        </div>

                        <div class="mb-2">
    <div class="d-flex justify-content-between align-items-center mb-1">
        <label class="form-label mb-0">
            <?php echo bloggy_icon('bs', 'code-slash', '16', '#000', 'me-1'); ?>
            Доступные шорткоды:
        </label>
        <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none" 
                data-bs-toggle="collapse" data-bs-target="#shortcodeHelp">
            <?php echo bloggy_icon('bs', 'info-circle', '16', '#000', 'me-1'); ?>
            Подробнее
        </button>
    </div>
    
    <div class="d-flex flex-wrap gap-1 mb-2" id="shortcode-buttons">
        <button type="button" class="btn btn-outline-secondary btn-sm shortcode-btn" 
                data-shortcode="{username}">
            {username}
        </button>
        <button type="button" class="btn btn-outline-secondary btn-sm shortcode-btn" 
                data-shortcode="{user_id}">
            {user_id}
        </button>
        <button type="button" class="btn btn-outline-secondary btn-sm shortcode-btn" 
                data-shortcode="{email}">
            {email}
        </button>
        <button type="button" class="btn btn-outline-secondary btn-sm shortcode-btn" 
                data-shortcode="{base_url}">
            {base_url}
        </button>
    </div>
    
    <div class="collapse" id="shortcodeHelp">
        <div class="card card-body bg-light p-2 small">
            <table class="table table-sm table-borderless mb-0">
                <tr>
                    <td class="p-1"><code>{username}</code></td>
                    <td class="p-1">Логин текущего пользователя</td>
                </tr>
                <tr>
                    <td class="p-1"><code>{user_id}</code></td>
                    <td class="p-1">ID текущего пользователя</td>
                </tr>
                <tr>
                    <td class="p-1"><code>{email}</code></td>
                    <td class="p-1">Email пользователя</td>
                </tr>
                <tr>
                    <td class="p-1"><code>{first_name}</code></td>
                    <td class="p-1">Имя пользователя</td>
                </tr>
                <tr>
                    <td class="p-1"><code>{last_name}</code></td>
                    <td class="p-1">Фамилия пользователя</td>
                </tr>
                <tr>
                    <td class="p-1"><code>{display_name}</code></td>
                    <td class="p-1">Отображаемое имя</td>
                </tr>
                <tr>
                    <td class="p-1"><code>{slug}</code></td>
                    <td class="p-1">URL-слаг пользователя</td>
                </tr>
                <tr>
                    <td class="p-1"><code>{base_url}</code></td>
                    <td class="p-1">Базовый URL сайта</td>
                </tr>
                <tr>
                    <td class="p-1"><code>{admin_url}</code></td>
                    <td class="p-1">URL админ-панели</td>
                </tr>
                <tr>
                    <td class="p-1"><code>{user_field:поле}</code></td>
                    <td class="p-1">Любое поле из таблицы пользователей</td>
                </tr>
            </table>
        </div>
    </div>
    
    <div class="shortcode-preview small text-muted mt-2" id="shortcode-preview" style="display: none;">
        <?php echo bloggy_icon('bs', 'eye', '16', '#000', 'me-1'); ?>
        Превью: <span id="preview-text"></span>
    </div>
</div>
                        
                        <div class="col-md-6">
                            <label class="form-label">
                                <?php echo bloggy_icon('bs', 'box-arrow-up-right', '16', '#000', 'me-1'); ?>
                                Открывать в
                            </label>
                            <select class="form-select" id="item-target">
                                <option value="_self">Текущем окне</option>
                                <option value="_blank">Новом окне</option>
                            </select>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">
                                <?php echo bloggy_icon('bs', 'code-slash', '16', '#000', 'me-1'); ?>
                                CSS классы
                            </label>
                            <input type="text" 
                                   class="form-control" 
                                   id="item-class" 
                                   placeholder="my-class another-class" 
                                   maxlength="50">
                            <div class="form-text">Дополнительные CSS классы</div>
                        </div>

                        <div class="border-top pt-3 mt-3">
                            <h6 class="text-muted mb-3">
                                <?php echo bloggy_icon('bs', 'image', '16', '#000', 'me-1'); ?>
                                Настройки иконки
                            </h6>
                            
                            <div class="row">
                                <div class="col-md-12">
                                    <label class="form-label">
                                        <?php echo bloggy_icon('bs', 'palette', '16', '#000', 'me-1'); ?>
                                        Иконка
                                    </label>
                                    <div class="input-group mb-2">
                                        <input type="text" 
                                            class="form-control" 
                                            id="item-icon-id" 
                                            placeholder="Идентификатор иконки"
                                            readonly>
                                        <button type="button" 
                                                class="btn btn-outline-primary" 
                                                id="select-icon-btn"
                                                onclick="window.menuIconManager.openIconSelector()">
                                            <?php echo bloggy_icon('bs', 'images', '16', '#000', 'me-1'); ?>
                                            Выбрать иконку
                                        </button>
                                        <button type="button" 
                                                class="btn btn-outline-danger" 
                                                id="clear-icon-btn">
                                            <?php echo bloggy_icon('bs', 'x-circle', '16', '#000'); ?>
                                        </button>
                                    </div>
                                    
                                    <div id="icon-preview" class="text-center mb-3" style="display: none;">
                                        <div id="selected-icon-preview" style="font-size: 48px;"></div>
                                        <small class="text-muted" id="icon-name"></small>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <label class="form-label">
                                        <?php echo bloggy_icon('bs', 'rulers', '16', '#000', 'me-1'); ?>
                                        Размер (px)
                                    </label>
                                    <input type="number" 
                                        class="form-control" 
                                        id="item-icon-size" 
                                        placeholder="16"
                                        min="8"
                                        max="128">
                                </div>
                                
                                <div class="col-md-6">
                                    <label class="form-label">
                                        <?php echo bloggy_icon('bs', 'palette', '16', '#000', 'me-1'); ?>
                                        Цвет
                                    </label>
                                    <input type="color" 
                                        class="form-control form-control-color" 
                                        id="item-icon-color" 
                                        value="#000000"
                                        title="Выберите цвет иконки">
                                </div>
                            </div>
                            
                            <div class="row mt-2">
                                <div class="col-md-12">
                                    <div class="form-check">
                                        <input class="form-check-input" 
                                            type="checkbox" 
                                            id="item-icon-only">
                                        <label class="form-check-label" for="item-icon-only">
                                            <?php echo bloggy_icon('bs', 'fonts', '16', '#000', 'me-1'); ?>
                                            Отображать только иконку (без текста)
                                        </label>
                                        <div class="form-text small">
                                            Если включено, в меню будет отображаться только иконка
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="border-top pt-3 mt-3">
                        <h6 class="text-muted mb-3">
                            <?php echo bloggy_icon('bs', 'shield-lock', '16', '#000', 'me-1'); ?>
                            Настройки видимости
                        </h6>
    
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label small">
                                    <?php echo bloggy_icon('bs', 'eye', '16', '#000', 'me-1'); ?>
                                    Показывать группам
                                </label>
                                <select class="form-select form-select-sm" id="item-show-to" multiple size="4">
                                    <option value="">Все группы (если не выбрано)</option>
                                    <?php 
                                    $groups = $this->getUserGroups();
                                    foreach ($groups as $group) { 
                                    ?>
                                        <option value="<?php echo $group['id']; ?>">
                                            <?php echo html($group['name']); ?>
                                        </option>
                                    <?php } ?>
                                </select>
                                <div class="form-text small">Оставьте пустым чтобы показывать всем</div>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label small">
                                    <?php echo bloggy_icon('bs', 'eye-slash', '16', '#000', 'me-1'); ?>
                                    Не показывать группам
                                </label>
                                <select class="form-select form-select-sm" id="item-hide-from" multiple size="4">
                                    <option value="">Никому не скрывать</option>
                                    <?php foreach ($groups as $group) { ?>
                                        <option value="<?php echo $group['id']; ?>">
                                            <?php echo html($group['name']); ?>
                                        </option>
                                    <?php } ?>
                                </select>
                                <div class="form-text small">Выберите группы которым скрыть этот пункт</div>
                            </div>
                        </div>
    
                        <div class="alert alert-info mt-2 p-2 small">
                            <?php echo bloggy_icon('bs', 'info-circle', '16', '#000', 'me-1'); ?>
                            <strong>Приоритет:</strong> Сначала проверяется "Показывать группам", затем "Не показывать группам"
                        </div>
                    </div>

                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <?php echo bloggy_icon('bs', 'x-circle', '16', '#000', 'me-1'); ?>
                    Отмена
                </button>
                <button type="button" class="btn btn-primary" id="save-menu-item">
                    <?php echo bloggy_icon('bs', 'check-lg', '16', '#fff', 'me-1'); ?>
                    Сохранить пункт
                </button>
            </div>
        </div>
    </div>
</div>

<div id="iconSelectorModal" class="custom-modal" style="display: none;">
    <div class="custom-modal-dialog">
        <div class="custom-modal-content">
            <div class="custom-modal-header">
                <h5 class="custom-modal-title">
                    <?php echo bloggy_icon('bs', 'images', '20', '#000', 'me-2'); ?>
                    Выбор иконки
                </h5>
                <button type="button" class="custom-modal-close" onclick="window.menuIconManager.closeIconSelector()">
                    <span>&times;</span>
                </button>
            </div>
            <div class="custom-modal-body">
                <div class="mb-3">
                    <div class="input-group">
                        <span class="input-group-text border-0 bg-light">
                            <?php echo bloggy_icon('bs', 'search', '16', '#000'); ?>
                        </span>
                        <input type="text" 
                            id="iconSearchModal" 
                            class="form-control border-0 bg-light" 
                            placeholder="Поиск иконок..."
                            autocomplete="off"
                            autocorrect="off"
                            autocapitalize="none"
                            spellcheck="false"
                            tabindex="0">
                    </div>
                </div>
                
                <div class="icon-selector-container">
                    <ul class="nav nav-tabs" id="iconSelectorTabs" role="tablist"></ul>
                    
                    <div class="tab-content pt-3" id="iconSelectorTabsContent"></div>
                </div>
            </div>
            <div class="custom-modal-footer">
                <button type="button" class="btn btn-outline-secondary" onclick="window.menuIconManager.closeIconSelector()">
                    <?php echo bloggy_icon('bs', 'x-circle', '16', '#000', 'me-1'); ?>
                    Отмена
                </button>
                <button type="button" class="btn btn-primary" onclick="window.menuIconManager.confirmIconSelection()">
                    <?php echo bloggy_icon('bs', 'check-lg', '16', '#fff', 'me-1'); ?>
                    Выбрать
                </button>
            </div>
        </div>
    </div>
</div>

<div id="iconSelectorOverlay" class="custom-modal-overlay" style="display: none;"></div>