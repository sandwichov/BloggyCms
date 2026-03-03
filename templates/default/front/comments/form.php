<?php
/**
 * Template Name: Форма комментирования
 */

$postId = $post['id'] ?? ($_POST['post_id'] ?? 0);
$parentId = $_POST['parent_id'] ?? 0;
$isLoggedIn = Auth::isLoggedIn();
$ajaxUrl = BASE_URL . '/comment/add';
$canComment = AuthHelper::canAddComment($postId);
$showGroups = SettingsHelper::get('controller_comments', 'show_groups', true);
$showAdminBadge = SettingsHelper::get('controller_comments', 'show_admin_badge', false);
$adminBadgeTitle = SettingsHelper::get('controller_comments', 'title_badge', 'Администратор');
$adminBadgeIcon = SettingsHelper::get('controller_comments', 'icon_badge', 'bs:rocket');
$showEmoji = SettingsHelper::get('controller_comments', 'show_emodji', false);
$emojiList = SettingsHelper::get('controller_comments', 'emodji_list', []);

if (!$postId) {
    echo '<div class="alert alert-danger">Ошибка: Не удалось определить пост для комментариев</div>';
    return;
}

if (!$canComment && !$isLoggedIn): ?>

<div class="tg-comment-login-prompt tg-mt-5">
    <div class="alert alert-info">
        <div class="d-flex align-items-center">
            <i class="bi bi-info-circle fs-5 me-3"></i>
            <div>
                <strong>Комментарии доступны только авторизованным пользователям</strong>
                <p class="mb-0 mt-2">
                    <a href="<?= BASE_URL ?>/login" class="btn btn-primary btn-sm me-2">
                        <?php echo bloggy_icon('bs', 'box-arrow-in-right', '16', 'currentColor', 'tg-mr-1'); ?> Войти
                    </a>
                    <a href="<?= BASE_URL ?>/register" class="btn btn-outline-primary btn-sm">
                        <?php echo bloggy_icon('bs', 'person-plus', '14', 'currentColor', 'tg-mr-1'); ?> Зарегистрироваться
                    </a>
                </p>
            </div>
        </div>
    </div>
</div>

<?php elseif (!$canComment): ?>

<div class="tg-comment-restricted tg-mt-5">
    <div class="alert alert-warning">
        <div class="d-flex align-items-center">
            <i class="bi bi-exclamation-triangle fs-5 me-3"></i>
            <div>
                <strong>Ограничение комментариев</strong>
                <p class="mb-0">У вас нет прав на добавление комментариев к этому посту.</p>
            </div>
        </div>
    </div>
</div>

<?php else: ?>

