<?php
class AlertBlock extends BasePostBlock {
    
    public function getName(): string {
        return 'Блок с предупреждением';
    }

    public function getSystemName(): string {
        return 'AlertBlock';
    }

    public function getDescription(): string {
        return 'Блок для отображения различных типов уведомлений: информация, предупреждение, успех, ошибка';
    }

    public function getIcon(): string {
        return 'bi bi-exclamation-triangle';
    }

    public function getCategory(): string {
        return 'interactive';
    }

    public function getTemplateWithShortcodes(): string {
        return '
        <div class="alert alert-{type} {dismissible_class} {custom_class}" role="alert" {aria_live}>
            {icon}
            <div class="alert-content">
                <div class="alert-title">{title}</div>
                <div class="alert-body">{content}</div>
            </div>
            {dismiss_button}
        </div>';
    }

    public function getDefaultContent(): array {
        return [
            'title' => 'Внимание!',
            'content' => 'Это важное сообщение, на которое следует обратить внимание.',
        ];
    }

    public function getDefaultSettings(): array {
        return [
            'type' => 'warning',
            'dismissible' => false,
            'custom_class' => '',
            'show_icon' => true,
            'aria_live' => 'polite'
        ];
    }

    public function getPreviewHtml($content = [], $settings = []): string {
        $content = $this->validateAndNormalizeContent($content);
        $settings = $this->validateAndNormalizeSettings($settings);
        
        $title = $content['title'] ?? 'Внимание!';
        $contentText = $content['content'] ?? 'Это важное сообщение, на которое следует обратить внимание.';
        $type = $settings['type'] ?? 'warning';
        $dismissible = $settings['dismissible'] ?? false;
        $customClass = $settings['custom_class'] ?? '';
        $showIcon = $settings['show_icon'] ?? true;
        
        $iconClass = match($type) {
            'success' => 'bi bi-check-circle',
            'danger' => 'bi bi-x-circle',
            'info' => 'bi bi-info-circle',
            'primary' => 'bi bi-bell',
            'secondary' => 'bi bi-exclamation-circle',
            'dark' => 'bi bi-moon',
            default => 'bi bi-exclamation-triangle'
        };
        
        $typeText = match($type) {
            'success' => 'Успех',
            'danger' => 'Ошибка',
            'info' => 'Информация',
            'primary' => 'Основное',
            'secondary' => 'Дополнительное',
            'dark' => 'Темное',
            default => 'Предупреждение'
        };
        
        ob_start();
        ?>
        <div class="post-block-preview post-block-preview-AlertBlock full-content-preview">
            <div class="preview-wrapper">
                <div class="preview-header">
                    <div class="preview-header-content">
                        <div class="preview-icon">
                            <i class="bi bi-exclamation-triangle"></i>
                        </div>
                        <div class="preview-info">
                            <div class="preview-title">
                                <strong>Блок с предупреждением</strong>
                                <span class="badge bg-<?= htmlspecialchars($type) ?> badge-sm"><?= htmlspecialchars($typeText) ?></span>
                            </div>
                            <div class="preview-stats">
                                <?= strlen($title) ?> симв. в заголовке
                                · <?= strlen($contentText) ?> симв. в тексте
                                <?php if ($dismissible): ?>
                                    · закрываемый
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
                    <?php if (!empty(trim($title)) || !empty(trim($contentText))): ?>
                        <div class="alert-preview-container">
                            <div class="alert alert-<?= htmlspecialchars($type) ?> mb-0 border shadow-sm <?= $dismissible ? 'alert-dismissible' : '' ?>">
                                <div class="d-flex align-items-start">
                                    <?php if ($showIcon): ?>
                                        <div class="me-3 flex-shrink-0">
                                            <i class="<?= $iconClass ?>" style="font-size: 1.2rem;"></i>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="flex-grow-1">
                                        <?php if (!empty(trim($title))): ?>
                                            <div class="alert-title fw-bold mb-1">
                                                <?= htmlspecialchars(mb_substr($title, 0, 30)) ?>
                                                <?php if (mb_strlen($title) > 30): ?>...<?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <?php if (!empty(trim($contentText))): ?>
                                            <div class="alert-body small">
                                                <?= htmlspecialchars(mb_substr($contentText, 0, 80)) ?>
                                                <?php if (mb_strlen($contentText) > 80): ?>...<?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <?php if ($dismissible): ?>
                                        <button type="button" class="btn-close ms-2 flex-shrink-0" disabled></button>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="alert-preview-info mt-3 small text-muted">
                                <div class="row">
                                    <div class="col-6">
                                        <div>
                                            <i class="bi bi-circle-fill text-<?= htmlspecialchars($type) ?> me-1"></i>
                                            Тип: <strong><?= htmlspecialchars($typeText) ?></strong>
                                        </div>
                                        <div>
                                            <i class="bi bi-<?= $showIcon ? 'eye' : 'eye-slash' ?> me-1"></i>
                                            Иконка: <strong><?= $showIcon ? 'Да' : 'Нет' ?></strong>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <?php if ($customClass): ?>
                                            <div><i class="bi bi-tag me-1"></i>Класс: <strong><?= htmlspecialchars($customClass) ?></strong></div>
                                        <?php endif; ?>
                                        <div>
                                            <i class="bi bi-<?= $dismissible ? 'x-circle' : 'circle' ?> me-1"></i>
                                            Закрываемый: <strong><?= $dismissible ? 'Да' : 'Нет' ?></strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="preview-empty-state">
                            <i class="bi bi-exclamation-triangle"></i>
                            <div class="empty-text">Содержимое не добавлено</div>
                            <button type="button" class="btn btn-sm btn-outline-primary mt-2" 
                                    onclick="postBlocksManager.editBlock('{block_id}')">
                                <i class="bi bi-plus-circle"></i> Добавить предупреждение
                            </button>
                            <div class="mt-3 small text-muted">
                                <i class="bi bi-info-circle"></i>
                                Используйте для отображения различных типов уведомлений и сообщений
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    public function getContentForm($currentContent = []): string {
        $currentContent = $this->validateAndNormalizeContent($currentContent);
        $title = $currentContent['title'] ?? '';
        $content = $currentContent['content'] ?? '';

        ob_start();
        ?>
        <div class="mb-4">
            <label class="form-label">Заголовок (опционально)</label>
            <input type="text" 
                   name="content[title]" 
                   class="form-control" 
                   value="<?= htmlspecialchars($title) ?>" 
                   placeholder="Например: Важно!">
            <div class="form-text">Заголовок сообщения. Если оставить пустым, будет показан только основной текст.</div>
        </div>

        <div class="mb-4">
            <label class="form-label">Текст сообщения *</label>
            <textarea name="content[content]" 
                     class="form-control" 
                     rows="4" 
                     placeholder="Введите текст сообщения..."
                     required><?= htmlspecialchars($content) ?></textarea>
            <div class="form-text">Основное содержание блока предупреждения.</div>
        </div>
        <?php
        return ob_get_clean();
    }

    public function getSettingsForm($currentSettings = []): string {
        $currentSettings = $this->validateAndNormalizeSettings($currentSettings);
        $type = $currentSettings['type'] ?? 'warning';
        $dismissible = $currentSettings['dismissible'] ?? false;
        $customClass = $currentSettings['custom_class'] ?? '';
        $showIcon = $currentSettings['show_icon'] ?? true;
        $ariaLive = $currentSettings['aria_live'] ?? 'polite';

        ob_start();
        ?>
        <div class="row">
            <div class="col-md-6">
                <div class="mb-4">
                    <label class="form-label">Тип предупреждения</label>
                    <select name="settings[type]" class="form-select" id="alert-type-select">
                        <option value="primary" <?= $type === 'primary' ? 'selected' : '' ?> data-icon="bi bi-bell">Основное (primary)</option>
                        <option value="secondary" <?= $type === 'secondary' ? 'selected' : '' ?> data-icon="bi bi-exclamation-circle">Дополнительное (secondary)</option>
                        <option value="success" <?= $type === 'success' ? 'selected' : '' ?> data-icon="bi bi-check-circle">Успех (success)</option>
                        <option value="danger" <?= $type === 'danger' ? 'selected' : '' ?> data-icon="bi bi-x-circle">Ошибка (danger)</option>
                        <option value="warning" <?= $type === 'warning' ? 'selected' : '' ?> data-icon="bi bi-exclamation-triangle">Предупреждение (warning)</option>
                        <option value="info" <?= $type === 'info' ? 'selected' : '' ?> data-icon="bi bi-info-circle">Информация (info)</option>
                        <option value="light" <?= $type === 'light' ? 'selected' : '' ?> data-icon="bi bi-lightbulb">Светлое (light)</option>
                        <option value="dark" <?= $type === 'dark' ? 'selected' : '' ?> data-icon="bi bi-moon">Темное (dark)</option>
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
                           placeholder="my-alert">
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="form-check form-switch mb-4">
                    <input class="form-check-input" 
                           type="checkbox" 
                           name="settings[dismissible]" 
                           id="dismissible"
                           value="1" 
                           <?= $dismissible ? 'checked' : '' ?>>
                    <label class="form-check-label" for="dismissible">
                        Можно закрыть
                    </label>
                    <div class="form-text">Добавляет кнопку закрытия уведомления</div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-check form-switch mb-4">
                    <input class="form-check-input" 
                           type="checkbox" 
                           name="settings[show_icon]" 
                           id="show_icon"
                           value="1" 
                           <?= $showIcon ? 'checked' : '' ?>>
                    <label class="form-check-label" for="show_icon">
                        Показывать иконку
                    </label>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="mb-4">
                    <label class="form-label">ARIA live region</label>
                    <select name="settings[aria_live]" class="form-select">
                        <option value="polite" <?= $ariaLive === 'polite' ? 'selected' : '' ?>>Polite (не срочное)</option>
                        <option value="assertive" <?= $ariaLive === 'assertive' ? 'selected' : '' ?>>Assertive (срочное)</option>
                        <option value="off" <?= $ariaLive === 'off' ? 'selected' : '' ?>>Off (отключено)</option>
                    </select>
                    <div class="form-text">Настройка для скринридеров (доступность)</div>
                </div>
            </div>
        </div>

        <div class="alert-preview mb-4" id="alert-type-preview">
            <div class="alert alert-<?= $type ?> <?= $dismissible ? 'alert-dismissible' : '' ?>">
                <div class="d-flex align-items-start">
                    <?php if ($showIcon): ?>
                        <?php 
                        $icon = match($type) {
                            'success' => 'check-circle',
                            'danger' => 'x-circle',
                            'info' => 'info-circle',
                            'primary' => 'bell',
                            'secondary' => 'exclamation-circle',
                            'dark' => 'moon',
                            default => 'exclamation-triangle'
                        };
                        ?>
                        <i class="bi bi-<?= $icon ?> me-3 flex-shrink-0" style="font-size: 1.2rem;"></i>
                    <?php endif; ?>
                    
                    <div class="flex-grow-1">
                        <div class="alert-title fw-bold mb-1">Пример заголовка</div>
                        <div class="alert-body small">
                            Это пример сообщения типа "<?= $this->getTypeName($type) ?>". Здесь можно разместить важную информацию.
                        </div>
                    </div>
                    
                    <?php if ($dismissible): ?>
                        <button type="button" class="btn-close ms-2 flex-shrink-0"></button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const typeSelect = document.getElementById('alert-type-select');
            const preview = document.getElementById('alert-type-preview');
            const dismissibleCheck = document.getElementById('dismissible');
            const showIconCheck = document.getElementById('show_icon');
            
            function updatePreview() {
                const type = typeSelect.value;
                const dismissible = dismissibleCheck.checked;
                const showIcon = showIconCheck.checked;
                
                preview.querySelector('.alert').className = 'alert alert-' + type + (dismissible ? ' alert-dismissible' : '');
                
                const iconElement = preview.querySelector('.bi');
                if (iconElement) {
                    if (showIcon) {
                        const iconName = typeSelect.options[typeSelect.selectedIndex].getAttribute('data-icon');
                        iconElement.className = iconName + ' me-3 flex-shrink-0';
                        iconElement.style.fontSize = '1.2rem';
                        iconElement.style.display = 'block';
                    } else {
                        iconElement.style.display = 'none';
                    }
                }
                
                const closeBtn = preview.querySelector('.btn-close');
                if (closeBtn) {
                    closeBtn.style.display = dismissible ? 'block' : 'none';
                }
                
                const exampleText = preview.querySelector('.alert-body');
                if (exampleText) {
                    const typeNames = {
                        'primary': 'Основное',
                        'secondary': 'Дополнительное', 
                        'success': 'Успех',
                        'danger': 'Ошибка',
                        'warning': 'Предупреждение',
                        'info': 'Информация',
                        'light': 'Светлое',
                        'dark': 'Темное'
                    };
                    exampleText.textContent = 'Это пример сообщения типа "' + (typeNames[type] || 'Предупреждение') + '". Здесь можно разместить важную информацию.';
                }
            }
            
            if (typeSelect) typeSelect.addEventListener('change', updatePreview);
            if (dismissibleCheck) dismissibleCheck.addEventListener('change', updatePreview);
            if (showIconCheck) showIconCheck.addEventListener('change', updatePreview);
            
            updatePreview();
        });
        </script>
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
        $type = $settings['type'] ?? 'warning';
        $dismissible = $settings['dismissible'] ?? false;
        $customClass = $settings['custom_class'] ?? '';
        $showIcon = $settings['show_icon'] ?? true;
        $ariaLive = $settings['aria_live'] ?? 'polite';
        $presetId = $settings['preset_id'] ?? null;
        $presetName = $settings['preset_name'] ?? '';

