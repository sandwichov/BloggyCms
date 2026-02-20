<?php
class HeaderBlock extends BasePostBlock {
    
    public function getName(): string {
        return 'Заголовок';
    }

    public function getSystemName(): string {
        return 'HeaderBlock';
    }

    public function getDescription(): string {
        return 'Блок заголовка с выбором уровня, выравниванием и дополнительными настройками';
    }

    public function getIcon(): string {
        return 'bi bi-type-h1';
    }

    public function getCategory(): string {
        return 'text';
    }

    public function getTemplateWithShortcodes(): string {
        return '<{level} class="post-block-header {alignment} {custom_class}"{style}>{text}</{level}>';
    }

    public function getDefaultContent(): array {
        return [
            'text' => 'Новый заголовок'
        ];
    }

    public function getDefaultSettings(): array {
        return [
            'level' => 'h2',
            'alignment' => 'left',
            'text_color' => '',
            'custom_class' => ''
        ];
    }

    public function getPreviewHtml($content = [], $settings = []): string {
        $content = $this->validateAndNormalizeContent($content);
        $settings = $this->validateAndNormalizeSettings($settings);
        
        $text = $content['text'] ?? 'Ваш заголовок';
        $level = $settings['level'] ?? 'h2';
        $alignment = $settings['alignment'] ?? 'left';
        
        ob_start();
        ?>
        <div class="post-block-preview post-block-preview-HeaderBlock full-content-preview">
            <div class="preview-wrapper">
                <div class="preview-header">
                    <div class="preview-header-content">
                        <div class="preview-icon">
                            <i class="bi bi-type-h1"></i>
                        </div>
                        <div class="preview-info">
                            <div class="preview-title">
                                <strong>Заголовок</strong>
                                <span class="badge bg-secondary badge-sm"><?= strtoupper($level) ?></span>
                            </div>
                            <div class="preview-stats">
                                <?= strlen($text) ?> симв.
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
                        <<?= $level ?> class="header-content" style="text-align: <?= htmlspecialchars($alignment) ?>; margin: 0;">
                            <?= htmlspecialchars($text) ?>
                        </<?= $level ?>>
                    <?php else: ?>
                        <div class="preview-empty-state">
                            <i class="bi bi-type-h1"></i>
                            <div class="empty-text">Заголовок не добавлен</div>
                            <button type="button" class="btn btn-sm btn-outline-primary mt-2" 
                                    onclick="postBlocksManager.editBlock('{block_id}')">
                                <i class="bi bi-plus-circle"></i> Добавить заголовок
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    protected function renderPreviewContent($content, $settings): string {
        $url = $content['url'] ?? '';
        $alt = $content['alt'] ?? '';
        $alignment = $settings['alignment'] ?? 'center';
        $size = $settings['size'] ?? 'medium';
        
        if (empty($url)) {
            return '';
        }
        
        $sizeClass = $this->getSizeClass($size);
        
        ob_start();
        ?>
        <div class="image-preview-container text-<?= htmlspecialchars($alignment) ?>">
            <div class="position-relative d-inline-block <?= $sizeClass ?>">
                <img src="<?= htmlspecialchars($url) ?>" 
                     alt="<?= htmlspecialchars($alt) ?>"
                     class="preview-image"
                     onerror="this.onerror=null; this.classList.add('image-error')">
                <?php if (!empty($size) && $size !== 'medium'): ?>
                    <span class="badge bg-dark position-absolute top-0 end-0 m-1">
                        <?= htmlspecialchars($size) ?>
                    </span>
                <?php endif; ?>
            </div>
            <?php if (!empty($alt)): ?>
                <div class="mt-2 small text-muted">
                    <i class="bi bi-card-text me-1"></i>
                    <?= htmlspecialchars(mb_substr($alt, 0, 60)) ?>
                    <?php if (mb_strlen($alt) > 60): ?>...<?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }
    
    private function getSizeClass($size): string {
        $sizes = [
            'small' => 'preview-image-sm',
            'medium' => 'preview-image-md',
            'large' => 'preview-image-lg',
            'full' => 'preview-image-full'
        ];
        
        return $sizes[$size] ?? 'preview-image-md';
    }
    
    protected function getPreviewStats($content, $settings): string {
        $stats = [];
        
        if (!empty($content['url'])) {
            $stats[] = 'Изображение загружено';
        }
        
        if (!empty($content['alt'])) {
            $stats[] = 'есть описание';
        }
        
        if (!empty($settings['size']) && $settings['size'] !== 'medium') {
            $stats[] = $settings['size'];
        }
        
        return implode(' · ', $stats);
    }
    
    protected function getEmptyIcon(): string {
        return 'bi bi-image';
    }
    
    protected function getEmptyText(): string {
        return 'Изображение не загружено';
    }

    public function getContentForm($currentContent = []): string {
        $currentContent = $this->validateAndNormalizeContent($currentContent);
        $text = $currentContent['text'] ?? '';
        
        ob_start();
        ?>
        <div class="mb-4">
            <label class="form-label">Текст заголовка</label>
            <input type="text" 
                   name="content[text]" 
                   class="form-control form-control-lg" 
                   value="<?= htmlspecialchars($text) ?>" 
                   placeholder="Введите текст заголовка"
                   required>
            <div class="form-text">Введите основной текст заголовка</div>
        </div>
        <?php
        return ob_get_clean();
    }

    public function getSettingsForm($currentSettings = []): string {
        $currentSettings = $this->validateAndNormalizeSettings($currentSettings);
        $level = $currentSettings['level'] ?? 'h2';
        $alignment = $currentSettings['alignment'] ?? 'left';
        $textColor = $currentSettings['text_color'] ?? '';
        $customClass = $currentSettings['custom_class'] ?? '';

        ob_start();
        ?>
        <div class="row">
            <div class="col-md-6">
                <div class="mb-4">
                    <label class="form-label">Уровень заголовка</label>
                    <select name="settings[level]" class="form-select">
                        <option value="h1" <?= $level === 'h1' ? 'selected' : '' ?>>H1 - Главный заголовок</option>
                        <option value="h2" <?= $level === 'h2' ? 'selected' : '' ?>>H2 - Основной заголовок</option>
                        <option value="h3" <?= $level === 'h3' ? 'selected' : '' ?>>H3 - Подзаголовок</option>
                        <option value="h4" <?= $level === 'h4' ? 'selected' : '' ?>>H4 - Мелкий заголовок</option>
                        <option value="h5" <?= $level === 'h5' ? 'selected' : '' ?>>H5 - Второстепенный заголовок</option>
                        <option value="h6" <?= $level === 'h6' ? 'selected' : '' ?>>H6 - Самый мелкий заголовок</option>
                    </select>
                    <div class="form-text">Выберите семантический уровень заголовка для SEO</div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="mb-4">
                    <label class="form-label">Выравнивание текста</label>
                    <select name="settings[alignment]" class="form-select">
                        <option value="left" <?= $alignment === 'left' ? 'selected' : '' ?>>По левому краю</option>
                        <option value="center" <?= $alignment === 'center' ? 'selected' : '' ?>>По центру</option>
                        <option value="right" <?= $alignment === 'right' ? 'selected' : '' ?>>По правому краю</option>
                        <option value="justify" <?= $alignment === 'justify' ? 'selected' : '' ?>>По ширине</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="mb-4">
                    <label class="form-label">Цвет текста</label>
                    <input type="color" 
                           name="settings[text_color]" 
                           class="form-control form-control-color" 
                           value="<?= htmlspecialchars($textColor) ?>" 
                           title="Выберите цвет текста">
                    <div class="form-text">Оставьте пустым для цвета по умолчанию</div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="mb-4">
                    <label class="form-label">Дополнительный CSS класс</label>
                    <input type="text" 
                           name="settings[custom_class]" 
                           class="form-control" 
                           value="<?= htmlspecialchars($customClass) ?>" 
                           placeholder="my-custom-class"
                           pattern="[a-zA-Z0-9-_ ]+">
                    <div class="form-text">Только буквы, цифры, дефисы и подчеркивания</div>
                </div>
            </div>
        </div>

        <div class="alert alert-info">
            <i class="bi bi-info-circle me-2"></i>
            <strong>Совет по SEO:</strong> Используйте H1 только для главного заголовка страницы. 
            Для подзаголовков используйте H2-H6 в иерархическом порядке.
        </div>
        <?php
        return ob_get_clean();
    }

    public function getEditorHtml($settings = [], $content = []): string {
        $settings = $this->validateAndNormalizeSettings($settings);
        $content = $this->validateAndNormalizeContent($content);
        
        $level = $settings['level'] ?? 'h2';
        $text = $content['text'] ?? 'Ваш заголовок';
        $alignment = $settings['alignment'] ?? 'left';
        $textColor = $settings['text_color'] ?? '';
        $customClass = $settings['custom_class'] ?? '';
        $class = trim("post-block-header {$alignment} {$customClass}");
        $style = $textColor ? " style=\"color: {$textColor}\"" : '';

        return "<{$level} class=\"{$class}\"{$style}>" . htmlspecialchars($text) . "</{$level}>";
    }

    public function processFrontend($content, $settings = []): string {
        return parent::processFrontend($content, $settings);
    }

    public function getShortcodes(): array {
        return array_merge(parent::getShortcodes(), [
            '{text}' => 'Текст заголовка',
            '{level}' => 'Уровень заголовка (h1-h6)',
            '{alignment}' => 'Выравнивание текста',
            '{custom_class}' => 'Дополнительный CSS класс',
            '{style}' => 'Инлайн стили (например: style="color: #000")',
            '{text_color}' => 'Цвет текста в HEX формате'
        ]);
    }

    public function validateSettings($settings): array {
        $errors = [];
        $settings = $this->validateAndNormalizeSettings($settings);

        if (!empty($settings['level']) && !in_array($settings['level'], ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'])) {
            $errors[] = 'Недопустимый уровень заголовка. Допустимые значения: h1-h6';
        }

        if (!empty($settings['alignment']) && !in_array($settings['alignment'], ['left', 'center', 'right', 'justify'])) {
            $errors[] = 'Недопустимое значение выравнивания';
        }

        if (!empty($settings['custom_class']) && !preg_match('/^[a-zA-Z0-9-_ ]+$/', $settings['custom_class'])) {
            $errors[] = 'CSS класс может содержать только буквы, цифры, дефисы, подчеркивания и пробелы';
        }

        if (!empty($settings['text_color']) && !preg_match('/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $settings['text_color'])) {
            $errors[] = 'Недопустимый формат цвета. Используйте HEX формат: #000000';
        }

        return [empty($errors), $errors];
    }

    public function prepareSettings($settings): array {
        $settings = parent::prepareSettings($settings);
        if (isset($settings['level'])) {
            $settings['level'] = strtolower($settings['level']);
            $validLevels = ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'];
            if (!in_array($settings['level'], $validLevels)) {
                $settings['level'] = 'h2';
            }
        }
        
        if (isset($settings['alignment'])) {
            $validAlignments = ['left', 'center', 'right', 'justify'];
            if (!in_array($settings['alignment'], $validAlignments)) {
                $settings['alignment'] = 'left';
            }
        }
        
        if (isset($settings['text_color'])) {
            $settings['text_color'] = trim($settings['text_color']);
            if (empty($settings['text_color']) || $settings['text_color'] === '#000000') {
                unset($settings['text_color']);
            }
        }
        
        if (isset($settings['custom_class'])) {
            $settings['custom_class'] = trim($settings['custom_class']);
            if (empty($settings['custom_class'])) {
                unset($settings['custom_class']);
            }
        }

        return $settings;
    }

    public function prepareContent($content): array {
        $content = parent::prepareContent($content);
        if (isset($content['text'])) {
            $content['text'] = trim($content['text']);
            if (mb_strlen($content['text']) > 200) {
                $content['text'] = mb_substr($content['text'], 0, 200) . '...';
            }
        } else {
            $content['text'] = 'Новый заголовок';
        }

        return $content;
    }

    public function extractFromHtml(string $html): ?array {
        if (preg_match('/<h([1-6])[^>]*>(.*?)<\/h\1>/i', $html, $matches)) {
            $text = trim(strip_tags($matches[2]));
            if (!empty($text)) {
                return [
                    'text' => $text
                ];
            }
        }
        
        $plainText = trim(strip_tags($html));
        if (!empty($plainText) && strlen($plainText) < 200) {
            return ['text' => $plainText];
        }
        
        return null;
    }

    public function canExtractFromHtml(): bool {
        return true;
    }

    public function getSeoRecommendations(): array {
        return [
            'Используйте только один H1 на странице',
            'Соблюдайте иерархию заголовков (H1 → H2 → H3 и т.д.)',
            'Заголовки должны точно отражать содержание раздела',
            'Избегайте слишком длинных заголовков',
            'Включайте ключевые слова в заголовки'
        ];
    }

    public function checkSeoOptimization($text, $level): array {
        $warnings = [];
        $textLength = mb_strlen($text);

        if ($level === 'h1') {
            if ($textLength < 20) {
                $warnings[] = 'H1 заголовок слишком короткий (рекомендуется 20-70 символов)';
            }
            if ($textLength > 70) {
                $warnings[] = 'H1 заголовок слишком длинный (рекомендуется 20-70 символов)';
            }
        } else {
            if ($textLength > 150) {
                $warnings[] = "{$level} заголовок слишком длинный (рекомендуется до 150 символов)";
            }
        }

        return $warnings;
    }
    
    protected function renderWithTemplate($content, $settings, $template): string {
        $content = $this->validateAndNormalizeContent($content);
        $settings = $this->validateAndNormalizeSettings($settings);
        
        $text = $content['text'] ?? '';
        $level = $settings['level'] ?? 'h2';
        $alignment = $settings['alignment'] ?? 'left';
        $textColor = $settings['text_color'] ?? '';
        $customClass = $settings['custom_class'] ?? '';
        $presetId = $settings['preset_id'] ?? null;
        $presetName = $settings['preset_name'] ?? '';
        $style = '';
        if (!empty($textColor)) {
            $style = ' style="color: ' . htmlspecialchars($textColor) . '"';
        }
        
        $result = $template;
        $replacements = [
            '{text}' => htmlspecialchars($text),
            '{level}' => $level,
            '{alignment}' => $alignment,
            '{custom_class}' => $customClass,
            '{style}' => $style,
            '{text_color}' => $textColor,
            '{preset_id}' => $presetId ? htmlspecialchars($presetId) : '',
            '{preset_name}' => $presetName ? htmlspecialchars($presetName) : '',
            '{block_type}' => $this->getSystemName(),
            '{block_name}' => $this->getName()
        ];
        
        foreach ($replacements as $placeholder => $value) {
            $result = str_replace($placeholder, $value, $result);
        }
        
        return $result;
    }
}