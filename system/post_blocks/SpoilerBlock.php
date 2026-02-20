<?php
class SpoilerBlock extends BasePostBlock {
    
    public function getName(): string {
        return 'Спойлер';
    }

    public function getSystemName(): string {
        return 'SpoilerBlock';
    }

    public function getDescription(): string {
        return 'Блок для скрытия контента под спойлером с настраиваемым заголовком';
    }

    public function getIcon(): string {
        return 'bi bi-eye';
    }

    public function getCategory(): string {
        return 'interactive';
    }

    public function getTemplateWithShortcodes(): string {
        return '
        <div class="post-block-spoiler {custom_class} {show_default} {no_animation_class}">
            <div class="spoiler-header">
                <button type="button" class="spoiler-toggle {icon_position}" aria-expanded="{aria_expanded}">
                    {icon_before}
                    <span class="spoiler-title">{title}</span>
                    {icon_after}
                    <span class="sr-only">Нажмите чтобы {action_text}</span>
                </button>
            </div>
            <div class="spoiler-content" aria-hidden="{aria_hidden}">
                <div class="spoiler-body">
                    {content}
                </div>
            </div>
        </div>';
    }

    public function getDefaultContent(): array {
        return [
            'title' => 'Нажмите чтобы раскрыть',
            'content' => 'Скрытый контент...'
        ];
    }

    public function getDefaultSettings(): array {
        return [
            'show_default' => '',
            'icon_before' => 'bi bi-chevron-down',
            'icon_after' => '',
            'icon_position' => 'icon-after',
            'custom_class' => '',
            'animation' => true
        ];
    }

    public function getContentForm($currentContent = []): string {
        $currentContent = $this->validateAndNormalizeContent($currentContent);
        $title = $currentContent['title'] ?? '';
        $content = $currentContent['content'] ?? '';

        ob_start();
        ?>
        <div class="mb-4">
            <label class="form-label">Заголовок спойлера *</label>
            <input type="text" 
                   name="content[title]" 
                   class="form-control" 
                   value="<?= htmlspecialchars($title) ?>" 
                   placeholder="Текст заголовка"
                   required>
            <div class="form-text">Текст, который будет виден когда спойлер закрыт</div>
        </div>

        <div class="mb-4">
            <label class="form-label">Содержимое спойлера *</label>
            <textarea name="content[content]" 
                     class="form-control" 
                     rows="6" 
                     placeholder="Скрытый контент..."
                     required><?= htmlspecialchars($content) ?></textarea>
            <div class="form-text">Контент, который будет скрыт под спойлером</div>
        </div>
        <?php
        return ob_get_clean();
    }

