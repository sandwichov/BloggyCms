<?php
/**
 * Header Block
 */

$logoUrl = !empty($settings['logo_path']) ? BlockImageHelper::getImageUrl($settings['logo_path']) : '';
$logoAlt = html($settings['logo_alt'] ?? 'Логотип сайта');
$siteName = html($settings['site_name'] ?? 'BloggyCMS');
$logoLink = html($settings['logo_link'] ?? '/');
$mainMenuContent = !empty($settings['main_menu_id']) ? MenuRenderer::renderById($settings['main_menu_id']) : '';
$profileMenuContent = !empty($settings['profile_menu_id']) ? MenuRenderer::renderById($settings['profile_menu_id']) : '';
$showSearch = !empty($settings['show_search']) && $settings['show_search'] == 1;
$searchPlaceholder = html($settings['search_placeholder'] ?? 'Поиск...');
$searchPage = html($settings['search_page'] ?? '/search');
$stickyClass = !empty($settings['sticky_header']) && $settings['sticky_header'] == 1 ? 'tg-sticky' : '';
$shadowClass = !empty($settings['show_shadow']) && $settings['show_shadow'] == 1 ? 'tg-shadow' : '';
$containerClass = html($settings['container_type'] ?? 'tg-container');
$headerHeight = html($settings['header_height'] ?? 'tg-py-3');
$isLoggedIn = isset($_SESSION['user_id']);
?>

<header class="tg-header <?php echo $stickyClass; ?> <?php echo $shadowClass; ?>">
    <div class="<?php echo $containerClass; ?>">
        <div class="tg-header-inner <?php echo $headerHeight; ?>">
            
            <div class="tg-logo">
                <a href="<?php echo $logoLink; ?>" class="tg-logo-link">
                    <?php if ($logoUrl) { ?>
                        <img src="<?php echo $logoUrl; ?>" alt="<?php echo $logoAlt; ?>" class="tg-logo-image">
                    <?php } ?>
                    <span class="tg-site-name"><?php echo $siteName; ?></span>
                </a>
            </div>

            <nav class="tg-nav">
                <?php echo $mainMenuContent; ?>
            </nav>

            <div class="tg-actions">
                
                <?php if ($showSearch) { ?>
                <div class="tg-search">
                    <form action="<?php echo $searchPage; ?>" method="get" class="tg-search-form">
                        <input type="text" 
                               name="q" 
                               class="tg-search-input" 
                               placeholder="<?php echo $searchPlaceholder; ?>"
                               value="<?php echo html($_GET['q'] ?? ''); ?>"
                               autocomplete="off">
                        <button type="submit" class="tg-search-btn" aria-label="Поиск">
                            <?php echo bloggy_icon('bs', 'search', '18 18', 'currentColor'); ?>
                        </button>
                    </form>
                </div>
                <?php } ?>

                <div class="tg-user">
                    <?php if ($isLoggedIn) { ?>
                        <div class="tg-user-dropdown">
                            <button class="tg-user-btn" type="button">
                                <?php echo getUserAvatarHtml(); ?>
                                <span class="tg-user-name"><?php echo getUserDisplayName(); ?></span>
                                <?php echo bloggy_icon('bs', 'chevron-down', '14 14', 'currentColor', 'tg-user-arrow'); ?>
                            </button>
                            
                            <?php if (!empty($profileMenuContent)) { ?>
                            <div class="tg-user-menu">
                                <?php echo $profileMenuContent; ?>
                            </div>
                            <?php } ?>
                        </div>
                    <?php } else { ?>
                        <a href="/login" class="tg-login-btn">
                            <?php echo bloggy_icon('bs', 'person', '18 18', 'currentColor', 'tg-mr-1'); ?>
                            <span>Войти</span>
                        </a>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
</header>

<?php
function getUserDisplayName(): string {
    $userId = $_SESSION['user_id'] ?? null;
    if (!$userId) return 'Гость';
    
    try {
        $db = DatabaseRegistry::getDb();
        $userModel = new UserModel($db);
        $user = $userModel->getById($userId);
        return html($user['display_name'] ?? $user['username'] ?? 'Пользователь');
    } catch (Exception $e) {
        return 'Пользователь';
    }
}

function getUserAvatarHtml(): string {
    $userId = $_SESSION['user_id'] ?? null;
    if (!$userId) return '';
    
    try {
        $db = DatabaseRegistry::getDb();
        $userModel = new UserModel($db);
        $user = $userModel->getById($userId);
        
        if ($user && !empty($user['avatar']) && $user['avatar'] !== 'default.jpg') {
            $avatar = $user['avatar'];
            if (strpos($avatar, '/') === false) {
                $avatarUrl = BASE_URL . '/uploads/avatars/' . $avatar;
            } elseif (strpos($avatar, 'http') === 0) {
                $avatarUrl = $avatar;
            } elseif (strpos($avatar, '/') === 0) {
                $avatarUrl = BASE_URL . $avatar;
            } else {
                $avatarUrl = BASE_URL . '/' . $avatar;
            }
        } else {
            $avatarUrl = BASE_URL . '/uploads/avatars/default.png';
        }
        
        return '<img src="' . $avatarUrl . '" alt="Avatar" class="tg-avatar">';
    } catch (Exception $e) {
        return '';
    }
}
?>