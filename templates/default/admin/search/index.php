<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">
            <i class="bi bi-search me-2"></i>
            История поисковых запросов
        </h4>
        <a href="<?= ADMIN_URL ?>/search-history/clear" 
           class="btn btn-danger"
           onclick="return confirm('Вы уверены, что хотите очистить всю историю поисковых запросов?')">
            <i class="bi bi-trash me-2"></i>Очистить историю
        </a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <?php if(empty($queries)): ?>
                <div class="text-center py-5">
                    <div class="mb-3">
                        <i class="bi bi-search text-muted" style="font-size: 3rem;"></i>
                    </div>
                    <h5 class="text-muted">История поисковых запросов пуста</h5>
                    <p class="text-muted">Здесь будут отображаться поисковые запросы пользователей</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Поисковый запрос</th>
                                <th>Количество поисков</th>
                                <th>Последний поиск</th>
                                <th>Первый поиск</th>
                                <th class="text-end">Действия</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($queries as $query): ?>
                                <tr>
                                    <td>
                                        <a href="<?= BASE_URL ?>/search?q=<?= urlencode($query['query']) ?>" 
                                           class="text-decoration-none"
                                           target="_blank">
                                            <i class="bi bi-search me-2 text-muted"></i>
                                            <?= html($query['query']) ?>
                                        </a>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark">
                                            <?= $query['count'] ?> раз
                                        </span>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            <?= date('d.m.Y H:i', strtotime($query['last_searched_at'])) ?>
                                        </small>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            <?= date('d.m.Y H:i', strtotime($query['created_at'])) ?>
                                        </small>
                                    </td>
                                    <td>
                                        <div class="d-flex justify-content-end">
                                            <a href="<?= ADMIN_URL ?>/search-history/delete/<?= $query['id'] ?>" 
                                               class="btn btn-sm btn-outline-danger"
                                               onclick="return confirm('Вы уверены, что хотите удалить этот запрос?')"
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

                <?php if(isset($pages) && $pages > 1): ?>
                    <nav aria-label="Pagination" class="mt-4">
                        <ul class="pagination justify-content-center">
                            <?php if($current_page > 1): ?>
                                <li class="page-item">
                                    <a class="page-link" href="<?= ADMIN_URL ?>/search-history?page=<?= $current_page - 1 ?>">
                                        <i class="bi bi-chevron-left"></i>
                                    </a>
                                </li>
                            <?php endif; ?>
                            
                            <?php
                            $start = max(1, $current_page - 2);
                            $end = min($pages, $start + 4);
                            if ($end - $start < 4) {
                                $start = max(1, $end - 4);
                            }
                            
                            for ($i = $start; $i <= $end; $i++):
                                $isActive = $i == $current_page;
                            ?>
                                <li class="page-item <?= $isActive ? 'active' : '' ?>">
                                    <a class="page-link" href="<?= ADMIN_URL ?>/search-history?page=<?= $i ?>">
                                        <?= $i ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                            
                            <?php if($current_page < $pages): ?>
                                <li class="page-item">
                                    <a class="page-link" href="<?= ADMIN_URL ?>/search-history?page=<?= $current_page + 1 ?>">
                                        <i class="bi bi-chevron-right"></i>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </nav>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>