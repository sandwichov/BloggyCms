<?php
add_admin_js('templates/default/admin/assets/js/controllers/ckeditor.js');
add_admin_js('templates/default/admin/assets/js/controllers/docs-form.js');
?>

<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">
            <i class="bi bi-journal-text me-2"></i>
            <?= isset($article['id']) ? 'Редактирование статьи' : 'Создание статьи' ?>
        </h4>
        <a href="<?= ADMIN_URL ?>/docs" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Назад к списку
        </a>
    </div>

    <form method="post" id="docs-form">
        <div class="row">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0">
                        <h5 class="card-title mb-0">Содержание статьи</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-4">
                            <label class="form-label">Заголовок статьи <span class="text-danger">*</span></label>
                            <input type="text" 
                                   name="title" 
                                   class="form-control form-control-lg" 
                                   value="<?= htmlspecialchars($article['title'] ?? '') ?>" 
                                   placeholder="Введите заголовок статьи"
                                   required>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label">Краткое описание (Excerpt)</label>
                            <textarea name="excerpt" 
                                      class="form-control" 
                                      rows="3" 
                                      placeholder="Краткое описание статьи, которое будет отображаться в списках"><?= htmlspecialchars($article['excerpt'] ?? '') ?></textarea>
                            <div class="form-text">До 255 символов</div>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label">Содержание статьи <span class="text-danger">*</span></label>
                            <textarea name="content" 
                                      id="content-editor" 
                                      class="form-control" 
                                      rows="15"
                                      placeholder="Полное содержание статьи"><?= htmlspecialchars($article['content'] ?? '') ?></textarea>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0">
                        <h5 class="card-title mb-0">Связанные статьи</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Выберите связанные статьи</label>
                            <select name="related_articles[]" 
                                    class="form-select" 
                                    multiple 
                                    size="5">
                                <option value="">-- Выберите статьи --</option>
                                <?php foreach ($allArticles as $art): ?>
                                    <?php if (isset($article['id']) && $art['id'] == $article['id']) continue; ?>
                                    <option value="<?= $art['id'] ?>" 
                                        <?= in_array($art['id'], $relatedArticles ?? []) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($art['title']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text">
                                Удерживайте Ctrl (Cmd на Mac) для выбора нескольких статей
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0">
                        <h5 class="card-title mb-0">Параметры публикации</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-4">
                            <label class="form-label">Статус статьи</label>
                            <select name="status" class="form-select" required>
                                <option value="draft" <?= ($article['status'] ?? 'draft') == 'draft' ? 'selected' : '' ?>>Черновик</option>
                                <option value="published" <?= ($article['status'] ?? '') == 'published' ? 'selected' : '' ?>>Опубликована</option>
                                <option value="archived" <?= ($article['status'] ?? '') == 'archived' ? 'selected' : '' ?>>В архиве</option>
                            </select>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label">Категория</label>
                            <select name="category_id" class="form-select">
                                <option value="">Без категории</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>" 
                                        <?= ($article['category_id'] ?? 0) == $cat['id'] ? 'selected' : '' ?>
                                        style="padding-left: <?= ($cat['level'] ?? 0) * 20 ?>px">
                                        <?= htmlspecialchars($cat['name']) ?>
                                        <?php if (isset($cat['articles_count'])): ?>
                                            (<?= $cat['articles_count'] ?>)
                                        <?php endif; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mb-4">
                            <div class="form-check form-switch">
                                <input class="form-check-input" 
                                       type="checkbox" 
                                       id="is_featured" 
                                       name="is_featured"
                                       <?= ($article['is_featured'] ?? 0) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="is_featured">
                                    Избранная статья
                                </label>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label">Порядок сортировки</label>
                            <input type="number" 
                                   name="sort_order" 
                                   class="form-control" 
                                   value="<?= $article['sort_order'] ?? 0 ?>" 
                                   min="0" 
                                   max="999">
                            <div class="form-text">Чем меньше число, тем выше в списке</div>
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
                                   value="<?= htmlspecialchars($article['meta_title'] ?? '') ?>" 
                                   placeholder="Если не указан, используется заголовок статьи">
                            <div class="form-text">До 60 символов</div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Meta Description</label>
                            <textarea name="meta_description" 
                                      class="form-control" 
                                      rows="3"
                                      placeholder="Краткое описание для поисковых систем"><?= htmlspecialchars($article['meta_description'] ?? '') ?></textarea>
                            <div class="form-text">До 160 символов</div>
                        </div>
                        
                        <?php if (isset($article['slug'])): ?>
                        <div class="mb-3">
                            <label class="form-label">URL Slug</label>
                            <div class="input-group">
                                <span class="input-group-text"><?= BASE_URL ?>/docs/</span>
                                <input type="text" 
                                       name="slug" 
                                       class="form-control" 
                                       value="<?= htmlspecialchars($article['slug']) ?>" 
                                       placeholder="автоматически-сгенерированный-url">
                            </div>
                            <div class="form-text">Уникальный идентификатор в URL</div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg me-1"></i> 
                        <?= isset($article['id']) ? 'Обновить статью' : 'Создать статью' ?>
                    </button>
                    <a href="<?= ADMIN_URL ?>/docs" class="btn btn-outline-secondary">
                        <i class="bi bi-x-lg me-1"></i> Отмена
                    </a>
                </div>
            </div>
        </div>
    </form>
</div>

<?php ob_start(); ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Инициализация CKEditor
    if (typeof ClassicEditor !== 'undefined') {
        ClassicEditor
            .create(document.querySelector('#content-editor'), {
                toolbar: {
                    items: [
                        'heading', '|',
                        'bold', 'italic', 'underline', 'strikethrough', '|',
                        'link', 'bulletedList', 'numberedList', '|',
                        'alignment', 'outdent', 'indent', '|',
                        'blockQuote', 'insertTable', 'undo', 'redo'
                    ]
                },
                language: 'ru',
                link: {
                    addTargetToExternalLinks: true
                }
            })
            .catch(error => {
                console.error(error);
            });
    }
});
</script>
<?php admin_bottom_js(ob_get_clean()); ?>