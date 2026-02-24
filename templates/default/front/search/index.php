<?php
/**
 * Страница поиска
 */
?>

<div class="tg-search-page">
    <div class="tg-container">
        <!-- Хлебные крошки -->
        <div class="tg-breadcrumbs tg-mb-4">
            <nav class="tg-breadcrumbs-nav">
                <a href="<?php echo BASE_URL; ?>/" class="tg-breadcrumb-item">
                    <?php echo bloggy_icon('bs', 'house', '14', 'currentColor', 'tg-mr-1'); ?>
                    Главная
                </a>
                <span class="tg-breadcrumb-sep">/</span>
                
                <?php if (!empty($query)) { ?>
                <a href="<?php echo BASE_URL; ?>/search" class="tg-breadcrumb-item">Поиск</a>
                <span class="tg-breadcrumb-sep">/</span>
                <span class="tg-breadcrumb-item tg-active"><?php echo html(mb_strimwidth($query, 0, 50, '...')); ?></span>
                <?php } else { ?>
                <span class="tg-breadcrumb-item tg-active">Поиск</span>
                <?php } ?>
            </nav>
        </div>
        
        <!-- Заголовок страницы -->
        <div class="tg-page-header tg-mb-4">
            <h1 class="tg-page-title">
                <?php echo bloggy_icon('bs', 'search', '24', 'var(--tg-primary)', 'tg-mr-2'); ?>
                Поиск по сайту
            </h1>
            <p class="tg-page-description tg-text-muted">
                Найдите интересующие вас публикации по ключевым словам
            </p>
        </div>
        
        <!-- Форма поиска -->
        <div class="tg-search-form-container tg-mb-5">
            <div class="tg-card">
                <div class="tg-card-body">
                    <form action="<?php echo BASE_URL; ?>/search" method="GET" class="tg-search-form">
                        <div class="tg-search-wrapper">
                            <div class="tg-search-input-wrapper">
                                <?php echo bloggy_icon('bs', 'search', '18', 'var(--tg-text-secondary)', 'tg-search-icon'); ?>
                                <input type="text" 
                                       name="q" 
                                       class="tg-search-input" 
                                       placeholder="Введите поисковый запрос..."
                                       value="<?php echo html($query ?? ''); ?>"
                                       autocomplete="off"
                                       autofocus>
                                <?php if (!empty($query)) { ?>
                                <button type="button" class="tg-search-clear" onclick="window.location.href='<?php echo BASE_URL; ?>/search'">
                                    <?php echo bloggy_icon('bs', 'x', '16', 'currentColor'); ?>
                                </button>
                                <?php } ?>
                            </div>
                            <button type="submit" class="tg-btn tg-btn-primary tg-search-submit">
                                Найти
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <?php if (isset($error)) { ?>
        <!-- Ошибка поиска -->
        <div class="tg-alert tg-alert-error tg-mb-4">
            <div class="tg-alert-icon">
                <?php echo bloggy_icon('bs', 'exclamation-triangle', '20', '#dc3545'); ?>
            </div>
            <div class="tg-alert-content">
                <strong>Ошибка</strong>
                <p><?php echo html($error); ?></p>
            </div>
        </div>
        <?php } ?>
        
        <?php if (empty($query)) { ?>
        <!-- Пустой поиск - популярные запросы и предложения -->
        <div class="tg-search-suggestions">
            <div class="row">
                <!-- Популярные запросы -->
                <?php if (!empty($popularQueries) && is_array($popularQueries)) { ?>
                <div class="col-lg-6 tg-mb-4">
                    <div class="tg-card h-100">
                        <div class="tg-card-header">
                            <h3 class="tg-card-title">
                                <?php echo bloggy_icon('bs', 'graph-up', '18', 'var(--tg-primary)', 'tg-mr-2'); ?>
                                Популярные запросы
                            </h3>
                        </div>
                        <div class="tg-card-body">
                            <div class="tg-popular-queries">
                                <?php foreach ($popularQueries as $popularQuery) { 
                                    // Проверяем, что $popularQuery - массив с ключом 'query'
                                    $queryText = is_array($popularQuery) ? ($popularQuery['query'] ?? '') : $popularQuery;
                                    $queryCount = is_array($popularQuery) ? ($popularQuery['count'] ?? 1) : 1;
                                    
                                    if (empty($queryText)) continue;
                                ?>
                                <a href="<?php echo BASE_URL; ?>/search?q=<?php echo urlencode($queryText); ?>" 
                                   class="tg-popular-query-item">
                                    <span class="tg-popular-query-text">
                                        <?php echo bloggy_icon('bs', 'search', '14', 'var(--tg-text-secondary)', 'tg-mr-2'); ?>
                                        <?php echo html($queryText); ?>
                                    </span>
                                    <span class="tg-popular-query-count"><?php echo $queryCount; ?></span>
                                </a>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php } ?>
                
                <!-- Предложения для поиска -->
                <?php if (!empty($suggestedSearches) && is_array($suggestedSearches)) { ?>
                <div class="col-lg-6 tg-mb-4">
                    <div class="tg-card h-100">
                        <div class="tg-card-header">
                            <h3 class="tg-card-title">
                                <?php echo bloggy_icon('bs', 'lightbulb', '18', 'var(--tg-primary)', 'tg-mr-2'); ?>
                                Что ищут сейчас
                            </h3>
                        </div>
                        <div class="tg-card-body">
                            <div class="tg-suggested-searches">
                                <?php foreach ($suggestedSearches as $suggested) { 
                                    // Проверяем, что $suggested - строка или массив
                                    $suggestedText = is_array($suggested) ? ($suggested['query'] ?? '') : $suggested;
                                    
                                    if (empty($suggestedText)) continue;
                                ?>
                                <a href="<?php echo BASE_URL; ?>/search?q=<?php echo urlencode($suggestedText); ?>" 
                                   class="tg-suggested-search-item">
                                    <?php echo bloggy_icon('bs', 'arrow-right', '14', 'var(--tg-primary)', 'tg-mr-2'); ?>
                                    <?php echo html($suggestedText); ?>
                                </a>
                                <?php } ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php } ?>
            </div>
            
            <!-- Быстрые ссылки -->
            <div class="tg-quick-links tg-mt-4">
                <div class="tg-card">
                    <div class="tg-card-body">
                        <div class="tg-quick-links-header tg-mb-3">
                            <h4 class="tg-quick-links-title">
                                <?php echo bloggy_icon('bs', 'link', '16', 'var(--tg-primary)', 'tg-mr-2'); ?>
                                Быстрые ссылки
                            </h4>
                        </div>
                        <div class="tg-quick-links-grid">
                            <a href="<?php echo BASE_URL; ?>/posts" class="tg-quick-link-item">
                                <span class="tg-quick-link-icon">
                                    <?php echo bloggy_icon('bs', 'file-text', '20', 'var(--tg-primary)'); ?>
                                </span>
                                <span class="tg-quick-link-text">Все публикации</span>
                            </a>
                            <a href="<?php echo BASE_URL; ?>/categories" class="tg-quick-link-item">
                                <span class="tg-quick-link-icon">
                                    <?php echo bloggy_icon('bs', 'folder', '20', 'var(--tg-primary)'); ?>
                                </span>
                                <span class="tg-quick-link-text">Категории</span>
                            </a>
                            <a href="<?php echo BASE_URL; ?>/tags" class="tg-quick-link-item">
                                <span class="tg-quick-link-icon">
                                    <?php echo bloggy_icon('bs', 'tags', '20', 'var(--tg-primary)'); ?>
                                </span>
                                <span class="tg-quick-link-text">Теги</span>
                            </a>
                            <a href="<?php echo BASE_URL; ?>/archive" class="tg-quick-link-item">
                                <span class="tg-quick-link-icon">
                                    <?php echo bloggy_icon('bs', 'archive', '20', 'var(--tg-primary)'); ?>
                                </span>
                                <span class="tg-quick-link-text">Архив</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <?php } elseif (!empty($results) && $total > 0) { ?>
        <!-- Результаты поиска -->
        <div class="tg-search-results">
            <!-- Статистика поиска -->
            <div class="tg-search-stats tg-mb-4">
                <div class="tg-card">
                    <div class="tg-card-body">
                        <div class="tg-search-stats-content">
                            <div class="tg-search-stats-info">
                                <?php echo bloggy_icon('bs', 'search', '16', 'var(--tg-primary)', 'tg-mr-2'); ?>
                                <span>
                                    По запросу <strong>«<?php echo html($query); ?>»</strong> 
                                    найдено <strong><?php echo $total; ?></strong> 
                                    <?php echo plural_form($total, ['результат', 'результата', 'результатов']); ?>
                                </span>
                            </div>
                            <?php if (!empty($suggestedSearches) && is_array($suggestedSearches)) { ?>
                            <div class="tg-search-suggestions-links">
                                <span class="tg-text-muted tg-mr-2">Возможно вы искали:</span>
                                <?php 
                                $suggestedCount = 0;
                                foreach ($suggestedSearches as $suggested) { 
                                    if ($suggestedCount >= 3) break;
                                    
                                    $suggestedText = is_array($suggested) ? ($suggested['query'] ?? '') : $suggested;
                                    if (empty($suggestedText)) continue;
                                    
                                    $suggestedCount++;
                                ?>
                                <a href="<?php echo BASE_URL; ?>/search?q=<?php echo urlencode($suggestedText); ?>" 
                                   class="tg-suggestion-link">
                                    <?php echo html($suggestedText); ?>
                                </a>
                                <?php } ?>
                            </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Список результатов -->
            <div class="tg-results-list">
                <?php foreach ($results as $item) { 
                    // Определяем тип контента
                    $contentType = $item['content_type'] ?? $item['type'] ?? 'post';
                    
                    // Только для постов выводим изображение
                    $featuredImage = null;
                    if ($contentType == 'post' && !empty($item['featured_image'])) {
                        $featuredImage = BASE_URL . '/uploads/images/' . html($item['featured_image']);
                    }
                    
                    $isPasswordProtected = isset($item['password_protected']) && $item['password_protected'] == 1;
                    
                    // Подсветка совпадений в тексте
                    $title = html($item['title'] ?? '');
                    $description = html($item['description'] ?? $item['short_description'] ?? '');
                    
                    if (!empty($query)) {
                        $words = explode(' ', $query);
                        foreach ($words as $word) {
                            if (mb_strlen($word) > 2) {
                                $title = preg_replace('/(' . preg_quote($word, '/') . ')/iu', '<mark>$1</mark>', $title);
                                $description = preg_replace('/(' . preg_quote($word, '/') . ')/iu', '<mark>$1</mark>', $description);
                            }
                        }
                    }
                    
                    // Определяем URL и дополнительные параметры в зависимости от типа
                    switch ($contentType) {
                        case 'post':
                            $url = BASE_URL . '/post/' . ($item['slug'] ?? '');
                            $typeLabel = 'Пост';
                            $typeClass = 'primary';
                            $metaInfo = '';
                            
                            if (!empty($item['category_name'])) {
                                $metaInfo .= '<a href="' . BASE_URL . '/category/' . html($item['category_slug'] ?? '') . '" class="tg-search-result-category">' . html($item['category_name']) . '</a>';
                            }
                            
                            $metaInfo .= '<span class="tg-search-result-date">' . 
                                bloggy_icon('bs', 'calendar', '12', 'currentColor', 'tg-mr-1') . 
                                time_ago($item['created_at'] ?? '') . 
                                '</span>';
                            
                            if (!empty($item['views'])) {
                                $metaInfo .= '<span class="tg-search-result-views" title="Просмотры">' . 
                                    bloggy_icon('bs', 'eye', '12', 'currentColor', 'tg-mr-1') . 
                                    $item['views'] . 
                                    '</span>';
                            }
                            
                            $stats = '';
                            if (!empty($item['comments_count'])) {
                                $stats .= '<span title="Комментарии">' . 
                                    bloggy_icon('bs', 'chat-dots', '12', 'currentColor', 'tg-mr-1') . 
                                    $item['comments_count'] . 
                                    '</span>';
                            }
                            break;
                            
                        case 'page':
                            $url = BASE_URL . '/page/' . ($item['slug'] ?? '');
                            $typeLabel = 'Страница';
                            $typeClass = 'info';
                            $metaInfo = '<span class="tg-search-result-date">' . 
                                bloggy_icon('bs', 'calendar', '12', 'currentColor', 'tg-mr-1') . 
                                time_ago($item['created_at'] ?? '') . 
                                '</span>';
                            $stats = '';
                            break;
                            
                        case 'category':
                            $url = BASE_URL . '/category/' . ($item['slug'] ?? '');
                            $typeLabel = 'Категория';
                            $typeClass = 'success';
                            $metaInfo = '<span class="tg-search-result-count">' . 
                                bloggy_icon('bs', 'folder', '12', 'currentColor', 'tg-mr-1') . 
                                ($item['posts_count'] ?? 0) . ' постов' . 
                                '</span>';
                            $stats = '';
                            break;
                            
                        case 'tag':
                            $url = BASE_URL . '/tag/' . ($item['slug'] ?? '');
                            $typeLabel = 'Тег';
                            $typeClass = 'warning';
                            $metaInfo = '<span class="tg-search-result-count">' . 
                                bloggy_icon('bs', 'tag', '12', 'currentColor', 'tg-mr-1') . 
                                ($item['posts_count'] ?? 0) . ' постов' . 
                                '</span>';
                            $stats = '';
                            break;
                            
                        case 'user':
                            $url = BASE_URL . '/user/' . ($item['slug'] ?? '');
                            $typeLabel = 'Пользователь';
                            $typeClass = 'secondary';
                            $metaInfo = '<span class="tg-search-result-date">' . 
                                bloggy_icon('bs', 'person', '12', 'currentColor', 'tg-mr-1') . 
                                'Зарегистрирован: ' . date('d.m.Y', strtotime($item['registered_at'] ?? $item['created_at'] ?? '')) . 
                                '</span>';
                            if (!empty($item['posts_count'])) {
                                $metaInfo .= '<span class="tg-search-result-count">' . 
                                    bloggy_icon('bs', 'file-text', '12', 'currentColor', 'tg-mr-1') . 
                                    $item['posts_count'] . ' постов' . 
                                    '</span>';
                            }
                            $stats = '';
                            break;
                            
                        default:
                            $url = '#';
                            $typeLabel = 'Пост';
                            $typeClass = 'primary';
                            $metaInfo = '';
                            $stats = '';
                    }
                ?>
                <div class="tg-search-result-item tg-mb-3">
                    <div class="tg-card">
                        <div class="tg-card-body">
                            <div class="tg-search-result-content">
                                <!-- Изображение только для постов -->
                                <?php if ($contentType == 'post' && $featuredImage) { ?>
                                <a href="<?php echo $url; ?>" class="tg-search-result-image">
                                    <img src="<?php echo $featuredImage; ?>" 
                                         alt="<?php echo html($item['title'] ?? ''); ?>"
                                         loading="lazy">
                                </a>
                                <?php } ?>
                                
                                <div class="tg-search-result-info <?php echo ($contentType != 'post' || !$featuredImage) ? 'tg-full-width' : ''; ?>">
                                    <div class="tg-search-result-meta-top">
                                        <span class="tg-badge tg-badge-<?php echo $typeClass; ?>">
                                            <?php echo $typeLabel; ?>
                                        </span>
                                        <?php echo $metaInfo; ?>
                                    </div>
                                    
                                    <h2 class="tg-search-result-title">
                                        <a href="<?php echo $url; ?>">
                                            <?php echo $title; ?>
                                        </a>
                                        <?php if ($contentType == 'post' && $isPasswordProtected) { ?>
                                            <span class="tg-post-lock" title="Защищено паролем">
                                                <?php echo bloggy_icon('bs', 'lock-fill', '14', 'currentColor'); ?>
                                            </span>
                                        <?php } ?>
                                    </h2>
                                    
                                    <?php if (!empty($description)) { ?>
                                    <p class="tg-search-result-excerpt">
                                        <?php echo mb_strimwidth($description, 0, 200, '...'); ?>
                                    </p>
                                    <?php } ?>
                                    
                                    <div class="tg-search-result-footer">
                                        <div class="tg-search-result-meta">
                                            <?php if ($contentType == 'post' && !empty($item['tags'])) { ?>
                                            <div class="tg-search-result-tags">
                                                <?php foreach (array_slice($item['tags'], 0, 3) as $tag) { ?>
                                                <a href="<?php echo BASE_URL; ?>/tag/<?php echo html($tag['slug']); ?>" 
                                                   class="tg-tag">
                                                    #<?php echo html($tag['name']); ?>
                                                </a>
                                                <?php } ?>
                                                <?php if (count($item['tags']) > 3) { ?>
                                                <span class="tg-tag-more">+<?php echo count($item['tags']) - 3; ?></span>
                                                <?php } ?>
                                            </div>
                                            <?php } ?>
                                            
                                            <?php if (!empty($stats)) { ?>
                                            <div class="tg-search-result-stats">
                                                <?php echo $stats; ?>
                                            </div>
                                            <?php } ?>
                                        </div>
                                        
                                        <a href="<?php echo $url; ?>" 
                                           class="tg-btn tg-btn-sm tg-btn-outline">
                                            <?php echo $contentType == 'post' ? 'Читать' : 'Перейти'; ?>
                                            <?php echo bloggy_icon('bs', 'arrow-right', '12', 'currentColor', 'tg-ml-1'); ?>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php } ?>
            </div>
            
            <!-- Пагинация -->
            <?php if ($pages > 1) { ?>
            <div class="tg-pagination tg-mt-5">
                <div class="tg-pagination-info tg-text-center tg-mb-3 tg-text-muted">
                    Страница <?php echo $current_page; ?> из <?php echo $pages; ?>
                </div>
                
                <div class="tg-pagination-links">
                    <?php if ($current_page > 1) { ?>
                    <a href="<?php echo BASE_URL; ?>/search?q=<?php echo urlencode($query); ?>&page=<?php echo $current_page - 1; ?>" 
                       class="tg-pagination-prev">
                        <?php echo bloggy_icon('bs', 'arrow-left', '16', 'currentColor'); ?>
                    </a>
                    <?php } ?>
                    
                    <?php
                    $start = max(1, $current_page - 2);
                    $end = min($pages, $current_page + 2);
                    
                    for ($i = $start; $i <= $end; $i++) { 
                    ?>
                    <a href="<?php echo BASE_URL; ?>/search?q=<?php echo urlencode($query); ?>&page=<?php echo $i; ?>" 
                       class="tg-pagination-link <?php echo $i == $current_page ? 'tg-active' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                    <?php } ?>
                    
                    <?php if ($current_page < $pages) { ?>
                    <a href="<?php echo BASE_URL; ?>/search?q=<?php echo urlencode($query); ?>&page=<?php echo $current_page + 1; ?>" 
                       class="tg-pagination-next">
                        <?php echo bloggy_icon('bs', 'arrow-right', '16', 'currentColor'); ?>
                    </a>
                    <?php } ?>
                </div>
            </div>
            <?php } ?>
            
        </div>
        
        <?php } elseif (!empty($query)) { ?>
        <!-- Ничего не найдено -->
        <div class="tg-search-empty">
            <div class="tg-card">
                <div class="tg-card-body tg-text-center tg-py-5">
                    <div class="tg-empty-state-icon tg-mb-4">
                        <?php echo bloggy_icon('bs', 'search', '48', 'var(--tg-text-secondary)'); ?>
                    </div>
                    <h3 class="tg-empty-state-title">Ничего не найдено</h3>
                    <p class="tg-empty-state-text tg-text-muted">
                        По запросу <strong>«<?php echo html($query); ?>»</strong> ничего не найдено.
                        Попробуйте изменить запрос или выбрать другие ключевые слова.
                    </p>
                    
                    <?php if (!empty($suggestedSearches) && is_array($suggestedSearches)) { ?>
                    <div class="tg-search-alternatives tg-mt-4">
                        <h4 class="tg-alternatives-title tg-mb-3">Возможно вы искали:</h4>
                        <div class="tg-alternatives-list">
                            <?php 
                            $suggestedCount = 0;
                            foreach ($suggestedSearches as $suggested) { 
                                if ($suggestedCount >= 6) break;
                                
                                $suggestedText = is_array($suggested) ? ($suggested['query'] ?? '') : $suggested;
                                if (empty($suggestedText)) continue;
                                
                                $suggestedCount++;
                            ?>
                            <a href="<?php echo BASE_URL; ?>/search?q=<?php echo urlencode($suggestedText); ?>" 
                               class="tg-alternative-item">
                                <?php echo html($suggestedText); ?>
                            </a>
                            <?php } ?>
                        </div>
                    </div>
                    <?php } ?>
                    
                    <div class="tg-empty-actions tg-mt-4">
                        <a href="<?php echo BASE_URL; ?>/posts" class="tg-btn tg-btn-primary">
                            <?php echo bloggy_icon('bs', 'file-text', '16', 'currentColor', 'tg-mr-1'); ?>
                            Все публикации
                        </a>
                        <a href="<?php echo BASE_URL; ?>/search" class="tg-btn tg-btn-outline tg-ml-2">
                            <?php echo bloggy_icon('bs', 'search', '16', 'currentColor', 'tg-mr-1'); ?>
                            Новый поиск
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php } ?>
        
    </div>
</div>

<!-- Дополнительные стили для страницы поиска -->
<style>
.tg-search-wrapper {
    display: flex;
    gap: 12px;
    align-items: center;
}

.tg-search-input-wrapper {
    flex: 1;
    position: relative;
}

.tg-search-icon {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--tg-text-secondary);
    pointer-events: none;
}

.tg-search-input {
    width: 100%;
    padding: 12px 12px 12px 40px;
    border: 1px solid var(--tg-border);
    border-radius: var(--tg-radius-md);
    background: var(--tg-surface);
    color: var(--tg-text);
    font-size: 16px;
    transition: var(--tg-transition);
}

.tg-search-input:focus {
    outline: none;
    border-color: var(--tg-primary);
    box-shadow: 0 0 0 3px rgba(43, 82, 120, 0.1);
}

.tg-search-clear {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    padding: 4px;
    color: var(--tg-text-secondary);
    cursor: pointer;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: var(--tg-transition);
}

.tg-search-clear:hover {
    background: var(--tg-hover);
    color: var(--tg-primary);
}

.tg-search-submit {
    padding: 12px 24px;
    font-size: 16px;
}

.tg-popular-queries {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.tg-popular-query-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 8px 12px;
    background: var(--tg-bg);
    border-radius: var(--tg-radius-md);
    text-decoration: none;
    transition: var(--tg-transition);
}

.tg-popular-query-item:hover {
    background: var(--tg-hover);
}

.tg-popular-query-text {
    display: flex;
    align-items: center;
    color: var(--tg-text);
    font-size: 14px;
}

.tg-popular-query-count {
    font-size: 12px;
    color: var(--tg-text-secondary);
    background: var(--tg-surface);
    padding: 2px 8px;
    border-radius: 12px;
}

.tg-suggested-searches {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.tg-suggested-search-item {
    display: flex;
    align-items: center;
    padding: 8px 12px;
    background: var(--tg-bg);
    border-radius: var(--tg-radius-md);
    text-decoration: none;
    color: var(--tg-text);
    font-size: 14px;
    transition: var(--tg-transition);
}

.tg-suggested-search-item:hover {
    background: var(--tg-hover);
    color: var(--tg-primary);
}

.tg-quick-links-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 12px;
}

@media (max-width: 768px) {
    .tg-quick-links-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

.tg-quick-link-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 16px;
    background: var(--tg-bg);
    border-radius: var(--tg-radius-md);
    text-decoration: none;
    transition: var(--tg-transition);
    gap: 8px;
}

.tg-quick-link-item:hover {
    background: var(--tg-hover);
    transform: translateY(-2px);
}

.tg-quick-link-icon {
    width: 48px;
    height: 48px;
    background: rgba(43, 82, 120, 0.1);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.tg-quick-link-text {
    font-size: 13px;
    color: var(--tg-text);
    font-weight: 500;
}

.tg-search-stats-content {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 16px;
}

.tg-search-stats-info {
    display: flex;
    align-items: center;
    font-size: 15px;
}

.tg-search-suggestions-links {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 8px;
}

.tg-suggestion-link {
    padding: 4px 12px;
    background: var(--tg-bg);
    border-radius: 20px;
    color: var(--tg-primary);
    text-decoration: none;
    font-size: 13px;
    transition: var(--tg-transition);
}

.tg-suggestion-link:hover {
    background: var(--tg-hover);
}

.tg-search-result-content {
    display: flex;
    gap: 20px;
}

@media (max-width: 768px) {
    .tg-search-result-content {
        flex-direction: column;
    }
}

.tg-search-result-image {
    flex-shrink: 0;
    width: 180px;
    height: 120px;
    border-radius: var(--tg-radius-md);
    overflow: hidden;
    text-decoration: none;
}

@media (max-width: 768px) {
    .tg-search-result-image {
        width: 100%;
        height: 160px;
    }
}

.tg-search-result-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.tg-search-result-item:hover .tg-search-result-image img {
    transform: scale(1.05);
}

.tg-search-result-info {
    flex: 1;
    min-width: 0;
}

.tg-search-result-info.tg-full-width {
    width: 100%;
}

.tg-search-result-meta-top {
    display: flex;
    align-items: center;
    gap: 16px;
    margin-bottom: 8px;
    font-size: 13px;
}

.tg-search-result-category {
    color: var(--tg-primary);
    text-decoration: none;
    font-weight: 500;
}

.tg-search-result-category:hover {
    text-decoration: underline;
}

.tg-search-result-date {
    color: var(--tg-text-secondary);
}

.tg-search-result-title {
    font-size: 18px;
    font-weight: 600;
    margin: 0 0 8px 0;
    display: flex;
    align-items: center;
    gap: 8px;
}

.tg-search-result-title a {
    color: var(--tg-text);
    text-decoration: none;
    transition: var(--tg-transition);
}

.tg-search-result-title a:hover {
    color: var(--tg-primary);
}

.tg-search-result-title mark {
    background: rgba(255, 193, 7, 0.3);
    color: inherit;
    padding: 0 2px;
    border-radius: 2px;
}

.tg-search-result-excerpt {
    color: var(--tg-text-secondary);
    font-size: 14px;
    line-height: 1.6;
    margin-bottom: 12px;
}

.tg-search-result-excerpt mark {
    background: rgba(255, 193, 7, 0.3);
    color: inherit;
    padding: 0 2px;
    border-radius: 2px;
}

.tg-search-result-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 16px;
    margin-top: 12px;
    padding-top: 12px;
    border-top: 1px solid var(--tg-border);
}

.tg-search-result-meta {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 16px;
}

.tg-search-result-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 4px;
}

.tg-tag-more {
    padding: 4px 8px;
    background: var(--tg-bg);
    border-radius: 20px;
    font-size: 11px;
    color: var(--tg-text-secondary);
}

.tg-search-result-stats {
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 12px;
    color: var(--tg-text-secondary);
}

.tg-search-result-stats span {
    display: flex;
    align-items: center;
}

.tg-pagination-links {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.tg-pagination-link {
    display: flex;
    align-items: center;
    justify-content: center;
    min-width: 36px;
    height: 36px;
    padding: 0 8px;
    border: 1px solid var(--tg-border);
    border-radius: var(--tg-radius-sm);
    color: var(--tg-text);
    text-decoration: none;
    transition: var(--tg-transition);
    font-size: 14px;
}

.tg-pagination-link:hover,
.tg-pagination-link.tg-active {
    background: var(--tg-primary);
    color: white;
    border-color: var(--tg-primary);
}

.tg-pagination-prev,
.tg-pagination-next {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 36px;
    height: 36px;
    border: 1px solid var(--tg-border);
    border-radius: var(--tg-radius-sm);
    color: var(--tg-text);
    text-decoration: none;
    transition: var(--tg-transition);
}

.tg-pagination-prev:hover,
.tg-pagination-next:hover {
    background: var(--tg-hover);
    color: var(--tg-primary);
    border-color: var(--tg-primary);
}

.tg-search-alternatives {
    max-width: 500px;
    margin: 0 auto;
}

.tg-alternatives-title {
    font-size: 16px;
    font-weight: 500;
    color: var(--tg-text);
}

.tg-alternatives-list {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 8px;
}

.tg-alternative-item {
    padding: 6px 16px;
    background: var(--tg-bg);
    border-radius: 20px;
    color: var(--tg-primary);
    text-decoration: none;
    font-size: 14px;
    transition: var(--tg-transition);
}

.tg-alternative-item:hover {
    background: var(--tg-hover);
}

.tg-py-5 {
    padding-top: 48px;
    padding-bottom: 48px;
}

.tg-ml-2 {
    margin-left: 8px;
}
</style>