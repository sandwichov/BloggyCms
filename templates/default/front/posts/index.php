<?php
/**
 * Список постов
 */
?>

<div class="tg-posts-page">
    <div class="tg-container">

        <div class="tg-breadcrumbs tg-mb-3">
            <nav class="tg-breadcrumbs-nav">
                <a href="<?php echo BASE_URL; ?>/" class="tg-breadcrumb-item">
                    <?php echo bloggy_icon('bs', 'house', '14', 'currentColor', 'tg-mr-1'); ?>
                    Главная
                </a>
                <span class="tg-breadcrumb-sep">/</span>
                
                <?php 
                if (!empty($_GET['category'])) {
                    $currentCategory = null;
                    foreach ($categories as $cat) {
                        if ($cat['slug'] === $_GET['category']) {
                            $currentCategory = $cat;
                            break;
                        }
                    }
                ?>
                    <a href="<?php echo BASE_URL; ?>/posts" class="tg-breadcrumb-item">Блог</a>
                    <span class="tg-breadcrumb-sep">/</span>
                    <span class="tg-breadcrumb-item tg-active">
                        <?php echo html($currentCategory['name'] ?? $_GET['category']); ?>
                    </span>
                <?php } else { ?>
                    <span class="tg-breadcrumb-item tg-active">Блог</span>
                <?php } ?>
            </nav>
        </div>
        
        <div class="tg-page-header tg-mb-4">
            <h1 class="tg-page-title">Блог</h1>
            <p class="tg-page-subtitle">Последние публикации и обновления</p>
        </div>
        
        <div class="tg-categories-filter tg-mb-4">
            <div class="tg-filter-header">
                <span class="tg-filter-title">Категории</span>
                <span class="tg-filter-count"><?php echo count($categories ?? []); ?></span>
            </div>
            
            <div class="tg-categories-grid">
                <a href="<?php echo BASE_URL; ?>/posts" 
                   class="tg-category-card <?php echo empty($_GET['category']) ? 'tg-active' : ''; ?>">
                    <div class="tg-category-icon">
                        <?php echo bloggy_icon('bs', 'grid-3x3-gap', '18', 'currentColor'); ?>
                    </div>
                    <div class="tg-category-info">
                        <span class="tg-category-name">Все публикации</span>
                        <span class="tg-category-meta"><?php echo $total_posts ?? 'Все'; ?> публикаций</span>
                    </div>
                </a>

                <?php foreach ($categories as $category) { 
                    $isActive = ($_GET['category'] ?? '') === $category['slug'];
                    $categoryIcon = 'folder';
                    $categoryName = strtolower($category['name']);
                ?>
                <a href="<?php echo BASE_URL; ?>/category/<?php echo html($category['slug']); ?>" 
                   class="tg-category-card <?php echo $isActive ? 'tg-active' : ''; ?>">
                    <div class="tg-category-icon">
                        <?php if (!empty($category['image'])) { ?>
                            <img src="<?php echo BASE_URL . '/uploads/images/' . html($category['image']); ?>" 
                                 alt="<?php echo html($category['name']); ?>">
                        <?php } else { ?>
                            <?php echo bloggy_icon('bs', $categoryIcon, '18', 'currentColor'); ?>
                        <?php } ?>
                    </div>
                    <div class="tg-category-info">
                        <span class="tg-category-name">
                            <?php echo html($category['name']); ?>
                            <?php if ($category['password_protected'] == 1) { ?>
                                <?php echo bloggy_icon('bs', 'lock', '12', 'currentColor', 'tg-ml-1'); ?>
                            <?php } ?>
                        </span>
                        <span class="tg-category-meta"><?php echo $category['posts_count'] ?? 0; ?> публикаций</span>
                    </div>
                </a>
                <?php } ?>
            </div>
        </div>
        
        <div class="tg-posts-list">
            <?php if (!empty($posts)) { ?>
                <?php foreach ($posts as $post) { 
                    $featuredImage = $post['featured_image'] 
                        ? BASE_URL . '/uploads/images/' . html($post['featured_image']) 
                        : null;
                    $isPasswordProtected = isset($post['password_protected']) && $post['password_protected'] == 1;
                ?>
                <article class="tg-post-card">
                    <?php if ($featuredImage) { ?>
                    <a href="<?php echo BASE_URL . '/post/' . html($post['slug']); ?>" class="tg-post-image-link">
                        <div class="tg-post-image">
                            <img src="<?php echo $featuredImage; ?>" 
                                 alt="<?php echo html($post['title']); ?>"
                                 loading="lazy">
                        </div>
                    </a>
                    <?php } ?>
                    
                    <div class="tg-post-content">
                        <div class="tg-post-meta-top">
                            <?php if (!empty($post['category_name'])) { ?>
                            <a href="<?php echo BASE_URL; ?>/category/<?php echo html($post['category_slug']); ?>" 
                               class="tg-post-category">
                                <?php echo html($post['category_name']); ?>
                            </a>
                            <?php } ?>
                            
                            <span class="tg-post-date">
                                <?php echo bloggy_icon('bs', 'calendar', '12', 'currentColor', 'tg-mr-1'); ?>
                                <?php echo time_ago($post['created_at']); ?>
                            </span>
                        </div>
                        
                        <h2 class="tg-post-title">
                            <a href="<?php echo BASE_URL . '/post/' . html($post['slug']); ?>">
                                <?php echo html($post['title']); ?>
                            </a>
                            <?php if ($isPasswordProtected) { ?>
                                <span class="tg-post-lock" title="Защищено паролем">
                                    <?php echo bloggy_icon('bs', 'lock-fill', '14', 'currentColor'); ?>
                                </span>
                            <?php } ?>
                        </h2>
                        
                        <?php if (!empty($post['short_description'])) { ?>
                        <p class="tg-post-excerpt">
                            <?php echo html($post['short_description']); ?>
                        </p>
                        <?php } ?>

                        <div class="tg-post-actions">
                            <div class="tg-post-actions-left">
                                <button class="tg-action-btn tg-like-btn <?php echo isset($post['userLiked']) && $post['userLiked'] ? 'tg-active' : ''; ?>" 
                                        data-post-id="<?php echo $post['id']; ?>"
                                        title="Нравится">
                                    <?php 
                                    $heartIcon = (isset($post['userLiked']) && $post['userLiked']) ? 'heart-fill' : 'heart';
                                    echo bloggy_icon('bs', $heartIcon, '16', 'currentColor', 'tg-mr-1');
                                    ?>
                                    <span class="tg-action-count"><?php echo $post['likes_count'] ?? 0; ?></span>
                                </button>
                                
                                <a href="<?php echo BASE_URL . '/post/' . html($post['slug']) . '#comments'; ?>" 
                                   class="tg-action-btn tg-comments-link"
                                   title="Комментарии">
                                    <?php echo bloggy_icon('bs', 'chat-dots', '16', 'currentColor', 'tg-mr-1'); ?>
                                    <span class="tg-action-count"><?php echo $post['comments_count'] ?? 0; ?></span>
                                </a>
                                
                                <button class="tg-action-btn tg-bookmark-btn <?php echo isset($post['userBookmarked']) && $post['userBookmarked'] ? 'tg-active' : ''; ?>" 
                                        data-post-id="<?php echo $post['id']; ?>"
                                        title="В закладки">
                                    <?php 
                                    $bookmarkIcon = (isset($post['userBookmarked']) && $post['userBookmarked']) ? 'bookmark-fill' : 'bookmark';
                                    echo bloggy_icon('bs', $bookmarkIcon, '16', 'currentColor');
                                    ?>
                                </button>
                            </div>
                            
                            <div class="tg-post-views">
                                <?php echo bloggy_icon('bs', 'eye', '14', 'currentColor', 'tg-mr-1'); ?>
                                <span><?php echo $post['views'] ?? 0; ?></span>
                            </div>
                        </div>
                        
                        <?php if (!empty($post['tags'])) { ?>
                        <div class="tg-post-tags">
                            <?php foreach ($post['tags'] as $tag) { ?>
                            <a href="<?php echo BASE_URL; ?>/tag/<?php echo html($tag['slug']); ?>" 
                               class="tg-tag">
                                #<?php echo html($tag['name']); ?>
                            </a>
                            <?php } ?>
                        </div>
                        <?php } ?>
                    </div>
                </article>
                <?php } ?>
            <?php } else { ?>
                <div class="tg-empty-state tg-py-5 tg-text-center">
                    <?php echo bloggy_icon('bs', 'file-text', '48', 'currentColor', 'tg-mb-3'); ?>
                    <h3 class="tg-empty-title">Пока нет публикаций</h3>
                    <p class="tg-empty-text tg-text-muted">Загляните позже, появятся новые материалы</p>
                </div>
            <?php } ?>
        </div>
        
        <?php if (!empty($pagination) && $pagination['has_more']) { ?>
        <div class="tg-load-more tg-mt-5 tg-text-center">
            <a href="<?php echo $pagination['next_url']; ?>" class="tg-btn tg-btn-outline">
                <?php echo bloggy_icon('bs', 'arrow-down', '16', 'currentColor', 'tg-mr-1'); ?>
                Загрузить еще
            </a>
        </div>
        <?php } ?>
        
    </div>
