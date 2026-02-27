<?php
/**
 * Template Name: Страница редактирования комментария
 */

$postId = $comment['post_id'] ?? 0;
$post = $postModel->getById($postId);
$postTitle = $post ? $post['title'] : 'Неизвестный пост';
$postSlug = $post ? $post['slug'] : '';
$isAdmin = $isAdmin ?? false;
$canEdit = AuthHelper::canEditComment($comment['user_id'] ?? null);

if (!$canEdit) {
    echo '<div class="tg-container tg-mt-5"><div class="tg-alert tg-alert-danger">У вас нет прав для редактирования этого комментария.</div></div>';
    return;
}
?>

<div class="tg-comment-edit-page">
    <div class="tg-container">
        
        <div class="tg-breadcrumbs tg-mb-4">
            <nav class="tg-breadcrumbs-nav">
                <a href="<?php echo BASE_URL; ?>/" class="tg-breadcrumb-item">
                    <?php echo bloggy_icon('bs', 'house', '14', 'currentColor', 'tg-mr-1'); ?>
                    Главная
                </a>
                <span class="tg-breadcrumb-sep">/</span>
                <a href="<?php echo BASE_URL; ?>/posts" class="tg-breadcrumb-item">Блог</a>
                <span class="tg-breadcrumb-sep">/</span>
                <a href="<?php echo BASE_URL; ?>/post/<?php echo $postSlug; ?>" class="tg-breadcrumb-item">
                    <?php echo htmlspecialchars($postTitle); ?>
                </a>
                <span class="tg-breadcrumb-sep">/</span>
                <span class="tg-breadcrumb-item tg-active">Редактирование комментария</span>
            </nav>
        </div>
        
        <div class="tg-page-header tg-mb-4">
            <h1 class="tg-page-title">
                <?php echo $isAdmin ? 'Редактирование комментария (админ)' : 'Редактирование комментария'; ?>
            </h1>
            <p class="tg-page-subtitle tg-text-muted">
                <?php echo htmlspecialchars($postTitle); ?>
            </p>
        </div>
        
        <div class="tg-card">
            <div class="tg-card-body">
                
                <div class="tg-post-info-card tg-mb-4">
                    <div class="tg-post-info-icon">
                        <?php echo bloggy_icon('bs', 'file-text', '20', 'var(--tg-primary)'); ?>
                    </div>
                    <div class="tg-post-info-content">
                        <span class="tg-post-info-label">Комментарий к посту:</span>
                        <a href="<?php echo BASE_URL; ?>/post/<?php echo $postSlug; ?>" class="tg-post-info-title">
                            <?php echo htmlspecialchars($postTitle); ?>
                        </a>
                    </div>
                    <a href="<?php echo BASE_URL; ?>/post/<?php echo $postSlug; ?>" class="tg-btn tg-btn-outline tg-btn-sm">
                        <?php echo bloggy_icon('bs', 'arrow-left', '14', 'currentColor', 'tg-mr-1'); ?>
                        Назад
                    </a>
                </div>
                
                <?php if (!$isAdmin && $comment['status'] === 'pending'): ?>
                <div class="tg-alert tg-alert-warning tg-mb-4">
                    <div class="tg-alert-icon">
                        <?php echo bloggy_icon('bs', 'exclamation-triangle', '20', 'currentColor'); ?>
                    </div>
                    <div class="tg-alert-content">
                        <strong>Ваш комментарий на модерации</strong>
                        <p class="tg-mb-0">
                            <?php if (!AuthHelper::can('comment_edit_no_moderations')): ?>
                            После редактирования комментарий снова отправится на проверку администратору.
                            <?php else: ?>
                            После редактирования комментарий останется опубликованным.
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
                <?php endif; ?>
                
                <form action="<?php echo BASE_URL; ?>/comment/edit/<?php echo $comment['id']; ?>" method="post" id="tg-edit-comment-form">
                    <div class="tg-field tg-mb-4">
                        <label for="content" class="tg-label">
                            <?php echo bloggy_icon('bs', 'chat-text', '14', 'currentColor', 'tg-mr-1'); ?>
                            Текст комментария <span class="tg-required">*</span>
                        </label>
                        <textarea name="content" 
                                  id="content" 
                                  rows="8" 
                                  class="tg-input tg-textarea" 
                                  placeholder="Введите текст комментария..."
                                  required><?php echo htmlspecialchars($comment['content'] ?? ''); ?></textarea>
                        <div class="tg-field-hint">
                            <?php echo bloggy_icon('bs', 'info-circle', '12', 'currentColor', 'tg-mr-1'); ?>
                            Минимальная длина: 10 символов
                        </div>
                    </div>
                    
                    <?php if ($isAdmin): ?>
                    <div class="tg-admin-section tg-mb-4">
                        <div class="tg-section-header">
                            <?php echo bloggy_icon('bs', 'gear', '16', 'currentColor', 'tg-mr-1'); ?>
                            <span>Настройки администратора</span>
                        </div>
                        
                        <div class="tg-section-body">
                            <div class="tg-form-row">
                                <div class="tg-form-col">
                                    <label for="author_name" class="tg-label">Имя автора</label>
                                    <input type="text" 
                                           name="author_name" 
                                           id="author_name" 
                                           class="tg-input" 
                                           value="<?php echo htmlspecialchars($comment['author_name'] ?? ''); ?>">
                                </div>
                                <div class="tg-form-col">
                                    <label for="author_email" class="tg-label">Email автора</label>
                                    <input type="email" 
                                           name="author_email" 
                                           id="author_email" 
                                           class="tg-input" 
                                           value="<?php echo htmlspecialchars($comment['author_email'] ?? ''); ?>">
                                </div>
                            </div>
                            
                            <div class="tg-field">
                                <label for="status" class="tg-label">Статус комментария</label>
                                <select name="status" id="status" class="tg-select">
                                    <option value="pending" <?php echo ($comment['status'] ?? 'pending') === 'pending' ? 'selected' : ''; ?>>На модерации</option>
                                    <option value="approved" <?php echo ($comment['status'] ?? 'pending') === 'approved' ? 'selected' : ''; ?>>Одобрен</option>
                                    <option value="spam" <?php echo ($comment['status'] ?? 'pending') === 'spam' ? 'selected' : ''; ?>>Спам</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <div class="tg-form-actions tg-border-top tg-pt-4">
                        <div class="tg-actions-left">
                            <a href="<?php echo BASE_URL; ?>/post/<?php echo $postSlug; ?>" class="tg-btn tg-btn-outline">
                                <?php echo bloggy_icon('bs', 'x', '14', 'currentColor', 'tg-mr-1'); ?>
                                Отмена
                            </a>
                        </div>
                        
                        <div class="tg-actions-right">
                            <?php if (AuthHelper::canDeleteComment($comment['user_id'] ?? null)): ?>
                            <button type="button" 
                                    class="tg-btn tg-btn-outline tg-btn-danger"
                                    onclick="if(confirm('Удалить этот комментарий?')) window.location.href='<?php echo BASE_URL; ?>/comment/delete/<?php echo $comment['id']; ?>'">
                                <?php echo bloggy_icon('bs', 'trash', '14', 'currentColor', 'tg-mr-1'); ?>
                                Удалить
                            </button>
                            <?php endif; ?>
                            <button type="submit" class="tg-btn tg-btn-primary">
                                <?php echo bloggy_icon('bs', 'check-lg', '14', 'currentColor', 'tg-mr-1'); ?>
                                Сохранить изменения
                            </button>
                        </div>
                    </div>
                    
                    <div class="tg-info-section tg-mt-4 tg-pt-4 tg-border-top">
                        <div class="tg-info-grid">
                            <div class="tg-info-col">
                                <h6 class="tg-info-title">
                                    <?php echo bloggy_icon('bs', 'info-circle', '14', 'currentColor', 'tg-mr-1'); ?>
                                    Информация
                                </h6>
                                <ul class="tg-info-list">
                                    <li>
                                        <?php echo bloggy_icon('bs', 'calendar', '12', 'currentColor', 'tg-mr-1'); ?>
                                        Создан: <?php echo date('d.m.Y H:i', strtotime($comment['created_at'])); ?>
                                    </li>
                                    <?php if (!empty($comment['updated_at']) && $comment['updated_at'] != $comment['created_at']): ?>
                                    <li>
                                        <?php echo bloggy_icon('bs', 'arrow-clockwise', '12', 'currentColor', 'tg-mr-1'); ?>
                                        Обновлен: <?php echo date('d.m.Y H:i', strtotime($comment['updated_at'])); ?>
                                    </li>
                                    <?php endif; ?>
                                    <li>
                                        <?php echo bloggy_icon('bs', 'person', '12', 'currentColor', 'tg-mr-1'); ?>
                                        Автор: <?php echo htmlspecialchars($comment['author_name'] ?? 'Неизвестно'); ?>
                                    </li>
                                    <li>
                                        <?php echo bloggy_icon('bs', 'shield', '12', 'currentColor', 'tg-mr-1'); ?>
                                        Статус: 
                                        <span class="tg-status-badge tg-status-<?php echo $comment['status'] ?? 'pending'; ?>">
                                            <?php 
                                            $statusText = [
                                                'pending' => 'На модерации',
                                                'approved' => 'Одобрен',
                                                'spam' => 'Спам'
                                            ];
                                            echo $statusText[$comment['status']] ?? $comment['status'];
                                            ?>
                                        </span>
                                    </li>
                                </ul>
                            </div>
                            
                            <div class="tg-info-col">
                                <h6 class="tg-info-title">
                                    <?php echo bloggy_icon('bs', 'lightbulb', '14', 'currentColor', 'tg-mr-1'); ?>
                                    Подсказки
                                </h6>
                                <ul class="tg-info-list tg-tips-list">
                                    <li>
                                        <?php echo bloggy_icon('bs', 'check-circle', '12', '#31b131', 'tg-mr-1'); ?>
                                        Сохраняйте вежливый тон
                                    </li>
                                    <li>
                                        <?php echo bloggy_icon('bs', 'check-circle', '12', '#31b131', 'tg-mr-1'); ?>
                                        Проверьте орфографию перед отправкой
                                    </li>
                                    <li>
                                        <?php echo bloggy_icon('bs', 'check-circle', '12', '#31b131', 'tg-mr-1'); ?>
                                        Будьте конструктивны в обсуждении
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                </form>
            </div>
        </div>
    </div>
</div>