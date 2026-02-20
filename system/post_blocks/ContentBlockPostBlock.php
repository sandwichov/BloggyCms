<?php
class ContentBlockPostBlock extends BasePostBlock {
    
    public function getName(): string {
        return 'Контент-блок';
    }

    public function getSystemName(): string {
        return 'ContentBlockPostBlock';
    }

    public function getDescription(): string {
        return 'Блок для вставки готовых контент-блоков в тело страницы или поста';
    }

    public function getIcon(): string {
        return 'bi bi-grid-3x3-gap';
    }

    public function getCategory(): string {
        return 'advanced';
    }

    public function getTemplateWithShortcodes(): string {
        return '{content_block_html}';
    }

    public function getContentForm($currentContent = []): string {
        $currentContent = $this->validateAndNormalizeContent($currentContent);
        
        $selectedBlockId = $currentContent['content_block_id'] ?? '';
        $selectedBlockName = $currentContent['content_block_name'] ?? '';
        
        $contentBlocks = $this->getAvailableContentBlocks();
        
        ob_start();
        ?>
        <div class="mb-4">
            <label class="form-label">Выберите контент-блок</label>
            
            <?php if (empty($contentBlocks)): ?>
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    Нет доступных контент-блоков. 
                    <a href="<?= ADMIN_URL ?>/html-blocks" target="_blank" class="alert-link">
                        Создайте HTML-блок сначала
                    </a>
                </div>
            <?php else: ?>
                <select name="content[content_block_id]" class="form-select" id="content-block-select" required>
                    <option value="">-- Выберите контент-блок --</option>
                    <?php foreach ($contentBlocks as $block): ?>
                        <option value="<?= $block['id'] ?>" 
                                data-name="<?= htmlspecialchars($block['name']) ?>"
                                data-type="<?= htmlspecialchars($block['block_type'] ?? 'DefaultBlock') ?>"
                                <?= $selectedBlockId == $block['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($block['name']) ?> 
                            (ID: <?= $block['id'] ?>, Slug: <?= $block['slug'] ?>, Type: <?= $block['block_type'] ?? 'DefaultBlock' ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
                <div class="form-text">
                    Выберите готовый контент-блок для вставки
                </div>
                
                <input type="hidden" name="content[content_block_name]" id="content-block-name" value="<?= htmlspecialchars($selectedBlockName) ?>">
                <input type="hidden" name="content[content_block_type]" id="content-block-type" value="<?= htmlspecialchars($currentContent['content_block_type'] ?? '') ?>">
                
                <div id="content-block-preview" class="mt-3 p-3 border rounded bg-light" style="<?= empty($selectedBlockId) ? 'display:none;' : '' ?>">
                    <?php if (!empty($selectedBlockId)): ?>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <strong>Выбранный блок:</strong>
                            <span class="badge bg-primary" id="preview-block-name"><?= htmlspecialchars($selectedBlockName) ?></span>
                        </div>
                        <div class="text-muted small">
                            ID: <span id="preview-block-id"><?= $selectedBlockId ?></span><br>
                            Type: <span id="preview-block-type"><?= htmlspecialchars($currentContent['content_block_type'] ?? 'DefaultBlock') ?></span>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const blockSelect = document.getElementById('content-block-select');
            const nameInput = document.getElementById('content-block-name');
            const typeInput = document.getElementById('content-block-type');
            const previewDiv = document.getElementById('content-block-preview');
            const previewName = document.getElementById('preview-block-name');
            const previewId = document.getElementById('preview-block-id');
            const previewType = document.getElementById('preview-block-type');
            
            if (blockSelect) {
                blockSelect.addEventListener('change', function() {
                    const selectedOption = this.options[this.selectedIndex];
                    const blockId = this.value;
                    const blockName = selectedOption ? selectedOption.getAttribute('data-name') : '';
                    const blockType = selectedOption ? selectedOption.getAttribute('data-type') : '';
                    
                    nameInput.value = blockName;
                    typeInput.value = blockType;
                    
                    if (blockId) {
                        previewName.textContent = blockName;
                        previewId.textContent = blockId;
                        previewType.textContent = blockType;
                        previewDiv.style.display = 'block';
                    } else {
                        previewDiv.style.display = 'none';
                    }
                });
            }
        });
        </script>
        <?php
        return ob_get_clean();
    }

    public function getSettingsForm($currentSettings = []): string {
        return '
        <div class="alert alert-info">
            <i class="bi bi-info-circle me-2"></i>
            Этот блок вставляет готовый контент-блок в тело страницы или поста.
            Настройки шаблона доступны в разделе "Настройки постблока" в админке.
        </div>';
    }

