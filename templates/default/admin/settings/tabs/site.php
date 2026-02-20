<div class="mb-4">
    <label class="form-label">Шаблон сайта</label>
    <select name="settings[site_template]" class="form-select">
        <?php
        $currentTemplate = SettingsHelper::get('site', 'site_template', DEFAULT_TEMPLATE);
        $templatesPath = TEMPLATES_PATH;
        
        if (is_dir($templatesPath)) {
            $templates = array_diff(scandir($templatesPath), ['.', '..']);
            foreach ($templates as $template) {
                if (is_dir($templatesPath . '/' . $template)) {
                    $selected = ($currentTemplate === $template) ? 'selected' : '';
                    echo '<option value="' . html($template) . '" ' . $selected . '>' 
                        . html(ucfirst($template)) . '</option>';
                }
            }
        }
        ?>
    </select>
</div>
<div class="mb-4">
    <label class="form-label">Базовый URL сайта</label>
    <input type="text" name="settings[base_url]" value="<?= SettingsHelper::get('site', 'base_url', BASE_URL) ?>" class="form-control" placeholder="https://ваш-сайт.ru">
    <div class="form-text">
        <small class="text-muted">
            <?php echo bloggy_icon('bs', 'info-circle', '12', '#000', 'controller-svg'); ?> Этот адрес используется для корректной работы ссылок на сайте. 
            Меняйте только если вы перемещаете сайт на другой домен. После изменения потребуется перезагрузка страницы.
            <br>Текущий конфиг: <code><?= BASE_URL ?></code>
        </small>
    </div>
</div>

<div class="card border-0 bg-light mb-4">
    <div class="card-body">
        <h6 class="card-title mb-3">
            <?php echo bloggy_icon('bs', 'files', '16', '#000', 'me-2 controller-svg'); ?>Резервные копии шаблонов
        </h6>
        
        <div class="mb-3">
            <div class="form-check form-switch">
                <input type="checkbox" class="form-check-input" id="template_backups_enabled" name="settings[template_backups_enabled]" value="1" <?= isset($settings['template_backups_enabled']) && $settings['template_backups_enabled'] ? 'checked' : '' ?>>
                <label class="form-check-label fw-medium" for="template_backups_enabled">Создавать резервные копии при редактировании шаблонов</label>
            </div>
            <div class="form-text">Перед сохранением изменений в файлах шаблонов будет создаваться резервная копия</div>
        </div>

        <div class="row ps-4">
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Максимум резервных копий на файл</label>
                    <input type="number" name="settings[template_backups_count]" value="<?= $settings['template_backups_count'] ?? 5 ?>" class="form-control" min="1" max="20" <?= !isset($settings['template_backups_enabled']) || !$settings['template_backups_enabled'] ? 'disabled' : '' ?>>
                    <div class="form-text">Сколько последних версий файлов хранить</div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-3">
                    <label class="form-label">Автоматически удалять старые копии</label>
                    <select name="settings[template_backups_cleanup]" class="form-select"
                        <?= !isset($settings['template_backups_enabled']) || !$settings['template_backups_enabled'] ? 'disabled' : '' ?>>
                        <option value="auto" <?= ($settings['template_backups_cleanup'] ?? 'auto') === 'auto' ? 'selected' : '' ?>>Автоматически</option>
                        <option value="manual" <?= ($settings['template_backups_cleanup'] ?? '') === 'manual' ? 'selected' : '' ?>>Вручную</option>
                        <option value="never" <?= ($settings['template_backups_cleanup'] ?? '') === 'never' ? 'selected' : '' ?>>Никогда не удалять</option>
                    </select>
                    <div class="form-text">Управление очисткой старых резервных копий</div>
                </div>
            </div>
        </div>

        <?php $backupStats = BackupHelper::getBackupStats();
            
            if ($backupStats['total_files'] > 0) { ?> 
            
                <div class="mt-3 p-3 bg-white rounded border">
                    <h6 class="mb-2">Статистика резервных копий</h6>
                    <div class="row small text-muted">
                        <div class="col-md-4">
                            <i class="bi bi-folder me-1"></i> Файлов: <?= $backupStats['total_files'] ?>
                        </div>
                        <div class="col-md-4">
                            <i class="bi bi-hdd me-1"></i> Размер: <?= $backupStats['total_size'] ?>
                        </div>
                        <div class="col-md-4">
                            <i class="bi bi-clock me-1"></i> Старший: <?= $backupStats['oldest_backup'] ?>
                        </div>
                    </div>
                    <?php if ($backupStats['total_files'] > 0) { ?>
                    <div class="mt-2">
                        <a href="<?= ADMIN_URL ?>/settings/cleanup-backups" class="btn btn-sm btn-outline-danger" onclick="return confirm('Вы уверены? Это действие нельзя отменить.')">
                            <?php echo bloggy_icon('bs', 'files', '18', '#000', 'me-1'); ?> Очистить все резервные копии
                        </a>
                    </div>
                    <?php } ?>
                </div>
        <?php } ?>
    </div>
</div>