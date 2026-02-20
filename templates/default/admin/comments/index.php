<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">
            <i class="bi bi-chat-dots me-2"></i>
            Управление комментариями
        </h4>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Пост</th>
                            <th>Автор</th>
                            <th>Комментарий</th>
                            <th>Статус</th>
                            <th>Дата</th>
                            <th class="text-end">Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($comments as $comment): ?>
                            <tr>
                                <td class="text-muted">#<?= $comment['id'] ?></td>
                                <td>
                                    <div class="text-truncate" style="max-width: 200px;">
                                        <?= html($comment['post_title']) ?>
                                    </div>
                                </td>
                                <td>
                                    <?= html($comment['author_username'] ?? $comment['author_name']) ?>
                                </td>
                                <td>
                                    <div class="text-truncate" style="max-width: 300px;">
                                        <?= nl2br(html($comment['content'])) ?>
                                    </div>
                                </td>
                                <td>
                                    <?php
                                    $statusClass = [
                                        'pending' => 'warning',
                                        'approved' => 'success',
                                        'spam' => 'danger'
                                    ][$comment['status']] ?? 'secondary';
                                    ?>
                                    <span class="badge bg-<?= $statusClass ?>">
                                        <?= $comment['status'] ?>
                                    </span>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        <?= date('d.m.Y H:i', strtotime($comment['created_at'])) ?>
                                    </small>
                                </td>
                                <td>
                                    <div class="d-flex justify-content-end gap-2">
                                        <a href="<?= ADMIN_URL ?>/comments/edit/<?= $comment['id'] ?>" 
                                           class="btn btn-sm btn-outline-primary"
                                           title="Редактировать">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <?php if ($comment['status'] === 'pending'): ?>
                                            <a href="<?= ADMIN_URL ?>/comments/approve/<?= $comment['id'] ?>" 
                                               class="btn btn-sm btn-outline-success"
                                               title="Одобрить">
                                                <i class="bi bi-check-lg"></i>
                                            </a>
                                        <?php endif; ?>
                                        <a href="<?= ADMIN_URL ?>/comments/delete/<?= $comment['id'] ?>" 
                                           class="btn btn-sm btn-outline-danger"
                                           onclick="return confirm('Вы уверены, что хотите удалить этот комментарий?')"
                                           title="Удалить">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($pages > 1): ?>
                <nav class="mt-4">
                    <ul class="pagination justify-content-center">
                        <?php for ($i = 1; $i <= $pages; $i++): ?>
                            <li class="page-item <?= $i === $current_page ? 'active' : '' ?>">
                                <a class="page-link" href="<?= ADMIN_URL ?>/comments?page=<?= $i ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        </div>
    </div>
</div>