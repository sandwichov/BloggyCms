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
        <title>На обслуживании - <?= SettingsHelper::get('general', 'site_name', 'BloggyCMS') ?></title>
        <?php echo base_front_css(['maintenance']); ?>
    </head>
    <body>
        <div class="maintenance-page">
            <div class="maintenance-icon">🛠️</div>
            <h1 class="maintenance-title">Технические работы</h1>
            <div class="maintenance-message">
                <?= nl2br(html(SettingsHelper::get('general', 'maintenance_message', 'Сайт временно недоступен. Ведутся технические работы.'))) ?>
            </div>
        </div>
    </body>
    </html>
    <?php exit;
}
?>

<?php preload_html_block_assets(['header', 'footer']); ?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= SettingsHelper::get('general', 'site_name', 'BloggyCMS') ?> - <?= $title ?? 'Главная' ?></title>
    <?php if(!empty(SettingsHelper::get('general', 'site_description'))) { ?>
    <meta name="description" content="<?php echo SettingsHelper::get('general', 'site_description'); ?>">
    <?php } ?>
    <?php if(!empty(SettingsHelper::get('general', 'site_author'))) { ?>
        <meta name="author" content="<?php echo SettingsHelper::get('general', 'site_author'); ?>" />
    <?php } ?>
    <?php echo base_front_css(['bootstrap.min','main']); ?>
    <?php echo render_front_css(); ?>
</head>
<body>

    <div id="front-notifications-container" class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999;"></div>
    
    <div class="page-wrapper">
        <?php echo render_html_block('header'); ?>
        <main class="main-content">
            <?php echo $content ?>
        </main>
        
    </div>
    
    <?php echo base_front_js(['bootstrap.bundle.min','notifications','main']); ?>
    
    <?php echo render_front_js(); ?>
    <?php echo render_front_bottom_js(); ?>

    <?php if(isset($_SESSION['toast'])) { ?>
        <div id="notification-data" 
             data-message="<?php echo html($_SESSION['toast']['message']); ?>" 
             data-type="<?php echo html($_SESSION['toast']['type']); ?>"
             style="display: none;"></div>
        <?php unset($_SESSION['toast']); ?>
    <?php } ?>

</body>
</html>