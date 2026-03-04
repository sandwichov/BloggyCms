<?php
/**
 * Шаблон услуг
 */

$theme = $settings['theme'] ?? 'light';
$align = $settings['align'] ?? 'center';
$columns = $settings['columns'] ?? '3';

$customStyles = [];
if($theme === 'custom') {
    if(!empty($settings['background_color'])) {
        $customStyles[] = '--bg-color: ' . html($settings['background_color']);
    }
    if(!empty($settings['text_color'])) {
        $customStyles[] = '--text-color: ' . html($settings['text_color']);
    }
}
if(!empty($settings['accent_color'])) {
    $customStyles[] = '--accent-color: ' . html($settings['accent_color']);
    $hex = ltrim($settings['accent_color'], '#');
    if(strlen($hex) === 6) {
        $rgb = hexdec(substr($hex, 0, 2)) . ', ' . 
               hexdec(substr($hex, 2, 2)) . ', ' . 
               hexdec(substr($hex, 4, 2));
        $customStyles[] = '--accent-rgb: ' . $rgb;
    }
}
if(!empty($settings['card_background'])) {
    $customStyles[] = '--card-bg: ' . html($settings['card_background']);
}

$paddingTop = (int)($settings['padding_top'] ?? 80);
$paddingBottom = (int)($settings['padding_bottom'] ?? 80);
$customStyles[] = '--padding-top: ' . $paddingTop . 'px';
$customStyles[] = '--padding-bottom: ' . $paddingBottom . 'px';
$sectionClass = 'services-character';
$sectionClass .= ' theme-' . $theme;
$sectionClass .= ' align-' . $align;
if(!empty($settings['custom_css_class'])) {
    $sectionClass .= ' ' . html($settings['custom_css_class']);
}

$buttonsHtml = '';
if(!empty($settings['buttons'])) {
    $buttonsHtml .= '<div class="buttons">';
    
    foreach($settings['buttons'] as $index => $btn) {
        $btnClass = $index === 0 ? 'primary' : 'secondary';
        $buttonsHtml .= '<a href="' . html($btn['url']) . '" class="btn ' . $btnClass . '">' . html($btn['text']) . '</a>';
    }
    
    $buttonsHtml .= '</div>';
}

$servicesHtml = '';
if(!empty($settings['services'])) {
    $servicesHtml .= '<div class="services-grid cols-' . $columns . '">';
    
    foreach($settings['services'] as $service) {
        $servicesHtml .= '<div class="service-card">';
        
        if(!empty($service['image'])) {
            $imageUrl = BlockImageHelper::getImageUrl($service['image']);
            $servicesHtml .= '<div class="service-image"><img src="' . $imageUrl . '" alt="' . html($service['title']) . '" style = "width: 20%;"></div>';
        } elseif(!empty($service['icon'])) {
            $iconParts = explode(':', $service['icon']);
            $iconSet = $iconParts[0] ?? 'bs';
            $iconName = $iconParts[1] ?? $service['icon'];
            
            if(function_exists('bloggy_icon')) {
                $servicesHtml .= '<div class="service-icon">' . bloggy_icon($iconSet, $iconName, '48 48', 'currentColor', '') . '</div>';
            }
        }
        
        if(!empty($service['title'])) {
            $servicesHtml .= '<h3 class="service-title">' . html($service['title']) . '</h3>';
        }
        
        if(!empty($service['description'])) {
            $servicesHtml .= '<div class="service-description">' . nl2br(html($service['description'])) . '</div>';
        }
        
        if(!empty($service['price'])) {
            $servicesHtml .= '<div class="service-price">' . html($service['price']) . '</div>';
        }
        
        $servicesHtml .= '</div>';
    }
    
    $servicesHtml .= '</div>';
}
?>

<section class="<?php echo $sectionClass; ?>" style="<?php echo implode('; ', $customStyles); ?>">
    <div class="container">
        
        <?php if(!empty($settings['badge']) || !empty($settings['title']) || !empty($settings['description'])) { ?>
        <div class="header">
            
            <?php if(!empty($settings['badge'])) { ?>
            <div class="badge"><?php echo html($settings['badge']); ?></div>
            <?php } ?>
            
            <?php if(!empty($settings['title'])) { ?>
            <h2><?php echo $settings['title']; ?></h2>
            <?php } ?>
            
            <?php if(!empty($settings['description'])) { ?>
            <div class="header-description"><?php echo nl2br(html($settings['description'])); ?></div>
            <?php } ?>
            
        </div>
        <?php } ?>
        
        <?php echo $servicesHtml; ?>
        
        <?php echo $buttonsHtml; ?>
        
    </div>
</section>