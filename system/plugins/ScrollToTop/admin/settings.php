<?php defined('BASE_PATH') || exit('No direct script access allowed'); ?>

<div class="card border-0 shadow-sm">
    <div class="card-body">
        <form method="post" action="<?= ADMIN_URL ?>/plugins/settings/ScrollToTop">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Позиция кнопки</label>
                        <select name="settings[button_position]" class="form-select">
                            <option value="right" <?= ($settings['button_position'] ?? 'right') === 'right' ? 'selected' : '' ?>>Справа</option>
                            <option value="left" <?= ($settings['button_position'] ?? '') === 'left' ? 'selected' : '' ?>>Слева</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Цвет кнопки</label>
                        <input type="color" 
                               name="settings[button_color]" 
                               value="<?= $settings['button_color'] ?? '#007bff' ?>" 
                               class="form-control form-control-color">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Размер кнопки (px)</label>
                        <input type="number" 
                               name="settings[button_size]" 
                               value="<?= $settings['button_size'] ?? 40 ?>" 
                               class="form-control"
                               min="30"
                               max="60">
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Показывать после прокрутки (px)</label>
                        <input type="number" 
                               name="settings[show_after_scroll]" 
                               value="<?= $settings['show_after_scroll'] ?? 300 ?>" 
                               class="form-control"
                               min="100"
                               max="1000">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Скорость прокрутки (мс)</label>
                        <input type="number" 
                               name="settings[animation_speed]" 
                               value="<?= $settings['animation_speed'] ?? 800 ?>" 
                               class="form-control"
                               min="300"
                               max="2000">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Иконка кнопки</label>
                        <select name="settings[button_icon]" class="form-select">
                            <option value="arrow" <?= ($settings['button_icon'] ?? 'arrow') === 'arrow' ? 'selected' : '' ?>>Стрелка</option>
                            <option value="chevron" <?= ($settings['button_icon'] ?? '') === 'chevron' ? 'selected' : '' ?>>Шеврон</option>
                        </select>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="bi bi-save me-2"></i>Сохранить настройки
            </button>
        </form>
    </div>
</div>