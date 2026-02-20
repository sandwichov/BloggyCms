<?php
class CodeBlock extends BasePostBlock {
    
    public function getName(): string {
        return 'Пример кода';
    }

    public function getSystemName(): string {
        return 'CodeBlock';
    }

    public function getDescription(): string {
        return 'Блок для вставки примеров кода с подсветкой синтаксиса и красивым оформлением';
    }

    public function getIcon(): string {
        return 'bi bi-code-slash';
    }

    public function getCategory(): string {
        return 'advanced';
    }

    public function getPreviewHtml($content = [], $settings = []): string {
        $content = $this->validateAndNormalizeContent($content);
        $settings = $this->validateAndNormalizeSettings($settings);
        
        $code = $content['code'] ?? '// Ваш код здесь...';
        $language = $content['language'] ?? 'javascript';
        $filename = $content['filename'] ?? '';
        $showLineNumbers = $settings['show_line_numbers'] ?? true;
        $copyButton = $settings['copy_button'] ?? true;
        $theme = $settings['theme'] ?? 'default';
        $showLanguageBadge = $settings['show_language_badge'] ?? true;
        $languageNames = [
            'javascript' => 'JavaScript',
            'typescript' => 'TypeScript',
            'php' => 'PHP',
            'html' => 'HTML',
            'css' => 'CSS',
            'scss' => 'SCSS',
            'python' => 'Python',
            'java' => 'Java',
            'cpp' => 'C++',
            'csharp' => 'C#',
            'sql' => 'SQL',
            'json' => 'JSON',
            'xml' => 'XML',
            'bash' => 'Bash',
            'markdown' => 'Markdown',
            'yaml' => 'YAML',
            'dockerfile' => 'Docker',
            'nginx' => 'Nginx',
            'plaintext' => 'Текст'
        ];
        
        $languageName = $languageNames[$language] ?? ucfirst($language);

        $previewCode = htmlspecialchars($code);
        if (strlen($previewCode) > 100) {
            $previewCode = substr($previewCode, 0, 100) . '...';
        }
        
        ob_start();
        ?>
        <div class="post-block-preview post-block-preview-CodeBlock full-content-preview">
            <div class="preview-wrapper">
                <div class="preview-header">
                    <div class="preview-header-content">
                        <div class="preview-icon">
                            <i class="bi bi-code-slash"></i>
                        </div>
                        <div class="preview-info">
                            <div class="preview-title">
                                <strong>Пример кода</strong>
                                <span class="badge bg-primary badge-sm"><?= htmlspecialchars($languageName) ?></span>
                            </div>
                            <div class="preview-stats">
                                <?= strlen($code) ?> симв.
                                <?php if ($showLineNumbers): ?>
                                    · номера строк
                                <?php endif; ?>
                                <?php if ($copyButton): ?>
                                    · копирование
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
                    <?php if (!empty(trim($code))): ?>
                        <div class="code-preview-container">
                            <div class="code-preview-header d-flex justify-content-between align-items-center bg-dark text-white p-2 rounded-top">
                                <div class="code-preview-info">
                                    <?php if ($filename): ?>
                                        <span class="badge bg-secondary me-2">
                                            <i class="bi bi-file-earmark-code"></i> <?= htmlspecialchars($filename) ?>
                                        </span>
                                    <?php endif; ?>
                                    <?php if ($showLanguageBadge): ?>
                                        <span class="badge bg-primary">
                                            <?= htmlspecialchars($languageName) ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <?php if ($copyButton): ?>
                                    <div class="code-preview-actions">
                                        <button class="btn btn-sm btn-outline-light btn-copy-preview" 
                                                title="Скопировать код" disabled>
                                            <i class="bi bi-clipboard"></i>
                                        </button>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="code-preview-content bg-light p-3 rounded-bottom">
                                <pre class="mb-0" style="font-family: 'Courier New', monospace; font-size: 13px; margin: 0; white-space: pre-wrap;">
                                    <code style="color: #333;"><?= $previewCode ?></code>
                                </pre>
                            </div>
                            
                            <div class="code-preview-meta mt-2 small text-muted">
                                <span><i class="bi bi-gear"></i> Тема: <?= htmlspecialchars($theme) ?></span>
                                <?php if ($showLineNumbers): ?>
                                    <span class="ms-3"><i class="bi bi-list-ol"></i> С номерами строк</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="preview-empty-state">
                            <i class="bi bi-code-slash"></i>
                            <div class="empty-text">Код не добавлен</div>
                            <button type="button" class="btn btn-sm btn-outline-primary mt-2" 
                                    onclick="postBlocksManager.editBlock('{block_id}')">
                                <i class="bi bi-plus-circle"></i> Добавить код
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
        <div class="code-block-wrapper {custom_class} theme-{theme}">
            <div class="code-header">
                <div class="code-meta">
                    {language_badge}
                    {filename}
                </div>
                <div class="code-actions">
                    {copy_button}
                </div>
            </div>
            <div class="code-container">
                <pre class="{line_numbers}" data-language="{language}"><code class="language-{language}">{code}</code></pre>
            </div>
        </div>';
    }

