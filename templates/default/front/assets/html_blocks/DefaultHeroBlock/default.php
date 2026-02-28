<?php
/**
 * Hero Block Template
 */

$showTitle = $settings['show_title'] ?? 1;
$titleText = html($settings['title_text'] ?? 'Заголовок');
$titleColor = $settings['title_color'] ?? 'var(--tg-text)';
$showSubtitle = $settings['show_subtitle'] ?? 1;
$subtitleText = html($settings['subtitle_text'] ?? '');
$subtitleColor = $settings['subtitle_color'] ?? 'var(--tg-text-secondary)';
$showDescription = $settings['show_description'] ?? 1;
$descriptionText = nl2br(html($settings['description_text'] ?? ''));
$buttons = $settings['buttons'] ?? [];
$showImage = $settings['show_image'] ?? 1;
$imageUrl = !empty($settings['image']) ? BlockImageHelper::getImageUrl($settings['image']) : '';
$imagePosition = $settings['image_position'] ?? 'right';
$imageAlignment = $settings['image_alignment'] ?? 'center';
$imageRounded = !empty($settings['image_rounded']) ? ' rounded' : '';
$imageShadow = !empty($settings['image_shadow']) ? ' shadow' : '';
$bgType = $settings['background_type'] ?? 'color';
$bgColor = $settings['background_color'] ?? 'var(--tg-surface)';
$gradientStart = $settings['gradient_start'] ?? 'var(--tg-surface)';
$gradientEnd = $settings['gradient_end'] ?? 'var(--tg-bg)';
$gradientDirection = $settings['gradient_direction'] ?? 'to bottom';
$bgImageUrl = !empty($settings['background_image']) ? BlockImageHelper::getImageUrl($settings['background_image']) : '';
$bgOverlay = $settings['background_overlay'] ?? 'none';

$bgStyle = '';
switch ($bgType) {
    case 'color':
        $bgStyle = "background-color: $bgColor;";
        break;
    case 'gradient':
        $bgStyle = "background: linear-gradient($gradientDirection, $gradientStart, $gradientEnd);";
        break;
    case 'image':
        if ($bgImageUrl) {
            $bgStyle = "background-image: url('$bgImageUrl'); background-size: cover; background-position: center;";
        }
        break;
}

$alignClass = 'text-' . html($settings['content_alignment'] ?? 'left');
$ptClass = 'pt-' . html($settings['padding_top'] ?? 5);
$pbClass = 'pb-' . html($settings['padding_bottom'] ?? 5);
$customClass = html($settings['custom_css_class'] ?? '');
$contentColClass = $showImage && $imageUrl ? 'col-md-6' : 'col-12';
$imageColClass = $showImage && $imageUrl ? 'col-md-6' : '';

if ($imagePosition === 'left') {
    $imageOrderClass = 'order-md-1';
    $contentOrderClass = 'order-md-2';
} else {
    $imageOrderClass = 'order-md-2';
    $contentOrderClass = 'order-md-1';
}

$alignSelfClass = $imageAlignment === 'top' ? 'align-items-start' : 'align-items-center';
$buttonsHtml = '';
if (!empty($buttons)) {
    $buttonsHtml .= '<div class="tg-hero-buttons d-flex gap-2 flex-wrap">';
    foreach ($buttons as $btn) {
        $btnClass = 'btn ';
        $btnClass .= ($btn['type'] === 'primary') ? 'btn-primary' : 'btn-outline-secondary';
        $btnClass .= ($btn['size'] === 'lg') ? ' btn-lg' : '';
        
        $iconHtml = '';
        if (!empty($btn['icon'])) {
            $iconParts = explode(':', $btn['icon']);
            $iconSet = $iconParts[0] ?? 'bs';
            $iconName = $iconParts[1] ?? $btn['icon'];
            $iconHtml = bloggy_icon($iconSet, $iconName, '20 20', 'currentColor', 'me-1');
        }
        
        $buttonsHtml .= sprintf(
            '<a href="%s" class="%s">%s%s</a>',
            html($btn['url']),
            $btnClass,
            $iconHtml,
            html($btn['text'])
        );
    }
    $buttonsHtml .= '</div>';
}

$overlayClass = '';
if ($bgType === 'image' && $bgOverlay !== 'none') {
    $overlayClass = 'tg-hero-overlay tg-overlay-' . html($bgOverlay);
}
?>

<section class="tg-hero <?php echo $ptClass; ?> <?php echo $pbClass; ?> <?php echo $customClass; ?>" style="<?php echo $bgStyle; ?>">
    
    <?php if ($overlayClass) { ?>
    <div class="<?php echo $overlayClass; ?>"></div>
    <?php } ?>
    
    <div class="container">
        <div class="row <?php echo $alignSelfClass; ?>">
            
            <?php if ($contentColClass) { ?>
            <div class="<?php echo $contentColClass; ?> <?php echo $contentOrderClass; ?>">
                <div class="tg-hero-content <?php echo $alignClass; ?>">

                    <?php if ($showTitle && $titleText) { ?>
                        <h1 class="tg-hero-title display-4 fw-bold mb-3" style="color: <?php echo $titleColor; ?>;">
                            <?php echo $titleText; ?>
                        </h1>
                    <?php } ?>

                    <?php if ($showSubtitle && $subtitleText) { ?>
                        <p class="tg-hero-subtitle text-primary fw-semibold mb-2" style="color: <?php echo $subtitleColor; ?>;">
                            <?php echo $subtitleText; ?>
                        </p>
                    <?php } ?>
                    
                    <?php if ($showDescription && $descriptionText) { ?>
                        <div class="tg-hero-description lead mb-4">
                            <?php echo $descriptionText; ?>
                        </div>
                    <?php } ?>
                    
                    <?php echo $buttonsHtml; ?>
                    
                </div>
            </div>
            <?php } ?>
            
            <?php if ($showImage && $imageUrl && $imageColClass) { ?>
            <div class="<?php echo $imageColClass; ?> <?php echo $imageOrderClass; ?>">
                <div class="tg-hero-image-wrapper text-center">
                    <img src="<?php echo $imageUrl; ?>" 
                         alt="<?php echo html($titleText); ?>" 
                         class="tg-hero-image img-fluid <?php echo $imageRounded; ?> <?php echo $imageShadow; ?>"
                         loading="lazy">
                </div>
            </div>
            <?php } ?>
            
        </div>
    </div>
</section>

<?php if ($bgType === 'image' && $bgOverlay !== 'none') { ?>
<style>
.tg-hero {
    position: relative;
    isolation: isolate;
}
.tg-hero-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 0;
    pointer-events: none;
}
.tg-overlay-dark { background-color: rgba(0, 0, 0, 0.5); }
.tg-overlay-light { background-color: rgba(255, 255, 255, 0.7); }
.tg-overlay-primary { background-color: rgba(43, 82, 120, 0.8); }
.tg-hero .container {
    position: relative;
    z-index: 1;
}
</style>
<?php } ?>