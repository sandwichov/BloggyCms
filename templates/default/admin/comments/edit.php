<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">
            <?php echo bloggy_icon('bs', 'chat-left-text', '24', '#000', 'me-2'); ?>
            Редактирование комментария
        </h4>
        <a href="<?php echo ADMIN_URL; ?>/comments" class="btn btn-outline-secondary btn-sm">
            <?php echo bloggy_icon('bs', 'arrow-left', '16', '#000', 'me-1'); ?>
            Назад к комментариям
        </a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <form action="<?php echo ADMIN_URL; ?>/comments/edit/<?php echo $comment['id']; ?>" method="post">
                <div class="mb-4">
                    <label class="form-label">Комментарий:</label>
                    <textarea 
                        name="content" 
                        id="content" 
                        rows="5" 
                        class="form-control"
                        placeholder="Текст комментария"
                    ><?php echo html($comment['content']); ?></textarea>
                </div>

                <div class="mb-4">
                    <label class="form-label">Статус:</label>
                    <select name="status" id="status" class="form-select">
                        <option value="pending" <?php echo $comment['status'] === 'pending' ? 'selected' : ''; ?>>
                            🕒 На модерации
                        </option>
                        <option value="approved" <?php echo $comment['status'] === 'approved' ? 'selected' : ''; ?>>
                            ✅ Одобрен
                        </option>
                        <option value="spam" <?php echo $comment['status'] === 'spam' ? 'selected' : ''; ?>>
                            ⚠️ Спам
                        </option>
                    </select>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <?php echo bloggy_icon('bs', 'check-lg', '16', '#fff', 'me-1'); ?>
                        Сохранить
                    </button>
                    <a href="<?php echo ADMIN_URL; ?>/comments" class="btn btn-outline-secondary">
                        <?php echo bloggy_icon('bs', 'x-lg', '16', '#000', 'me-1'); ?>
                        Отмена
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>