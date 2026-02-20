<div class="container-fluid p-0">
    <div class="row g-4">

    <?php if(SettingsHelper::get('controller_admin', 'all_posts') == true) { ?>
        <div class="col-md-3">
            <div class="card <?php if(SettingsHelper::get('controller_admin', 'show_button') == true) { ?>h-100<?php } ?> border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-light p-3 rounded-3 me-3">
                            <?php echo bloggy_icon('bs', 'pencil', '32', '#000'); ?>
                        </div>
                        <div>
                            <h6 class="card-title mb-1">Посты</h6>
                            <h2 class="mb-0"><?= $stats['posts'] ?? 0 ?></h2>
                        </div>
                    </div>
                    <?php if(SettingsHelper::get('controller_admin', 'show_button') == true) { ?>
                        <a href="<?= ADMIN_URL ?>/posts" class="btn btn-primary btn-sm w-100">Управление</a>
                    <?php } ?>
                </div>
            </div>
        </div>
    <?php } ?>

    <?php if(SettingsHelper::get('controller_admin', 'categories') == true) { ?>
        <div class="col-md-3">
            <div class="card <?php if(SettingsHelper::get('controller_admin', 'show_button') == true) { ?>h-100<?php } ?> border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-light p-3 rounded-3 me-3">
                            <?php echo bloggy_icon('bs', 'folder2-open', '32', '#000'); ?>
                        </div>
                        <div>
                            <h6 class="card-title mb-1">Категории</h6>
                            <h2 class="mb-0"><?= $stats['categories'] ?? 0 ?></h2>
                        </div>
                    </div>
                    <?php if(SettingsHelper::get('controller_admin', 'show_button') == true) { ?>
                        <a href="<?= ADMIN_URL ?>/categories" class="btn btn-primary btn-sm w-100">Управление</a>
                    <?php } ?>
                </div>
            </div>
        </div>
    <?php } ?>

    <?php if(SettingsHelper::get('controller_admin', 'tags') == true) { ?>
        <div class="col-md-3">
            <div class="card <?php if(SettingsHelper::get('controller_admin', 'show_button') == true) { ?>h-100<?php } ?> border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-light p-3 rounded-3 me-3">
                            <?php echo bloggy_icon('bs', 'hash', '32', '#000'); ?>
                        </div>
                        <div>
                            <h6 class="card-title mb-1">Теги</h6>
                            <h2 class="mb-0"><?= $stats['tags'] ?? 0 ?></h2>
                        </div>
                    </div>
                    <?php if(SettingsHelper::get('controller_admin', 'show_button') == true) { ?>
                        <a href="<?= ADMIN_URL ?>/tags" class="btn btn-primary btn-sm w-100">Управление</a>
                    <?php } ?>
                </div>
            </div>
        </div>
    <?php } ?>

    <?php if(SettingsHelper::get('controller_admin', 'pages') == true) { ?>
        <div class="col-md-3">
            <div class="card <?php if(SettingsHelper::get('controller_admin', 'show_button') == true) { ?>h-100<?php } ?> border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-light p-3 rounded-3 me-3">
                            <?php echo bloggy_icon('bs', 'file-earmark-richtext', '32', '#000'); ?>
                        </div>
                        <div>
                            <h6 class="card-title mb-1">Страницы</h6>
                            <h2 class="mb-0"><?= $stats['pages'] ?? 0 ?></h2>
                        </div>
                    </div>
                    <?php if(SettingsHelper::get('controller_admin', 'show_button') == true) { ?>
                        <a href="<?= ADMIN_URL ?>/pages" class="btn btn-primary btn-sm w-100">Управление</a>
                    <?php } ?>
                </div>
            </div>
        </div>
    <?php } ?>

    <?php if(SettingsHelper::get('controller_admin', 'comments') == true) { ?>
        <div class="col-md-3">
            <div class="card <?php if(SettingsHelper::get('controller_admin', 'show_button') == true) { ?>h-100<?php } ?> border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-light p-3 rounded-3 me-3">
                            <?php echo bloggy_icon('bs', 'chat-left-dots', '32', '#000'); ?>
                        </div>
                        <div>
                            <h6 class="card-title mb-1">Комментарии</h6>
                            <h2 class="mb-0"><?= $stats['comments'] ?? 0 ?></h2>
                        </div>
                    </div>
                    <?php if(SettingsHelper::get('controller_admin', 'show_button') == true) { ?>
                        <a href="<?= ADMIN_URL ?>/comments" class="btn btn-primary btn-sm w-100">Управление</a>
                    <?php } ?>
                </div>
            </div>
        </div>
    <?php } ?>

    <?php if(SettingsHelper::get('controller_admin', 'users') == true) { ?>
        <div class="col-md-3">
            <div class="card <?php if(SettingsHelper::get('controller_admin', 'show_button') == true) { ?>h-100<?php } ?> border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-light p-3 rounded-3 me-3">
                            <?php echo bloggy_icon('bs', 'person', '32', '#000'); ?>
                        </div>
                        <div>
                            <h6 class="card-title mb-1">Пользователи</h6>
                            <h2 class="mb-0"><?= $stats['users'] ?? 0 ?></h2>
                        </div>
                    </div>
                    <?php if(SettingsHelper::get('controller_admin', 'show_button') == true) { ?>
                        <a href="<?= ADMIN_URL ?>/users" class="btn btn-primary btn-sm w-100">Управление</a>
                    <?php } ?>
                </div>
            </div>
        </div>
    <?php } ?>

    <?php if(SettingsHelper::get('controller_admin', 'content_blocks') == true) { ?>
        <div class="col-md-3">
            <div class="card <?php if(SettingsHelper::get('controller_admin', 'show_button') == true) { ?>h-100<?php } ?> border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-light p-3 rounded-3 me-3">
                            <?php echo bloggy_icon('bs', 'layout-wtf', '32', '#000'); ?>
                        </div>
                        <div>
                            <h6 class="card-title mb-1">Контент-блоки</h6>
                            <h2 class="mb-0"><?= $stats['content_blocks'] ?? 0 ?></h2>
                        </div>
                    </div>
                    <?php if(SettingsHelper::get('controller_admin', 'show_button') == true) { ?>
                        <a href="<?= ADMIN_URL ?>/html-blocks" class="btn btn-primary btn-sm w-100">Управление</a>
                    <?php } ?>
                </div>
            </div>
        </div>
    <?php } ?>

    <?php if(SettingsHelper::get('controller_admin', 'plugins') == true) { ?>
        <div class="col-md-3">
            <div class="card <?php if(SettingsHelper::get('controller_admin', 'show_button') == true) { ?>h-100<?php } ?> border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-light p-3 rounded-3 me-3">
                            <?php echo bloggy_icon('bs', 'plug', '32', '#000'); ?>
                        </div>
                        <div>
                            <h6 class="card-title mb-1">Плагины</h6>
                            <h2 class="mb-0"><?= $stats['plugins'] ?? 0 ?></h2>
                        </div>
                    </div>
                    <?php if(SettingsHelper::get('controller_admin', 'show_button') == true) { ?>
                        <a href="<?= ADMIN_URL ?>/plugins" class="btn btn-primary btn-sm w-100">Управление</a>
                    <?php } ?>
                </div>
            </div>
        </div>
    <?php } ?>

    <?php if(SettingsHelper::get('controller_admin', 'last_posts') == true) { ?>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 d-flex align-items-center">
                    <div class="p-2 rounded me-3">
                        <?php echo bloggy_icon('bs', 'clock', '20', '#0d6efd'); ?>
                    </div>
                    <h5 class="card-title mb-0">Новые посты</h5>
                </div>
                <div class="card-body p-0">
                    <?php if(!empty($recentPosts)) { ?>
                        <div class="list-group list-group-flush">
                            <?php foreach($recentPosts as $post): ?>
                                <a href="<?= BASE_URL ?>/post/<?= $post['slug'] ?>" target = "blank" class="list-group-item list-group-item-action border-0 py-3 px-4">
                                    <div class="d-flex align-items-start">
                                        <div class="flex-grow-1">
                                            <h6 class="mb-2 text-dark"><?php echo html($post['title']) ?></h6>
                                            <div class="d-flex align-items-center text-muted small">
                                                <?php echo bloggy_icon('bs', 'calendar', '14', '#6c757d', 'me-1'); ?>
                                                <span><?= date('d.m.Y', strtotime($post['created_at'])) ?></span>
                                            </div>
                                        </div>
                                        <div class="flex-shrink-0 ms-2 text-muted">
                                            <?php echo bloggy_icon('bs', 'chevron-right', '16', 'currentColor'); ?>
                                        </div>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php } else { ?>
                        <div class="text-center py-5 text-muted">
                            <?php echo bloggy_icon('bs', 'file-text', '32', '#dee2e6', 'mb-3'); ?>
                            <p class="mb-0">Постов пока нет</p>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    <?php } ?>

    <?php if(SettingsHelper::get('controller_admin', 'popular_posts') == true) { ?>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 d-flex align-items-center">
                    <div class="p-2 rounded me-3">
                        <?php echo bloggy_icon('bs', 'fire', '20', '#198754'); ?>
                    </div>
                    <h5 class="card-title mb-0">Популярные</h5>
                </div>
                <div class="card-body p-0">
                    <?php if(!empty($popularPosts)) { ?>
                        <div class="list-group list-group-flush">
                            <?php foreach($popularPosts as $post): ?>
                                <a href="<?= BASE_URL ?>/post/<?= $post['slug'] ?>" target = "blank" class="list-group-item list-group-item-action border-0 py-3 px-4">
                                    <div class="d-flex align-items-start">
                                        <div class="flex-grow-1">
                                            <h6 class="mb-2 text-dark"><?php echo html($post['title']) ?></h6>
                                            <div class="d-flex align-items-center text-success small">
                                                <?php echo bloggy_icon('bs', 'eye', '14', 'currentColor', 'me-1'); ?>
                                                <span><?php echo html($post['views']) ?></span>
                                            </div>
                                        </div>
                                        <div class="flex-shrink-0 ms-2 text-muted">
                                            <?php echo bloggy_icon('bs', 'chevron-right', '16', 'currentColor'); ?>
                                        </div>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php } else { ?>
                        <div class="text-center py-5 text-muted">
                            <?php echo bloggy_icon('bs', 'bar-chart', '32', '#dee2e6', 'mb-3'); ?>
                            <p class="mb-0">Популярных постов нет</p>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    <?php } ?>

    <?php if(SettingsHelper::get('controller_admin', 'comments_posts') == true) { ?>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 d-flex align-items-center">
                    <div class="p-2 rounded me-3">
                        <?php echo bloggy_icon('bs', 'chat-dots', '20', '#0dcaf0'); ?>
                    </div>
                    <h5 class="card-title mb-0">Обсуждаемые</h5>
                </div>
                <div class="card-body p-0">
                    <?php if(!empty($commentedPosts)) { ?>
                        <div class="list-group list-group-flush">
                            <?php foreach($commentedPosts as $post): ?>
                                <a href="<?= BASE_URL ?>/post/<?= $post['slug'] ?>" target = "blank" class="list-group-item list-group-item-action border-0 py-3 px-4">
                                    <div class="d-flex align-items-start">
                                        <div class="flex-grow-1">
                                            <h6 class="mb-2 text-dark"><?php echo html($post['title']) ?></h6>
                                            <div class="d-flex align-items-center text-info small">
                                                <?php echo bloggy_icon('bs', 'chat', '14', 'currentColor', 'me-1'); ?>
                                                <span><?php echo html($post['comments_count']) ?></span>
                                            </div>
                                        </div>
                                        <div class="flex-shrink-0 ms-2 text-muted">
                                            <?php echo bloggy_icon('bs', 'chevron-right', '16', 'currentColor'); ?>
                                        </div>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php } else { ?>
                        <div class="text-center py-5 text-muted">
                            <?php echo bloggy_icon('bs', 'chat', '32', '#dee2e6', 'mb-3'); ?>
                            <p class="mb-0">Обсуждаемых постов нет</p>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    <?php } ?>

    <?php if(SettingsHelper::get('controller_admin', 'show_drafts') == true) { ?>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 d-flex align-items-center">
                    <div class="p-2 rounded me-3">
                        <?php echo bloggy_icon('bs', 'file-earmark', '20', '#ffc107'); ?>
                    </div>
                    <h5 class="card-title mb-0">Черновики</h5>
                </div>
                <div class="card-body p-0">
                    <?php if(!empty($draftPosts)) { ?>
                        <div class="list-group list-group-flush">
                            <?php foreach($draftPosts as $post): ?>
                                <div class="list-group-item border-0 py-3 px-4">
                                    <div class="d-flex align-items-start">
                                        <div class="flex-grow-1">
                                            <h6 class="mb-2 text-dark"><?php echo html($post['title']) ?></h6>
                                            <div class="d-flex align-items-center text-warning small">
                                                <?php echo bloggy_icon('bs', 'clock', '14', 'currentColor', 'me-1'); ?>
                                                <span><?= date('d.m.Y', strtotime($post['created_at'])) ?></span>
                                            </div>
                                        </div>
                                        <div class="flex-shrink-0 ms-2">
                                            <div class="btn-group btn-group-sm">
                                                <a href="<?= ADMIN_URL ?>/posts/edit/<?= $post['id'] ?>" 
                                                class="btn"
                                                title="Редактировать">
                                                    <?php echo bloggy_icon('bs', 'pencil', '14', 'blue'); ?>
                                                </a>
                                                <a href="<?= ADMIN_URL ?>/posts/toggle-status/<?= $post['id'] ?>" 
                                                class="btn"
                                                title="Опубликовать"
                                                onclick="return confirm('Опубликовать пост?')">
                                                    <?php echo bloggy_icon('bs', 'check-lg ', '14', 'green'); ?>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php } else { ?>
                        <div class="text-center py-5 text-muted">
                            <?php echo bloggy_icon('bs', 'file-earmark', '32', '#dee2e6', 'mb-3'); ?>
                            <p class="mb-0">Черновиков нет</p>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    <?php } ?>
    
    <?php if(SettingsHelper::get('controller_admin', 'show_search') == true) { ?>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0">
                    <h5 class="card-title mb-0">Последние поисковые запросы</h5>
                </div>
                <div class="card-body">
                    <?php if(!empty($recentSearches)): ?>
                        <div class="list-group list-group-flush">
                            <?php foreach($recentSearches as $search): ?>
                                <div class="list-group-item px-0">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-1">
                                                <a href="<?= BASE_URL ?>/search?q=<?= urlencode($search['query']) ?>" 
                                                class="text-decoration-none" target="_blank">
                                                    <?= htmlspecialchars($search['query']) ?>
                                                </a>
                                            </h6>
                                            <small class="text-muted">
                                                <i class="bi bi-search"></i> <?= $search['count'] ?> <?= plural_form($search['count'], ['раз', 'раза', 'раз']) ?>
                                                <i class="bi bi-clock ms-2"></i> 
                                                <?= date('d.m.Y H:i', strtotime($search['last_searched_at'])) ?>
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="mt-3">
                            <a href="<?= ADMIN_URL ?>/search-history" class="btn btn-sm btn-primary">
                                <i class="bi bi-list"></i> Все запросы
                            </a>
                        </div>
                    <?php else: ?>
                        <p class="text-muted text-center py-4">Поисковых запросов пока нет</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php } ?>

    <?php if(SettingsHelper::get('controller_admin', 'show_popular_search') == true) { ?>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0">
                    <h5 class="card-title mb-0">Популярные поисковые запросы</h5>
                </div>
                <div class="card-body">
                    <?php if(!empty($popularSearches)): ?>
                        <div class="list-group list-group-flush">
                            <?php foreach($popularSearches as $search): ?>
                                <div class="list-group-item px-0">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="mb-1">
                                                <a href="<?= BASE_URL ?>/search?q=<?= urlencode($search['query']) ?>" 
                                                class="text-decoration-none" target="_blank">
                                                    <?= htmlspecialchars($search['query']) ?>
                                                </a>
                                            </h6>
                                            <small class="text-muted">
                                                <i class="bi bi-search"></i> <?= $search['count'] ?> <?= plural_form($search['count'], ['раз', 'раза', 'раз']) ?>
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div class="mt-3">
                            <a href="<?= ADMIN_URL ?>/search-history" class="btn btn-sm btn-primary">
                                <i class="bi bi-list"></i> Все запросы
                            </a>
                        </div>
                    <?php else: ?>
                        <p class="text-muted text-center py-4">Популярных поисковых запросов пока нет</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php } ?>

    </div>
</div>