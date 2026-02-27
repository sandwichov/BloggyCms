<?php
/**
 * Template Name: Отдельный комментарий
 */

$isPending = $comment['status'] === 'pending';
$isOwnComment = isset($_SESSION['user_id']) && $comment['user_id'] == $_SESSION['user_id'];
$isAdmin = isset($_SESSION['is_admin']);
$canEdit = $isOwnComment || $isAdmin;
$authorName = htmlspecialchars($comment['author_username'] ?? $comment['author_name'] ?? 'Аноним');
$commentDate = date('d.m.Y H:i', strtotime($comment['created_at']));
?>

<div class="tg-comment-item" id="tg-comment-<?php echo $comment['id']; ?>">
    
    <div class="tg-comment-container">
        <div class="tg-comment-header">
            
            <div class="tg-comment-avatar">
                <?php if (!empty($comment['author_avatar']) && $comment['author_avatar'] !== 'default.jpg'): ?>
                    <img src="<?php echo BASE_URL; ?>/uploads/avatars/<?php echo htmlspecialchars($comment['author_avatar']); ?>" 
                         alt="<?php echo $authorName; ?>">
                <?php else: ?>
                    <div class="tg-avatar-placeholder">
                        <?php echo strtoupper(substr($authorName, 0, 1)); ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="tg-comment-info">
                <div class="tg-comment-author-row">
                    <span class="tg-author-name"><?php echo $authorName; ?></span>
                    
                    <?php if ($isOwnComment): ?>
                        <span class="tg-badge tg-badge-own" title="Ваш комментарий">
                            <?php echo bloggy_icon('bs', 'person-check', '10', 'currentColor', 'tg-mr-1'); ?>
                            Вы
                        </span>
                    <?php endif; ?>
                    
                    <?php if ($isAdmin && !$isOwnComment): ?>
                        <span class="tg-badge tg-badge-admin" title="Администратор">
                            <?php echo bloggy_icon('bs', 'shield', '10', 'currentColor', 'tg-mr-1'); ?>
                            Админ
                        </span>
                    <?php endif; ?>
                    
                    <?php if ($isPending): ?>
                        <span class="tg-badge tg-badge-pending" title="На модерации">
                            <?php echo bloggy_icon('bs', 'clock', '10', 'currentColor', 'tg-mr-1'); ?>
                            На модерации
                        </span>
                    <?php endif; ?>
                </div>
                
                <div class="tg-comment-meta">
                    <span class="tg-comment-date">
                        <?php echo bloggy_icon('bs', 'calendar', '10', 'currentColor', 'tg-mr-1'); ?>
                        <?php echo $commentDate; ?>
                    </span>
                    
                    <?php if (!empty($comment['was_edited']) && $comment['was_edited']): ?>
                        <span class="tg-comment-edited" title="Отредактировано">
                            • ред.
                        </span>
                    <?php endif; ?>
                </div>
            </div>
            
            <?php if (!empty($comment['parent_id'])): ?>
            <div class="tg-reply-indicator" title="Ответ">
                <?php echo bloggy_icon('bs', 'reply', '12', 'currentColor'); ?>
            </div>
            <?php endif; ?>
        </div>
        
        <div class="tg-comment-content">
            <?php echo nl2br(htmlspecialchars($comment['content'])); ?>
        </div>
        
        <div class="tg-comment-actions">
            <button type="button" 
                    class="tg-action-btn tg-reply-btn"
                    data-comment-id="<?php echo $comment['id']; ?>"
                    data-comment-author="<?php echo $authorName; ?>">
                <?php echo bloggy_icon('bs', 'reply', '14', 'currentColor', 'tg-mr-1'); ?>
                Ответить
            </button>
            
            <?php if ($canEdit): ?>
                <a href="<?php echo BASE_URL; ?>/comment/edit/<?php echo $comment['id']; ?>" 
                   class="tg-action-btn tg-edit-btn">
                    <?php echo bloggy_icon('bs', 'pencil', '14', 'currentColor', 'tg-mr-1'); ?>
                    Редактировать
                </a>
                
                <a href="<?php echo BASE_URL; ?>/comment/delete/<?php echo $comment['id']; ?>" 
                   class="tg-action-btn tg-delete-btn"
                   onclick="return confirm('Удалить комментарий?')">
                    <?php echo bloggy_icon('bs', 'trash', '14', 'currentColor', 'tg-mr-1'); ?>
                    Удалить
                </a>
                
            <?php endif; ?>
            
            <?php if ($isAdmin && $isPending): ?>
                <a href="<?php echo ADMIN_URL; ?>/comments/approve/<?php echo $comment['id']; ?>" 
                   class="tg-action-btn tg-approve-btn"
                   title="Одобрить">
                    <?php echo bloggy_icon('bs', 'check-lg', '14', 'currentColor', 'tg-mr-1'); ?>
                    Одобрить
                </a>
            <?php endif; ?>
        </div>
        
        <div class="tg-comment-replies" id="tg-replies-<?php echo $comment['id']; ?>"></div>
    </div>
</div>