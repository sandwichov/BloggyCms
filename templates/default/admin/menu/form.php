<?php
    add_admin_js('templates/default/admin/assets/js/controllers/menu-builder.js');
    add_admin_js('templates/default/admin/assets/js/controllers/menu-icons.js');
?>

<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">
            <i class="bi bi-<?= isset($menu['id']) ? 'pencil' : 'plus-circle' ?> me-2"></i>
            <?= isset($menu['id']) ? 'Редактирование меню' : 'Создание меню' ?>
        </h4>
        <a href="<?= ADMIN_URL ?>/menu" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Назад к списку
        </a>
    </div>

    <div class="alert alert-info mb-4">
        <div class="d-flex align-items-center">
            <i class="bi bi-info-circle me-2"></i>
            <div>
                <strong>Текущая тема:</strong> <?= html($currentTheme) ?>
                <div class="small">Шаблоны меню загружаются из: <code>templates/<?= html($currentTheme) ?>/front/assets/menu/</code></div>
            </div>
        </div>
    </div>

    <?php if (empty($availableTemplates)): ?>
    <div class="alert alert-warning">
        <div class="d-flex align-items-center">
            <i class="bi bi-exclamation-triangle me-2"></i>
            <div>
                <strong>Шаблоны меню не найдены!</strong>
                <div class="small">
                    Создайте PHP файлы в директории: <code>templates/<?= html($currentTheme) ?>/front/assets/menu/</code>
                    <br>Например: <code>main.php</code>, <code>footer.php</code>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <form method="POST" id="menu-form">
        <div class="row">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Структура меню</h5>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-outline-primary btn-sm" id="add-menu-item" data-bs-toggle="modal" data-bs-target="#menuItemModal">
                                <i class="bi bi-plus-circle me-1"></i>Добавить пункт
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" id="expand-all">
                                <i class="bi bi-arrows-expand me-1"></i>Развернуть все
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" id="collapse-all">
                                <i class="bi bi-arrows-collapse me-1"></i>Свернуть все
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="menu-builder">
                            <div class="mb-3">
                                <div class="alert alert-light border">
                                    <div class="d-flex align-items-center">
                                        <i class="bi bi-lightbulb text-warning me-2"></i>
                                        <small>Нажмите на пункт меню для редактирования. Перетаскивайте для изменения порядка.</small>
                                    </div>
                                </div>
                            </div>
                            
                            <div id="menu-items-container" class="sortable-menu menu-tree">
                                <?php if (!empty($menuStructure)): ?>
                                    <?php 
                                    function renderMenuItem($item, $index, $level = 0) {
                                        $title = html($item['title'] ?? '');
                                        $url = html($item['url'] ?? '');
                                        $class = html($item['class'] ?? '');
                                        $target = $item['target'] ?? '_self';
                                        $children = $item['children'] ?? [];
                                        $hasChildren = !empty($children);
                                        $levelClass = 'level-' . min($level, 4);
                                        $itemData = html(json_encode([
                                            'title' => $item['title'] ?? '',
                                            'url' => $item['url'] ?? '',
                                            'class' => $item['class'] ?? '',
                                            'target' => $item['target'] ?? '_self'
                                        ]));
                                        ?>
                                        <div class="menu-item-card card mb-2 <?= $levelClass ?>" 
                                             data-index="<?= $index ?>" 
                                             data-level="<?= $level ?>"
                                             data-item="<?= $itemData ?>">
                                            <div class="card-body p-3">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div class="d-flex align-items-center flex-grow-1">
                                                        <div class="menu-level-indicator me-3">
                                                            <?php for ($i = 0; $i < $level; $i++): ?>
                                                                <span class="level-line"></span>
                                                            <?php endfor; ?>
                                                            <span class="level-dot"></span>
                                                        </div>
                                                        <div class="flex-grow-1">
                                                            <div class="d-flex align-items-center">
                                                                <?php if ($hasChildren): ?>
                                                                    <i class="bi bi-folder-fill text-warning me-2"></i>
                                                                <?php else: ?>
                                                                    <i class="bi bi-link-45deg text-primary me-2"></i>
                                                                <?php endif; ?>
                                                                <div>
                                                                    <h6 class="mb-1"><?= !empty($title) ? $title : 'Без названия' ?></h6>
                                                                    <small class="text-muted"><?= $url ?></small>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="btn-group btn-group-sm">
                                                        <button type="button" class="btn btn-outline-secondary menu-item-handle" title="Перетащить">
                                                            <i class="bi bi-arrows-move"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-outline-primary edit-menu-item" 
                                                                title="Редактировать"
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#menuItemModal">
                                                            <i class="bi bi-pencil"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-outline-success add-child-item" 
                                                                title="Добавить подпункт"
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#menuItemModal"
                                                                data-parent-index="<?= $index ?>">
                                                            <i class="bi bi-patch-plus"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-outline-danger remove-menu-item" title="Удалить">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                                
                                                <?php if ($hasChildren): ?>
                                                    <div class="menu-children-container mt-3">
                                                        <div class="border-top pt-3">
                                                            <div class="menu-children sortable-menu">
                                                                <?php foreach ($children as $childIndex => $child): ?>
                                                                    <?php renderMenuItem($child, $index . '_' . $childIndex, $level + 1); ?>
                                                                <?php endforeach; ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <?php
                                    }
                                    
                                    foreach ($menuStructure as $index => $item) {
                                        renderMenuItem($item, $index, 0);
                                    }
                                    ?>
                                <?php endif; ?>
                            </div>
                            
                            <div id="menu-empty" class="text-center text-muted p-5 <?= !empty($menuStructure) ? 'd-none' : '' ?>">
                                <div class="mb-3">
                                    <i class="bi bi-list-ul display-4 opacity-50"></i>
                                </div>
                                <h5 class="text-muted">Меню пустое</h5>
                                <p class="text-muted mb-3">Добавьте первый пункт меню чтобы начать работу</p>
                                <button type="button" class="btn btn-primary" id="add-first-item" data-bs-toggle="modal" data-bs-target="#menuItemModal">
                                    <i class="bi bi-plus-circle me-1"></i>Добавить первый пункт
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
                            <i class="bi bi-gear me-2"></i>Настройки меню
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">
                                <i class="bi bi-tag me-1"></i>Название меню
                                <span class="text-danger">*</span>
                            </label>
                            <input type="text" 
                                   class="form-control" 
                                   name="name" 
                                   value="<?= html($menu['name'] ?? '') ?>" 
                                   required
                                   placeholder="Например: Главное меню">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">
                                <i class="bi bi-layout-wtf me-1"></i>Шаблон меню
                                <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" name="template" required <?= empty($availableTemplates) ? 'disabled' : '' ?>>
                                <option value="">Выберите шаблон</option>
                                <?php foreach ($availableTemplates as $templateKey => $templateName): ?>
                                    <option value="<?= $templateKey ?>" 
                                        <?= ($menu['template'] ?? '') === $templateKey ? 'selected' : '' ?>>
                                        <?= html($templateName) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text">
                                <i class="bi bi-folder me-1"></i>Путь: <code>templates/<?= html($currentTheme) ?>/front/assets/menu/</code>
                            </div>
                            <?php if (empty($availableTemplates)): ?>
                            <div class="alert alert-warning mt-2 p-2 small">
                                <i class="bi bi-exclamation-triangle me-1"></i>
                                Нет доступных шаблонов. Создайте файлы в указанной директории.
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">
                                <i class="bi bi-power me-1"></i>Статус
                            </label>
                            <select class="form-select" name="status">
                                <option value="active" <?= ($menu['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Активно</option>
                                <option value="inactive" <?= ($menu['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Неактивно</option>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary" <?= empty($availableTemplates) ? 'disabled' : '' ?>>
                                <i class="bi bi-check-lg me-2"></i>
                                <?= isset($menu['id']) ? 'Обновить меню' : 'Создать меню' ?>
                            </button>
                            
                            <?php if (isset($menu['id'])): ?>
                            <a href="<?= ADMIN_URL ?>/menu/preview/<?= $menu['id'] ?>" 
                               class="btn btn-outline-secondary">
                                <i class="bi bi-eye me-2"></i>Предпросмотр
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mt-3">
                    <div class="card-header bg-white border-0">
                        <h6 class="card-title mb-0">
                            <i class="bi bi-graph-up me-2"></i>Статистика
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
        
        <input type="hidden" name="menu_structure" id="menu-structure" value='<?= json_encode($menuStructure ?? [], JSON_UNESCAPED_UNICODE) ?>'>
    </form>
</div>

<div class="modal fade" id="menuItemModal" tabindex="-1" aria-labelledby="menuItemModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="menuItemModalLabel">
                    <i class="bi bi-plus-circle me-2"></i>Добавить пункт меню
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
                                <i class="bi bi-fonts me-1"></i>Название пункта
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
                                <i class="bi bi-link me-1"></i>URL адрес
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
            <i class="bi bi-code-slash me-1"></i>Доступные шорткоды:
        </label>
        <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none" 
                data-bs-toggle="collapse" data-bs-target="#shortcodeHelp">
            <i class="bi bi-info-circle"></i> Подробнее
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
        <i class="bi bi-eye me-1"></i>Превью: <span id="preview-text"></span>
    </div>
</div>
                        
                        <div class="col-md-6">
                            <label class="form-label">
                                <i class="bi bi-box-arrow-up-right me-1"></i>Открывать в
                            </label>
                            <select class="form-select" id="item-target">
                                <option value="_self">Текущем окне</option>
                                <option value="_blank">Новом окне</option>
                            </select>
                        </div>
                        
                        <div class="col-md-6">
                            <label class="form-label">
                                <i class="bi bi-code-slash me-1"></i>CSS классы
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
                                <i class="bi bi-image me-1"></i>Настройки иконки
                            </h6>
                            
                            <div class="row">
                                <div class="col-md-12">
                                    <label class="form-label">
                                        <i class="bi bi-palette me-1"></i>Иконка
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
                                            <i class="bi bi-images"></i> Выбрать иконку
                                        </button>
                                        <button type="button" 
                                                class="btn btn-outline-danger" 
                                                id="clear-icon-btn">
                                            <i class="bi bi-x-circle"></i>
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
                                        <i class="bi bi-rulers me-1"></i>Размер (px)
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
                                        <i class="bi bi-palette me-1"></i>Цвет
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
                                            <i class="bi bi-fonts me-1"></i>Отображать только иконку (без текста)
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
                            <i class="bi bi-shield-lock me-1"></i>Настройки видимости
                        </h6>
    
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label small">
                                    <i class="bi bi-eye me-1"></i>Показывать группам
                                </label>
                                <select class="form-select form-select-sm" id="item-show-to" multiple size="4">
                                    <option value="">Все группы (если не выбрано)</option>
                                    <?php 
                                    $groups = $this->getUserGroups();
                                    foreach ($groups as $group): 
                                    ?>
                                        <option value="<?= $group['id'] ?>">
                                            <?= html($group['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="form-text small">Оставьте пустым чтобы показывать всем</div>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label small">
                                    <i class="bi bi-eye-slash me-1"></i>Не показывать группам
                                </label>
                                <select class="form-select form-select-sm" id="item-hide-from" multiple size="4">
                                    <option value="">Никому не скрывать</option>
                                    <?php foreach ($groups as $group): ?>
                                        <option value="<?= $group['id'] ?>">
                                            <?= html($group['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="form-text small">Выберите группы которым скрыть этот пункт</div>
                            </div>
                        </div>
    
                        <div class="alert alert-info mt-2 p-2 small">
                            <i class="bi bi-info-circle me-1"></i>
                            <strong>Приоритет:</strong> Сначала проверяется "Показывать группам", затем "Не показывать группам"
                        </div>
                    </div>

                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1"></i>Отмена
                </button>
                <button type="button" class="btn btn-primary" id="save-menu-item">
                    <i class="bi bi-check-lg me-1"></i>Сохранить пункт
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
                    <i class="bi bi-images me-2"></i>Выбор иконки
                </h5>
                <button type="button" class="custom-modal-close" onclick="window.menuIconManager.closeIconSelector()">
                    <span>&times;</span>
                </button>
            </div>
            <div class="custom-modal-body">
                <div class="mb-3">
                    <div class="input-group">
                        <span class="input-group-text border-0 bg-light">
                            <i class="bi bi-search"></i>
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
                    <i class="bi bi-x-circle me-1"></i>Отмена
                </button>
                <button type="button" class="btn btn-primary" onclick="window.menuIconManager.confirmIconSelection()">
                    <i class="bi bi-check-lg me-1"></i>Выбрать
                </button>
            </div>
        </div>
    </div>
</div>

<div id="iconSelectorOverlay" class="custom-modal-overlay" style="display: none;"></div>