<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">
            <i class="bi bi-folder-plus me-2"></i>
            <?= isset($category['id']) ? 'Редактирование категории' : 'Создание категории' ?>
        </h4>
        <a href="<?= ADMIN_URL ?>/docs/categories" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Назад к списку
        </a>
    </div>

    <form method="post">
        <div class="row">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0">
                        <h5 class="card-title mb-0">Основная информация</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-4">
                            <label class="form-label">Название категории <span class="text-danger">*</span></label>
                            <input type="text" 
                                   name="name" 
                                   class="form-control form-control-lg" 
                                   value="<?= htmlspecialchars($category['name'] ?? '') ?>" 
                                   placeholder="Введите название категории"
                                   required>
                        </div>
                        
                        <?php if (isset($category['slug'])): ?>
                        <div class="mb-4">
                            <label class="form-label">URL Slug</label>
                            <div class="input-group">
                                <span class="input-group-text"><?= BASE_URL ?>/docs/category/</span>
                                <input type="text" 
                                       name="slug" 
                                       class="form-control" 
                                       value="<?= htmlspecialchars($category['slug']) ?>" 
                                       placeholder="автоматически-сгенерированный-url">
                            </div>
                            <div class="form-text">Уникальный идентификатор в URL</div>
                        </div>
                        <?php endif; ?>
                        
                        <div class="mb-4">
                            <label class="form-label">Описание</label>
                            <textarea name="description" 
                                      class="form-control" 
                                      rows="4" 
                                      placeholder="Добавьте описание категории"><?= htmlspecialchars($category['description'] ?? '') ?></textarea>
                            <div class="form-text">Это описание будет отображаться на странице категории</div>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label">Родительская категория</label>
                            <select name="parent_id" class="form-select">
                                <option value="0">Нет (корневая категория)</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>" 
                                        <?= ($category['parent_id'] ?? 0) == $cat['id'] ? 'selected' : '' ?>
                                        style="padding-left: <?= ($cat['level'] ?? 0) * 20 ?>px">
                                        <?= htmlspecialchars($cat['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0">
                        <h5 class="card-title mb-0">SEO настройки</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">SEO Title</label>
                            <input type="text" 
                                   name="meta_title" 
                                   class="form-control" 
                                   value="<?= htmlspecialchars($category['meta_title'] ?? '') ?>" 
                                   placeholder="Если не указан, используется название категории">
                            <div class="form-text">До 60 символов</div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Meta Description</label>
                            <textarea name="meta_description" 
                                      class="form-control" 
                                      rows="3"
                                      placeholder="Краткое описание для поисковых систем"><?= htmlspecialchars($category['meta_description'] ?? '') ?></textarea>
                            <div class="form-text">До 160 символов</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0">
                        <h5 class="card-title mb-0">Настройки сортировки</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Порядок сортировки</label>
                            <input type="number" 
                                   name="sort_order" 
                                   class="form-control" 
                                   value="<?= $category['sort_order'] ?? 0 ?>" 
                                   min="0" 
                                   max="999">
                            <div class="form-text">Чем меньше число, тем выше в списке. 0 - по умолчанию</div>
                        </div>
                    </div>
                    <div class="card-footer bg-white border-0">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-lg me-1"></i> 
                                <?= isset($category['id']) ? 'Обновить категорию' : 'Создать категорию' ?>
                            </button>
                            <a href="<?= ADMIN_URL ?>/docs/categories" class="btn btn-outline-secondary">
                                <i class="bi bi-x-lg me-1"></i> Отмена
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>