<?php
/**
 * Template Name: Страница регистрации
 */

$registrationDisabled = $registration_disabled ?? false;
$errorMessage = $error ?? '';
?>

<?php if ($registrationDisabled) { ?>

<div class="tg-page">
    <div class="tg-container tg-container-sm">
        <div class="tg-card tg-text-center">
            <div class="tg-card-body" style="padding: 48px 32px;">
                <div class="tg-icon-large tg-mb-3" style="color: #dc3545;">
                    <?php echo bloggy_icon('bs', 'person-x', '48', 'currentColor'); ?>
                </div>
                
                <h2 class="tg-title tg-mb-2">Регистрация закрыта</h2>
                
                <?php if ($errorMessage) { ?>
                <p class="tg-text-muted tg-mb-4"><?php echo htmlspecialchars($errorMessage); ?></p>
                <?php } else { ?>
                <p class="tg-text-muted tg-mb-4">Регистрация новых пользователей временно недоступна</p>
                <?php } ?>
                
                <div class="tg-divider tg-mb-4"></div>
                
                <a href="<?php echo BASE_URL; ?>/login" class="tg-btn tg-btn-primary">
                    <?php echo bloggy_icon('bs', 'box-arrow-in-right', '16', 'currentColor', 'tg-mr-1'); ?>
                    Войти
                </a>
            </div>
        </div>
    </div>
</div>

<?php  } else { ?>

    <div class="tg-page">
        <div class="tg-container tg-container-sm">
            
            <div class="tg-page-header">
                <h1 class="tg-page-title">Создать аккаунт</h1>
                <p class="tg-page-subtitle">Присоединяйтесь к нашему сообществу</p>
            </div>
            
            <div class="tg-benefits tg-mb-4">
                <div class="tg-benefit-item">
                    <?php echo bloggy_icon('bs', 'chat-dots', '16', 'var(--tg-primary)'); ?>
                    <span>Комментировать</span>
                </div>
                <div class="tg-benefit-item">
                    <?php echo bloggy_icon('bs', 'person-badge', '16', 'var(--tg-primary)'); ?>
                    <span>Профиль</span>
                </div>
                <div class="tg-benefit-item">
                    <?php echo bloggy_icon('bs', 'download', '16', 'var(--tg-primary)'); ?>
                    <span>Загрузки</span>
                </div>
                <div class="tg-benefit-item">
                    <?php echo bloggy_icon('bs', 'trophy', '16', 'var(--tg-primary)'); ?>
                    <span>Достижения</span>
                </div>
            </div>
            
            <div class="tg-card">
                <div class="tg-card-body">
                    
                    <?php if (isset($error) && $error) { ?>
                    <div class="tg-alert tg-alert-error tg-mb-4">
                        <div class="tg-alert-icon">
                            <?php echo bloggy_icon('bs', 'exclamation-triangle', '18', 'currentColor'); ?>
                        </div>
                        <div class="tg-alert-content">
                            <strong>Ошибка:</strong> <?php echo htmlspecialchars($error); ?>
                        </div>
                    </div>
                    <?php } ?>
                    
                    <form method="post" action="<?php echo BASE_URL; ?>/register" id="tg-register-form">
                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                        
                        <div class="tg-field">
                            <label for="username" class="tg-label">
                                Имя пользователя <span class="tg-text-muted">*</span>
                                <span class="tg-field-status" id="username-status"></span>
                            </label>
                            <div class="tg-input-wrapper">
                                <span class="tg-input-icon">
                                    <?php echo bloggy_icon('bs', 'person', '16', 'currentColor'); ?>
                                </span>
                                <input type="text" 
                                    id="username"
                                    name="username" 
                                    class="tg-input" 
                                    placeholder="ivanov" 
                                    required 
                                    value="<?php echo htmlspecialchars($username ?? ''); ?>" 
                                    minlength="3"
                                    maxlength="30"
                                    pattern="[a-zA-Z0-9_]+"
                                    title="Только буквы, цифры и знак подчеркивания">
                            </div>
                            <div class="tg-field-hint">
                                3-30 символов, буквы, цифры и _
                            </div>
                        </div>
                        
                        <div class="tg-field">
                            <label for="display_name" class="tg-label">Отображаемое имя</label>
                            <div class="tg-input-wrapper">
                                <span class="tg-input-icon">
                                    <?php echo bloggy_icon('bs', 'person-badge', '16', 'currentColor'); ?>
                                </span>
                                <input type="text" 
                                    id="display_name"
                                    name="display_name" 
                                    class="tg-input" 
                                    placeholder="Иван Иванов"
                                    value="<?php echo htmlspecialchars($display_name ?? ''); ?>">
                            </div>
                            <div class="tg-field-hint">
                                Если не указано, будет использовано имя пользователя
                            </div>
                        </div>
                        
                        <div class="tg-field">
                            <label for="email" class="tg-label">
                                Email <span class="tg-text-muted">*</span>
                                <span class="tg-field-status" id="email-status"></span>
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
                                    value="<?php echo htmlspecialchars($email ?? ''); ?>">
                            </div>
                        </div>
                        
                        <div class="tg-field">
                            <label for="password" class="tg-label">
                                Пароль <span class="tg-text-muted">*</span>
                                <span class="tg-field-status" id="password-strength"></span>
                            </label>
                            <div class="tg-input-wrapper">
                                <span class="tg-input-icon">
                                    <?php echo bloggy_icon('bs', 'lock', '16', 'currentColor'); ?>
                                </span>
                                <input type="password" 
                                    id="password"
                                    name="password" 
                                    class="tg-input" 
                                    placeholder="••••••••" 
                                    required 
                                    minlength="6">
                                <button type="button" class="tg-input-toggle" id="toggle-password">
                                    <?php echo bloggy_icon('bs', 'eye', '16', 'currentColor'); ?>
                                </button>
                            </div>
                            <div class="tg-field-requirements tg-mt-2">
                                <div class="tg-requirement" id="req-length">
                                    <?php echo bloggy_icon('bs', 'dash-circle', '12', 'var(--tg-text-secondary)', 'tg-mr-1'); ?>
                                    Минимум 6 символов
                                </div>
                            </div>
                        </div>
                        
                        <div class="tg-field">
                            <label for="password_confirm" class="tg-label">
                                Подтверждение пароля <span class="tg-text-muted">*</span>
                                <span class="tg-field-status" id="password-match"></span>
                            </label>
                            <div class="tg-input-wrapper">
                                <span class="tg-input-icon">
                                    <?php echo bloggy_icon('bs', 'lock-fill', '16', 'currentColor'); ?>
                                </span>
                                <input type="password" 
                                    id="password_confirm"
                                    name="password_confirm" 
                                    class="tg-input" 
                                    placeholder="••••••••" 
                                    required>
                                <button type="button" class="tg-input-toggle" id="toggle-confirm-password">
                                    <?php echo bloggy_icon('bs', 'eye', '16', 'currentColor'); ?>
                                </button>
                            </div>
                        </div>
                        
                        <div class="tg-field tg-field-row">
                            <label class="tg-checkbox">
                                <input type="checkbox" name="terms" id="terms" required>
                                <span class="tg-checkbox-mark"></span>
                                <span class="tg-checkbox-label">
                                    Я принимаю 
                                    <a href="<?php echo BASE_URL; ?>/terms" target="_blank" class="tg-link">Условия использования</a>
                                    и 
                                    <a href="<?php echo BASE_URL; ?>/privacy" target="_blank" class="tg-link">Политику конфиденциальности</a>
                                </span>
                            </label>
                        </div>
                        
                        <button type="submit" class="tg-btn tg-btn-primary tg-btn-block" id="submit-btn">
                            <?php echo bloggy_icon('bs', 'person-plus', '16', 'currentColor', 'tg-mr-1'); ?>
                            Создать аккаунт
                        </button>
                    </form>
                    
                    <div class="tg-form-footer">
                        <div class="tg-login-links">
                            <a href="<?php echo BASE_URL; ?>/login" class="tg-link">
                                <?php echo bloggy_icon('bs', 'box-arrow-in-right', '14', 'currentColor', 'tg-mr-1'); ?>
                                Уже есть аккаунт?
                            </a>
                            
                            <span class="tg-link-sep">•</span>
                            
                            <a href="<?php echo BASE_URL; ?>/forgot-password" class="tg-link">
                                <?php echo bloggy_icon('bs', 'key', '14', 'currentColor', 'tg-mr-1'); ?>
                                Забыли пароль?
                            </a>
                        </div>
                        
                        <div class="tg-login-security">
                            <small class="tg-text-muted">
                                <?php echo bloggy_icon('bs', 'shield-check', '12', 'currentColor', 'tg-mr-1'); ?>
                                Ваши данные в безопасности
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php } ?>