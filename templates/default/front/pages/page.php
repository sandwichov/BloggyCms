<?php
/**
 * Шаблон статичной страницы
 */
?>

<div class="tg-page">
    <div class="tg-container">
        <div class="tg-breadcrumbs tg-mb-4">
            <nav class="tg-breadcrumbs-nav">
                <a href="<?php echo BASE_URL; ?>/" class="tg-breadcrumb-item">
                    <?php echo bloggy_icon('bs', 'house', '14', 'currentColor', 'tg-mr-1'); ?>
                    Главная
                </a>
                <span class="tg-breadcrumb-sep">/</span>
                <span class="tg-breadcrumb-item tg-active"><?php echo html($page['title']); ?></span>
            </nav>
        </div>
        <div class="tg-page-header tg-mb-4">
            <h1 class="tg-page-title"><?php echo html($page['title']); ?></h1>
            
            <?php if (!empty($page['short_description'])): ?>
            <p class="tg-page-description tg-text-muted">
                <?php echo html($page['short_description']); ?>
            </p>
            <?php endif; ?>
            
            <?php if (!empty($page['updated_at'])): ?>
            <div class="tg-page-meta">
                <span class="tg-meta-item">
                    <?php echo bloggy_icon('bs', 'calendar', '14', 'currentColor', 'tg-mr-1'); ?>
                    Обновлено <?php echo date('d.m.Y', strtotime($page['updated_at'])); ?>
                </span>
            </div>
            <?php endif; ?>
        </div>
        <div class="tg-page-content tg-mb-5">
            
            <?php if (!empty($blocks)): ?>
                <?php foreach ($blocks as $block): ?>
                    <div class="tg-page-block tg-page-block-<?php echo $block['type']; ?> tg-mb-4">
                        <?php 
                        if (is_array($block['content'])) {
                            echo BlockRenderer::render($block);
                        } else {
                            echo $block['content'];
                        }
                        ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
            <?php
            $fieldModel = new FieldModel($this->db);
            $customFields = $fieldModel->getActiveByEntityType('page');
            ?>
            
            <?php if (!empty($customFields)): ?>
                <?php foreach ($customFields as $field): 
                    $value = $fieldModel->getFieldValue('page', $page['id'], $field['system_name']);
                    if (!empty($value)): 
                ?>
                <div class="tg-custom-field-block tg-mb-3">
                    <span class="tg-custom-field-label"><?php echo html($field['name']); ?>:</span>
                    <span class="tg-custom-field-value">
                        <?php echo $fieldModel->renderFieldDisplay($field, $value, 'page', $page['id']); ?>
                    </span>
                </div>
                <?php endif; endforeach; ?>
            <?php endif; ?>
            
        </div>
        
        <div class="tg-cta-section">
            <div class="tg-card tg-text-center">
                <div class="tg-card-body">
                    <h2 class="tg-cta-title">Хватит читать — пора действовать!</h2>
                    <p class="tg-cta-text tg-text-muted tg-mb-4">
                        Теория без практики остаётся теорией. Давайте превратим ваши знания в работающий блог!
                    </p>
                    <a href="/page/zagruzite-bloggycms" class="tg-btn tg-btn-primary tg-btn-lg">
                        <?php echo bloggy_icon('bs', 'download', '18', 'currentColor', 'tg-mr-1'); ?>
                        Скачать систему
                    </a>
                </div>
            </div>
        </div>
        
    </div>
</div>