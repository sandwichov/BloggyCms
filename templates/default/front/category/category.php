<?php
/**
 * Template Name: Страница категории
 */

$fieldModel = new FieldModel($this->db);
$customFields = $fieldModel->getActiveByEntityType('post');
?>

<div class="tg-category-page">
    <div class="tg-container">
        
        <div class="tg-category-header tg-mb-4">
            <div class="tg-category-header-left">
                <div class="tg-category-icon-large">
                    <?php if (!empty($category['image'])): ?>
                        <img src="<?php echo BASE_URL . '/uploads/images/' . html($category['image']); ?>" 
                             alt="<?php echo html($category['name']); ?>">
                    <?php else: ?>
                        <?php echo bloggy_icon('bs', 'folder-fill', '28', 'var(--tg-primary)'); ?>
                    <?php endif; ?>
                </div>
                <div class="tg-category-info">
                    <h1 class="tg-category-title">
                        <?php echo html($category['name']); ?>
                        <?php if ($category['password_protected']): ?>
                        <span class="tg-category-protected" title="Защищено паролем">
                            <?php echo bloggy_icon('bs', 'lock-fill', '16', 'currentColor'); ?>
                        </span>
                        <?php endif; ?>
                    </h1>
                    
                    <?php if (!empty($category['description'])): ?>
                    <p class="tg-category-description tg-text-muted">
                        <?php echo html($category['description']); ?>
                    </p>
                    <?php endif; ?>
                    
                    <div class="tg-category-meta">
                        <span class="tg-meta-item">
                            <?php echo bloggy_icon('bs', 'file-text', '14', 'currentColor', 'tg-mr-1'); ?>
                            <?php echo $total_posts ?? count($posts ?? []); ?> публикаций
                        </span>
                        
                        <?php if (!empty($category['created_at'])): ?>
                        <span class="tg-meta-item">
                            <?php echo bloggy_icon('bs', 'calendar', '14', 'currentColor', 'tg-mr-1'); ?>
                            Создана <?php echo date('d.m.Y', strtotime($category['created_at'])); ?>
                        </span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="tg-category-actions">
                <a href="<?php echo BASE_URL; ?>/posts" class="tg-btn tg-btn-outline tg-btn-sm">
                    <?php echo bloggy_icon('bs', 'grid-3x3-gap', '14', 'currentColor', 'tg-mr-1'); ?>
                    Все посты
                </a>
                <a href="<?php echo BASE_URL; ?>/categories" class="tg-btn tg-btn-outline tg-btn-sm">
                    <?php echo bloggy_icon('bs', 'folders', '14', 'currentColor', 'tg-mr-1'); ?>
                    Все категории
                </a>
            </div>
        </div>
        
        <?php if ($category['password_protected'] && !$hasAccess): ?>
        
        <div class="tg-password-card tg-mb-4">
            <div class="tg-password-icon">
                <?php echo bloggy_icon('bs', 'shield-lock', '48', 'var(--tg-primary)'); ?>
            </div>
            <h2 class="tg-password-title">Категория защищена паролем</h2>
            <p class="tg-password-text tg-text-muted">
                Введите пароль для доступа к публикациям в этой категории
            </p>
            
            <form id="tg-category-password-form" class="tg-password-form">
                <input type="hidden" name="category_id" value="<?php echo $category['id']; ?>">
                
                <div class="tg-field tg-mb-3">
                    <div class="tg-input-wrapper">
                        <span class="tg-input-icon">
                            <?php echo bloggy_icon('bs', 'key', '16', 'currentColor'); ?>
                        </span>
                        <input type="password" 
                               name="password" 
                               class="tg-input" 
                               placeholder="Введите пароль" 
                               required>
                    </div>
                </div>
                
                <div class="tg-password-actions">
                    <button type="submit" class="tg-btn tg-btn-primary tg-btn-block" id="tg-submit-password">
                        <?php echo bloggy_icon('bs', 'unlock', '16', 'currentColor', 'tg-mr-1'); ?>
                        Разблокировать
                    </button>
                </div>
                
                <div id="tg-password-error" class="tg-alert tg-alert-error tg-mt-3" style="display: none;"></div>
            </form>
        </div>
        
        <?php else: ?>
        
        <div class="tg-category-posts">
            
            <?php if (!empty($posts)): ?>
            
            <div class="tg-posts-list">
                <?php foreach ($posts as $post): 
                    $featuredImage = $post['featured_image'] 
                        ? BASE_URL . '/uploads/images/' . html($post['featured_image']) 
                        : null;
                    $isPasswordProtected = isset($post['password_protected']) && $post['password_protected'] == 1;
                    
                    $tagModel = new TagModel($db);
                    $postTags = $tagModel->getForPost($post['id']);
                ?>
                <article class="tg-post-card">
                    <?php if ($featuredImage): ?>
                    <a href="<?php echo BASE_URL . '/post/' . html($post['slug']); ?>" class="tg-post-image-link">
                        <div class="tg-post-image">
                            <img src="<?php echo $featuredImage; ?>" 
                                 alt="<?php echo html($post['title']); ?>"
                                 loading="lazy">
                        </div>
                    </a>
                    <?php endif; ?>
                    
                    <div class="tg-post-content">
                        <div class="tg-post-meta-top">
                            <a href="<?php echo BASE_URL; ?>/category/<?php echo html($category['slug']); ?>" 
                               class="tg-post-category">
                                <?php echo html($category['name']); ?>
                            </a>
                            
                            <span class="tg-post-date">
                                <?php echo bloggy_icon('bs', 'calendar', '12', 'currentColor', 'tg-mr-1'); ?>
                                <?php echo time_ago($post['created_at']); ?>
                            </span>
                        </div>

                        <h2 class="tg-post-title">
                            <a href="<?php echo BASE_URL . '/post/' . html($post['slug']); ?>">
                                <?php echo html($post['title']); ?>
                            </a>
                            <?php if ($isPasswordProtected): ?>
                                <span class="tg-post-lock" title="Защищено паролем">
                                    <?php echo bloggy_icon('bs', 'lock-fill', '14', 'currentColor'); ?>
                                </span>
                            <?php endif; ?>
                        </h2>
                        
                        <?php if (!empty($post['short_description'])): ?>
                        <p class="tg-post-excerpt">
                            <?php echo html($post['short_description']); ?>
                        </p>
                        <?php endif; ?>
                        
                        <?php if (!empty($customFields)): ?>
                        <div class="tg-post-custom-fields tg-mb-3">
                            <?php foreach ($customFields as $field): 
                                $value = $fieldModel->getFieldValue('post', $post['id'], $field['system_name']);
                                if (!empty($value) && ($field['show_in_list'] ?? false)): 
                            ?>
                            <div class="tg-custom-field">
                                <?php echo $fieldModel->renderFieldDisplay($field, $value, 'post', $post['id']); ?>
                            </div>
                            <?php endif; endforeach; ?>
                        </div>
                        <?php endif; ?>
                        
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
                        
                        <?php if (!empty($postTags)): ?>
                        <div class="tg-post-tags tg-mt-3">
                            <?php foreach ($postTags as $tag): ?>
                            <a href="<?php echo BASE_URL; ?>/tag/<?php echo html($tag['slug']); ?>" 
                               class="tg-tag">
                                #<?php echo html($tag['name']); ?>
                            </a>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
            
            <?php if (!empty($pagination) && $pagination['has_more']): ?>
            <div class="tg-load-more tg-mt-5 tg-text-center">
                <a href="<?php echo $pagination['next_url']; ?>" class="tg-btn tg-btn-outline">
                    <?php echo bloggy_icon('bs', 'arrow-down', '16', 'currentColor', 'tg-mr-1'); ?>
                    Показать еще
                </a>
            </div>
            <?php endif; ?>
            
            <?php else: ?>
            
            <div class="tg-empty-state">
                <div class="tg-empty-state-icon">
                    <?php echo bloggy_icon('bs', 'folder-x', '48', 'var(--tg-text-secondary)'); ?>
                </div>
                <h3 class="tg-empty-state-title">В этой категории пока нет публикаций</h3>
                <p class="tg-empty-state-text tg-text-muted">
                    Загляните сюда позже или посмотрите другие категории
                </p>
                <div class="tg-empty-actions">
                    <a href="<?php echo BASE_URL; ?>/posts" class="tg-btn tg-btn-primary">
                        <?php echo bloggy_icon('bs', 'grid-3x3-gap', '16', 'currentColor', 'tg-mr-1'); ?>
                        Все посты
                    </a>
                    <a href="<?php echo BASE_URL; ?>/categories" class="tg-btn tg-btn-outline tg-ml-2">
                        <?php echo bloggy_icon('bs', 'folders', '16', 'currentColor', 'tg-mr-1'); ?>
                        Все категории
                    </a>
                </div>
            </div>
            
            <?php endif; ?>
            
        </div>
        
        <?php endif; ?>
        
    </div>
