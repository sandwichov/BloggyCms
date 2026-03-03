<?php
/**
 * Template Name: Страница поста, защищенного паролем
 */
?>

<div class="tg-password-page">
    <div class="tg-container tg-container-sm">
        
        <div class="tg-card">
            <div class="tg-card-body">
                
                <div class="tg-password-icon tg-text-center tg-mb-3">
                    <?php echo bloggy_icon('bs', 'lock-fill', '48', 'var(--tg-primary)'); ?>
                </div>
                
                <h1 class="tg-password-title tg-text-center tg-mb-2">
                    Защищенный пост
                </h1>
                
                <div class="tg-password-post-title tg-text-center tg-mb-4">
                    <span class="tg-text-muted">Доступ ограничен для:</span>
                    <strong class="tg-post-title-block"><?php echo html($post['title']); ?></strong>
                </div>
                
                <?php if (!empty($post['short_description'])) { ?>
                <p class="tg-password-description tg-text-center tg-text-muted tg-mb-4">
                    <?php echo html($post['short_description']); ?>
                </p>
                <?php } ?>
                
                <?php if ($error) { ?>
                <div class="tg-alert tg-alert-error tg-mb-4">
                    <div class="tg-alert-icon">
                        <?php echo bloggy_icon('bs', 'exclamation-triangle', '18', 'currentColor'); ?>
                    </div>
                    <div class="tg-alert-content">
                        <strong>Неверный пароль</strong>
                        <p class="tg-mb-0">Пожалуйста, попробуйте еще раз.</p>
                    </div>
                </div>
                <?php } ?>
                
                <form method="post" action="<?php echo BASE_URL; ?>/post/check-password/<?php echo $post['id']; ?>" class="tg-password-form">
                    <input type="hidden" name="redirect" value="<?php echo BASE_URL; ?>/post/<?php echo html($post['slug']); ?>">
                    
                    <div class="tg-field tg-mb-4">
                        <label for="password" class="tg-label">Пароль доступа</label>
                        <div class="tg-input-wrapper">
                            <span class="tg-input-icon">
                                <?php echo bloggy_icon('bs', 'key', '16', 'currentColor'); ?>
                            </span>
                            <input type="password" 
                                   id="password"
                                   name="password" 
                                   class="tg-input" 
                                   placeholder="Введите пароль..." 
                                   required
                                   autofocus>
                        </div>
                        <div class="tg-field-hint">
                            <?php echo bloggy_icon('bs', 'info-circle', '12', 'currentColor', 'tg-mr-1'); ?>
                            Введите пароль для доступа к этому посту
                        </div>
                    </div>
                    
                    <div class="tg-password-actions">
                        <button type="submit" class="tg-btn tg-btn-primary tg-btn-block">
                            <?php echo bloggy_icon('bs', 'unlock', '16', 'currentColor', 'tg-mr-1'); ?>
                            Открыть пост
                        </button>
                        
                        <a href="<?php echo BASE_URL; ?>/posts" class="tg-password-back-link tg-text-center tg-mt-3">
                            <?php echo bloggy_icon('bs', 'arrow-left', '14', 'currentColor', 'tg-mr-1'); ?>
                            Вернуться к списку постов
                        </a>
                    </div>
                </form>
                
            </div>
        </div>
        
        <div class="tg-password-help tg-text-center tg-mt-4">
            <small class="tg-text-muted">
                <?php echo bloggy_icon('bs', 'shield-check', '12', 'currentColor', 'tg-mr-1'); ?>
                Этот пост защищен паролем автором блога
            </small>
        </div>
        
    </div>
</div>