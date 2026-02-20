<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">
            <i class="bi bi-chat-left-text me-2"></i>
            Редактирование комментария
        </h4>
        <a href="<?= ADMIN_URL ?>/comments" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Назад к комментариям
        </a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <form action="<?= ADMIN_URL ?>/comments/edit/<?= $comment['id'] ?>" method="post">
                <div class="mb-4">
                    <label class="form-label">Комментарий:</label>
                    <textarea 
                        name="content" 
                        id="content" 
                        rows="5" 
                        class="form-control"
                        placeholder="Текст комментария"
                    ><?= html($comment['content']) ?></textarea>
                </div>

                <div class="mb-4">
                    <label class="form-label">Статус:</label>
                    <select name="status" id="status" class="form-select">
                        <option value="pending" <?= $comment['status'] === 'pending' ? 'selected' : '' ?>>
                            🕒 На модерации
                        </option>
                        <option value="approved" <?= $comment['status'] === 'approved' ? 'selected' : '' ?>>
                            ✅ Одобрен
                        </option>
                        <option value="spam" <?= $comment['status'] === 'spam' ? 'selected' : '' ?>>
                            ⚠️ Спам
                        </option>
                    </select>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i> Сохранить
                    </button>
                    <a href="<?= ADMIN_URL ?>/comments" class="btn btn-outline-secondary">
                        <i class="bi bi-x-lg me-1"></i> Отмена
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>