</div>

<?php if ($category['password_protected'] && !$hasAccess): ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('tg-category-password-form');
    const errorDiv = document.getElementById('tg-password-error');
    const submitBtn = document.getElementById('tg-submit-password');
    
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const originalText = submitBtn.innerHTML;
            
            submitBtn.innerHTML = '<?php echo bloggy_icon('bs', 'hourglass-split', '14', 'currentColor', 'tg-mr-1'); ?> Проверка...';
            submitBtn.disabled = true;
            errorDiv.style.display = 'none';
            
            fetch('/category/check-password/<?php echo $category['id']; ?>', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    submitBtn.innerHTML = '<?php echo bloggy_icon('bs', 'check', '14', 'currentColor', 'tg-mr-1'); ?> Успешно!';
                    setTimeout(() => location.reload(), 1000);
                } else {
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                    
                    errorDiv.textContent = data.message || 'Неверный пароль';
                    errorDiv.style.display = 'flex';
                    
                    const input = form.querySelector('input[name="password"]');
                    input.classList.add('tg-error');
                    input.value = '';
                    
                    setTimeout(() => {
                        input.classList.remove('tg-error');
                        errorDiv.style.display = 'none';
                    }, 3000);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
                
                errorDiv.textContent = 'Ошибка при проверке пароля';
                errorDiv.style.display = 'flex';
            });
        });
    }
});
</script>
<?php endif; ?>

<?php 
ob_start();
?>
<script>
window.baseUrl = '<?= BASE_URL; ?>';
window.userLoggedIn = <?= isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;
</script>
<?php front_bottom_js(ob_get_clean()); ?>
<?php echo add_frontend_js('/templates/default/front/assets/js/user-action.js'); ?>