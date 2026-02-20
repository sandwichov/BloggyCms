<?php
/**
 * Template Name: Основной макет
 */

$maintenanceMode = SettingsHelper::get('general', 'maintenance_mode', false);
$isAdmin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'];

if ($maintenanceMode && !$isAdmin) {
    http_response_code(503);
    header('Retry-After: 3600');
    ?>
    <!DOCTYPE html>
    <html lang="ru">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Сайт на обслуживании - <?= SettingsHelper::get('general', 'site_name', 'BloggyCMS') ?></title>
            <link rel="stylesheet" href="<?= BASE_URL ?>/templates/default/front/assets/css/maintenance.css">
        </head>
    <body>
        <div class="maintenance-page">
            <div class="maintenance-content">
                <div class="maintenance-icon">🚧</div>
                <h1 class="maintenance-title">Ведутся технические работы</h1>
                <div class="maintenance-message">
                    <?= nl2br(htmlspecialchars(SettingsHelper::get('general', 'maintenance_message', 'Сайт временно недоступен. Ведутся технические работы.'))) ?>
                </div>
            </div>
        </div>
    </body>
    </html>
    <?php exit;
}
?>

<?php preload_html_block_assets(['start', 'footer']); ?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= SettingsHelper::get('general', 'site_name', 'BloggyCMS') ?> - <?= $title ?? 'Главная' ?></title>
    
    <?php echo base_front_css(['bootstrap.min', 'swiper-bundle.min', 'style']); ?>
    <?php echo render_front_css(); ?>
</head>
<body>

    <div id="front-notifications-container" class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999;"></div>
    
    <main class="page-wrapper">
        <?php echo render_html_block('start'); ?>
        <?php echo $content ?>
        <?php echo render_html_block('footer'); ?>
    </main>
    
    <?php echo base_front_js(['jquery.min', 'bootstrap.bundle.min', 'swiper-bundle.min', 'isotope.min', 'notifications']); ?>
    <?php echo render_front_js(); ?>
    <?php echo render_front_bottom_js() ?>
    <script src="/templates/default/front/assets/js/script.js"></script>

    <?php if(isset($_SESSION['toast'])) { ?>
        <div id="notification-data" 
             data-message="<?php echo htmlspecialchars($_SESSION['toast']['message']) ?>" 
             data-type="<?php echo htmlspecialchars($_SESSION['toast']['type']) ?>"
             style="display: none;"></div>
        <?php unset($_SESSION['toast']); ?>
    <?php } ?>
    
    <div class="scroll-progress cursor-pointer active-progress">
        <svg class="progress-circle svg-content" width="100%" height="100%" viewBox="-1 -1 102 102">
            <path d="M50,1 a49,49 0 0,1 0,98 a49,49 0 0,1 0,-98" style="transition: stroke-dashoffset 10ms linear; stroke-dasharray: 307.919px, 307.919px; stroke-dashoffset: 269.891px;"></path>
        </svg>
    </div>

</body>
</html>