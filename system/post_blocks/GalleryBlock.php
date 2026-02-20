<?php
class GalleryBlock extends BasePostBlock {
    
    public function getName(): string {
        return 'Галерея';
    }

    public function getSystemName(): string {
        return 'GalleryBlock';
    }

    public function getDescription(): string {
        return 'Блок для создания галереи изображений с возможностью добавления нескольких фото';
    }

    public function getIcon(): string {
        return 'bi bi-images';
    }

    public function getCategory(): string {
        return 'media';
    }

    public function getTemplateWithShortcodes(): string {
        return '
        <div class="post-block-gallery {layout} {custom_class}">
            <div class="gallery-container">
                {gallery_items}
                <div class="gallery-item">
                    <img src="{image_url}" alt="{alt_text}" class="gallery-image">
                    {caption}
                </div>
                {/gallery_items}
            </div>
        </div>';
    }

    public function getDefaultContent(): array {
        return [
            'images' => [
                [
                    'image_url' => '',
                    'alt_text' => '',
                    'caption' => ''
                ]
            ]
        ];
    }

    public function getDefaultSettings(): array {
        return [
            'layout' => 'grid',
            'columns' => 3,
            'image_size' => 'medium',
            'custom_class' => '',
            'show_captions' => true,
            'lightbox' => true
        ];
    }

