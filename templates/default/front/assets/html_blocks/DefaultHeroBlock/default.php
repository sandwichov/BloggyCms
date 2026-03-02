<?php
/**
 * Hero Block Template
 */

$imageColSize = $settings['image_column_size'] ?? '6';
$contentColSize = 12 - (int)$imageColSize;
$imageColClass = $settings['show_image'] && !empty($settings['image']) ? 'col-md-' . $imageColSize : '';
$contentColClass = $settings['show_image'] && !empty($settings['image']) ? 'col-md-' . $contentColSize : 'col-12';
$imagePosition = $settings['image_position'] ?? 'right';
if ($imagePosition === 'left') {
    $imageOrderClass = 'order-md-1';
    $contentOrderClass = 'order-md-2';
} else {
    $imageOrderClass = 'order-md-2';
    $contentOrderClass = 'order-md-1';
}

$alignSelfClass = ($settings['image_alignment'] ?? 'center') === 'top' ? 'align-items-start' : 'align-items-center';

$buttonsHtml = '';
if (!empty($settings['buttons'])) {
    $buttonsHtml .= '<div class="tg-hero-buttons d-flex gap-2 flex-wrap">';
    foreach ($settings['buttons'] as $btn) {
        $btnClass = 'btn btn-' . html($btn['type']);
        if (!empty($btn['size']) && $btn['size'] !== 'md') {
            $btnClass .= ' btn-' . html($btn['size']);
        }
        
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

$bgStyle = '';
switch ($settings['background_type'] ?? 'color') {
    case 'color':
        $bgStyle = "background-color: " . ($settings['background_color'] ?? 'var(--tg-surface)') . ";";
        break;
    case 'gradient':
        $bgStyle = "background: linear-gradient(" . 
                   ($settings['gradient_direction'] ?? 'to bottom') . ", " . 
                   ($settings['gradient_start'] ?? 'var(--tg-surface)') . ", " . 
                   ($settings['gradient_end'] ?? 'var(--tg-bg)') . ");";
        break;
    case 'image':
        $bgImageUrl = !empty($settings['background_image']) ? BlockImageHelper::getImageUrl($settings['background_image']) : '';
        if ($bgImageUrl) {
            $bgStyle = "background-image: url('$bgImageUrl'); background-size: cover; background-position: center;";
        }
        break;
}

$overlayClass = '';
if (($settings['background_type'] ?? '') === 'image' && !empty($settings['background_overlay']) && $settings['background_overlay'] !== 'none') {
    $overlayClass = 'tg-hero-overlay tg-overlay-' . html($settings['background_overlay']);
}
?>

<section class="tg-hero pt-<?php echo html($settings['padding_top'] ?? 5); ?> pb-<?php echo html($settings['padding_bottom'] ?? 5); ?> <?php echo html($settings['custom_css_class'] ?? ''); ?>" style="<?php echo $bgStyle; ?>">
    
    <?php if ($overlayClass): ?>
    <div class="<?php echo $overlayClass; ?>"></div>
    <?php endif; ?>
    
    <div class="container">
        <div class="row <?php echo $alignSelfClass; ?>">
            
            <div class="<?php echo $contentColClass; ?> <?php echo $contentOrderClass; ?>">
                <div class="tg-hero-content text-<?php echo html($settings['content_alignment'] ?? 'left'); ?>">

                    <?php if (!empty($settings['show_title']) && !empty($settings['title_text'])): ?>
                        <h1 class="tg-hero-title display-4 fw-bold mb-3" style="color: <?php echo html($settings['title_color'] ?? 'var(--tg-text)'); ?>;">
                            <?php echo html($settings['title_text']); ?>
                        </h1>
                    <?php endif; ?>

                    <?php if (!empty($settings['show_subtitle']) && !empty($settings['subtitle_text'])): ?>
                        <p class="tg-hero-subtitle text-primary fw-semibold mb-2" style="color: <?php echo html($settings['subtitle_color'] ?? 'var(--tg-text-secondary)'); ?>;">
                            <?php echo html($settings['subtitle_text']); ?>
                        </p>
                    <?php endif; ?>
                    
                    <?php if (!empty($settings['show_description']) && !empty($settings['description_text'])): ?>
                        <div class="tg-hero-description lead mb-4">
                            <?php echo nl2br(html($settings['description_text'])); ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php echo $buttonsHtml; ?>
                    
                </div>
            </div>
            
            <?php if (!empty($settings['show_image']) && !empty($settings['image'])): ?>
            <div class="<?php echo $imageColClass; ?> <?php echo $imageOrderClass; ?>">
                <div class="tg-hero-image-wrapper text-center">
                    <img src="<?php echo BlockImageHelper::getImageUrl($settings['image']); ?>" 
                         alt="<?php echo html($settings['title_text'] ?? ''); ?>" 
                         class="tg-hero-image img-fluid <?php echo !empty($settings['image_rounded']) ? 'rounded' : ''; ?> <?php echo !empty($settings['image_shadow']) ? 'shadow' : ''; ?>"
                         loading="lazy">
                </div>
            </div>
            <?php endif; ?>
            
        </div>
    </div>
</section>

<?php if (($settings['background_type'] ?? '') === 'image' && !empty($settings['background_overlay']) && $settings['background_overlay'] !== 'none'): ?>
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
<?php endif; ?>