        if (empty(trim($contentText))) {
            return '<!-- AlertBlock: пустой контент -->';
        }

        $presetClass = '';
        if ($presetId) {
            $presetClass = 'preset-' . (int)$presetId;
            if ($presetName) {
                $presetClass .= ' preset-' . preg_replace('/[^a-z0-9_-]/i', '-', strtolower($presetName));
            }
        }

        $iconHtml = '';
        if ($showIcon) {
            $iconName = match($type) {
                'success' => 'check-circle',
                'danger' => 'x-circle',
                'info' => 'info-circle',
                'primary' => 'bell',
                'secondary' => 'exclamation-circle',
                'dark' => 'moon',
                default => 'exclamation-triangle'
            };
            $iconHtml = bloggy_icon('bs', $iconName, '16 16', null, 'me-3 flex-shrink-0');
        }

        $dismissButton = '';
        if ($dismissible) {
            $dismissButton = '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Закрыть"></button>';
        }

        $result = $template;
        
        $replacements = [
            '{type}' => $type,
            '{title}' => htmlspecialchars($title),
            '{content}' => $contentText,
            '{icon}' => $iconHtml,
            '{dismissible_class}' => $dismissible ? 'alert-dismissible' : '',
            '{dismiss_button}' => $dismissButton,
            '{custom_class}' => trim($customClass . ' ' . $presetClass),
            '{aria_live}' => 'aria-live="' . $ariaLive . '"',
            '{preset_id}' => $presetId ? htmlspecialchars($presetId) : '',
            '{preset_name}' => $presetName ? htmlspecialchars($presetName) : '',
            '{block_type}' => $this->getSystemName(),
            '{block_name}' => $this->getName()
        ];
        
