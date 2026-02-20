<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo html($pageTitle) ?> - Панель управления</title>
    <meta name="generator" content="BloggyCMS">
    <meta name="author" content="Albo Soft">
    <meta name="copyright" content="© <?php echo date('Y') ?> Albo Soft. Все права защищены.">
    <meta name="application-name" content="BloggyCms">
    <?php echo favicon(); ?>
    <?php echo base_admin_css(['bootstrap', 'icons', 'main']); ?>
    <?php echo render_admin_css(); ?>
</head>
<body>
    <?php if(isset($_SESSION['user_id'])) { 
        $currentUser = $userModel->getById($_SESSION['user_id']);
        $currentUri = $_SERVER['REQUEST_URI'];
        $basePath = parse_url(BASE_URL, PHP_URL_PATH) ?? '';
        $adminPath = $basePath ? $basePath . '/admin/' : '/admin/';

        $relativeUri = str_replace($basePath, '', $currentUri);
        $pathParts = explode('/', trim($relativeUri, '/'));

        $currentSection = '';
        if (isset($pathParts[0]) && $pathParts[0] === 'admin') {
            $currentSection = $pathParts[1] ?? '';
        }

        if ($currentUri === ADMIN_URL . '/' || $currentUri === ADMIN_URL || empty($currentSection)) {
            $currentSection = 'posts';
        }
    ?>
        <div class="admin-wrapper">
            <aside class="sidebar" <?php if(!empty(SettingsHelper::get('controller_admin', 'bg_panel'))) { ?>style="background-image: url('<?php echo BASE_URL ?>/uploads/settings/admin/<?php echo SettingsHelper::get('controller_admin', 'bg_panel') ?>"<?php } ?>>
                <a href="<?= ADMIN_URL ?>" class="brand-logo d-flex align-items-center">
                    <img src = "/templates/default/admin/assets/img/logo-outline-light.png" style = "width: 32px; margin-right: 6px;">
                    BLOGGY<span style = "color:rgb(163, 230, 237)">CMS</span>
                </a>
                <nav class="nav flex-column">
                    <?= admin_menu_item('posts', ADMIN_URL . '/posts', 'file-text', 'Посты') ?>
                    <?= admin_menu_item('categories', ADMIN_URL . '/categories', 'folder', 'Категории') ?>
                    <?= admin_menu_item('tags', ADMIN_URL . '/tags', 'tags', 'Теги') ?>
                    <?= admin_menu_item('comments', ADMIN_URL . '/comments', 'chat-dots', 'Комментарии') ?>
                    <?= admin_menu_item('users', ADMIN_URL . '/users', 'people', 'Пользователи') ?>
                    <?= admin_menu_item('pages', ADMIN_URL . '/pages', 'file-earmark', 'Страницы') ?>
                    <?= admin_menu_item('html-blocks', ADMIN_URL . '/html-blocks', 'grid-1x2', 'Контент-блоки') ?>
                    <?= admin_menu_item('post-blocks', ADMIN_URL . '/post-blocks', 'bricks', 'Постблоки') ?>
                    <?= admin_menu_item('fields', ADMIN_URL . '/fields', 'input-cursor-text', 'Поля') ?>
                    <?= admin_menu_item('menu', ADMIN_URL . '/menu', 'view-list', 'Конструктор меню') ?>
                    <?= admin_menu_item('forms', ADMIN_URL . '/forms', 'mailbox', 'Конструктор форм') ?>
                    <?= admin_menu_item('icons', ADMIN_URL . '/icons', 'emoji-heart-eyes', 'Иконки') ?>
                    <?= admin_menu_item('plugins', ADMIN_URL . '/plugins', 'plug', 'Плагины') ?>
                    <?= admin_menu_item('settings', ADMIN_URL . '/settings', 'gear', 'Настройки') ?>
                    <?= admin_menu_item('templates', ADMIN_URL . '/templates', 'palette', 'Шаблон') ?>
                    <?= admin_menu_item('addons', ADMIN_URL . '/addons', 'plugin', 'Дополнения') ?>
                    <?= admin_menu_item('docs', ADMIN_URL . '/docs', 'filetype-doc', 'Документация') ?>
                </nav>
            </aside>

            <main class="content-wrapper">
                <div class="top-bar d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><a href = "<?= ADMIN_URL ?>" style = "text-decoration:none"><?php echo bloggy_icon('bs', 'house', '16 16', '#000; position:relative; bottom:2px;'); ?> Панель управления</a> / <?= htmlspecialchars($pageTitle) ?></h6>
                    <div class="d-flex align-items-center">
                        <div class="d-flex align-items-center me-3" style="gap: 10px;">
                            <div class="dropdown">
                                <a href="#" class="admin-header-btn admin-btn-notifications d-flex align-items-center dropdown-toggle" 
                                data-bs-toggle="dropdown" 
                                data-notifications-count="0"
                                title="Уведомления">
                                    <div class="btn-icon-wrapper">
                                        <i class="bi bi-bell"></i>
                                        <span class="notification-badge" style="display: none;"></span>
                                    </div>
                                    <span class="btn-text">Уведомления</span>
                                </a>
                                <div class="dropdown-menu dropdown-menu-end shadow-lg p-0" style="min-width: 320px;">
                                    <div class="dropdown-header bg-primary text-white d-flex justify-content-between align-items-center">
                                        <h6 class="mb-0">Уведомления</h6>
                                        <a href="<?= ADMIN_URL ?>/notifications" class="text-white text-decoration-none">
                                            <small>Все</small>
                                        </a>
                                    </div>
                                    <div class="dropdown-body" style="max-height: 400px; overflow-y: auto;">
                                        <div id="notifications-dropdown-content" class="p-3">
                                            <div class="text-center py-3">
                                                <div class="spinner-border spinner-border-sm text-primary" role="status">
                                                    <span class="visually-hidden">Загрузка...</span>
                                                </div>
                                                <p class="text-muted mt-2 mb-0">Загрузка уведомлений...</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="dropdown-footer p-2 border-top">
                                        <div class="d-flex justify-content-between">
                                            <a href="#" class="btn btn-sm btn-outline-success" id="mark-all-read-dropdown">
                                                <i class="bi bi-check2-all me-1"></i> Прочитать все
                                            </a>
                                            <a href="<?= ADMIN_URL ?>/notifications" class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-arrow-right me-1"></i> Перейти
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <a href="<?= ADMIN_URL ?>/controllers" class="admin-header-btn admin-btn-controllers d-flex align-items-center" title="Контроллеры">
                                <div class="btn-icon-wrapper">
                                    <?php echo bloggy_icon('bs', 'cpu', '16 16', '#7b1fa2') ?>
                                </div>
                                <span class="btn-text">Контроллеры</span>
                            </a>
                        </div>
                        <div class="dropdown">
                            <a href="#" class="d-flex align-items-center text-decoration-none dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                <?php if(!empty($currentUser['avatar']) && $currentUser['avatar'] !== 'default.jpg'): ?>
                                    <img src="<?= BASE_URL ?>/uploads/avatars/<?= $currentUser['avatar'] ?>" 
                                         class="rounded-circle me-2" 
                                         style="width: 32px; height: 32px; object-fit: cover;"
                                         alt="<?= htmlspecialchars($currentUser['username']) ?>">
                                <?php else: ?>
                                    <div class="rounded-circle me-2 d-flex align-items-center justify-content-center bg-secondary text-white" 
                                         style="width: 32px; height: 32px;">
                                        <i class="bi bi-person" style="font-size: 16px;"></i>
                                    </div>
                                <?php endif; ?>
                                <span class="text-muted">
                                    <?= htmlspecialchars($currentUser['display_name'] ?: $currentUser['username']) ?>
                                </span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end shadow">
                                <li>
                                    <a class="dropdown-item d-flex align-items-center" href="<?= ADMIN_URL ?>/users/edit/<?= $currentUser['id'] ?>">
                                        <i class="bi bi-pencil me-2"></i> Редактировать профиль
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item d-flex align-items-center" href="<?= BASE_URL ?>/profile" target="_blank">
                                        <i class="bi bi-person-circle me-2"></i> Мой публичный профиль
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item d-flex align-items-center" href="<?= BASE_URL ?>" target="_blank">
                                        <i class="bi bi-eye me-2"></i> Перейти на сайт
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item d-flex align-items-center text-danger" href="<?= ADMIN_URL ?>/logout">
                                        <i class="bi bi-box-arrow-right me-2"></i> Выйти
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="container-fluid px-4">
                    <?php echo $content ?>
                </div>
            </main>
        </div>
    <?php } else { ?>
        <?php echo $content ?>
    <?php } ?>

    <div class="toast-container position-fixed bottom-0 end-0 p-3">
        <div id="toast" class="toast align-items-center border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                </div>
                <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    </div>

    <?php if(isset($_SESSION['user_id'])) { ?>
        <?= QuickActionsHelper::renderQuickActions() ?>
    <?php } ?>

    <?php echo base_admin_js(['jquery-3.6.0.min', 'bootstrap', 'Sortable.min', 'main', 'jquery-ui.min', 'notifications']); ?>
    <?php echo render_admin_js(); ?>
    <?php echo render_admin_bottom_js() ?>
    
    <?php if(isset($_SESSION['toast'])) { ?>
        <div id="notification-data" data-message="<?php echo html($_SESSION['toast']['message']) ?>" data-type="<?php echo html($_SESSION['toast']['type']) ?>" data-position="<?php echo SettingsHelper::get('controller_admin', 'notification_position', 'top-left') ?>" style="display: none;"></div>
        <?php unset($_SESSION['toast']); ?>
    <?php } ?>
    <script>
    window.BASE_URL = '<?= BASE_URL ?>';
    window.ADMIN_URL = '<?= ADMIN_URL ?>';
    </script>
</body>
</html>