    public function getSettingsForm($currentSettings = []): string {
        $currentSettings = $this->validateAndNormalizeSettings($currentSettings);
        $showDefault = $currentSettings['show_default'] ?? '';
        $iconBefore = $currentSettings['icon_before'] ?? 'bi bi-chevron-down';
        $iconAfter = $currentSettings['icon_after'] ?? '';
        $iconPosition = $currentSettings['icon_position'] ?? 'icon-after';
        $customClass = $currentSettings['custom_class'] ?? '';
        $animation = $currentSettings['animation'] ?? true;

        $spoilerIcons = [
            '' => 'Без иконки',
            'bi bi-chevron-down' => 'Стрелка вниз',
            'bi bi-chevron-right' => 'Стрелка вправо',
            'bi bi-plus' => 'Плюс',
            'bi bi-dash' => 'Минус',
            'bi bi-caret-down' => 'Уголок вниз',
            'bi bi-caret-right' => 'Уголок вправо',
            'bi bi-arrow-down' => 'Стрелка вниз (жирная)',
            'bi bi-arrow-right' => 'Стрелка вправо (жирная)',
            'bi bi-eye' => 'Глаз',
            'bi bi-info-circle' => 'Информация',
            'bi bi-question-circle' => 'Вопрос',
            'bi bi-chevron-double-down' => 'Двойная стрелка вниз',
            'bi bi-chevron-double-right' => 'Двойная стрелка вправо',
            'bi bi-arrow-down-circle' => 'Стрелка вниз в круге',
            'bi bi-arrow-right-circle' => 'Стрелка вправо в круге',
            'bi bi-caret-down-fill' => 'Закрашенный уголок вниз',
            'bi bi-caret-right-fill' => 'Закрашенный уголок вправо'
        ];

        ob_start();
        ?>
        <div class="row">
            <div class="col-md-6">
                <div class="mb-4">
                    <label class="form-label">Иконка перед заголовком</label>
                    <select name="settings[icon_before]" class="form-select">
                        <?php foreach($spoilerIcons as $value => $name): ?>
                            <option value="<?= $value ?>" <?= $iconBefore === $value ? 'selected' : '' ?>>
                                <?= $name ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-4">
                    <label class="form-label">Иконка после заголовка</label>
                    <select name="settings[icon_after]" class="form-select">
                        <?php foreach($spoilerIcons as $value => $name): ?>
                            <option value="<?= $value ?>" <?= $iconAfter === $value ? 'selected' : '' ?>>
                                <?= $name ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="mb-4">
                    <label class="form-label">Позиция иконки</label>
                    <select name="settings[icon_position]" class="form-select">
                        <option value="icon-before" <?= $iconPosition === 'icon-before' ? 'selected' : '' ?>>Только перед текстом</option>
                        <option value="icon-after" <?= $iconPosition === 'icon-after' ? 'selected' : '' ?>>Только после текста</option>
                        <option value="icon-both" <?= $iconPosition === 'icon-both' ? 'selected' : '' ?>>С обеих сторон</option>
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
                           placeholder="my-spoiler">
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="form-check form-switch mb-4">
                    <input class="form-check-input" 
                           type="checkbox" 
                           name="settings[show_default]" 
                           id="show_default"
                           value="show" 
                           <?= $showDefault === 'show' ? 'checked' : '' ?>>
                    <label class="form-check-label" for="show_default">
                        Открыт по умолчанию
                    </label>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-check form-switch mb-4">
                    <input class="form-check-input" 
                           type="checkbox" 
                           name="settings[animation]" 
                           id="animation"
                           value="1" 
                           <?= $animation ? 'checked' : '' ?>>
                    <label class="form-check-label" for="animation">
                        Анимация раскрытия
                    </label>
                </div>
            </div>
        </div>

        <div class="alert alert-info">
            <i class="bi bi-info-circle me-2"></i>
            Для работы спойлера необходим Bootstrap 5. Убедитесь, что он подключен в вашем шаблоне.
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
        
        $title = $content['title'] ?? '';
        $contentText = $content['content'] ?? '';

        if (empty(trim($title))) {
            return '<!-- SpoilerBlock: пустой заголовок -->';
        }

        $showDefault = $settings['show_default'] ?? '';
        $iconBefore = $settings['icon_before'] ?? 'bi bi-chevron-down';
        $iconAfter = $settings['icon_after'] ?? '';
        $iconPosition = $settings['icon_position'] ?? 'icon-after';
        $customClass = $settings['custom_class'] ?? '';
        $animation = $settings['animation'] ?? true;
        $presetId = $settings['preset_id'] ?? null;
        $presetName = $settings['preset_name'] ?? '';
        $isOpen = $showDefault === 'show';
        $ariaExpanded = $isOpen ? 'true' : 'false';
        $ariaHidden = $isOpen ? 'false' : 'true';
        $actionText = $isOpen ? 'скрыть' : 'раскрыть';
        $noAnimationClass = !$animation ? 'no-animation' : '';
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

        $iconBeforeHtml = '';
        $iconAfterHtml = '';

        if ($iconPosition === 'icon-before' || $iconPosition === 'icon-both') {
            if (!empty($iconBefore)) {
                $iconName = $this->extractIconName($iconBefore);
                if ($iconName) {
                    $iconBeforeHtml = bloggy_icon('bs', $iconName, '16 16', null, 'me-2');
                }
            }
        }
        
        if ($iconPosition === 'icon-after' || $iconPosition === 'icon-both') {
            if (!empty($iconAfter)) {
                $iconName = $this->extractIconName($iconAfter);
                if ($iconName) {
                    $iconAfterHtml = bloggy_icon('bs', $iconName, '16 16', null, 'ms-2');
                }
            }
        }

        $result = $template;
        
        $result = str_replace('{custom_class}', trim($customClass . ' ' . $presetClass), $result);
        $result = str_replace('{icon_position}', htmlspecialchars($iconPosition), $result);
        $result = str_replace('{icon_before}', $iconBeforeHtml, $result);
        $result = str_replace('{icon_after}', $iconAfterHtml, $result);
        $result = str_replace('{title}', htmlspecialchars($title), $result);
        $result = str_replace('{show_default}', $isOpen ? 'show' : '', $result);
        $result = str_replace('{content}', $contentText, $result);
        $result = str_replace('{preset_id}', $presetId ? htmlspecialchars($presetId) : '', $result);
        $result = str_replace('{preset_name}', $presetName ? htmlspecialchars($presetName) : '', $result);
        $result = str_replace('{block_type}', $this->getSystemName(), $result);
        $result = str_replace('{block_name}', $this->getName(), $result);
        $result = str_replace('{aria_expanded}', $ariaExpanded, $result);
        $result = str_replace('{aria_hidden}', $ariaHidden, $result);
        $result = str_replace('{action_text}', $actionText, $result);
        $result = str_replace('{no_animation_class}', $noAnimationClass, $result);

        return $result;
    }