<div class="comments-form card border-0 tg-card" id="comment-form">
    <div class="card-header bg-white border-0 tg-card-header">
        <h5 class="card-title mb-0 tg-card-title">
            <i class="bi bi-chat-left-text me-2"></i>
            <span id="comment-form-title"><?= $parentId ? 'Ответить на комментарий' : 'Оставить комментарий' ?></span>
        </h5>
    </div>
    
    <div class="card-body tg-card-body">
        <form action="<?= $ajaxUrl ?>" method="post" id="comment-form-element">
            <input type="hidden" name="post_id" value="<?= $postId ?>">
            <input type="hidden" name="parent_id" value="<?= $parentId ?>" id="comment-parent-id">
            
            <div id="comment-form-status" style="display: none;"></div>
            
            <?php if (!$isLoggedIn): ?>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="author_name" class="form-label">
                            <i class="bi bi-person me-1"></i> <?php echo SettingsHelper::get('controller_comments', 'z10', 'Имя'); ?> *
                        </label>
                        <input type="text" 
                               name="author_name" 
                               id="author_name" 
                               class="form-control" 
                               required 
                               placeholder="<?php echo SettingsHelper::get('controller_comments', 'z9', 'Введите ваше имя'); ?>" 
                               value="<?= htmlspecialchars($_POST['author_name'] ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label for="author_email" class="form-label">
                            <i class="bi bi-envelope me-1"></i> <?php echo SettingsHelper::get('controller_comments', 'z11', 'Email'); ?> *
                        </label>
                        <input type="email" 
                               name="author_email" 
                               id="author_email" 
                               class="form-control" 
                               required 
                               placeholder="<?php echo SettingsHelper::get('controller_comments', 'z12', 'Введите ваш email'); ?>" 
                               value="<?= htmlspecialchars($_POST['author_email'] ?? '') ?>">
                        <small class="text-muted">
                            <i class="bi bi-info-circle me-1"></i> <?php echo SettingsHelper::get('controller_comments', 'z13', 'Ваш email не будет опубликован'); ?>
                        </small>
                    </div>
                </div>
            <?php else: ?>
                <?php
                    $userGroups = [];
                    if ($showGroups) {
                        try {
                            $userModel = new UserModel($GLOBALS['db'] ?? null);
                            $groupIds = $userModel->getUserGroups(Auth::getUserId());
                            
                            foreach ($groupIds as $groupId) {
                                $group = $userModel->getGroupById($groupId);
                                if ($group) {
                                    $userGroups[] = $group['name'];
                                }
                            }
                            
                            if (!empty($userGroups)) {
                                echo '<div class="tg-user-groups-info mb-3">';
                                echo '<span class="text-muted me-2">Ваши группы:</span>';
                                foreach ($userGroups as $groupName) {
                                    echo '<span class="badge bg-light text-dark me-1">' . html($groupName) . '</span>';
                                }
                                echo '</div>';
                            }
                        } catch (Exception $e) {}
                    }
                ?>
            <?php endif; ?>
            
            <div class="mb-3">
                <label for="content" class="form-label">
                    <i class="bi bi-chat-text me-1"></i> <?php echo SettingsHelper::get('controller_comments', 'z15', 'Комментарий'); ?> *
                </label>
                
                <div class="comment-input-wrapper position-relative">
                    <textarea name="content" 
                              id="content" 
                              rows="4" 
                              class="form-control" 
                              placeholder="<?php echo SettingsHelper::get('controller_comments', 'z16', 'Напишите ваш комментарий...'); ?>" 
                              required></textarea>
                    
                    <?php if ($showEmoji && !empty($emojiList)): ?>
                    <button type="button" 
                            class="btn-emoji-trigger position-absolute" 
                            id="emojiTrigger"
                            title="Добавить эмоцию">
                        <svg class="emoji-icon" width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm3.5-9c.83 0 1.5-.67 1.5-1.5S16.33 8 15.5 8 14 8.67 14 9.5s.67 1.5 1.5 1.5zm-7 0c.83 0 1.5-.67 1.5-1.5S9.33 8 8.5 8 7 8.67 7 9.5 7.67 11 8.5 11zm3.5 6.5c2.33 0 4.31-1.46 5.11-3.5H6.89c.8 2.04 2.78 3.5 5.11 3.5z"/>
                        </svg>
                    </button>
                    <?php endif; ?>
                </div>
                
                <div class="emoji-picker-container d-none" id="emojiPicker">
                    <div class="emoji-picker-header">
                        <h6 class="mb-0">Выберите эмоцию</h6>
                        <button type="button" class="btn-close-emoji" id="closeEmojiPicker">
                            <?= bloggy_icon('bs', 'x-lg', '12', 'currentColor', 'mr-1') ?>
                        </button>
                    </div>
                    <div class="emoji-picker-body" id="emojiPickerBody">
                        <?php foreach ($emojiList as $emojiItem): ?>
                            <?php if (!empty($emojiItem['icon'])): ?>
                                <button type="button" class="emoji-item" data-emoji="<?= htmlspecialchars(trim($emojiItem['icon'])) ?>">
                                    <?= trim($emojiItem['icon']) ?>
                                </button>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                    <div class="emoji-picker-footer text-muted">
                        <small>Нажмите на эмоцию, чтобы добавить её в комментарий</small>
                    </div>
                </div>
                
                <small class="text-muted">
                    <i class="bi bi-lightbulb me-1"></i> Минимальная длина: 10 символов
                </small>
            </div>
            
            <div class="d-flex justify-content-between align-items-center">
                
                <div class="d-flex gap-2 align-items-center">
                    <div id="comment-loading" class="spinner-border spinner-border-sm text-primary" style="display: none;" role="status">
                        <span class="visually-hidden">Загрузка...</span>
                    </div>
                    
                    <button type="submit" class="btn btn-dark" id="comment-submit-btn">
                        <?= bloggy_icon('bs', 'pen', '22', 'currentColor', 'mr-1 pb-1') ?>  
                        <?= $parentId ? 'Отправить ответ' : 'Отправить комментарий' ?>
                    </button>
                </div>
            </div>
            
            <?php if (!AuthHelper::canAddCommentWithoutModeration() && !Auth::isAdmin()): ?>
                <div class="alert alert-warning mt-3 mb-0">
                    <div class="d-flex">
                        <i class="bi bi-info-circle fs-5 me-3 mt-1"></i>
                        <div>
                            <strong>Модерация комментариев</strong>
                            <p class="mb-0">
                                Все комментарии проходят проверку модератором перед публикацией.
                                Обычно это занимает не более 10 минут.
                            </p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </form>
        
        <?php if ($parentId): ?>
            <div class="mt-3">
                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="cancelReply()">
                    <i class="bi bi-x-lg me-1"></i> Отменить ответ
                </button>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php endif; ?>