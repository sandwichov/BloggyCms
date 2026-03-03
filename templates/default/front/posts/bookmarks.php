<?php
/**
 * Template Name: Страница закладок
 */
?>

<div class="tg-bookmarks-page">
    <div class="tg-container">
        
        <div class="tg-bookmarks-header tg-mb-4">
            <div class="tg-bookmarks-header-left">
                <div class="tg-bookmarks-icon">
                    <?php echo bloggy_icon('bs', 'bookmark-star', '24', 'var(--tg-primary)'); ?>
                </div>
                <div class="tg-bookmarks-info">
                    <h1 class="tg-bookmarks-title">Мои закладки</h1>
                    <p class="tg-bookmarks-subtitle tg-text-muted">
                        <?php echo bloggy_icon('bs', 'bookmark', '14', 'currentColor', 'tg-mr-1'); ?>
                        <?php echo $bookmarks_count ?? 0; ?> сохранённых публикаций
                    </p>
                </div>
            </div>
            
            <?php if (!empty($posts)) { ?>
            <a href="<?php echo BASE_URL; ?>/posts" class="tg-btn tg-btn-outline tg-btn-sm">
                <?php echo bloggy_icon('bs', 'compass', '14', 'currentColor', 'tg-mr-1'); ?>
                Все публикации
            </a>
            <?php } ?>
        </div>
        
        <?php if (empty($posts)) { ?>
            <div class="tg-empty-state">
                <div class="tg-empty-state-icon">
                    <?php echo bloggy_icon('bs', 'bookmark', '48', 'var(--tg-text-secondary)'); ?>
                </div>
                <h3 class="tg-empty-state-title">Закладок пока нет</h3>
                <p class="tg-empty-state-text tg-text-muted">
                    Сохраняйте интересные публикации, чтобы вернуться к ним позже
                </p>
                <a href="<?php echo BASE_URL; ?>/posts" class="tg-btn tg-btn-primary">
                    <?php echo bloggy_icon('bs', 'compass', '16', 'currentColor', 'tg-mr-1'); ?>
                    Найти публикации
                </a>
            </div>
            
        <?php } else { ?>
            
            <div class="tg-bookmarks-grid">
                <?php foreach ($posts as $post) { 
                    $featuredImage = $post['featured_image'] 
                        ? BASE_URL . '/uploads/images/' . html($post['featured_image']) 
                        : null;
                ?>
                <div class="tg-bookmark-item" data-post-id="<?php echo $post['id']; ?>">
                    
                    <?php if ($featuredImage) { ?>
                    <a href="<?php echo BASE_URL . '/post/' . html($post['slug']); ?>" class="tg-bookmark-image">
                        <img src="<?php echo $featuredImage; ?>" 
                             alt="<?php echo html($post['title']); ?>"
                             loading="lazy">
                    </a>
                    <?php } ?>
                    
                    <div class="tg-bookmark-content">
                        <?php if (!empty($post['category_name'])) { ?>
                        <a href="<?php echo BASE_URL; ?>/category/<?php echo html($post['category_slug']); ?>" 
                           class="tg-bookmark-category">
                            <?php echo html($post['category_name']); ?>
                        </a>
                        <?php } ?>
                        
                        <h3 class="tg-bookmark-title">
                            <a href="<?php echo BASE_URL . '/post/' . html($post['slug']); ?>">
                                <?php echo html($post['title']); ?>
                            </a>
                        </h3>
                        
                        <div class="tg-bookmark-meta">
                            <span class="tg-bookmark-date">
                                <?php echo bloggy_icon('bs', 'bookmark', '12', 'currentColor', 'tg-mr-1'); ?>
                                Сохранено <?php echo time_ago($post['bookmarked_at']); ?>
                            </span>
                            
                            <?php if ($post['views'] > 0) { ?>
                            <span class="tg-bookmark-views">
                                <?php echo bloggy_icon('bs', 'eye', '12', 'currentColor', 'tg-mr-1'); ?>
                                <?php echo $post['views']; ?> просмотров
                            </span>
                            <?php } ?>
                        </div>
                    </div>

                    <button class="tg-bookmark-remove" 
                            data-post-id="<?php echo $post['id']; ?>"
                            title="Удалить из закладок">
                        <?php echo bloggy_icon('bs', 'x', '16', 'currentColor'); ?>
                    </button>
                </div>
                <?php } ?>
            </div>
            
        <?php } ?>
        
    </div>
</div>

<?php 
ob_start();
?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const removeButtons = document.querySelectorAll('.tg-bookmark-remove');
    
    removeButtons.forEach(button => {
        button.addEventListener('click', function() {
            const postId = this.dataset.postId;
            const bookmarkItem = this.closest('.tg-bookmark-item');
            
            if (!confirm('Удалить из закладок?')) {
                return;
            }
            
            bookmarkItem.style.opacity = '0.5';
            bookmarkItem.style.pointerEvents = 'none';
            
            fetch(`<?php echo BASE_URL; ?>/post/bookmark/${postId}`, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    bookmarkItem.style.transition = 'all 0.3s ease';
                    bookmarkItem.style.transform = 'translateX(100%)';
                    bookmarkItem.style.opacity = '0';
                    
                    setTimeout(() => {
                        bookmarkItem.remove();
                        const remainingItems = document.querySelectorAll('.tg-bookmark-item');
                        if (remainingItems.length === 0) {
                            location.reload();
                        }
                        
                        const countElement = document.querySelector('.tg-bookmarks-subtitle');
                        if (countElement) {
                            const currentCount = parseInt(countElement.textContent) || 0;
                            countElement.innerHTML = `<?php echo bloggy_icon('bs', 'bookmark', '14', 'currentColor', 'tg-mr-1'); ?> ${currentCount - 1} сохранённых публикаций`;
                        }
                    }, 300);
                } else {
                    bookmarkItem.style.opacity = '1';
                    bookmarkItem.style.pointerEvents = 'auto';
                    alert('Ошибка при удалении из закладок');
                }
            })
            .catch(() => {
                bookmarkItem.style.opacity = '1';
                bookmarkItem.style.pointerEvents = 'auto';
                alert('Ошибка при удалении из закладок');
            });
        });
    });
});
</script>
<?php front_bottom_js(ob_get_clean()); ?>

<?php 
ob_start();
?>
<script>
window.baseUrl = '<?= BASE_URL ?>';
window.userLoggedIn = <?= isset($_SESSION['user_id']) ? 'true' : 'false' ?>;
</script>

<?php front_bottom_js(ob_get_clean()); ?>

<?php echo add_frontend_js('/templates/default/front/assets/js/bookmarks.js'); ?>