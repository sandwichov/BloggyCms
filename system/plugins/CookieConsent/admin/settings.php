<?php defined('BASE_PATH') || exit('No direct script access allowed'); ?>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0">Настройки Cookie Consent</h5>
    </div>
    <div class="card-body">
        <form method="post" action="<?= ADMIN_URL ?>/plugins/settings/CookieConsent">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Позиция уведомления</label>
                        <select name="settings[position]" class="form-select">
                            <option value="bottom" <?php selected($settings['position'] ?? 'bottom', 'bottom') ?>>Снизу</option>
                            <option value="top" <?php selected($settings['position'] ?? '', 'top') ?>>Сверху</option>
                            <option value="bottom-left" <?php selected($settings['position'] ?? '', 'bottom-left') ?>>Снизу слева</option>
                            <option value="bottom-right" <?php selected($settings['position'] ?? '', 'bottom-right') ?>>Снизу справа</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Тема</label>
                        <select name="settings[theme]" class="form-select">
                            <option value="light" <?php selected($settings['theme'] ?? 'light', 'light') ?>>Светлая</option>
                            <option value="dark" <?php selected($settings['theme'] ?? '', 'dark') ?>>Темная</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Сообщение</label>
                        <textarea name="settings[message]" class="form-control" rows="3"><?= htmlspecialchars($settings['message'] ?? 'Мы используем cookies для улучшения вашего опыта на сайте.') ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Текст кнопки</label>
                        <input type="text" name="settings[button_text]" value="<?= htmlspecialchars($settings['button_text'] ?? 'Принять') ?>" class="form-control">
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Ссылка на политику</label>
                        <input type="text" name="settings[policy_link]" value="<?= htmlspecialchars($settings['policy_link'] ?? '/privacy-policy') ?>" class="form-control">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Текст ссылки</label>
                        <input type="text" name="settings[policy_text]" value="<?= htmlspecialchars($settings['policy_text'] ?? 'Узнать больше') ?>" class="form-control">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Срок хранения (дней)</label>
                        <input type="number" name="settings[expiry_days]" value="<?= $settings['expiry_days'] ?? 30 ?>" class="form-control" min="1" max="365">
                    </div>

                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="settings[enable_analytics]" id="enableAnalytics" <?php checked($settings['enable_analytics'] ?? false, true) ?>>
                        <label class="form-check-label" for="enableAnalytics">Разрешить аналитические cookies</label>
                    </div>

                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="settings[enable_marketing]" id="enableMarketing" <?php checked($settings['enable_marketing'] ?? false, true) ?>>
                        <label class="form-check-label" for="enableMarketing">Разрешить маркетинговые cookies</label>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary mt-3">
                <i class="bi bi-save me-2"></i>Сохранить настройки
            </button>
        </form>
    </div>
</div>