    public function getDefaultContent(): array {
        return [
            'code' => '// Ваш код здесь...',
            'language' => 'javascript',
            'filename' => ''
        ];
    }

    public function getDefaultSettings(): array {
        return [
            'show_line_numbers' => true,
            'copy_button' => true,
            'theme' => 'default',
            'custom_class' => '',
            'show_language_badge' => true
        ];
    }

    public function getContentForm($currentContent = []): string {
        $currentContent = $this->validateAndNormalizeContent($currentContent);
        $code = $currentContent['code'] ?? '';
        $language = $currentContent['language'] ?? 'javascript';
        $filename = $currentContent['filename'] ?? '';

        $languages = [
            'javascript' => 'JavaScript',
            'typescript' => 'TypeScript',
            'php' => 'PHP',
            'html' => 'HTML',
            'css' => 'CSS',
            'scss' => 'SCSS',
            'python' => 'Python',
            'java' => 'Java',
            'cpp' => 'C++',
            'csharp' => 'C#',
            'sql' => 'SQL',
            'json' => 'JSON',
            'xml' => 'XML',
            'bash' => 'Bash',
            'markdown' => 'Markdown',
            'yaml' => 'YAML',
            'dockerfile' => 'Dockerfile',
            'nginx' => 'Nginx',
            'plaintext' => 'Текст'
        ];

        ob_start();
        ?>
        <div class="row">
            <div class="col-md-6">
                <div class="mb-4">
                    <label class="form-label">Язык программирования</label>
                    <select name="content[language]" class="form-select" id="code-language-select">
                        <?php foreach($languages as $value => $name): ?>
                            <option value="<?= $value ?>" <?= $language === $value ? 'selected' : '' ?>>
                                <?= $name ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-4">
                    <label class="form-label">Имя файла (опционально)</label>
                    <input type="text" 
                           name="content[filename]" 
                           class="form-control" 
                           value="<?= htmlspecialchars($filename) ?>" 
                           placeholder="script.js, style.css, etc.">
                    <div class="form-text">
                        Отображается в заголовке блока с кодом
                    </div>
                </div>
            </div>
        </div>

        <div class="mb-4">
            <label class="form-label">Код</label>
            <div id="code-editor-container" style="height: 400px; border: 1px solid #dee2e6; border-radius: 0.375rem;"></div>
            <textarea name="content[code]" 
                     id="code-editor-textarea" 
                     style="display: none;"
                     required><?= htmlspecialchars($code) ?></textarea>
            <div class="form-text">
                Поддерживается подсветка синтаксиса для различных языков программирования
            </div>
        </div>

        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            if (form) {
                form.addEventListener('submit', function() {
                    const editorContainer = document.getElementById('code-editor-container');
                    const textarea = document.getElementById('code-editor-textarea');
                    
                    if (window.ace && editorContainer && textarea) {
                        const editor = ace.edit(editorContainer);
                        if (editor) {
                            textarea.value = editor.getValue();
                        }
                    }
                });
            }
        });
        </script>
        <?php
        return ob_get_clean();
    }

    public function getSettingsForm($currentSettings = []): string {
        $currentSettings = $this->validateAndNormalizeSettings($currentSettings);
        $showLineNumbers = $currentSettings['show_line_numbers'] ?? true;
        $copyButton = $currentSettings['copy_button'] ?? true;
        $theme = $currentSettings['theme'] ?? 'default';
        $customClass = $currentSettings['custom_class'] ?? '';
        $showLanguageBadge = $currentSettings['show_language_badge'] ?? true;

        $themes = [
            'default' => 'Стандартная (светлая)',
            'dark' => 'Темная',
            'material' => 'Material',
            'github' => 'GitHub',
            'coy' => 'Coy',
            'okaidia' => 'Okaidia',
            'tomorrow' => 'Tomorrow',
            'twilight' => 'Twilight'
        ];

        ob_start();
        ?>
        <div class="row">
            <div class="col-md-6">
                <div class="mb-4">
                    <label class="form-label">Тема подсветки</label>
                    <select name="settings[theme]" class="form-select">
                        <?php foreach($themes as $value => $name): ?>
                            <option value="<?= $value ?>" <?= $theme === $value ? 'selected' : '' ?>>
                                <?= $name ?>
                            </option>
                        <?php endforeach; ?>
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
                           placeholder="my-code-block">
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <div class="form-check form-switch mb-4">
                    <input class="form-check-input" 
                           type="checkbox" 
                           name="settings[show_line_numbers]" 
                           id="show_line_numbers"
                           value="1" 
                           <?= $showLineNumbers ? 'checked' : '' ?>>
                    <label class="form-check-label" for="show_line_numbers">
                        Номера строк
                    </label>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-check form-switch mb-4">
                    <input class="form-check-input" 
                           type="checkbox" 
                           name="settings[copy_button]" 
                           id="copy_button"
                           value="1" 
                           <?= $copyButton ? 'checked' : '' ?>>
                    <label class="form-check-label" for="copy_button">
                        Кнопка копирования
                    </label>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-check form-switch mb-4">
                    <input class="form-check-input" 
                           type="checkbox" 
                           name="settings[show_language_badge]" 
                           id="show_language_badge"
                           value="1" 
                           <?= $showLanguageBadge ? 'checked' : '' ?>>
                    <label class="form-check-label" for="show_language_badge">
                        Бейдж языка
                    </label>
                </div>
            </div>
        </div>

        <div class="alert alert-info">
            <i class="bi bi-info-circle me-2"></i>
            Для работы подсветки синтаксиса необходимо подключить библиотеку Prism.js в шаблоне
        </div>
        <?php
        return ob_get_clean();
    }

    public function getEditorHtml($settings = [], $content = []): string {
        $content = $this->validateAndNormalizeContent($content);
        
        $code = $content['code'] ?? '// Ваш код здесь...';
        $language = $content['language'] ?? 'javascript';
        $filename = $content['filename'] ?? '';
        $previewCode = $code;
        if (strlen($previewCode) > 150) {
            $previewCode = substr($previewCode, 0, 150) . '...';
        }

        $languageNames = [
            'javascript' => 'JavaScript',
            'typescript' => 'TypeScript',
            'php' => 'PHP',
            'html' => 'HTML',
            'css' => 'CSS',
            'python' => 'Python',
            'java' => 'Java',
            'cpp' => 'C++',
            'sql' => 'SQL',
            'json' => 'JSON',
            'xml' => 'XML',
            'bash' => 'Bash',
            'markdown' => 'Markdown',
            'plaintext' => 'Текст'
        ];

        $languageName = $languageNames[$language] ?? ucfirst($language);

        return '
        <div class="post-block-code-preview card">
            <div class="card-header py-2">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="badge bg-primary">' . htmlspecialchars($languageName) . '</span>
                    <small class="text-muted">Пример кода</small>
                </div>
            </div>
            <div class="card-body">
                <pre class="m-0"><code>' . htmlspecialchars($previewCode) . '</code></pre>
            </div>
        </div>';
    }

    public function processFrontend($content, $settings = []): string {
        return parent::processFrontend($content, $settings);
    }

    public function getShortcodes(): array {
        return array_merge(parent::getShortcodes(), [
            '{code}' => 'Код',
            '{language}' => 'Язык программирования (техническое имя)',
            '{language_badge}' => 'Бейдж с названием языка',
            '{filename}' => 'Имя файла',
            '{copy_button}' => 'Кнопка копирования',
            '{custom_class}' => 'Дополнительный CSS класс',
            '{line_numbers}' => 'Класс для номеров строк',
            '{theme}' => 'Тема подсветки кода'
        ]);
    }

    public function getAdminJs(): array {
        return [
            'templates/default/admin/assets/js/controllers/ace.js',
            'templates/default/admin/assets/js/controllers/mode-html.js',
            'templates/default/admin/assets/js/controllers/theme-monokai.js',
            'templates/default/admin/assets/js/blocks/code.js'
        ];
    }

    public function validateSettings($settings): array {
        $errors = [];

        if (!empty($settings['custom_class']) && !preg_match('/^[a-zA-Z0-9-_ ]+$/', $settings['custom_class'])) {
            $errors[] = 'CSS класс может содержать только буквы, цифры, дефисы и подчеркивания';
        }

        $allowedThemes = ['default', 'dark', 'material', 'github', 'coy', 'okaidia', 'tomorrow', 'twilight'];
        if (!empty($settings['theme']) && !in_array($settings['theme'], $allowedThemes)) {
            $errors[] = 'Недопустимая тема подсветки';
        }

        return [empty($errors), $errors];
    }

    public function prepareSettings($settings): array {
        $settings = parent::prepareSettings($settings);

        if (isset($settings['show_line_numbers'])) {
            $settings['show_line_numbers'] = (bool)$settings['show_line_numbers'];
        }
        
        if (isset($settings['copy_button'])) {
            $settings['copy_button'] = (bool)$settings['copy_button'];
        }
        
        if (isset($settings['show_language_badge'])) {
            $settings['show_language_badge'] = (bool)$settings['show_language_badge'];
        }
        
        if (isset($settings['theme'])) {
            $allowedThemes = ['default', 'dark', 'material', 'github', 'coy', 'okaidia', 'tomorrow', 'twilight'];
            if (!in_array($settings['theme'], $allowedThemes)) {
                $settings['theme'] = 'default';
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
        
        if (isset($content['code'])) {
            $content['code'] = trim($content['code']);
            if (mb_strlen($content['code']) > 10000) {
                $content['code'] = mb_substr($content['code'], 0, 10000) . '...';
            }
        }
        
        if (isset($content['language'])) {
            $allowedLanguages = [
                'javascript', 'typescript', 'php', 'html', 'css', 'scss', 
                'python', 'java', 'cpp', 'csharp', 'sql', 'json', 'xml', 
                'bash', 'markdown', 'yaml', 'dockerfile', 'nginx', 'plaintext'
            ];
            
            $content['language'] = strtolower(trim($content['language']));
            if (!in_array($content['language'], $allowedLanguages)) {
                $content['language'] = 'plaintext';
            }
        }
        
        if (isset($content['filename'])) {
            $content['filename'] = trim($content['filename']);
            $content['filename'] = preg_replace('/[^\w\s.-]/', '', $content['filename']);
            if (mb_strlen($content['filename']) > 100) {
                $content['filename'] = mb_substr($content['filename'], 0, 100);
            }
        }

        return $content;
    }

    public function extractFromHtml(string $html): ?array {
        if (preg_match('/<pre[^>]*>\s*<code[^>]*>(.*?)<\/code>\s*<\/pre>/s', $html, $matches)) {
            $code = trim(html_entity_decode(strip_tags($matches[1])));
            if (!empty($code)) {
                return [
                    'code' => $code,
                    'language' => 'plaintext'
                ];
            }
        }
        
        $language = 'plaintext';
        if (preg_match('/class="[^"]*language-([^"\s]+)/', $html, $langMatches)) {
            $language = $langMatches[1];
        }

        $plainText = trim(strip_tags($html));
        if (!empty($plainText)) {
            return [
                'code' => $plainText,
                'language' => $language
            ];
        }

        return null;
    }

    public function canExtractFromHtml(): bool {
        return true;
    }

    protected function renderWithTemplate($content, $settings, $template): string {
        $content = $this->validateAndNormalizeContent($content);
        $settings = $this->validateAndNormalizeSettings($settings);
        
        $code = $content['code'] ?? '';
        $language = $content['language'] ?? 'javascript';
        $filename = $content['filename'] ?? '';
        $showLineNumbers = $settings['show_line_numbers'] ?? true;
        $copyButton = $settings['copy_button'] ?? true;
        $theme = $settings['theme'] ?? 'default';
        $customClass = $settings['custom_class'] ?? '';
        $showLanguageBadge = $settings['show_language_badge'] ?? true;
        $presetId = $settings['preset_id'] ?? null;
        $presetName = $settings['preset_name'] ?? '';
        $languageNames = [
            'javascript' => 'JavaScript',
            'typescript' => 'TypeScript', 
            'php' => 'PHP',
            'html' => 'HTML',
            'css' => 'CSS',
            'scss' => 'SCSS',
            'python' => 'Python',
            'java' => 'Java',
            'cpp' => 'C++',
            'csharp' => 'C#',
            'sql' => 'SQL',
            'json' => 'JSON',
            'xml' => 'XML',
            'bash' => 'Bash',
            'markdown' => 'Markdown',
            'yaml' => 'YAML',
            'dockerfile' => 'Docker',
            'nginx' => 'Nginx',
            'plaintext' => 'Текст'
        ];

        $languageName = $languageNames[$language] ?? ucfirst($language);

        $copyButtonHtml = '';
        if ($copyButton) {
            $copyButtonHtml = '
            <div class="code-actions">
                <button class="btn-copy-code" type="button" title="Скопировать код">
                    <div class="btn-copy-content">
                        <span class="btn-copy-text">
                            <i class="bi bi-clipboard btn-copy-icon"></i>
                            Копировать
                        </span>
                        <span class="btn-copy-success">
                            <i class="bi bi-check-circle-fill"></i>
                            Скопировано
                        </span>
                    </div>
                </button>
            </div>';
        }
        
        $languageBadgeHtml = '';
        if ($showLanguageBadge) {
            $languageBadgeHtml = '<span class="code-language">' . htmlspecialchars($languageName) . '</span>';
        }

        $lineNumbersClass = $showLineNumbers ? 'line-numbers' : '';
        $filenameHtml = $filename ? '<span class="code-filename">' . htmlspecialchars($filename) . '</span>' : '';
        $result = $template;
        $replacements = [
            '{code}' => htmlspecialchars($code),
            '{language}' => $language,
            '{language_badge}' => $languageBadgeHtml,
            '{filename}' => $filenameHtml,
            '{copy_button}' => $copyButtonHtml,
            '{custom_class}' => $customClass,
            '{line_numbers}' => $lineNumbersClass,
            '{theme}' => $theme,
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

    public function getFrontendJs(): array {
        return [
            '/templates/default/front/assets/postblocks/codeblock/code.js',
        ];
    }

    public function getFrontendCss(): array {
        return [
            '/templates/default/front/assets/postblocks/codeblock/code.css',
        ];
    }

    public function getSupportedLanguages(): array {
        return [
            'javascript' => ['name' => 'JavaScript', 'extension' => 'js'],
            'typescript' => ['name' => 'TypeScript', 'extension' => 'ts'],
            'php' => ['name' => 'PHP', 'extension' => 'php'],
            'html' => ['name' => 'HTML', 'extension' => 'html'],
            'css' => ['name' => 'CSS', 'extension' => 'css'],
            'python' => ['name' => 'Python', 'extension' => 'py'],
            'java' => ['name' => 'Java', 'extension' => 'java'],
            'sql' => ['name' => 'SQL', 'extension' => 'sql'],
            'json' => ['name' => 'JSON', 'extension' => 'json'],
            'xml' => ['name' => 'XML', 'extension' => 'xml']
        ];
    }
}