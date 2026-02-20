<div class="row">
    <div class="col-md-6">
        <div class="mb-4">
            <label class="form-label">Название сайта <span class="text-danger">*</span></label>
            <input type="text" name="settings[site_name]" value="<?= $settings['site_name'] ?? '' ?>" class="form-control" placeholder="Введите название сайта" required>
            <div class="form-text">Отображается в заголовке страницы</div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="mb-4">
            <label class="form-label">Слоган сайта</label>
            <input type="text" name="settings[site_tagline]" value="<?= $settings['site_tagline'] ?? '' ?>" class="form-control" placeholder="Краткое описание вашего блога">
            <div class="form-text">Короткий слоган, который описывает ваш блог. По умолчанию нигде не выводится, но Вы можете добавить его самостоятельно в ваш шаблон.</div>
        </div>
    </div>
</div>

<div class="card border-0 bg-light mb-4">
    <div class="card-body">
        <h6 class="card-title mb-3">
            <?php echo bloggy_icon('bs', 'image', '16', '#000', 'me-2 controller-svg'); ?>Favicon сайта
        </h6>
        
        <div class="row">
            <div class="col-md-8">
                <div class="mb-3">
                    <label class="form-label">Загрузить иконку</label>
                    <input type="file" 
                           name="favicon_file" 
                           class="form-control" 
                           id="faviconInput"
                           accept=".ico,.png,.svg,image/x-icon,image/png,image/svg+xml">
                    <div class="form-text">Допустимые форматы: ICO, PNG, SVG. Рекомендуемый размер: 32x32 пикселя</div>
                </div>
                
                <?php if (!empty($settings['favicon'])): ?>
                <div class="mb-2">
                    <label class="form-label">Текущая иконка:</label>
                    <div class="d-flex align-items-center">
                        <div class="me-3">
                            <img src="<?= BASE_URL . '/' . $settings['favicon'] ?>" 
                                 alt="Favicon" 
                                 style="max-width: 32px; max-height: 32px;" 
                                 id="currentFavicon"
                                 onerror="this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'32\' height=\'32\' viewBox=\'0 0 32 32\'%3E%3Crect width=\'32\' height=\'32\' fill=\'%23f0f0f0\'/%3E%3Ctext x=\'16\' y=\'22\' font-size=\'14\' text-anchor=\'middle\' fill=\'%23999\' font-family=\'Arial\'%3E?%3C/text%3E%3C/svg%3E'">
                        </div>
                        <div>
                            <a href="<?= BASE_URL . '/' . $settings['favicon'] ?>" target="_blank" class="btn btn-sm btn-outline-primary me-2">
                                Просмотр
                            </a>
                            <label class="btn btn-sm btn-outline-danger">
                                <input type="checkbox" name="remove_favicon" value="1" style="display: none;"> Удалить
                            </label>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <div class="col-md-4">
                <div class="favicon-preview border rounded p-3 text-center bg-white">
                    <label class="form-label text-muted small">Предпросмотр:</label>
                    <div class="d-flex justify-content-center align-items-center" style="min-height: 64px;">
                        <img src="<?= !empty($settings['favicon']) ? BASE_URL . '/' . $settings['favicon'] : 'data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'32\' height=\'32\' viewBox=\'0 0 32 32\'%3E%3Crect width=\'32\' height=\'32\' fill=\'%23f0f0f0\'/%3E%3Ctext x=\'16\' y=\'22\' font-size=\'14\' text-anchor=\'middle\' fill=\'%23999\' font-family=\'Arial\'%3E?%3C/text%3E%3C/svg%3E' ?>" 
                             alt="Favicon preview" 
                             id="faviconPreview"
                             style="max-width: 64px; max-height: 64px; image-rendering: pixelated;">
                    </div>
                    <div class="mt-2 text-muted small">
                        <span id="faviconFormat">
                            <?php 
                            if (!empty($settings['favicon'])) {
                                $ext = pathinfo($settings['favicon'], PATHINFO_EXTENSION);
                                echo strtoupper($ext);
                            } else {
                                echo 'Не выбран';
                            }
                            ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="mb-4">
    <label class="form-label">Описание сайта для главной страницы</label>
    <textarea name="settings[site_description]" class="form-control" rows="3" placeholder="Подробное описание вашего блога для поисковых систем"><?php echo html($settings['site_description'] ?? '') ?></textarea>
    <div class="form-text">Это описание будет использоваться в мета-теге description для главной страницы. Описание на всех остальных страницах будет генерироваться из настроек контроллеров</div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="mb-4">
            <label class="form-label">Контактный email</label>
            <input type="email" name="settings[contact_email]" value="<?= $settings['contact_email'] ?? '' ?>" class="form-control" placeholder="contact@example.com">
            <div class="form-text">Email для обратной связи</div>
        </div>
    </div>
</div>