</div>
<?php 
ob_start();
?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const likeButtons = document.querySelectorAll('.tg-like-btn');
    const bookmarkButtons = document.querySelectorAll('.tg-bookmark-btn');
    
    function handleAction(button, action, postId) {
        const isLoggedIn = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;
        
        if (!isLoggedIn) {
            window.location.href = '<?php echo BASE_URL; ?>/login';
            return;
        }
        
        const countSpan = button.querySelector('.tg-action-count');
        const icon = button.querySelector('svg use');
        const wasActive = button.classList.contains('tg-active');
        
        button.classList.toggle('tg-active');
        
        if (countSpan) {
            const currentCount = parseInt(countSpan.textContent) || 0;
            countSpan.textContent = wasActive ? currentCount - 1 : currentCount + 1;
        }
        
        if (icon) {
            const iconHref = icon.getAttribute('href');
            if (action === 'like') {
                const newIcon = wasActive ? 'heart' : 'heart-fill';
                icon.setAttribute('href', iconHref.replace(/heart(-fill)?/, newIcon));
            } else if (action === 'bookmark') {
                const newIcon = wasActive ? 'bookmark' : 'bookmark-fill';
                icon.setAttribute('href', iconHref.replace(/bookmark(-fill)?/, newIcon));
            }
        }
        
        fetch(`<?php echo BASE_URL; ?>/post/${action}/${postId}`, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        }).catch(error => {
            button.classList.toggle('tg-active');
            if (countSpan) {
                const currentCount = parseInt(countSpan.textContent) || 0;
                countSpan.textContent = wasActive ? currentCount + 1 : currentCount - 1;
            }
        });
    }
    
    likeButtons.forEach(button => {
        button.addEventListener('click', function() {
            const postId = this.dataset.postId;
            handleAction(this, 'like', postId);
        });
    });
    
    bookmarkButtons.forEach(button => {
        button.addEventListener('click', function() {
            const postId = this.dataset.postId;
            handleAction(this, 'bookmark', postId);
        });
    });
});
</script>
<?php front_bottom_js(ob_get_clean()); ?>
<?php 
ob_start();
?>
<script>
window.baseUrl = '<?php echo BASE_URL; ?>';
window.userLoggedIn = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;
window.bloggyIcon = function(iconSet, iconName, size, color, classes = '') {
    return `<i class="${classes}" style="color: ${color}; font-size: ${size}px"></i>`;
};
</script>
<?php echo add_frontend_js('/templates/default/front/assets/js/user-action.js'); ?>
<?php front_bottom_js(ob_get_clean()); ?>