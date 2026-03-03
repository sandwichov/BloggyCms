<?php
/**
 * Template Name: Восстановление пароля
 */

$success = $success ?? false;
$error = $error ?? '';
$email = $email ?? '';
?>

<div class="tg-forgot-page">
    <div class="tg-container tg-container-sm" style = "max-width: 760px;">
        
        <div class="tg-forgot-header">
            <h1 class="tg-forgot-title">Восстановление пароля</h1>
            <p class="tg-forgot-subtitle">Забыли пароль? Мы поможем вам восстановить доступ</p>
        </div>
        
        <div class="tg-card">
            <div class="tg-card-body">
                
                <?php if ($success) { ?>
                <div class="tg-alert tg-alert-success tg-mb-4">
                    <div class="tg-alert-icon">
                        <?php echo bloggy_icon('bs', 'check-circle', '18', 'currentColor'); ?>
                    </div>
                    <div class="tg-alert-content">
                        <strong>Письмо отправлено!</strong>
                        <p>На указанный email отправлена ссылка для восстановления пароля.</p>
                        <div class="tg-alert-small">Проверьте папку "Спам", если не нашли письмо.</div>
                    </div>
                </div>
                <?php } elseif ($error) { ?>
                <div class="tg-alert tg-alert-error tg-mb-4">
                    <div class="tg-alert-icon">
                        <?php echo bloggy_icon('bs', 'exclamation-triangle', '18', 'currentColor'); ?>
                    </div>
                    <div class="tg-alert-content">
                        <strong>Ошибка:</strong> <?php echo htmlspecialchars($error); ?>
                    </div>
                </div>
                <?php } ?>
                
                <form method="post" action="">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    
                    <div class="tg-field">
                        <label for="email" class="tg-label">
                            Email <span class="tg-text-muted">*</span>
                        </label>
                        <div class="tg-input-wrapper">
                            <span class="tg-input-icon">
                                <?php echo bloggy_icon('bs', 'envelope', '16', 'currentColor'); ?>
                            </span>
                            <input type="email" 
                                   id="email"
                                   name="email" 
                                   class="tg-input" 
                                   placeholder="ваш@email.ru" 
                                   required 
                                   value="<?php echo htmlspecialchars($email); ?>" 
                                   autofocus>
                        </div>
                        <div class="tg-field-hint">
                            <?php echo bloggy_icon('bs', 'info-circle', '12', 'var(--tg-text-muted)', 'tg-mr-1'); ?>
                            На этот email будет отправлена ссылка для восстановления
                        </div>
                    </div>
                    
                    <div class="tg-info-box tg-mb-4">
                        <div class="tg-info-box-icon">
                            <?php echo bloggy_icon('bs', 'shield-exclamation', '16', 'var(--tg-primary)'); ?>
                        </div>
                        <div class="tg-info-box-content">
                            <strong>Безопасность:</strong>
                            <span>Ссылка для восстановления действительна только 1 час.</span>
                            <small class="tg-text-muted">После истечения этого времени необходимо запросить новую ссылку.</small>
                        </div>
                    </div>
                    
                    <button type="submit" class="tg-btn tg-btn-primary tg-btn-block">
                        <?php echo bloggy_icon('bs', 'send', '16', 'currentColor', 'tg-mr-1'); ?>
                        Отправить ссылку для восстановления
                    </button>
                </form>
                
                <div class="tg-forgot-footer tg-mt-4">
                    <div class="tg-login-links">
                        <a href="<?php echo BASE_URL; ?>/login" class="tg-link">
                            <?php echo bloggy_icon('bs', 'box-arrow-in-right', '14', 'currentColor', 'tg-mr-1'); ?>
                            Войти в аккаунт
                        </a>
                        
                        <span class="tg-link-sep">•</span>
                        
                        <a href="<?php echo BASE_URL; ?>/register" class="tg-link">
                            <?php echo bloggy_icon('bs', 'person-plus', '14', 'currentColor', 'tg-mr-1'); ?>
                            Создать аккаунт
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Дополнительные стили для информационного блока */
.tg-info-box {
    background: var(--tg-bg-light, #f8f9fa);
    border-radius: 12px;
    padding: 16px;
    display: flex;
    gap: 12px;
    border: 1px solid var(--tg-border-light, #e9ecef);
}

.tg-info-box-icon {
    flex-shrink: 0;
    width: 32px;
    height: 32px;
    background: rgba(var(--tg-primary-rgb, 13, 110, 253), 0.1);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--tg-primary);
}

.tg-info-box-content {
    display: flex;
    flex-direction: column;
    gap: 4px;
    font-size: 14px;
    line-height: 1.5;
}

.tg-info-box-content small {
    font-size: 12px;
    color: var(--tg-text-muted);
}

/* Стили для преимуществ */
.tg-forgot-benefits {
    display: flex;
    justify-content: center;
    gap: 24px;
    flex-wrap: wrap;
    margin: 24px 0;
}

.tg-forgot-benefits .tg-benefit-item {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
    color: var(--tg-text-secondary);
}
</style>