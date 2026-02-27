<?php
/**
 * Template Name: Архив блога
 */

$monthNames = [
    1 => 'Январь', 2 => 'Февраль', 3 => 'Март', 4 => 'Апрель',
    5 => 'Май', 6 => 'Июнь', 7 => 'Июль', 8 => 'Август',
    9 => 'Сентябрь', 10 => 'Октябрь', 11 => 'Ноябрь', 12 => 'Декабрь'
];
?>

<div class="tg-archive-page">
    <div class="tg-container">
        
        <div class="tg-breadcrumbs tg-mb-4">
            <nav class="tg-breadcrumbs-nav">
                <a href="<?php echo BASE_URL; ?>/" class="tg-breadcrumb-item">
                    <?php echo bloggy_icon('bs', 'house', '14', 'currentColor', 'tg-mr-1'); ?>
                    Главная
                </a>
                <span class="tg-breadcrumb-sep">/</span>
                <span class="tg-breadcrumb-item tg-active">Архив</span>
            </nav>
        </div>
        
        <div class="tg-archive-header tg-mb-4">
            <div class="tg-archive-header-left">
                <div class="tg-archive-icon">
                    <?php echo bloggy_icon('bs', 'archive', '24', 'var(--tg-primary)'); ?>
                </div>
                <div class="tg-archive-info">
                    <h1 class="tg-archive-title">Архив публикаций</h1>
                    <p class="tg-archive-subtitle tg-text-muted">
                        Хронология всех постов блога
                    </p>
                </div>
            </div>
        </div>
        
        <?php
        $totalYears = count($postsByMonth ?? []);
        $totalMonths = 0;
        $totalPosts = 0;
        $firstYear = null;
        $lastYear = null;
        
        if (!empty($postsByMonth)) {
            $years = array_keys($postsByMonth);
            if (!empty($years)) {
                $firstYear = min($years);
                $lastYear = max($years);
            }
            
            foreach ($postsByMonth as $year => $months) {
                $totalMonths += count($months);
                foreach ($months as $month => $posts) {
                    $totalPosts += count($posts);
                }
            }
        }
        ?>
        
        <div class="tg-stats-grid tg-mb-4">
            <div class="tg-stat-card">
                <div class="tg-stat-icon" style="background: rgba(43, 82, 120, 0.1);">
                    <?php echo bloggy_icon('bs', 'calendar-range', '20', 'var(--tg-primary)'); ?>
                </div>
                <div class="tg-stat-content">
                    <span class="tg-stat-label">Период</span>
                    <span class="tg-stat-value">
                        <?php if ($firstYear && $lastYear): ?>
                            <?php echo $firstYear ?> — <?php echo $lastYear ?>
                        <?php else: ?>
                            Нет данных
                        <?php endif; ?>
                    </span>
                </div>
            </div>
            
            <div class="tg-stat-card">
                <div class="tg-stat-icon" style="background: rgba(43, 82, 120, 0.1);">
                    <?php echo bloggy_icon('bs', 'calendar-month', '20', 'var(--tg-primary)'); ?>
                </div>
                <div class="tg-stat-content">
                    <span class="tg-stat-label">Всего лет</span>
                    <span class="tg-stat-value"><?php echo $totalYears; ?> <?php echo plural_form($totalYears, ['год', 'года', 'лет']); ?></span>
                </div>
            </div>
            
            <div class="tg-stat-card">
                <div class="tg-stat-icon" style="background: rgba(43, 82, 120, 0.1);">
                    <?php echo bloggy_icon('bs', 'calendar-week', '20', 'var(--tg-primary)'); ?>
                </div>
                <div class="tg-stat-content">
                    <span class="tg-stat-label">Всего месяцев</span>
                    <span class="tg-stat-value"><?php echo $totalMonths; ?> <?php echo plural_form($totalMonths, ['месяц', 'месяца', 'месяцев']); ?></span>
                </div>
            </div>
            
            <div class="tg-stat-card">
                <div class="tg-stat-icon" style="background: rgba(43, 82, 120, 0.1);">
                    <?php echo bloggy_icon('bs', 'file-text', '20', 'var(--tg-primary)'); ?>
                </div>
                <div class="tg-stat-content">
                    <span class="tg-stat-label">Всего постов</span>
                    <span class="tg-stat-value"><?php echo $totalPosts; ?> <?php echo plural_form($totalPosts, ['пост', 'поста', 'постов']); ?></span>
                </div>
            </div>
        </div>
        
        <?php if (!empty($postsByMonth)): ?>
        
        <div class="tg-archive-content">
            
            <?php 
            $yearIndex = 0;
            foreach ($postsByMonth as $year => $months): 
                $yearIndex++;
                $yearPostsCount = 0;
                foreach ($months as $month => $posts) {
                    $yearPostsCount += count($posts);
                }
            ?>
            
            <div class="tg-year-section tg-mb-4">
                <div class="tg-year-header">
                    <h2 class="tg-year-title"><?php echo $year; ?> год</h2>
                    <span class="tg-year-count">
                        <?php echo $yearPostsCount; ?> <?php echo plural_form($yearPostsCount, ['пост', 'поста', 'постов']); ?>
                    </span>
                </div>
                
                <div class="tg-months-grid">
                    <?php 
                    $monthIndex = 0;
                    foreach ($months as $month => $posts): 
                        if (empty($posts)) continue;
                        
                        $monthName = $monthNames[$month] ?? 'Месяц';
                        $monthIndex++;
                    ?>
                    
                    <div class="tg-month-card">
                        <div class="tg-month-header">
                            <div class="tg-month-icon">
                                <?php echo bloggy_icon('bs', 'calendar-month', '18', 'var(--tg-primary)'); ?>
                            </div>
                            <h3 class="tg-month-title"><?php echo $monthName; ?></h3>
                            <span class="tg-month-count"><?php echo count($posts); ?></span>
                        </div>
                        
                        <div class="tg-month-posts" data-month="<?php echo $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT); ?>">
                            <?php foreach ($posts as $index => $post): ?>
                            <div class="tg-archive-post-item <?php echo $index >= 3 ? 'tg-hidden' : ''; ?>">
                                <div class="tg-post-date d-block">
                                    <span class="tg-post-day"><?php echo date('d', strtotime($post['created_at'])); ?></span>
                                    <span class="tg-post-month-short mx-1">
                                        <?php 
                                        $monthNum = date('n', strtotime($post['created_at']));
                                        $monthShort = mb_substr($monthNames[$monthNum], 0, 3, 'UTF-8');
                                        echo $monthShort;
                                        ?>
                                    </span>
                                </div>
                                
                                <div class="tg-post-info">
                                    <h4 class="tg-post-title">
                                        <a href="<?php echo BASE_URL; ?>/post/<?php echo html($post['slug']); ?>">
                                            <?php echo html($post['title']); ?>
                                        </a>
                                    </h4>
                                    
                                    <div class="tg-post-meta">
                                        <?php if (!empty($post['category_name'])): ?>
                                        <a href="<?php echo BASE_URL; ?>/category/<?php echo html($post['category_slug']); ?>" 
                                           class="tg-post-category-link">
                                            <?php echo bloggy_icon('bs', 'folder', '10', 'currentColor', 'tg-mr-1'); ?>
                                            <?php echo html($post['category_name']); ?>
                                        </a>
                                        <?php endif; ?>
                                        
                                        <span class="tg-post-views">
                                            <?php echo bloggy_icon('bs', 'eye', '10', 'currentColor', 'tg-mr-1'); ?>
                                            <?php echo $post['views'] ?? 0; ?>
                                        </span>
                                    </div>
                                </div>
                                
                                <a href="<?php echo BASE_URL; ?>/post/<?php echo html($post['slug']); ?>" class="tg-post-link">
                                    <?php echo bloggy_icon('bs', 'chevron-right', '14', 'currentColor'); ?>
                                </a>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <?php if (count($posts) > 3): ?>
                        <button class="tg-show-more-btn" data-month="<?php echo $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT); ?>">
                            <span>Показать все <?php echo count($posts); ?> постов</span>
                            <?php echo bloggy_icon('bs', 'chevron-down', '14', 'currentColor', 'tg-ml-1'); ?>
                        </button>
                        <?php endif; ?>
                    </div>
                    
                    <?php endforeach; ?>
                </div>
            </div>
            
            <?php endforeach; ?>
            
        </div>
        
        <?php else: ?>
        
        <div class="tg-empty-state">
            <div class="tg-empty-state-icon">
                <?php echo bloggy_icon('bs', 'archive', '48', 'var(--tg-text-secondary)'); ?>
            </div>
            <h3 class="tg-empty-state-title">Архив пуст</h3>
            <p class="tg-empty-state-text tg-text-muted">
                Пока нет опубликованных постов для формирования архива
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
        
        <?php endif; ?>
        
    </div>
</div>