    public function getEditorHtml($settings = [], $content = []): string {
        $content = $this->validateAndNormalizeContent($content);
        
        $blockId = $content['content_block_id'] ?? '';
        $blockName = $content['content_block_name'] ?? '';
        
        if (empty($blockId)) {
            return '<div class="alert alert-warning p-2 m-0">[Контент-блок не выбран]</div>';
        }
        
        return '
        <div class="content-block-postblock-preview border rounded p-3 bg-light">
            <div class="d-flex align-items-center mb-2">
                <i class="bi bi-grid-3x3-gap text-primary me-2"></i>
                <strong>Контент-блок:</strong>
                <span class="badge bg-primary ms-2">' . htmlspecialchars($blockName) . '</span>
            </div>
            <div class="text-muted small">
                ID: ' . $blockId . '
            </div>
            <div class="mt-2 p-2 bg-white border rounded">
                <em class="text-muted">Содержимое контент-блока будет отображено на фронтенде</em>
            </div>
        </div>';
    }

    public function processFrontend($content, $settings = []): string {
        return parent::processFrontend($content, $settings);
    }

    public function getShortcodes(): array {
        return array_merge(parent::getShortcodes(), [
            '{content_block_html}' => 'HTML код выбранного контент-блока',
            '{content_block_id}' => 'ID выбранного контент-блока',
            '{content_block_name}' => 'Название выбранного контент-блока'
        ]);
    }

    public function getAdminJs(): array {
        return [
            'templates/default/admin/assets/js/blocks/content-block.js'
        ];
    }

