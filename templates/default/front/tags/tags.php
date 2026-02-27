<?php
/**
 * Template Name: Страница всех тегов
 */

$minPostsToShow = SettingsHelper::get('controller_tags', 'min_posts_to_show', 1);
$defaultTagImage = SettingsHelper::get('controller_tags', 'default_tag_image', '');
$tagPrefix = SettingsHelper::get('controller_tags', 'tag_prefix', '#');

$colorPalette = [
    '#c11c3b', '#0a11a8', '#002306', '#c76234',
    '#8a2be2', '#20c997', '#fd7e14', '#6f42c1',
    '#139090', '#d59801', '#296e4a'
];
?>

<div class="tg-tags-page">
    <div class="tg-container">
        <div class="tg-breadcrumbs tg-mb-4">
            <nav class="tg-breadcrumbs-nav">
                <a href="<?php echo BASE_URL; ?>/" class="tg-breadcrumb-item">
                    <?php echo bloggy_icon('bs', 'house', '14', 'currentColor', 'tg-mr-1'); ?>
                    Главная
                </a>
                <span class="tg-breadcrumb-sep">/</span>
                <span class="tg-breadcrumb-item tg-active">Все теги</span>
            </nav>
        </div>
        <div class="tg-page-header tg-mb-4">
            <h1 class="tg-page-title">
                Коллекция тегов
                <span class="tg-page-title-dot">.</span>
            </h1>
            <p class="tg-page-description tg-text-muted">
                Исследуйте все теги, используемые на сайте, чтобы найти интересующие вас темы
            </p>
        </div>
        
        <div class="tg-tags-stats tg-mb-5">
            <div class="tg-card">
                <div class="tg-card-body">
                    <h3 class="tg-card-title tg-mb-4">
                        <?php echo bloggy_icon('bs', 'tags', '18', 'var(--tg-primary)', 'tg-mr-2'); ?>
                        Статистика тегов
                    </h3>
                    
                    <div class="tg-stats-grid">
                        <div class="tg-stat-card">
                            <div class="tg-stat-icon" style="background: rgba(43, 82, 120, 0.1);">
                                <?php echo bloggy_icon('bs', 'tag-fill', '18', 'var(--tg-primary)'); ?>
                            </div>
                            <div class="tg-stat-content">
                                <span class="tg-stat-label">Всего тегов</span>
                                <span class="tg-stat-value"><?php echo count($tags); ?></span>
                            </div>
                        </div>
                        
                        <?php 
                        $mostPopularTag = null;
                        $maxPostsCount = 0;
                        
                        foreach ($tags as $tag) {
                            if ($tag['posts_count'] > $maxPostsCount) {
                                $maxPostsCount = $tag['posts_count'];
                                $mostPopularTag = $tag;
                            }
                        }
                        ?>
                        
                        <?php if ($mostPopularTag) { ?>
                        <a href="<?php echo BASE_URL; ?>/tag/<?php echo html($mostPopularTag['slug']); ?>" class="tg-stat-card" style="text-decoration: none;">
                            <div class="tg-stat-icon" style="background: rgba(220, 53, 69, 0.1);">
                                <?php echo bloggy_icon('bs', 'fire', '18', '#dc3545'); ?>
                            </div>
                            <div class="tg-stat-content">
                                <span class="tg-stat-label">Популярный тег</span>
                                <span class="tg-stat-value"><?php echo html($tagPrefix); ?><?php echo html($mostPopularTag['name']); ?></span>
                            </div>
                        </a>
                        <?php } ?>
                        
                        <div class="tg-stat-card">
                            <div class="tg-stat-icon" style="background: rgba(43, 82, 120, 0.1);">
                                <?php echo bloggy_icon('bs', 'newspaper', '18', 'var(--tg-primary)'); ?>
                            </div>
                            <div class="tg-stat-content">
                                <span class="tg-stat-label">Всего постов</span>
                                <span class="tg-stat-value">
                                    <?php 
                                    $totalPostsInTags = 0;
                                    foreach ($tags as $tag) {
                                        $totalPostsInTags += $tag['posts_count'];
                                    }
                                    echo $totalPostsInTags; ?>
                                </span>
                            </div>
                        </div>
                        
                        <a href="<?php echo BASE_URL; ?>/posts" class="tg-stat-card" style="text-decoration: none;">
                            <div class="tg-stat-icon" style="background: rgba(43, 82, 120, 0.1);">
                                <?php echo bloggy_icon('bs', 'grid-3x3-gap', '18', 'var(--tg-primary)'); ?>
                            </div>
                            <div class="tg-stat-content">
                                <span class="tg-stat-label">Все посты</span>
                                <span class="tg-stat-value">Смотреть все</span>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <?php if (!empty($tags)) { ?>
        
        <div class="tg-tags-header tg-mb-4">
            <h2 class="tg-section-title">
                Все теги
                <span class="tg-section-title-count">(<?php echo count($tags); ?>)</span>
            </h2>
        </div>
        
        <div class="row">
            <?php foreach ($tags as $index => $tag) { 
                $bgColor = $colorPalette[$index % count($colorPalette)];
                $tagImage = '';
                
                if (!empty($tag['image'])) {
                    $tagImage = BASE_URL . '/uploads/tags/' . html($tag['image']);
                } elseif (!empty($defaultTagImage)) {
                    $tagImage = BASE_URL . '/uploads/settings/tags/' . html($defaultTagImage);
                }
            ?>
            <div class="col-lg-4 col-md-6 tg-mb-4">
                <div class="tg-card tg-tag-card h-100">
                    <div class="tg-card-body">
                        <div class="d-flex align-items-start tg-mb-3">
                            <div class="tg-mr-3">
                                <?php if ($tagImage) { ?>
                                <div class="rounded-circle overflow-hidden" style="width: 50px; height: 50px;">
                                    <img src="<?php echo $tagImage; ?>" 
                                         alt="<?php echo html($tag['name']); ?>" 
                                         class="w-100 h-100" style="object-fit: cover;">
                                </div>
                                <?php } else { ?>
                                <div class="rounded-circle d-flex align-items-center justify-content-center" 
                                     style="width: 50px; height: 50px; background-color: <?php echo $bgColor; ?>20;">
                                    <?php echo bloggy_icon('bs', 'tag', '22', $bgColor); ?>
                                </div>
                                <?php } ?>
                            </div>
                            
                            <div class="flex-grow-1">
                                <h5 class="tg-card-title tg-mb-1">
                                    <a href="<?php echo BASE_URL; ?>/tag/<?php echo html($tag['slug']); ?>" 
                                       class="text-decoration-none text-dark">
                                        <?php echo html($tagPrefix); ?><?php echo html($tag['name']); ?>
                                    </a>
                                </h5>
                                <div class="tg-text-muted small">
                                    <span class="d-flex align-items-center">
                                        <?php echo bloggy_icon('bs', 'file-text', '12', 'currentColor', 'tg-mr-1'); ?>
                                        <?php echo $tag['posts_count']; ?> 
                                        <?php echo plural_form($tag['posts_count'], ['пост', 'поста', 'постов']); ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <?php if (!empty($tag['description'])) { ?>
                        <div class="tg-card-text tg-text-muted small tg-mb-3" style="line-height: 1.5;">
                            <?php echo html(mb_strimwidth($tag['description'], 0, 120, '...')); ?>
                        </div>
                        <?php } ?>
                        
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="tg-text-muted small">
                                <?php if (!empty($tag['created_at'])) { ?>
                                Добавлен <?php echo date('d.m.Y', strtotime($tag['created_at'])); ?>
                                <?php } ?>
                            </span>
                            
                            <a href="<?php echo BASE_URL; ?>/tag/<?php echo html($tag['slug']); ?>" 
                               class="tg-btn tg-btn-sm tg-btn-outline">
                                Смотреть
                                <?php echo bloggy_icon('bs', 'arrow-right', '14', 'currentColor', 'tg-ml-1'); ?>
                            </a>
                        </div>
                    </div>
                    
                    <div class="tg-card-footer bg-transparent d-flex flex-wrap gap-1">
                        <span class="tg-badge">
                            <?php echo $tag['posts_count']; ?> постов
                        </span>
                        <?php if (!empty($tag['updated_at'])) { ?>
                        <span class="tg-badge">
                            Обновлён <?php echo date('d.m', strtotime($tag['updated_at'])); ?>
                        </span>
                        <?php } ?>
                    </div>
                </div>
            </div>
            <?php } ?>
        </div>
        
        <?php if (!empty($pagination) && $pagination['total_pages'] > 1) { ?>
        <div class="tg-pagination tg-mt-5 tg-text-center">
            <?php if ($pagination['current_page'] < $pagination['total_pages']) { ?>
            <a href="<?php echo BASE_URL; ?>/tags?page=<?php echo $pagination['current_page'] + 1; ?>" class="tg-btn tg-btn-outline tg-btn-lg">
                <?php echo bloggy_icon('bs', 'arrow-down', '16', 'currentColor', 'tg-mr-1'); ?>
                Показать еще
            </a>
            <?php } else { ?>
            <div class="tg-text-muted tg-py-3">
                Вы просмотрели все теги
            </div>
            <?php } ?>
        </div>
        <?php } ?>
        
        <?php } else { ?>

        <div class="tg-empty-state">
            <div class="tg-empty-state-icon">
                <?php echo bloggy_icon('bs', 'tags', '48', 'var(--tg-text-secondary)'); ?>
            </div>
            <h3 class="tg-empty-state-title">Теги не найдены</h3>
            <p class="tg-empty-state-text tg-text-muted">
                Пока на сайте нет тегов или они не назначены постам.
            </p>
            <div class="tg-empty-actions">
                <a href="<?php echo BASE_URL; ?>/posts" class="tg-btn tg-btn-primary">
                    <?php echo bloggy_icon('bs', 'newspaper', '16', 'currentColor', 'tg-mr-1'); ?>
                    Все посты
                </a>
                <a href="<?php echo BASE_URL; ?>/categories" class="tg-btn tg-btn-outline tg-ml-2">
                    <?php echo bloggy_icon('bs', 'folder', '16', 'currentColor', 'tg-mr-1'); ?>
                    Категории
                </a>
            </div>
        </div>
        
        <?php } ?>
        
    </div>
</div>