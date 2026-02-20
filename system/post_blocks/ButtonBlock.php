<?php
class ButtonBlock extends BasePostBlock {
    
    public function getName(): string {
        return 'Кнопка';
    }

    public function getSystemName(): string {
        return 'ButtonBlock';
    }

    public function getDescription(): string {
        return 'Блок для создания стильной кнопки с ссылкой и настройками внешнего вида';
    }

    public function getIcon(): string {
        return 'bi bi-link-45deg';
    }

    public function getCategory(): string {
        return 'interactive';
    }

    public function getPreviewHtml($content = [], $settings = []): string {
        $content = $this->validateAndNormalizeContent($content);
        $settings = $this->validateAndNormalizeSettings($settings);
        
        $text = $content['text'] ?? 'Нажми меня';
        $url = $content['url'] ?? '#';
        $target = $content['target'] ?? '_self';
        $style = $settings['style'] ?? 'primary';
        $size = $settings['size'] ?? 'medium';
        $alignment = $settings['alignment'] ?? 'left';
        $fullWidth = $settings['full_width'] ?? '';
        $iconBefore = $settings['icon_before'] ?? '';
        $iconAfter = $settings['icon_after'] ?? '';
        $download = $settings['download'] ?? false;
        $sizeClass = $this->getSizeClass($size);
        $alignmentClass = $this->getAlignmentClass($alignment);
        $isFullWidth = $fullWidth === 'btn-block' ? 'btn-block' : '';
        
        ob_start();
        ?>
        <div class="post-block-preview post-block-preview-ButtonBlock full-content-preview">
            <div class="preview-wrapper">
                <div class="preview-header">
                    <div class="preview-header-content">
                        <div class="preview-icon">
                            <i class="bi bi-link-45deg"></i>
                        </div>
                        <div class="preview-info">
                            <div class="preview-title">
                                <strong>Кнопка</strong>
                                <?php if ($style !== 'primary'): ?>
                                    <span class="badge bg-info badge-sm"><?= htmlspecialchars($style) ?></span>
                                <?php endif; ?>
                                <?php if ($size !== 'medium'): ?>
                                    <span class="badge bg-secondary badge-sm"><?= htmlspecialchars($size) ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="preview-stats">
                                <?= strlen($text) ?> симв.
                                <?php if ($target === '_blank'): ?>
                                    · новое окно
                                <?php endif; ?>
                                <?php if ($download): ?>
                                    · скачать
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="preview-actions">
                        <button type="button" class="btn btn-xs btn-outline-secondary preview-edit-btn" 
                                onclick="postBlocksManager.editBlock('{block_id}')">
                            <i class="bi bi-pencil"></i>
                        </button>
                    </div>
                </div>
                
                <div class="preview-body">
                    <?php if (!empty(trim($text))): ?>
                        <div class="button-preview-container <?= htmlspecialchars($alignmentClass) ?>">
                            <a href="#" 
                            class="btn btn-<?= htmlspecialchars($style) ?> <?= htmlspecialchars($sizeClass) ?> <?= htmlspecialchars($isFullWidth) ?>"
                            style="pointer-events: none; cursor: default; opacity: 0.8;">
                                <?php if ($iconBefore): ?>
                                    <i class="<?= htmlspecialchars($iconBefore) ?> me-1"></i>
                                <?php endif; ?>
                                <?= htmlspecialchars($text) ?>
                                <?php if ($iconAfter): ?>
                                    <i class="<?= htmlspecialchars($iconAfter) ?> ms-1"></i>
                                <?php endif; ?>
                            </a>
                            <div class="mt-2 small text-muted">
                                <span>URL: <?= htmlspecialchars($url) ?></span>
                                <?php if ($target === '_blank'): ?>
                                    <span class="ms-2"><i class="bi bi-box-arrow-up-right"></i> Новое окно</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="preview-empty-state">
                            <i class="bi bi-link-45deg"></i>
                            <div class="empty-text">Текст кнопки не указан</div>
                            <button type="button" class="btn btn-sm btn-outline-primary mt-2" 
                                    onclick="postBlocksManager.editBlock('{block_id}')">
                                <i class="bi bi-plus-circle"></i> Настроить кнопку
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    public function getTemplateWithShortcodes(): string {
        return '
        <div class="post-block-button {alignment} {custom_class}">
            <a href="{url}" 
               class="btn {style} {size} {full_width}" 
               target="{target}" 
               {rel_attribute}
               {download_attribute}
               style="background-color: {bg_color}; color: {text_color}; border-color: {border_color};">
                {icon_before}
                {text}
                {icon_after}
            </a>
        </div>';
    }

    public function getDefaultContent(): array {
        return [
            'text' => 'Нажми меня',
            'url' => '#',
            'target' => '_self'
        ];
    }

    public function getDefaultSettings(): array {
        return [
            'style' => 'primary',
            'size' => 'medium',
            'alignment' => 'left',
            'full_width' => '',
            'bg_color' => '',
            'text_color' => '',
            'border_color' => '',
            'icon_before' => '',
            'icon_after' => '',
            'custom_class' => '',
            'download' => false,
            'rel' => ''
        ];
    }

    public function getContentForm($currentContent = []): string {
        $currentContent = $this->validateAndNormalizeContent($currentContent);
        $text = $currentContent['text'] ?? '';
        $url = $currentContent['url'] ?? '';
        $target = $currentContent['target'] ?? '_self';

        ob_start();
        ?>
        <div class="row">
            <div class="col-md-6">
                <div class="mb-4">
                    <label class="form-label">Текст кнопки *</label>
                    <input type="text" 
                           name="content[text]" 
                           class="form-control" 
                           value="<?= htmlspecialchars($text) ?>" 
                           placeholder="Текст на кнопке"
                           required>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-4">
                    <label class="form-label">URL ссылки *</label>
                    <input type="url" 
                           name="content[url]" 
                           class="form-control" 
                           value="<?= htmlspecialchars($url) ?>" 
                           placeholder="https://example.com"
                           required>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="mb-4">
                    <label class="form-label">Открывать ссылку</label>
                    <select name="content[target]" class="form-select">
                        <option value="_self" <?= $target === '_self' ? 'selected' : '' ?>>В текущем окне</option>
                        <option value="_blank" <?= $target === '_blank' ? 'selected' : '' ?>>В новом окне</option>
                        <option value="_parent" <?= $target === '_parent' ? 'selected' : '' ?>>В родительском фрейме</option>
                        <option value="_top" <?= $target === '_top' ? 'selected' : '' ?>>Поверх всех фреймов</option>
                    </select>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    public function getSettingsForm($currentSettings = []): string {
        $currentSettings = $this->validateAndNormalizeSettings($currentSettings);
        $style = $currentSettings['style'] ?? 'primary';
        $size = $currentSettings['size'] ?? 'medium';
        $alignment = $currentSettings['alignment'] ?? 'left';
        $fullWidth = $currentSettings['full_width'] ?? '';
        $bgColor = $currentSettings['bg_color'] ?? '';
        $textColor = $currentSettings['text_color'] ?? '';
        $borderColor = $currentSettings['border_color'] ?? '';
        $iconBefore = $currentSettings['icon_before'] ?? '';
        $iconAfter = $currentSettings['icon_after'] ?? '';
        $customClass = $currentSettings['custom_class'] ?? '';
        $download = $currentSettings['download'] ?? false;
        $rel = $currentSettings['rel'] ?? '';

        $bootstrapIcons = [
            '' => 'Без иконки',
            'bi bi-arrow-right' => 'Стрелка вправо',
            'bi bi-download' => 'Скачать',
            'bi bi-external-link' => 'Внешняя ссылка',
            'bi bi-heart' => 'Сердце',
            'bi bi-star' => 'Звезда',
            'bi bi-play-fill' => 'Воспроизвести',
            'bi bi-info-circle' => 'Информация',
            'bi bi-check' => 'Галочка',
            'bi bi-plus' => 'Плюс',
            'bi bi-search' => 'Поиск',
            'bi bi-share' => 'Поделиться'
        ];

        ob_start();
        ?>
        <div class="row">
            <div class="col-md-6">
                <div class="mb-4">
                    <label class="form-label">Стиль кнопки</label>
                    <select name="settings[style]" class="form-select">
                        <option value="primary" <?= $style === 'primary' ? 'selected' : '' ?>>Основной</option>
                        <option value="secondary" <?= $style === 'secondary' ? 'selected' : '' ?>>Вторичный</option>
                        <option value="success" <?= $style === 'success' ? 'selected' : '' ?>>Успех</option>
                        <option value="danger" <?= $style === 'danger' ? 'selected' : '' ?>>Опасность</option>
                        <option value="warning" <?= $style === 'warning' ? 'selected' : '' ?>>Предупреждение</option>
                        <option value="info" <?= $style === 'info' ? 'selected' : '' ?>>Информация</option>
                        <option value="light" <?= $style === 'light' ? 'selected' : '' ?>>Светлый</option>
                        <option value="dark" <?= $style === 'dark' ? 'selected' : '' ?>>Темный</option>
                        <option value="outline-primary" <?= $style === 'outline-primary' ? 'selected' : '' ?>>Контурный основной</option>
                        <option value="outline-secondary" <?= $style === 'outline-secondary' ? 'selected' : '' ?>>Контурный вторичный</option>
                        <option value="link" <?= $style === 'link' ? 'selected' : '' ?>>Ссылка</option>
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-4">
                    <label class="form-label">Размер кнопки</label>
                    <select name="settings[size]" class="form-select">
                        <option value="small" <?= $size === 'small' ? 'selected' : '' ?>>Маленький</option>
                        <option value="medium" <?= $size === 'medium' ? 'selected' : '' ?>>Средний</option>
                        <option value="large" <?= $size === 'large' ? 'selected' : '' ?>>Большой</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <div class="mb-4">
                    <label class="form-label">Выравнивание</label>
                    <select name="settings[alignment]" class="form-select">
                        <option value="left" <?= $alignment === 'left' ? 'selected' : '' ?>>Слева</option>
                        <option value="center" <?= $alignment === 'center' ? 'selected' : '' ?>>По центру</option>
                        <option value="right" <?= $alignment === 'right' ? 'selected' : '' ?>>Справа</option>
                    </select>
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-4">
                    <label class="form-label">Иконка перед текстом</label>
                    <select name="settings[icon_before]" class="form-select">
                        <?php foreach($bootstrapIcons as $value => $name): ?>
                            <option value="<?= $value ?>" <?= $iconBefore === $value ? 'selected' : '' ?>>
                                <?= $name ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-4">
                    <label class="form-label">Иконка после текста</label>
                    <select name="settings[icon_after]" class="form-select">
                        <?php foreach($bootstrapIcons as $value => $name): ?>
                            <option value="<?= $value ?>" <?= $iconAfter === $value ? 'selected' : '' ?>>
                                <?= $name ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <div class="mb-4">
                    <label class="form-label">Цвет фона</label>
                    <input type="color" 
                           name="settings[bg_color]" 
                           class="form-control form-control-color" 
                           value="<?= htmlspecialchars($bgColor) ?>">
                    <div class="form-text">Оставьте пустым для цвета по умолчанию</div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-4">
                    <label class="form-label">Цвет текста</label>
                    <input type="color" 
                           name="settings[text_color]" 
                           class="form-control form-control-color" 
                           value="<?= htmlspecialchars($textColor) ?>">
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-4">
                    <label class="form-label">Цвет рамки</label>
                    <input type="color" 
                           name="settings[border_color]" 
                           class="form-control form-control-color" 
                           value="<?= htmlspecialchars($borderColor) ?>">
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="mb-4">
                    <label class="form-label">Дополнительный CSS класс</label>
                    <input type="text" 
                           name="settings[custom_class]" 
                           class="form-control" 
                           value="<?= htmlspecialchars($customClass) ?>" 
                           placeholder="my-custom-button">
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-4">
                    <label class="form-label">Атрибут rel</label>
                    <input type="text" 
                           name="settings[rel]" 
                           class="form-control" 
                           value="<?= htmlspecialchars($rel) ?>" 
                           placeholder="noopener noreferrer">
                    <div class="form-text">Для SEO и безопасности ссылок</div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="form-check form-switch mb-4">
                    <input class="form-check-input" 
                           type="checkbox" 
                           name="settings[full_width]" 
                           id="full_width"
                           value="btn-block" 
                           <?= $fullWidth === 'btn-block' ? 'checked' : '' ?>>
                    <label class="form-check-label" for="full_width">
                        Во всю ширину
                    </label>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-check form-switch mb-4">
                    <input class="form-check-input" 
                           type="checkbox" 
                           name="settings[download]" 
                           id="download"
                           value="1" 
                           <?= $download ? 'checked' : '' ?>>
                    <label class="form-check-label" for="download">
                        Скачать файл
                    </label>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    public function getEditorHtml($settings = [], $content = []): string {
        $content = $this->validateAndNormalizeContent($content);
        $settings = $this->validateAndNormalizeSettings($settings);
        
        $text = $content['text'] ?? 'Нажми меня';
        $url = $content['url'] ?? '#';
        $style = $settings['style'] ?? 'primary';
        $size = $settings['size'] ?? 'medium';
        $alignment = $settings['alignment'] ?? 'left';

        $sizeClass = $this->getSizeClass($size);
        $alignmentClass = $this->getAlignmentClass($alignment);

        return '
        <div class="post-block-button-preview ' . $alignmentClass . '">
            <a href="' . htmlspecialchars($url) . '" 
               class="btn btn-' . $style . ' ' . $sizeClass . '" 
               style="pointer-events: none; text-decoration: none;">
                ' . htmlspecialchars($text) . '
            </a>
        </div>';
    }

    public function getShortcodes(): array {
        return array_merge(parent::getShortcodes(), [
            '{text}' => 'Текст кнопки',
            '{url}' => 'URL ссылки',
            '{target}' => 'Цель открытия ссылки',
            '{style}' => 'CSS классы стиля кнопки',
            '{size}' => 'CSS классы размера кнопки',
            '{alignment}' => 'CSS класс выравнивания',
            '{full_width}' => 'CSS класс для кнопки во всю ширину',
            '{bg_color}' => 'Цвет фона',
            '{text_color}' => 'Цвет текста',
            '{border_color}' => 'Цвет рамки',
            '{icon_before}' => 'Иконка перед текстом',
            '{icon_after}' => 'Иконка после текста',
            '{custom_class}' => 'Дополнительный CSS класс',
            '{rel_attribute}' => 'Атрибут rel',
            '{download_attribute}' => 'Атрибут download'
        ]);
    }

    public function validateSettings($settings): array {
        $errors = [];

        if (!empty($settings['custom_class']) && !preg_match('/^[a-zA-Z0-9-_ ]+$/', $settings['custom_class'])) {
            $errors[] = 'CSS класс может содержать только буквы, цифры, дефисы и подчеркивания';
        }

        $allowedStyles = ['primary', 'secondary', 'success', 'danger', 'warning', 'info', 'light', 'dark', 
                         'outline-primary', 'outline-secondary', 'outline-success', 'outline-danger', 
                         'outline-warning', 'outline-info', 'outline-light', 'outline-dark', 'link'];
        if (!empty($settings['style']) && !in_array($settings['style'], $allowedStyles)) {
            $errors[] = 'Недопустимый стиль кнопки';
        }

        $allowedSizes = ['small', 'medium', 'large'];
        if (!empty($settings['size']) && !in_array($settings['size'], $allowedSizes)) {
            $errors[] = 'Недопустимый размер кнопки';
        }

        $allowedAlignments = ['left', 'center', 'right'];
        if (!empty($settings['alignment']) && !in_array($settings['alignment'], $allowedAlignments)) {
            $errors[] = 'Недопустимое выравнивание';
        }

        return [empty($errors), $errors];
    }

    private function getSizeClass($size): string {
        switch ($size) {
            case 'small': return 'btn-sm';
            case 'large': return 'btn-lg';
            default: return '';
        }
    }

    private function getAlignmentClass($alignment): string {
        switch ($alignment) {
            case 'center': return 'text-center';
            case 'right': return 'text-end';
            default: return 'text-start';
        }
    }

    public function extractFromHtml(string $html): ?array {
        if (preg_match('/<a[^>]+href="([^"]*)"[^>]*>(.*?)<\/a>/is', $html, $matches)) {
            $url = $matches[1];
            $text = trim(strip_tags($matches[2]));
            
            if (!empty($text)) {
                $target = '_self';
                if (preg_match('/target="([^"]*)"/i', $html, $targetMatch)) {
                    $target = $targetMatch[1];
                }
                
                return [
                    'text' => $text,
                    'url' => $url,
                    'target' => $target
                ];
            }
        }
        
        return null;
    }
}