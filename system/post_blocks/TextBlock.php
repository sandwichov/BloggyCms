<?php
class TextBlock extends BasePostBlock {
    
    public function getName(): string {
        return 'Текст';
    }

    public function getSystemName(): string {
        return 'TextBlock';
    }

    public function getDescription(): string {
        return 'Блок текста с поддержкой HTML';
    }

    public function getIcon(): string {
        return 'bi bi-text-paragraph';
    }

    public function getCategory(): string {
        return 'text';
    }

    public function getTemplateWithShortcodes(): string {
        return '<div class="post-block-text {custom_class}">{content}</div>';
    }

    public function getDefaultContent(): array {
        return [
            'content' => 'Ваш текст здесь...'
        ];
    }

    public function getDefaultSettings(): array {
        return [
            'custom_class' => '',
            'text_align' => 'left',
            'font_size' => '',
            'line_height' => ''
        ];
    }

    public function getPreviewHtml($content = [], $settings = []): string {
        $content = $this->validateAndNormalizeContent($content);
        $text = $content['text'] ?? $content['content'] ?? '';
        
        if (empty($text)) {
            foreach ($content as $value) {
                if (is_string($value) && trim($value) !== '') {
                    $text = $value;
                    break;
                }
            }
        }
        
        $alignment = $settings['alignment'] ?? 'left';
        $charCount = strlen($text);
        
        ob_start();
        ?>
        <div class="post-block-preview post-block-preview-TextBlock full-content-preview">
            <div class="preview-wrapper">
                <div class="preview-header">
                    <div class="preview-header-content">
                        <div class="preview-icon">
                            <i class="bi bi-text-left"></i>
                        </div>
                        <div class="preview-info">
                            <div class="preview-title">
                                <strong>Текст</strong>
                                <?php if ($alignment !== 'left'): ?>
                                    <span class="badge bg-secondary badge-sm"><?= htmlspecialchars($alignment) ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="preview-stats">
                                <?= $charCount ?> симв.
                                <?php if ($alignment !== 'left'): ?>
                                    · <?= htmlspecialchars($alignment) ?>
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
                
                <div class="preview-body full-text-content">
                    <?php if (!empty(trim($text))): ?>
                        <div class="text-content" style="text-align: <?= htmlspecialchars($alignment) ?>">
                            <?= nl2br(htmlspecialchars($text)) ?>
                        </div>
                    <?php else: ?>
                        <div class="preview-empty-state">
                            <i class="bi bi-fonts"></i>
                            <div class="empty-text">Текст не добавлен</div>
                            <button type="button" class="btn btn-sm btn-outline-primary mt-2" 
                                    onclick="postBlocksManager.editBlock('{block_id}')">
                                <i class="bi bi-plus-circle"></i> Добавить текст
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
    
    protected function getPreviewStats($content, $settings): string {
        $text = $content['text'] ?? '';
        $charCount = strlen($text);
        
        $stats = [];
        $stats[] = $charCount . ' симв.';
        
        if (!empty($settings['alignment']) && $settings['alignment'] !== 'left') {
            $stats[] = 'выравнивание: ' . $settings['alignment'];
        }
        
        return implode(' · ', $stats);
    }

    public function getContentForm($currentContent = []): string {
        $currentContent = $this->validateAndNormalizeContent($currentContent);
        $content = $currentContent['content'] ?? '';
        
        return '
        <div class="mb-4">
            <label class="form-label">Текст</label>
            <textarea name="content[content]" 
                      class="form-control" 
                      rows="6" 
                      placeholder="Введите текст..."
                      required>' . htmlspecialchars($content) . '</textarea>
            <div class="form-text">Основной текст блока. Поддерживает HTML разметку.</div>
        </div>';
    }

    public function getSettingsForm($currentSettings = []): string {
        $currentSettings = $this->validateAndNormalizeSettings($currentSettings);
        
        $customClass = $currentSettings['custom_class'] ?? '';
        $textAlign = $currentSettings['text_align'] ?? 'left';
        $fontSize = $currentSettings['font_size'] ?? '';
        $lineHeight = $currentSettings['line_height'] ?? '';

        ob_start();
        ?>
        <div class="row">
            <div class="col-md-6">
                <div class="mb-4">
                    <label class="form-label">Выравнивание текста</label>
                    <select name="settings[text_align]" class="form-select">
                        <option value="left" <?= $textAlign === 'left' ? 'selected' : '' ?>>По левому краю</option>
                        <option value="center" <?= $textAlign === 'center' ? 'selected' : '' ?>>По центру</option>
                        <option value="right" <?= $textAlign === 'right' ? 'selected' : '' ?>>По правому краю</option>
                        <option value="justify" <?= $textAlign === 'justify' ? 'selected' : '' ?>>По ширине</option>
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-4">
                    <label class="form-label">Дополнительный CSS класс</label>
                    <input type="text" 
                           name="settings[custom_class]" 
                           class="form-control" 
                           value="<?= htmlspecialchars($customClass) ?>" 
                           placeholder="my-text-block">
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="mb-4">
                    <label class="form-label">Размер шрифта (опционально)</label>
                    <input type="text" 
                           name="settings[font_size]" 
                           class="form-control" 
                           value="<?= htmlspecialchars($fontSize) ?>" 
                           placeholder="16px или 1rem">
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-4">
                    <label class="form-label">Межстрочный интервал (опционально)</label>
                    <input type="text" 
                           name="settings[line_height]" 
                           class="form-control" 
                           value="<?= htmlspecialchars($lineHeight) ?>" 
                           placeholder="1.5 или 24px">
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    public function getEditorHtml($settings = [], $content = []): string {
        return parent::getEditorHtml($settings, $content);
    }

    public function processFrontend($content, $settings = []): string {
        return parent::processFrontend($content, $settings);
    }

    protected function renderWithTemplate($content, $settings, $template): string {
        $content = $this->validateAndNormalizeContent($content);
        $settings = $this->validateAndNormalizeSettings($settings);
        
        $text = $content['content'] ?? '';
        $customClass = $settings['custom_class'] ?? '';
        $textAlign = $settings['text_align'] ?? 'left';
        $fontSize = $settings['font_size'] ?? '';
        $lineHeight = $settings['line_height'] ?? '';
        $presetId = $settings['preset_id'] ?? null;
        $presetName = $settings['preset_name'] ?? '';

        if (empty(trim($text))) {
            return '<!-- TextBlock: пустой текст -->';
        }

        $presetClass = '';
        if ($presetId) {
            $presetClass = 'preset-' . (int)$presetId;
            
            if (!empty($presetName)) {
                $cleanPresetName = preg_replace('/[^a-z0-9_-]+/i', '-', strtolower($presetName));
                $cleanPresetName = preg_replace('/-+/', '-', $cleanPresetName);
                $cleanPresetName = trim($cleanPresetName, '-');
                
                if (!empty($cleanPresetName)) {
                    $presetClass .= ' preset-' . $cleanPresetName;
                }
            }
        }

        $style = '';
        if ($fontSize) {
            $style .= 'font-size: ' . htmlspecialchars($fontSize) . '; ';
        }
        if ($lineHeight) {
            $style .= 'line-height: ' . htmlspecialchars($lineHeight) . '; ';
        }
        if ($textAlign && $textAlign !== 'left') {
            $style .= 'text-align: ' . htmlspecialchars($textAlign) . '; ';
        }

        $result = $template;
        
        $result = str_replace('{custom_class}', trim($customClass . ' ' . $presetClass), $result);
        $result = str_replace('{content}', $text, $result);
        
        if (!empty($style)) {
            $result = preg_replace(
                '/class="([^"]*)"/',
                'class="$1" style="' . $style . '"',
                $result
            );
        }
        
        $result = str_replace('{preset_id}', $presetId ? htmlspecialchars($presetId) : '', $result);
        $result = str_replace('{preset_name}', $presetName ? htmlspecialchars($presetName) : '', $result);
        $result = str_replace('{block_type}', $this->getSystemName(), $result);
        $result = str_replace('{block_name}', $this->getName(), $result);
        $result = str_replace('{text_align}', $textAlign, $result);
        $result = str_replace('{font_size}', htmlspecialchars($fontSize), $result);
        $result = str_replace('{line_height}', htmlspecialchars($lineHeight), $result);

        return $result;
    }

    public function getShortcodes(): array {
        return array_merge(parent::getShortcodes(), [
            '{content}' => 'Текст содержимого',
            '{custom_class}' => 'Дополнительный CSS класс',
            '{text_align}' => 'Выравнивание текста',
            '{font_size}' => 'Размер шрифта',
            '{line_height}' => 'Межстрочный интервал'
        ]);
    }

    public function prepareContent($content): array {
        if (!is_array($content)) {
            $content = [];
        }
        
        if (isset($_POST['content']) && is_array($_POST['content'])) {
            if (isset($_POST['content']['content'])) {
                $content['content'] = trim($_POST['content']['content']);
            }
        }
        
        if (!isset($content['content'])) {
            $content['content'] = 'Ваш текст здесь...';
        }

        return $content;
    }

    public function prepareSettings($settings): array {
        if (!is_array($settings)) {
            $settings = [];
        }
        
        if (isset($_POST['settings']) && is_array($_POST['settings'])) {
            $settings = array_merge($settings, $_POST['settings']);
        }
        
        if (isset($settings['custom_class'])) {
            $settings['custom_class'] = trim($settings['custom_class']);
        }
        
        if (isset($settings['font_size'])) {
            $settings['font_size'] = trim($settings['font_size']);
        }
        
        if (isset($settings['line_height'])) {
            $settings['line_height'] = trim($settings['line_height']);
        }

        return $settings;
    }

    public function extractFromHtml(string $html): ?array {
        $plainText = trim(strip_tags($html));
        if (!empty($plainText)) {
            return [
                'content' => $plainText
            ];
        }
        
        if (!empty(trim($html))) {
            return [
                'content' => $html
            ];
        }
        
        return null;
    }

    public function validateSettings($settings): array {
        $errors = [];

        if (!empty($settings['custom_class']) && !preg_match('/^[a-zA-Z0-9-_ ]+$/', $settings['custom_class'])) {
            $errors[] = 'CSS класс может содержать только буквы, цифры, дефисы и подчеркивания';
        }

        $allowedAlign = ['left', 'center', 'right', 'justify'];
        if (!empty($settings['text_align']) && !in_array($settings['text_align'], $allowedAlign)) {
            $errors[] = 'Недопустимое значение выравнивания текста';
        }

        return [empty($errors), $errors];
    }

    public function validateAndNormalizeContent($content): array {
        if (is_string($content)) {
            $decoded = json_decode($content, true);
            return is_array($decoded) ? $decoded : ['content' => $content];
        }
        
        if (!is_array($content)) {
            return ['content' => (string)$content];
        }
        
        return $content;
    }

    public function validateAndNormalizeSettings($settings): array {
        if (is_string($settings)) {
            $decoded = json_decode($settings, true);
            return is_array($decoded) ? $decoded : [];
        }
        
        if (!is_array($settings)) {
            return [];
        }
        
        $defaults = $this->getDefaultSettings();
        foreach ($defaults as $key => $value) {
            if (!isset($settings[$key])) {
                $settings[$key] = $value;
            }
        }
        
        return $settings;
    }
}