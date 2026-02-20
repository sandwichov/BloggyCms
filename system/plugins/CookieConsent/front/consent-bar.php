<div id="cookie-consent-bar" class="cookie-consent <?= $settings['theme'] ?? 'light' ?>" style="display: none;">
    <div class="cookie-message">
        <?= htmlspecialchars($settings['message'] ?? '') ?>
        <a href="<?= htmlspecialchars($settings['policy_link'] ?? '#') ?>" class="cookie-policy-link">
            <?= htmlspecialchars($settings['policy_text'] ?? 'Узнать больше') ?>
        </a>
    </div>
    <div class="cookie-buttons">
        <?php if ($settings['enable_analytics'] ?? false): ?>
            <button type="button" class="btn btn-sm btn-outline-secondary cookie-btn-reject">
                Отклонить
            </button>
        <?php endif; ?>
        <button type="button" class="btn btn-sm btn-primary cookie-btn-accept">
            <?= htmlspecialchars($settings['button_text'] ?? 'Принять') ?>
        </button>
    </div>
</div>