    private function getAvailableContentBlocks(): array {
        try {
            $db = Database::getInstance();
            return $db->fetchAll("
                SELECT hb.id, hb.name, hb.slug, hbt.system_name as block_type
                FROM html_blocks hb 
                LEFT JOIN html_block_types hbt ON hb.type_id = hbt.id 
                ORDER BY hb.name ASC
            ");
        } catch (Exception $e) {
            return [];
        }
    }

    private function getContentBlockHtml($blockId): string {
        try {
            $db = Database::getInstance();
            $htmlBlock = $db->fetch("
                SELECT hb.*, hbt.system_name as block_type, hbt.template as block_type_template 
                FROM html_blocks hb 
                LEFT JOIN html_block_types hbt ON hb.type_id = hbt.id 
                WHERE hb.id = ?
            ", [$blockId]);
            
            if (!$htmlBlock) {
                return '';
            }
            
            $blockType = $htmlBlock['block_type'] ?? 'DefaultBlock';
            $template = $htmlBlock['template'] ?? 'default';
            $settings = [];

            if (!empty($htmlBlock['settings'])) {
                $decodedSettings = json_decode($htmlBlock['settings'], true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $settings = $decodedSettings;
                }
            }
            
            $settings['block_id'] = $htmlBlock['id'];
            $settings['block_name'] = $htmlBlock['name'];
            $settings['block_slug'] = $htmlBlock['slug'];
            
            $blockTypeManager = new HtmlBlockTypeManager($db);
            
            if (!$blockTypeManager->isBlockTypeActive($blockType)) {
                return '<!-- Тип блока отключен: ' . $blockType . ' -->';
            }
            
            $processedContent = $blockTypeManager->processBlockContent($blockType, $settings, $template);
            
            return $processedContent ?: '<!-- Пустой контент-блок -->';
            
        } catch (Exception $e) {
            return '<!-- Ошибка при получении контент-блока: ' . $e->getMessage() . ' -->';
        }
    }

    public function validateSettings($settings): array {
        $errors = [];
        return [empty($errors), $errors];
    }

    public function validateAndNormalizeContent($content): array {
        if (is_string($content)) {
            $decoded = json_decode($content, true);
            return is_array($decoded) ? $decoded : ['content_block_id' => '', 'content_block_name' => '', 'content_block_type' => ''];
        }
        
        if (!is_array($content)) {
            return ['content_block_id' => '', 'content_block_name' => '', 'content_block_type' => ''];
        }
        
        $content['content_block_id'] = $content['content_block_id'] ?? '';
        $content['content_block_name'] = $content['content_block_name'] ?? '';
        $content['content_block_type'] = $content['content_block_type'] ?? '';
        
        return $content;
    }

    public function prepareContent($content): array {
        $content = parent::prepareContent($content);
        $fields = ['content_block_id', 'content_block_name', 'content_block_type'];
        foreach ($fields as $field) {
            if (isset($content[$field])) {
                $content[$field] = trim($content[$field]);
            }
        }
        
        return $content;
    }

    public function prepareSettings($settings): array {
        return parent::prepareSettings($settings);
    }

    public function extractFromHtml(string $html): ?array {
        return null;
    }

    public function canExtractFromHtml(): bool {
        return false;
    }
    
    protected function renderWithTemplate($content, $settings, $template): string {
        $content = $this->validateAndNormalizeContent($content);
        $settings = $this->validateAndNormalizeSettings($settings);
        
        $blockId = $content['content_block_id'] ?? '';
        $blockName = $content['content_block_name'] ?? '';
        $blockType = $content['content_block_type'] ?? '';
        $presetId = $settings['preset_id'] ?? null;
        $presetName = $settings['preset_name'] ?? '';
        $blockHtml = $this->getContentBlockHtml($blockId);
        
        if (empty($blockHtml)) {
            $blockHtml = '<!-- Контент-блок не найден или пуст -->';
        }

        $result = $template;
        $replacements = [
            '{content_block_html}' => $blockHtml,
            '{content_block_id}' => $blockId ? htmlspecialchars($blockId) : '',
            '{content_block_name}' => $blockName ? htmlspecialchars($blockName) : '',
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

    public function getPreviewHtml($content = [], $settings = []): string {
        $content = $this->validateAndNormalizeContent($content);
        
        $blockId = $content['content_block_id'] ?? '';
        $blockName = $content['content_block_name'] ?? '';
        $blockType = $content['content_block_type'] ?? '';
        
        ob_start();
        ?>
        <div class="post-block-preview post-block-preview-ContentBlockPostBlock full-content-preview">
            <div class="preview-wrapper">
                <div class="preview-header">
                    <div class="preview-header-content">
                        <div class="preview-icon">
                            <i class="bi bi-grid-3x3-gap"></i>
                        </div>
                        <div class="preview-info">
                            <div class="preview-title">
                                <strong>Контент-блок</strong>
                                <?php if ($blockType): ?>
                                    <span class="badge bg-info badge-sm"><?= htmlspecialchars($blockType) ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="preview-stats">
                                <?php if ($blockId): ?>
                                    ID: <?= htmlspecialchars($blockId) ?>
                                    <?php if ($blockName): ?>
                                        · <?= htmlspecialchars(mb_substr($blockName, 0, 20)) ?>
                                        <?php if (mb_strlen($blockName) > 20): ?>...<?php endif; ?>
                                    <?php endif; ?>
                                <?php else: ?>
                                    Не выбран
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
                    <?php if ($blockId): ?>
                        <div class="content-block-preview-container">
                            <div class="content-block-info p-3 border rounded bg-light">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="bi bi-card-heading text-primary me-2"></i>
                                    <div class="flex-grow-1">
                                        <strong>Выбранный контент-блок:</strong>
                                    </div>
                                    <span class="badge bg-primary">ID: <?= htmlspecialchars($blockId) ?></span>
                                </div>
                                
                                <?php if ($blockName): ?>
                                    <div class="mb-2">
                                        <span class="fw-semibold">Название:</span>
                                        <?= htmlspecialchars($blockName) ?>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($blockType): ?>
                                    <div class="mb-2">
                                        <span class="fw-semibold">Тип блока:</span>
                                        <span class="badge bg-secondary"><?= htmlspecialchars($blockType) ?></span>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="alert alert-info mt-2 p-2 small mb-0">
                                    <i class="bi bi-info-circle me-1"></i>
                                    Полное содержимое контент-блока будет отображено на странице
                                </div>
                            </div>
                            
                            <div class="content-block-mockup mt-3 p-3 border rounded" style="background: linear-gradient(45deg, #f8f9fa 25%, #e9ecef 25%, #e9ecef 50%, #f8f9fa 50%, #f8f9fa 75%, #e9ecef 75%, #e9ecef 100%); background-size: 20px 20px;">
                                <div class="text-center text-muted">
                                    <i class="bi bi-grid-3x3 display-4 d-block mb-2 opacity-50"></i>
                                    <div class="fw-semibold">Контент-блок</div>
                                    <small>Содержимое будет загружено динамически</small>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="preview-empty-state">
                            <i class="bi bi-grid-3x3-gap"></i>
                            <div class="empty-text">Контент-блок не выбран</div>
                            <button type="button" class="btn btn-sm btn-outline-primary mt-2" 
                                    onclick="postBlocksManager.editBlock('{block_id}')">
                                <i class="bi bi-plus-circle"></i> Выбрать блок
                            </button>
                            <div class="mt-3 small text-muted">
                                <i class="bi bi-info-circle"></i>
                                Выберите готовый HTML-блок для вставки в этот пост
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