    private function extractIconName($iconClass): string {
        if (empty($iconClass)) {
            return '';
        }

        $parts = explode(' ', $iconClass);
        
        foreach ($parts as $part) {
            if (strpos($part, 'bi-') === 0) {
                return substr($part, 3);
            }
        }
        
        $lastPart = end($parts);
        if (strpos($lastPart, '-') !== false) {
            return $lastPart;
        }
        
        return $iconClass;
    }

    public function getShortcodes(): array {
        return array_merge(parent::getShortcodes(), [
            '{title}' => 'Заголовок спойлера',
            '{content}' => 'Содержимое спойлера (может содержать HTML)',
            '{block_id}' => 'Уникальный ID блока',
            '{show_default}' => 'Классы для состояния по умолчанию',
            '{icon_before}' => 'Иконка перед заголовком',
            '{icon_after}' => 'Иконка после заголовком',
            '{icon_position}' => 'Позиция иконки',
            '{custom_class}' => 'Дополнительный CSS класс'
        ]);
    }

    public function getAdminCss(): array {
        return [
            'templates/default/admin/assets/css/blocks/spoiler.css'
        ];
    }

    public function getSystemCss(): array {
        return [
            'templates/default/admin/assets/css/blocks/spoiler.css',
        ];
    }

    public function validateSettings($settings): array {
        $errors = [];

        if (!empty($settings['custom_class']) && !preg_match('/^[a-zA-Z0-9-_ ]+$/', $settings['custom_class'])) {
            $errors[] = 'CSS класс может содержать только буквы, цифры, дефисы и подчеркивания';
        }

        $allowedIconPositions = ['icon-before', 'icon-after', 'icon-both'];
        if (!empty($settings['icon_position']) && !in_array($settings['icon_position'], $allowedIconPositions)) {
            $errors[] = 'Недопустимая позиция иконки';
        }

        return [empty($errors), $errors];
    }