    public function getContentForm($currentContent = []): string {
        $currentContent = $this->validateAndNormalizeContent($currentContent);
        $images = $currentContent['images'] ?? [['image_url' => '', 'alt_text' => '', 'caption' => '']];
        
        ob_start();
        ?>
        <div class="mb-4">
            <label class="form-label">Изображения галереи</label>
            <div id="gallery-items-container">
                <?php foreach($images as $index => $image): ?>
                <div class="gallery-item card mb-3">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-1 text-center">
                                <span class="gallery-item-handle text-muted">
                                    <i class="bi bi-grip-vertical"></i>
                                </span>
                            </div>
                            <div class="col-8">
                                <div class="mb-3">
                                    <label class="form-label small">Загрузить изображение *</label>
                                    <input type="file" 
                                           name="gallery_image_<?= $index ?>" 
                                           class="form-control form-control-sm gallery-image-input" 
                                           accept="image/*"
                                           <?= empty($image['image_url']) ? 'required' : '' ?>>
                                    <div class="form-text small">
                                        Форматы: JPG, PNG, GIF, WebP. Макс. размер: 5MB
                                    </div>
                                </div>
                                <input type="hidden" 
                                       name="content[images][<?= $index ?>][image_url]" 
                                       class="gallery-image-url" 
                                       value="<?= htmlspecialchars($image['image_url']) ?>">
                                <div class="mb-3">
                                    <label class="form-label small">Alt текст *</label>
                                    <input type="text" 
                                           name="content[images][<?= $index ?>][alt_text]" 
                                           class="form-control form-control-sm" 
                                           value="<?= htmlspecialchars($image['alt_text']) ?>" 
                                           placeholder="Описание изображения"
                                           required>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label small">Подпись</label>
                                    <input type="text" 
                                           name="content[images][<?= $index ?>][caption]" 
                                           class="form-control form-control-sm" 
                                           value="<?= htmlspecialchars($image['caption']) ?>" 
                                           placeholder="Необязательная подпись">
                                </div>
                                <?php if (!empty($image['image_url'])): ?>
                                <div class="current-image-preview mt-2 p-2 border rounded bg-light">
                                    <div class="d-flex align-items-center">
                                        <img src="<?= htmlspecialchars($image['image_url']) ?>" 
                                             alt="Текущее изображение" 
                                             class="img-thumbnail me-2"
                                             style="max-height: 60px;">
                                        <div class="flex-grow-1">
                                            <small class="text-muted d-block"><?= htmlspecialchars($image['image_url']) ?></small>
                                            <div class="form-check mt-1">
                                                <input class="form-check-input" type="checkbox" 
                                                       name="remove_gallery_image_<?= $index ?>" 
                                                       value="1" 
                                                       id="removeImage<?= $index ?>">
                                                <label class="form-check-label small" for="removeImage<?= $index ?>">
                                                    Удалить изображение
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php endif; ?>
                                <div class="new-image-preview mt-2" style="display: none;">
                                    <div class="border rounded p-2 bg-light">
                                        <img src="" alt="Предпросмотр" class="img-thumbnail preview-image" style="max-height: 60px;">
                                    </div>
                                </div>
                            </div>
                            <div class="col-2 text-end">
                                <button type="button" class="btn btn-danger btn-sm remove-gallery-item" <?= count($images) === 1 ? 'disabled' : '' ?>>
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <button type="button" class="btn btn-secondary mt-2" id="add-gallery-item">
                <i class="bi bi-plus"></i> Добавить изображение
            </button>
        </div>

        <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.gallery-image-input').forEach(function(input) {
                input.addEventListener('change', function(e) {
                    const file = e.target.files[0];
                    if (file) {
                        const previewContainer = this.closest('.gallery-item').querySelector('.new-image-preview');
                        const previewImg = previewContainer.querySelector('.preview-image');
                        const reader = new FileReader();
                        
                        reader.onload = function(e) {
                            previewImg.src = e.target.result;
                            previewContainer.style.display = 'block';
                        }
                        
                        reader.readAsDataURL(file);
                    }
                });
            });
        });
        </script>
        <?php
        return ob_get_clean();
    }

    public function getSettingsForm($currentSettings = []): string {
        $currentSettings = $this->validateAndNormalizeSettings($currentSettings);
        $layout = $currentSettings['layout'] ?? 'grid';
        $columns = $currentSettings['columns'] ?? 3;
        $imageSize = $currentSettings['image_size'] ?? 'medium';
        $customClass = $currentSettings['custom_class'] ?? '';
        $showCaptions = $currentSettings['show_captions'] ?? true;
        $lightbox = $currentSettings['lightbox'] ?? true;

        ob_start();
        ?>
        <div class="row">
            <div class="col-md-6">
                <div class="mb-4">
                    <label class="form-label">Макет галереи</label>
                    <select name="settings[layout]" class="form-select">
                        <option value="grid" <?= $layout === 'grid' ? 'selected' : '' ?>>Сетка</option>
                        <option value="masonry" <?= $layout === 'masonry' ? 'selected' : '' ?>>Масонри</option>
                        <option value="carousel" <?= $layout === 'carousel' ? 'selected' : '' ?>>Карусель</option>
                    </select>
                </div>
            </div>
            <div class="col-md-6">
                <div class="mb-4">
                    <label class="form-label">Количество колонок</label>
                    <select name="settings[columns]" class="form-select">
                        <option value="2" <?= $columns == 2 ? 'selected' : '' ?>>2 колонки</option>
                        <option value="3" <?= $columns == 3 ? 'selected' : '' ?>>3 колонки</option>
                        <option value="4" <?= $columns == 4 ? 'selected' : '' ?>>4 колонки</option>
                        <option value="5" <?= $columns == 5 ? 'selected' : '' ?>>5 колонок</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="mb-4">
                    <label class="form-label">Размер изображений</label>
                    <select name="settings[image_size]" class="form-select">
                        <option value="small" <?= $imageSize === 'small' ? 'selected' : '' ?>>Маленький</option>
                        <option value="medium" <?= $imageSize === 'medium' ? 'selected' : '' ?>>Средний</option>
                        <option value="large" <?= $imageSize === 'large' ? 'selected' : '' ?>>Большой</option>
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
                           placeholder="my-gallery">
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="form-check form-switch mb-4">
                    <input class="form-check-input" 
                           type="checkbox" 
                           name="settings[show_captions]" 
                           id="show_captions"
                           value="1" 
                           <?= $showCaptions ? 'checked' : '' ?>>
                    <label class="form-check-label" for="show_captions">
                        Показывать подписи
                    </label>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-check form-switch mb-4">
                    <input class="form-check-input" 
                           type="checkbox" 
                           name="settings[lightbox]" 
                           id="lightbox"
                           value="1" 
                           <?= $lightbox ? 'checked' : '' ?>>
                    <label class="form-check-label" for="lightbox">
                        Lightbox (увеличение по клику)
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
        
        $images = $content['images'] ?? [];
        $imageCount = count($images);

        if ($imageCount === 0) {
            return '
            <div class="post-block-gallery-preview text-center p-4 border rounded bg-light">
                <i class="bi bi-images display-4 text-muted d-block mb-2"></i>
                <span class="text-muted">Галерея пуста</span>
            </div>';
        }

        $previewHtml = '
        <div class="post-block-gallery-preview">
            <div class="d-flex align-items-center mb-2">
                <i class="bi bi-images text-primary me-2"></i>
                <strong>Галерея</strong>
                <span class="badge bg-secondary ms-2">' . $imageCount . ' изображений</span>
            </div>
            <div class="gallery-preview-grid">';
        $previewImages = array_slice($images, 0, 4);
        foreach ($previewImages as $image) {
            if (!empty($image['image_url'])) {
                $previewHtml .= '
                <div class="gallery-preview-item">
                    <img src="' . htmlspecialchars($image['image_url']) . '" 
                         alt="' . htmlspecialchars($image['alt_text']) . '" 
                         class="img-thumbnail">
                </div>';
            }
        }

        $previewHtml .= '
            </div>
        </div>';

        return $previewHtml;
    }

    public function processFrontend($content, $settings = []): string {
        return parent::processFrontend($content, $settings);
    }

    public function prepareContent($content): array {
        if (!is_array($content)) {
            $content = [];
        }
        
        $images = [];
        
        if (isset($_POST['content']['images']) && is_array($_POST['content']['images'])) {
            foreach ($_POST['content']['images'] as $index => $imageData) {
                $image = [
                    'image_url' => trim($imageData['image_url'] ?? ''),
                    'alt_text' => trim($imageData['alt_text'] ?? ''),
                    'caption' => trim($imageData['caption'] ?? '')
                ];
                
                $fileInputName = 'gallery_image_' . $index;
                if (isset($_FILES[$fileInputName]) && $_FILES[$fileInputName]['error'] === UPLOAD_ERR_OK) {
                    $uploadResult = $this->handleImageUpload($_FILES[$fileInputName]);
                    if ($uploadResult['success']) {
                        $image['image_url'] = $uploadResult['file_path'];
                    }
                }
                
                $removeInputName = 'remove_gallery_image_' . $index;
                if (isset($_POST[$removeInputName]) && $_POST[$removeInputName] == '1') {
                    if (!empty($image['image_url']) && file_exists($image['image_url'])) {
                        unlink($image['image_url']);
                    }
                    $image['image_url'] = '';
                }
                
                if (!empty($image['image_url'])) {
                    $images[] = $image;
                }
            }
        }
        
        if (empty($images)) {
            $images[] = [
                'image_url' => '',
                'alt_text' => '',
                'caption' => ''
            ];
        }
        
        $content['images'] = $images;
        return $content;
    }

    public function prepareSettings($settings): array {
        if (!is_array($settings)) {
            $settings = [];
        }
        
        if (isset($_POST['settings']) && is_array($_POST['settings'])) {
            $settings = array_merge($settings, $_POST['settings']);
        }
        
        if (isset($settings['show_captions'])) {
            $settings['show_captions'] = (bool)$settings['show_captions'];
        }
        if (isset($settings['lightbox'])) {
            $settings['lightbox'] = (bool)$settings['lightbox'];
        }

        if (isset($settings['columns'])) {
            $settings['columns'] = (int)$settings['columns'];
        }

        if (isset($settings['custom_class'])) {
            $settings['custom_class'] = trim($settings['custom_class']);
        }

        return $settings;
    }

    protected function renderWithTemplate($content, $settings, $template): string {
        $content = $this->validateAndNormalizeContent($content);
        $settings = $this->validateAndNormalizeSettings($settings);
        
        $images = $content['images'] ?? [];
        $layout = $settings['layout'] ?? 'grid';
        $columns = $settings['columns'] ?? 3;
        $imageSize = $settings['image_size'] ?? 'medium';
        $customClass = $settings['custom_class'] ?? '';
        $showCaptions = $settings['show_captions'] ?? true;
        $lightbox = $settings['lightbox'] ?? true;
        $presetId = $settings['preset_id'] ?? null;
        $presetName = $settings['preset_name'] ?? '';

        if (empty($images)) {
            return '<!-- GalleryBlock: нет изображений -->';
        }

        $template = $settings['template'] ?? $this->getTemplateWithShortcodes();
        
        if (strpos($template, '{gallery_items}') !== false && strpos($template, '{/gallery_items}') !== false) {
            preg_match('/\{gallery_items\}(.*?)\{\/gallery_items\}/s', $template, $matches);
            $itemTemplate = $matches[1] ?? '';
            $itemsHtml = '';
            foreach ($images as $image) {
                if (empty($image['image_url'])) {
                    continue;
                }

                $imageUrl = $image['image_url'];
                $altText = $image['alt_text'] ?? '';
                $caption = $image['caption'] ?? '';

                if ($imageUrl[0] !== '/') {
                    $imageUrl = '/' . $imageUrl;
                }

                $itemHtml = $itemTemplate;
                
                $itemHtml = str_replace('{image_url}', htmlspecialchars($imageUrl), $itemHtml);
                $itemHtml = str_replace('{alt_text}', htmlspecialchars($altText), $itemHtml);
                
                if ($showCaptions && !empty($caption)) {
                    $itemHtml = str_replace('{caption}', '<div class="gallery-caption">' . htmlspecialchars($caption) . '</div>', $itemHtml);
                } else {
                    $itemHtml = str_replace('{caption}', '', $itemHtml);
                }
                
                if ($lightbox) {
                    $itemHtml = preg_replace('/<img([^>]*)>/', '<img$1 data-lightbox="gallery">', $itemHtml);
                }
                
                $itemsHtml .= $itemHtml;
            }

            $result = preg_replace('/\{gallery_items\}.*?\{\/gallery_items\}/s', $itemsHtml, $template);
        } else {
            $itemsHtml = '';
            foreach ($images as $image) {
                if (empty($image['image_url'])) {
                    continue;
                }

                $imageUrl = $image['image_url'];
                $altText = $image['alt_text'] ?? '';
                $caption = $image['caption'] ?? '';

                if ($imageUrl[0] !== '/') {
                    $imageUrl = '/' . $imageUrl;
                }

                $captionHtml = '';
                if ($showCaptions && !empty($caption)) {
                    $captionHtml = '<div class="gallery-caption">' . htmlspecialchars($caption) . '</div>';
                }

                $lightboxAttr = $lightbox ? ' data-lightbox="gallery"' : '';

                $itemsHtml .= '
                <div class="gallery-item">
                    <img src="' . htmlspecialchars($imageUrl) . '" 
                        alt="' . htmlspecialchars($altText) . '" 
                        class="gallery-image"'
                        . $lightboxAttr . '>
                    ' . $captionHtml . '
                </div>';
            }

            $result = str_replace('{gallery_items}', $itemsHtml, $template);
            $result = str_replace('{/gallery_items}', '', $result);
        }
        
        $result = str_replace('{layout}', $layout, $result);
        $result = str_replace('{custom_class}', $customClass, $result);
        $result = str_replace('post-block-gallery', 'post-block-gallery columns-' . $columns . ' size-' . $imageSize, $result);
        $result = str_replace('{preset_id}', $presetId ? htmlspecialchars($presetId) : '', $result);
        $result = str_replace('{preset_name}', $presetName ? htmlspecialchars($presetName) : '', $result);
        $result = str_replace('{block_type}', $this->getSystemName(), $result);
        $result = str_replace('{block_name}', $this->getName(), $result);

        return $result;
    }

    private function handleImageUpload($file) {
        try {
            if ($file['error'] !== UPLOAD_ERR_OK) {
                return ['success' => false, 'error' => 'Ошибка загрузки файла'];
            }

            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $fileType = mime_content_type($file['tmp_name']);
            
            if (!in_array($fileType, $allowedTypes)) {
                return ['success' => false, 'error' => 'Недопустимый тип файла'];
            }

            if ($file['size'] > 5 * 1024 * 1024) {
                return ['success' => false, 'error' => 'Файл слишком большой'];
            }

            $uploadDir = 'uploads/images/gallery/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $fileName = 'gallery_' . uniqid() . '_' . time() . '.' . $fileExtension;
            $filePath = $uploadDir . $fileName;

            if (!move_uploaded_file($file['tmp_name'], $filePath)) {
                return ['success' => false, 'error' => 'Не удалось сохранить файл'];
            }

            return [
                'success' => true, 
                'file_path' => '/' . $filePath,
                'file_name' => $fileName
            ];

        } catch (Exception $e) {
            return ['success' => false, 'error' => 'Исключение при загрузке: ' . $e->getMessage()];
        }
    }

    public function getShortcodes(): array {
        return array_merge(parent::getShortcodes(), [
            '{layout}' => 'Макет галереи (grid/masonry/carousel)',
            '{custom_class}' => 'Дополнительный CSS класс',
            '{gallery_items}...{/gallery_items}' => 'Цикл по изображениям галереи',
            '{image_url}' => 'URL изображения',
            '{alt_text}' => 'Alt текст изображения',
            '{caption}' => 'Подпись изображения'
        ]);
    }

    public function getAdminJs(): array {
        return [
            'templates/default/admin/assets/js/blocks/gallery.js'
        ];
    }

    public function validateSettings($settings): array {
        $errors = [];

        if (!empty($settings['custom_class']) && !preg_match('/^[a-zA-Z0-9-_ ]+$/', $settings['custom_class'])) {
            $errors[] = 'CSS класс может содержать только буквы, цифры, дефисы и подчеркивания';
        }

        $allowedLayouts = ['grid', 'masonry', 'carousel'];
        if (!empty($settings['layout']) && !in_array($settings['layout'], $allowedLayouts)) {
            $errors[] = 'Недопустимый макет галереи';
        }

        $allowedColumns = [2, 3, 4, 5];
        if (isset($settings['columns']) && !in_array($settings['columns'], $allowedColumns)) {
            $errors[] = 'Недопустимое количество колонок';
        }

        $allowedSizes = ['small', 'medium', 'large'];
        if (!empty($settings['image_size']) && !in_array($settings['image_size'], $allowedSizes)) {
            $errors[] = 'Недопустимый размер изображений';
        }

        return [empty($errors), $errors];
    }

    public function extractFromHtml(string $html): ?array {
        if (preg_match_all('/<img[^>]+src="([^"]+)"[^>]+alt="([^"]*)"[^>]*>/i', $html, $matches, PREG_SET_ORDER)) {
            $images = [];
            foreach ($matches as $match) {
                $images[] = [
                    'image_url' => $match[1],
                    'alt_text' => $match[2] ?? '',
                    'caption' => ''
                ];
            }
            
            if (!empty($images)) {
                return ['images' => $images];
            }
        }
        
        return null;
    }

    public function validateAndNormalizeContent($content): array {
        if (is_string($content)) {
            $decoded = json_decode($content, true);
            return is_array($decoded) ? $decoded : ['images' => []];
        }
        
        if (!is_array($content)) {
            return ['images' => []];
        }
        
        if (!isset($content['images']) || !is_array($content['images'])) {
            $content['images'] = [];
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

    public function getPreviewHtml($content = [], $settings = []): string {
        $content = $this->validateAndNormalizeContent($content);
        $settings = $this->validateAndNormalizeSettings($settings);
        
        $images = $content['images'] ?? [];
        $layout = $settings['layout'] ?? 'grid';
        $columns = $settings['columns'] ?? 3;
        $imageSize = $settings['image_size'] ?? 'medium';
        $customClass = $settings['custom_class'] ?? '';
        $showCaptions = $settings['show_captions'] ?? true;
        $lightbox = $settings['lightbox'] ?? true;
        $validImages = array_filter($images, function($image) {
            return !empty($image['image_url']);
        });
        
        $imageCount = count($validImages);
        
        ob_start();
        ?>
        <div class="post-block-preview post-block-preview-GalleryBlock full-content-preview">
            <div class="preview-wrapper">
                <div class="preview-header">
                    <div class="preview-header-content">
                        <div class="preview-icon">
                            <i class="bi bi-images"></i>
                        </div>
                        <div class="preview-info">
                            <div class="preview-title">
                                <strong>Галерея</strong>
                                <?php if ($layout !== 'grid'): ?>
                                    <span class="badge bg-info badge-sm"><?= htmlspecialchars($layout) ?></span>
                                <?php endif; ?>
                                <?php if ($columns !== 3): ?>
                                    <span class="badge bg-secondary badge-sm"><?= $columns ?> кол.</span>
                                <?php endif; ?>
                            </div>
                            <div class="preview-stats">
                                <?= $imageCount ?> изображени<?= $imageCount == 1 ? 'е' : ($imageCount > 1 && $imageCount < 5 ? 'я' : 'й') ?>
                                <?php if ($lightbox): ?>
                                    · lightbox
                                <?php endif; ?>
                                <?php if ($showCaptions): ?>
                                    · подписи
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
                    <?php if ($imageCount > 0): ?>
                        <div class="gallery-preview-container">
                            <div class="gallery-preview-grid" style="display: grid; grid-template-columns: repeat(<?= min($imageCount, 4) ?>, 1fr); gap: 8px;">
                                <?php 
                                $previewImages = array_slice($validImages, 0, 4);
                                foreach ($previewImages as $index => $image): 
                                    $imageUrl = $image['image_url'];
                                    $altText = $image['alt_text'] ?? '';
                                    $caption = $image['caption'] ?? '';
                                ?>
                                    <div class="gallery-preview-item position-relative">
                                        <img src="<?= htmlspecialchars($imageUrl) ?>" 
                                            alt="<?= htmlspecialchars($altText) ?>"
                                            class="img-fluid rounded"
                                            style="width: 100%; height: 80px; object-fit: cover;"
                                            onerror="this.onerror=null; this.style.backgroundColor='#f8f9fa'; this.innerHTML='<i class=\'bi bi-image text-muted\' style=\'font-size: 24px; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);\'></i>'">
                                        
                                        <?php if ($index === 3 && $imageCount > 4): ?>
                                            <div class="position-absolute top-0 start-0 w-100 h-100 bg-dark bg-opacity-50 d-flex align-items-center justify-content-center rounded">
                                                <span class="text-white fw-bold">+<?= $imageCount - 4 ?></span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <div class="gallery-preview-info mt-3">
                                <div class="row small text-muted">
                                    <div class="col-6">
                                        <div><i class="bi bi-grid-3x3 me-1"></i>Макет: <strong><?= htmlspecialchars($layout) ?></strong></div>
                                        <?php if ($showCaptions): ?>
                                            <div><i class="bi bi-card-text me-1"></i>Подписи включены</div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-6">
                                        <div><i class="bi bi-layout-three-columns me-1"></i>Колонок: <strong><?= $columns ?></strong></div>
                                        <?php if ($lightbox): ?>
                                            <div><i class="bi bi-zoom-in me-1"></i>Lightbox включен</div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="preview-empty-state">
                            <i class="bi bi-images"></i>
                            <div class="empty-text">Изображения не добавлены</div>
                            <button type="button" class="btn btn-sm btn-outline-primary mt-2" 
                                    onclick="postBlocksManager.editBlock('{block_id}')">
                                <i class="bi bi-plus-circle"></i> Добавить изображения
                            </button>
                            <div class="mt-3 small text-muted">
                                <i class="bi bi-info-circle"></i>
                                Добавьте несколько изображений для создания галереи
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