        foreach ($replacements as $shortcode => $replacement) {
            $result = str_replace($shortcode, $replacement, $result);
        }

        return $result;
    }

    public function getShortcodes(): array {
        return array_merge(parent::getShortcodes(), [
            '{type}' => 'Тип предупреждения (primary, secondary, success, danger, warning, info, light, dark)',
            '{title}' => 'Заголовок сообщения',
            '{content}' => 'Основной текст сообщения',
            '{icon}' => 'Иконка типа',
            '{dismissible_class}' => 'Класс для закрываемого блока',
            '{dismiss_button}' => 'Кнопка закрытия',
            '{custom_class}' => 'Дополнительный CSS класс',
            '{aria_live}' => 'ARIA атрибут для скринридеров'
        ]);
    }

    public function getAdminCss(): array {
        return [
            'templates/default/admin/assets/css/blocks/alert.css'
        ];
    }

    public function validateSettings($settings): array {
        $errors = [];

        if (!empty($settings['custom_class']) && !preg_match('/^[a-zA-Z0-9-_ ]+$/', $settings['custom_class'])) {
            $errors[] = 'CSS класс может содержать только буквы, цифры, дефисы и подчеркивания';
        }

        $allowedTypes = ['primary', 'secondary', 'success', 'danger', 'warning', 'info', 'light', 'dark'];
        if (!empty($settings['type']) && !in_array($settings['type'], $allowedTypes)) {
            $errors[] = 'Недопустимый тип предупреждения';
        }

        $allowedAriaLive = ['polite', 'assertive', 'off'];
        if (!empty($settings['aria_live']) && !in_array($settings['aria_live'], $allowedAriaLive)) {
            $errors[] = 'Недопустимое значение ARIA live';
        }

        return [empty($errors), $errors];
    }

    public function extractFromHtml(string $html): ?array {
        if (preg_match('/<div[^>]*class="[^"]*alert[^"]*alert-([^"\s]+)[^"]*"[^>]*>(.*?)<\/div>/s', $html, $matches)) {
            $alertType = $matches[1];
            $alertContent = $matches[2];
            $cleanContent = preg_replace('/<i[^>]*>.*?<\/i>|<button[^>]*>.*?<\/button>/s', '', $alertContent);
            $cleanContent = trim(strip_tags($cleanContent, '<strong><em><b><i><a><code>'));
            $lines = preg_split('/\r\n|\r|\n/', $cleanContent);
            $title = '';
            $content = '';
            
            if (count($lines) > 0) {
                $firstLine = trim($lines[0]);
                if (strlen($firstLine) < 100 && !empty($firstLine)) {
                    $title = $firstLine;
                    $content = implode(' ', array_slice($lines, 1));
                } else {
                    $content = $cleanContent;
                }
            }
            
            if (!empty($cleanContent)) {
                return [
                    'title' => $title,
                    'content' => $content ?: $cleanContent
                ];
            }
        }
        
        if (preg_match('/([A-ZА-Я][^.!?]*![^.!?]*[.!?])/', $html, $matches)) {
            return [
                'title' => '',
                'content' => trim($matches[1])
            ];
        }
        
        return null;
    }

    private function getTypeName($type): string {
        return match($type) {
            'success' => 'Успех',
            'danger' => 'Ошибка',
            'info' => 'Информация',
            'primary' => 'Основное',
            'secondary' => 'Дополнительное',
            'dark' => 'Темное',
            'light' => 'Светлое',
            default => 'Предупреждение'
        };
    }

    public function validateAndNormalizeContent($content): array {
        if (is_string($content)) {
            $decoded = json_decode($content, true);
            return is_array($decoded) ? $decoded : ['title' => '', 'content' => $content];
        }
        
        if (!is_array($content)) {
            return ['title' => '', 'content' => (string)$content];
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
            $content['title'] = '';
        }
        if (!isset($content['content'])) {
            $content['content'] = 'Это важное сообщение, на которое следует обратить внимание.';
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
        
        if (isset($settings['dismissible'])) {
            $settings['dismissible'] = (bool)$settings['dismissible'];
        }
        
        if (isset($settings['show_icon'])) {
            $settings['show_icon'] = (bool)$settings['show_icon'];
        }
        
        if (isset($settings['custom_class'])) {
            $settings['custom_class'] = trim($settings['custom_class']);
        }

        return $settings;
    }

    public function getFrontendJs(): array {
        return [
            '/templates/default/front/assets/postblocks/alert/alert.js',
        ];
    }

    public function getFrontendCss(): array {
        return [
            '/templates/default/front/assets/postblocks/alert/alert.css',
        ];
    }
}