<div class="card border-0 bg-light mb-4">
    <div class="card-body">
        <h6 class="card-title mb-3">
            <?php echo bloggy_icon('bs', 'translate', '16', '#000', 'me-2 controller-svg'); ?>Язык и регион
        </h6>
        
        <div class="mb-3">
            <label class="form-label">Формат даты и времени</label>
            <div class="row">
                <div class="col-md-6">
                    <select name="settings[date_format]" class="form-select mb-2">
                        <option value="d.m.Y" <?= ($settings['date_format'] ?? 'd.m.Y') === 'd.m.Y' ? 'selected' : '' ?>>31.12.2025</option>
                        <option value="Y-m-d" <?= ($settings['date_format'] ?? '') === 'Y-m-d' ? 'selected' : '' ?>>2025-12-31</option>
                        <option value="m/d/Y" <?= ($settings['date_format'] ?? '') === 'm/d/Y' ? 'selected' : '' ?>>12/31/2025</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <select name="settings[time_format]" class="form-select">
                        <option value="H:i" <?= ($settings['time_format'] ?? 'H:i') === 'H:i' ? 'selected' : '' ?>>23:59 (24ч)</option>
                        <option value="h:i A" <?= ($settings['time_format'] ?? '') === 'h:i A' ? 'selected' : '' ?>>11:59 PM (12ч)</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 bg-light mb-4">
    <div class="card-body">
        <h6 class="card-title mb-3">
            <?php echo bloggy_icon('bs', 'search', '16', '#000', 'me-2 controller-svg'); ?>Базовые настройки SEO
        </h6>
        
        <div class="mb-3">
            <label class="form-label">Ключевые слова (keywords)</label>
            <textarea name="settings[meta_keywords]" class="form-control" rows="2" placeholder="ключевые, слова, через, запятую"><?php echo html($settings['meta_keywords'] ?? '') ?></textarea>
            <div class="form-text">Ключевые слова для мета-тега keywords</div>
        </div>
        
        <div class="mb-3">
            <label class="form-label">Разработчик сайта</label>
            <input type="text" name="settings[site_author]" value="<?= $settings['site_author'] ?? '' ?>" class="form-control" placeholder="Имя автора или компании">
        </div>
        
        <div class="mb-3">
            <div class="form-check form-switch">
                <input type="checkbox" class="form-check-input" id="enable_sitemap" name="settings[enable_sitemap]" value="1" <?= isset($settings['enable_sitemap']) && $settings['enable_sitemap'] ? 'checked' : '' ?>>
                <label class="form-check-label" for="enable_sitemap">Автоматически генерировать sitemap.xml</label>
            </div>
            <div class="form-text">Sitemap будет доступен по адресу: <?= BASE_URL ?>/sitemap.xml</div>
        </div>
        
        <div class="mb-3">
            <div class="form-check form-switch">
                <input type="checkbox" class="form-check-input" id="enable_robots_txt" name="settings[enable_robots_txt]" value="1" <?= isset($settings['enable_robots_txt']) && $settings['enable_robots_txt'] ? 'checked' : '' ?>>
                <label class="form-check-label" for="enable_robots_txt">Автоматически генерировать robots.txt</label>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 bg-light mb-4">
    <div class="card-body">
        <h6 class="card-title mb-3">
            <?php echo bloggy_icon('bs', 'gear', '16', '#000', 'me-2 controller-svg'); ?>Системные настройки
        </h6>
        
        <div class="mb-3">
            <div class="form-check form-switch">
                <input type="checkbox" class="form-check-input" id="maintenance_mode" name="settings[maintenance_mode]" value="1" <?= isset($settings['maintenance_mode']) && $settings['maintenance_mode'] ? 'checked' : '' ?>>
                <label class="form-check-label" for="maintenance_mode">Режим обслуживания</label>
            </div>
            <div class="form-text">При включении сайт будет недоступен для обычных посетителей</div>
        </div>
        
        <div class="mb-3">
            <label class="form-label">Сообщение в режиме обслуживания</label>
            <textarea name="settings[maintenance_message]" class="form-control" rows="2" placeholder="Сайт временно недоступен. Ведутся технические работы."><?php echo html($settings['maintenance_message'] ?? '') ?></textarea>
        </div>
        
    </div>
</div>

<script>
document.getElementById('faviconInput')?.addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const validTypes = ['image/x-icon', 'image/png', 'image/svg+xml', 'image/vnd.microsoft.icon'];
        const validExtensions = ['.ico', '.png', '.svg'];
        
        const fileExtension = '.' + file.name.split('.').pop().toLowerCase();
        
        if (!validTypes.includes(file.type) && !validExtensions.includes(fileExtension)) {
            alert('Пожалуйста, выберите файл в формате ICO, PNG или SVG');
            e.target.value = '';
            return;
        }
        const reader = new FileReader();
        reader.onload = function(event) {
            document.getElementById('faviconPreview').src = event.target.result;
            document.getElementById('faviconFormat').textContent = fileExtension.substring(1).toUpperCase();
        };
        reader.readAsDataURL(file);
    }
});
</script>

<style>
.favicon-preview {
    background-color: #f8f9fa;
    border: 1px solid #dee2e6;
}

.favicon-preview img {
    image-rendering: -webkit-optimize-contrast;
    image-rendering: crisp-edges;
    -ms-interpolation-mode: nearest-neighbor;
}
</style>