    public function extractFromHtml(string $html): ?array {
        if (preg_match('/<div[^>]*class="[^"]*spoiler-header[^"]*"[^>]*>.*?<button[^>]*>.*?<span[^>]*class="[^"]*spoiler-title[^"]*"[^>]*>(.*?)<\/span>.*?<\/button>.*?<\/div>.*?<div[^>]*class="[^"]*spoiler-content[^"]*"[^>]*>.*?<div[^>]*class="[^"]*spoiler-body[^"]*"[^>]*>(.*?)<\/div>.*?<\/div>/is', $html, $matches)) {
            $title = trim(strip_tags($matches[1]));
            $content = trim($matches[2]);
            
            if (!empty($title)) {
                return [
                    'title' => $title,
                    'content' => $content
                ];
            }
        }
        
        if (preg_match('/<h[1-6][^>]*>(.*?)<\/h[1-6]>/i', $html, $titleMatch)) {
            $title = trim(strip_tags($titleMatch[1]));
            $content = trim(strip_tags($html));
            $content = preg_replace('/^.*?<\/h[1-6]>/is', '', $content);
            
            if (!empty($title) && !empty($content)) {
                return [
                    'title' => $title,
                    'content' => $content
                ];
            }
        }
        
        return null;
    }

    public function validateAndNormalizeContent($content): array {
        if (is_string($content)) {
            $decoded = json_decode($content, true);
            return is_array($decoded) ? $decoded : ['title' => '', 'content' => ''];
        }
        
        if (!is_array($content)) {
            return ['title' => '', 'content' => ''];
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
        
        return $settings;
    }

    public function prepareContent($content): array {
        if (!is_array($content)) {
            $content = [];
        }
        
        if (isset($_POST['content']) && is_array($_POST['content'])) {
            if (isset($_POST['content']['title'])) {
                $content['title'] = trim($_POST['content']['title']);
            }
            if (isset($_POST['content']['content'])) {
                $content['content'] = trim($_POST['content']['content']);
            }
        }
        
        if (!isset($content['title'])) {
            $content['title'] = 'Нажмите чтобы раскрыть';
        }
        if (!isset($content['content'])) {
            $content['content'] = 'Скрытый контент...';
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
        
        if (isset($settings['animation'])) {
            $settings['animation'] = (bool)$settings['animation'];
        }

        if (isset($settings['show_default'])) {
            $settings['show_default'] = $settings['show_default'] === 'show' ? 'show' : '';
        }

        if (isset($settings['custom_class'])) {
            $settings['custom_class'] = trim($settings['custom_class']);
        }

        return $settings;
    }

    public function getFrontendJs(): array {
        return [
            '/templates/default/front/assets/postblocks/spoiler/spoiler.js',
        ];
    }

    public function getFrontendCss(): array {
        return [
            '/templates/default/front/assets/postblocks/spoiler/spoiler.css',
        ];
    }

    public function getPreviewHtml($content = [], $settings = []): string {
        $content = $this->validateAndNormalizeContent($content);
        $settings = $this->validateAndNormalizeSettings($settings);
        
        $title = $content['title'] ?? 'Нажмите чтобы раскрыть';
        $contentText = $content['content'] ?? 'Скрытый контент...';
        $showDefault = $settings['show_default'] ?? '';
        $iconBefore = $settings['icon_before'] ?? 'bi bi-chevron-down';
        $iconAfter = $settings['icon_after'] ?? '';
        $iconPosition = $settings['icon_position'] ?? 'icon-after';
        $customClass = $settings['custom_class'] ?? '';
        $animation = $settings['animation'] ?? true;
        
        $isOpen = $showDefault === 'show';
        $contentLength = strlen($contentText);
        $previewIcon = 'bi bi-chevron-down';
        if (!empty($iconBefore) && $iconBefore !== '' && $iconBefore !== 'null') {
            $previewIcon = $iconBefore;
        } elseif (!empty($iconAfter) && $iconAfter !== '' && $iconAfter !== 'null') {
            $previewIcon = $iconAfter;
        }
        
        $iconPositionText = match($iconPosition) {
            'icon-before' => 'Слева',
            'icon-after' => 'Справа',
            'icon-both' => 'С обеих сторон',
            default => 'Справа'
        };
        
        ob_start();
        ?>
        <div class="post-block-preview post-block-preview-SpoilerBlock full-content-preview">
            <div class="preview-wrapper">
                <div class="preview-header">
                    <div class="preview-header-content">
                        <div class="preview-icon">
                            <i class="bi bi-eye"></i>
                        </div>
                        <div class="preview-info">
                            <div class="preview-title">
                                <strong>Спойлер</strong>
                                <?php if ($isOpen): ?>
                                    <span class="badge bg-success badge-sm">Открыт</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary badge-sm">Закрыт</span>
                                <?php endif; ?>
                            </div>
                            <div class="preview-stats">
                                <?= strlen($title) ?> симв. в заголовке
                                · <?= $contentLength ?> симв. в контенте
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
                    <?php if (!empty(trim($title)) || !empty(trim($contentText))): ?>
                        <div class="spoiler-preview-container">
                            <div class="spoiler-header-preview border rounded p-3 mb-2 bg-light">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="flex-grow-1">
                                        <div class="d-flex align-items-center">
                                            <?php if (($iconPosition === 'icon-before' || $iconPosition === 'icon-both') && !empty($previewIcon)): ?>
                                                <i class="<?= htmlspecialchars($previewIcon) ?> me-2 text-primary"></i>
                                            <?php endif; ?>
                                            
                                            <span class="fw-semibold" style="color: #374151;">
                                                <?= htmlspecialchars(mb_substr($title, 0, 40)) ?>
                                                <?php if (mb_strlen($title) > 40): ?>...<?php endif; ?>
                                            </span>
                                            
                                            <?php if (($iconPosition === 'icon-after' || $iconPosition === 'icon-both') && !empty($previewIcon)): ?>
                                                <i class="<?= htmlspecialchars($previewIcon) ?> ms-2 text-primary"></i>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="ms-3">
                                        <span class="badge <?= $isOpen ? 'bg-success' : 'bg-secondary' ?>">
                                            <?= $isOpen ? 'Открыт' : 'Закрыт' ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="spoiler-content-preview border rounded p-3 bg-white <?= $isOpen ? '' : 'bg-light' ?>">
                                <div class="small text-muted mb-2 d-flex justify-content-between">
                                    <span><i class="bi bi-eye-slash"></i> Скрытый контент</span>
                                    <span><?= $contentLength ?> симв.</span>
                                </div>
                                
                                <?php if (!empty(trim($contentText))): ?>
                                    <div class="spoiler-text-preview small" style="color: #6b7280; line-height: 1.5;">
                                        <?= htmlspecialchars(mb_substr(strip_tags($contentText), 0, 80)) ?>
                                        <?php if (mb_strlen(strip_tags($contentText)) > 80): ?>...<?php endif; ?>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center py-2 text-muted">
                                        <i class="bi bi-eye-slash"></i>
                                        <div class="small mt-1">Контент не добавлен</div>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="spoiler-preview-info mt-3 small text-muted">
                                <div class="row">
                                    <div class="col-6">
                                        <div><i class="bi bi-<?= $isOpen ? 'unlock' : 'lock' ?> me-1"></i>По умолчанию: <strong><?= $isOpen ? 'Открыт' : 'Закрыт' ?></strong></div>
                                        <div><i class="bi bi-gear me-1"></i>Иконки: <strong><?= htmlspecialchars($iconPositionText) ?></strong></div>
                                    </div>
                                    <div class="col-6">
                                        <?php if ($customClass): ?>
                                            <div><i class="bi bi-tag me-1"></i>Класс: <strong><?= htmlspecialchars($customClass) ?></strong></div>
                                        <?php endif; ?>
                                        <div><i class="bi bi-play-circle me-1"></i>Анимация: <strong><?= $animation ? 'Да' : 'Нет' ?></strong></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="preview-empty-state">
                            <i class="bi bi-eye"></i>
                            <div class="empty-text">Содержимое не добавлено</div>
                            <button type="button" class="btn btn-sm btn-outline-primary mt-2" 
                                    onclick="postBlocksManager.editBlock('{block_id}')">
                                <i class="bi bi-plus-circle"></i> Добавить спойлер
                            </button>
                            <div class="mt-3 small text-muted">
                                <i class="bi bi-info-circle"></i>
                                Используйте для скрытия контента под заголовком
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }


}