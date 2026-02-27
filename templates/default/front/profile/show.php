<?php
/**
 * Template Name: Профиль пользователя
 */

$fieldModel = new FieldModel($this->db);
?>

<div class="tg-profile">
    <div class="tg-container">
        <div class="tg-profile-header">
            <div class="tg-profile-avatar">
                <?php if (!empty($user['avatar']) && $user['avatar'] !== 'default.jpg') { ?>
                    <img src="<?php echo BASE_URL; ?>/uploads/avatars/<?php echo html($user['avatar']); ?>" 
                         alt="<?php echo html($user['display_name'] ?? $user['username']); ?>">
                <?php } else { ?>
                    <div class="tg-avatar-placeholder">
                        <?php echo strtoupper(substr($user['username'], 0, 1)); ?>
                    </div>
                <?php } ?>
            </div>
            
            <div class="tg-profile-info">
                <h1 class="tg-profile-name">
                    <?php echo html($user['display_name'] ?? $user['username']); ?>
                    <?php if ($is_online) { ?>
                        <span class="tg-online" title="В сети"></span>
                    <?php } ?>
                </h1>
                
                <div class="tg-profile-meta">
                    <span class="tg-username">@<?php echo html($user['username']); ?></span>
                    <?php if (!$is_online && !empty($last_activity_human)) { ?>
                        <span class="tg-last-seen">• <?php echo $last_activity_human; ?></span>
                    <?php } ?>
                </div>
                
                <?php if (!empty($groups)) { ?>
                <div class="tg-profile-groups">
                    <?php foreach ($groups as $group) { ?>
                        <span class="tg-group-badge"><?php echo html($group['name']); ?></span>
                    <?php } ?>
                </div>
                <?php } ?>
            </div>
            
            <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $user['id']) { ?>
            <a href="<?php echo BASE_URL; ?>/profile/edit" class="tg-edit-btn">
                <?php echo bloggy_icon('bs', 'pencil', '16', 'currentColor'); ?>
            </a>
            <?php } ?>
        </div>
        
        <div class="tg-profile-grid">
            <div class="tg-profile-sidebar">
                <div class="tg-card">
                    <div class="tg-card-body">
                        <h3 class="tg-card-title">
                            <?php echo bloggy_icon('bs', 'person', '18', 'currentColor', 'tg-mr-1'); ?>
                            О себе
                        </h3>
                        <?php if (!empty($user['bio'])) { ?>
                            <div class="tg-bio"><?php echo nl2br(html($user['bio'])); ?></div>
                        <?php } else { ?>
                            <div class="tg-bio tg-bio-empty">
                                <?php echo html($user['display_name'] ?? $user['username']); ?> еще не добавил информацию о себе.
                            </div>
                        <?php } ?>
                    </div>
                </div>
                
                <div class="tg-card">
                    <div class="tg-card-body">
                        <h3 class="tg-card-title">
                            <?php echo bloggy_icon('bs', 'bar-chart', '18', 'currentColor', 'tg-mr-1'); ?>
                            Статистика
                        </h3>
                        <div class="tg-stats">
                            <div class="tg-stat">
                                <span class="tg-stat-value"><?php echo !empty($posts) ? count($posts) : 0; ?></span>
                                <span class="tg-stat-label">публикаций</span>
                            </div>
                            <div class="tg-stat">
                                <span class="tg-stat-value"><?php echo $commentsCount ?? 0; ?></span>
                                <span class="tg-stat-label">комментариев</span>
                            </div>
                            <div class="tg-stat">
                                <span class="tg-stat-value"><?php echo $daysSinceRegistration ?? 0; ?></span>
                                <span class="tg-stat-label">дней</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <?php if (!empty($user['website']) || !empty($user['email'])) { ?>
                <div class="tg-card">
                    <div class="tg-card-body">
                        <h3 class="tg-card-title">
                            <?php echo bloggy_icon('bs', 'link', '18', 'currentColor', 'tg-mr-1'); ?>
                            Контакты
                        </h3>
                        <div class="tg-contacts">
                            <?php if (!empty($user['website'])) { ?>
                            <a href="<?php echo html($user['website']); ?>" target="_blank" class="tg-contact">
                                <?php echo bloggy_icon('bs', 'globe', '16', 'currentColor', 'tg-mr-1'); ?>
                                <?php echo html($user['website']); ?>
                            </a>
                            <?php } ?>
                            
                            <?php if (!empty($user['email'])) { ?>
                            <div class="tg-contact">
                                <?php echo bloggy_icon('bs', 'envelope', '16', 'currentColor', 'tg-mr-1'); ?>
                                <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $user['id']) { ?>
                                    <?php echo html($user['email']); ?>
                                <?php } else { ?>
                                    <span class="tg-hidden">Скрыто</span>
                                <?php } ?>
                            </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
                <?php } ?>
                
                <?php
                if (!empty($customFields)) {
                    $hasVisibleFields = false;
                    foreach ($customFields as $field) {
                        $value = $fieldModel->getFieldValue('user', $user['id'], $field['system_name']);
                        if (!empty($value)) {
                            $hasVisibleFields = true;
                            break;
                        }
                    }
                    
                    if ($hasVisibleFields) {
                ?>
                <div class="tg-card">
                    <div class="tg-card-body">
                        <h3 class="tg-card-title">
                            <?php echo bloggy_icon('bs', 'info-circle', '18', 'currentColor', 'tg-mr-1'); ?>
                            Дополнительно
                        </h3>
                        <div class="tg-details">
                            <?php foreach ($customFields as $field) { 
                                $value = $fieldModel->getFieldValue('user', $user['id'], $field['system_name']);
                                if (!empty($value)) {
                            ?>
                            <div class="tg-detail">
                                <span class="tg-detail-label"><?php echo html($field['name']); ?></span>
                                <span class="tg-detail-value"><?php echo $fieldModel->renderFieldDisplay($field, $value, 'user', $user['id']); ?></span>
                            </div>
                            <?php } } ?>
                        </div>
                    </div>
                </div>
                <?php 
                    }
                } 
                ?>
                
                <div class="tg-card">
                    <div class="tg-card-body">
                        <div class="tg-achievements-header">
                            <h3 class="tg-card-title">
                                <?php echo bloggy_icon('bs', 'trophy', '18', 'currentColor', 'tg-mr-1'); ?>
                                Достижения
                            </h3>
                            <span class="tg-achievements-count">
                                <?php echo $unlockedCount ?? 0; ?>/<?php echo count($achievements ?? []); ?>
                            </span>
                        </div>
                        
                        <div class="tg-achievements">
                            <?php if (!empty($achievements)) { ?>
                                <?php foreach ($achievements as $achievement) { ?>
                                    <?php if ($achievement['is_unlocked']) { ?>
                                    <div class="tg-achievement" title="<?php echo html($achievement['name']); ?> — <?php echo html($achievement['description']); ?>">
                                        <?php if ($achievement['image']) { ?>
                                            <img src="<?php echo BASE_URL; ?>/uploads/achievements/<?php echo html($achievement['image']); ?>" 
                                                 alt="<?php echo html($achievement['name']); ?>">
                                        <?php } else { ?>
                                            <div class="tg-achievement-icon" style="background: <?php echo $achievement['icon_color'] ?? '#2b5278'; ?>">
                                                <?php echo bloggy_icon('bs', $achievement['icon'] ?? 'trophy', '20', 'white'); ?>
                                            </div>
                                        <?php } ?>
                                    </div>
                                    <?php } ?>
                                <?php } ?>
                            <?php } else { ?>
                                <div class="tg-no-achievements">
                                    <?php echo bloggy_icon('bs', 'emoji-frown', '20', 'currentColor'); ?>
                                    <span>Пока нет достижений</span>
                                </div>
                            <?php } ?>
                        </div>
                        
                        <?php if (!empty($achievements) && ($unlockedCount ?? 0) < count($achievements)) { ?>
                        <div class="tg-locked-achievements">
                            <?php echo bloggy_icon('bs', 'lock', '12', 'currentColor', 'tg-mr-1'); ?>
                            <small><?php echo count($achievements) - $unlockedCount; ?> заблокировано</small>
                        </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
            
            <div class="tg-profile-posts">
                <div class="tg-card">
                    <div class="tg-card-header">
                        <h3 class="tg-card-title">
                            <?php echo bloggy_icon('bs', 'file-text', '18', 'currentColor', 'tg-mr-1'); ?>
                            Публикации
                        </h3>
                        <?php if (!empty($posts)) { ?>
                        <span class="tg-posts-count"><?php echo count($posts); ?></span>
                        <?php } ?>
                    </div>
                    
                    <?php if (!empty($posts)) { ?>
                    <div class="tg-posts-list">
                        <?php foreach ($posts as $post) { ?>
                        <div class="tg-post-item">
                            <?php if ($post['featured_image']) { ?>
                            <a href="<?php echo BASE_URL . '/post/' . html($post['slug']); ?>" class="tg-post-image">
                                <img src="<?php echo BASE_URL; ?>/uploads/images/<?php echo html($post['featured_image']); ?>" 
                                     alt="<?php echo html($post['title']); ?>">
                            </a>
                            <?php } ?>
                            
                            <div class="tg-post-content">
                                <?php if (!empty($post['category_name'])) { ?>
                                <a href="<?php echo BASE_URL; ?>/category/<?php echo html($post['category_slug']); ?>" 
                                   class="tg-post-category">
                                    <?php echo html($post['category_name']); ?>
                                </a>
                                <?php } ?>
                                
                                <h4 class="tg-post-title">
                                    <a href="<?php echo BASE_URL . '/post/' . html($post['slug']); ?>">
                                        <?php echo html($post['title']); ?>
                                    </a>
                                </h4>
                                
                                <?php if (!empty($post['short_description'])) { ?>
                                <p class="tg-post-excerpt">
                                    <?php echo html(substr($post['short_description'], 0, 120)); ?>...
                                </p>
                                <?php } ?>
                                
                                <div class="tg-post-meta">
                                    <span><?php echo bloggy_icon('bs', 'calendar', '12', 'currentColor', 'tg-mr-1'); ?> <?php echo time_ago($post['created_at']); ?></span>
                                    <?php if ($post['views'] > 0) { ?>
                                    <span><?php echo bloggy_icon('bs', 'eye', '12', 'currentColor', 'tg-mr-1'); ?> <?php echo $post['views']; ?></span>
                                    <?php } ?>
                                    <?php if (isset($post['comments_count']) && $post['comments_count'] > 0) { ?>
                                    <span><?php echo bloggy_icon('bs', 'chat', '12', 'currentColor', 'tg-mr-1'); ?> <?php echo $post['comments_count']; ?></span>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>
                        <?php } ?>
                    </div>
                    <?php } else { ?>
                    <div class="tg-no-posts">
                        <?php echo bloggy_icon('bs', 'file-text', '32', 'currentColor', 'tg-mb-2'); ?>
                        <p>Пока нет пустов</p>
                        <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $user['id']) { ?>
                        <a href="<?php echo BASE_URL; ?>/post/create" class="tg-btn tg-btn-primary tg-btn-sm">
                            <?php echo bloggy_icon('bs', 'plus', '14', 'currentColor', 'tg-mr-1'); ?>
                            Создать первый пост
                        </a>
                        <?php } ?>
                    </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
</div>