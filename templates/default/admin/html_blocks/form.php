<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">
            <i class="bi bi-code-square me-2"></i>
            <?= isset($block) ? 'Редактирование блока' : 'Создание блока' ?>
            <?php if($selectedType !== 'DefaultBlock'): ?>
                <span class="badge bg-primary ms-2">
                    <?= html($blockTypes[$selectedType]['name'] ?? $selectedType) ?>
                </span>
            <?php endif; ?>
        </h4>
        <a href="<?= ADMIN_URL ?>/html-blocks" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Назад к блокам
        </a>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <form method="POST" id="blockForm" enctype="multipart/form-data">
                <input type="hidden" name="block_type" value="<?= $selectedType ?>">
                
                <nav class="px-4 pt-4">
                    <div class="nav nav-tabs" id="nav-tab" role="tablist">
                        <button class="nav-link active" id="nav-basic-tab" data-bs-toggle="tab" data-bs-target="#nav-basic" type="button" role="tab">
                            <i class="bi bi-info-circle me-2"></i>Основное
                        </button>
                        
                        <?php if($selectedType !== 'DefaultBlock'): ?>
                        <button class="nav-link" id="nav-settings-tab" data-bs-toggle="tab" data-bs-target="#nav-settings" type="button" role="tab">
                            <i class="bi bi-gear me-2"></i>Настройки
                        </button>
                        <?php endif; ?>
                        
                        <button class="nav-link" id="nav-assets-tab" data-bs-toggle="tab" data-bs-target="#nav-assets" type="button" role="tab">
                            <i class="bi bi-palette me-2"></i>Стили и скрипты
                        </button>
                    </div>
                </nav>

                <div class="tab-content p-4" id="nav-tabContent">
                    
                    <div class="tab-pane fade show active" id="nav-basic" role="tabpanel">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-4">
                                    <label class="form-label fw-semibold">Название блока</label>
                                    <input type="text" 
                                           name="name" 
                                           class="form-control form-control-lg" 
                                           value="<?= html($block['name'] ?? '') ?>" 
                                           placeholder="Введите название блока"
                                           required>
                                    <div class="form-text">Отображаемое название блока в админке</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-4">
                                    <label class="form-label fw-semibold d-flex align-items-center">
                                        Системное имя
                                        <span class="text-danger ms-1">*</span>
                                    </label>
                                    <input type="text" 
                                           name="slug" 
                                           class="form-control" 
                                           value="<?= html($block['slug'] ?? '') ?>" 
                                           placeholder="например: header_menu"
                                           required>
                                    <div class="form-text">Уникальный идентификатор для использования в коде</div>
                                </div>
                            </div>

                            <?php if($selectedType !== 'DefaultBlock'): ?>
                                <div class="col-md-4">
                                    <div class="mb-4">
                                        <label class="form-label fw-semibold">Шаблон отображения</label>
                                        <?php
                                        $blockType = $blockTypes[$selectedType] ?? null;
                                        $availableTemplates = $blockType && $blockType['class'] ? 
                                            $blockType['class']->getAvailableTemplates() : 
                                            ['default' => 'Стандартный шаблон'];
                                        
                                        $selectedTemplate = $block['template'] ?? 'default';
                                        ?>
                                        <select name="template" class="form-select" id="block-template-select">
                                            <?php foreach($availableTemplates as $templateKey => $templateName): ?>
                                                <option value="<?= html($templateKey) ?>" 
                                                        <?= $selectedTemplate === $templateKey ? 'selected' : '' ?>>
                                                    <?= html($templateName) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div class="form-text">
                                            Выберите шаблон для отображения этого блока на сайте
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <?php if($selectedType !== 'DefaultBlock'): ?>
                        <div class="alert alert-info">
                            <div class="d-flex">
                                <i class="bi bi-info-circle-fill me-2 mt-1"></i>
                                <div>
                                    <strong>Тип блока:</strong> <?= html($blockTypes[$selectedType]['name'] ?? $selectedType) ?>
                                    <?php if(isset($blockTypes[$selectedType]['description'])): ?>
                                        <br><small><?= html($blockTypes[$selectedType]['description']) ?></small>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>

                    <?php if($selectedType !== 'DefaultBlock'): ?>
                    <div class="tab-pane fade" id="nav-settings" role="tabpanel">
                        <div class="mb-4">
                            <h6 class="fw-semibold mb-3">Настройки блока</h6>
                            <div id="block-settings-container">
                                <?php 
                                $blockType = $blockTypes[$selectedType] ?? null;
                                if ($blockType && $blockType['class']) {
                                    echo $blockType['class']->getSettingsForm($settings ?? []);
                                }
                                ?>
                            </div>
                        </div>
                        
                    </div>
                    <?php endif; ?>

                    <div class="tab-pane fade" id="nav-assets" role="tabpanel">
                        
                        <div class="mb-4">
                            <label class="form-label fw-semibold d-flex align-items-center">
                                <i class="bi bi-filetype-css text-primary me-2"></i>
                                Встроенные стили (CSS)
                            </label>
                            <div class="mb-2">
                                <small class="text-muted">CSS код, который будет добавлен на страницу</small>
                            </div>
                            <div id="inline-css-container" class="border rounded">
                                <div id="inline-css-editor" style="height: 200px;"></div>
                            </div>
                            <textarea name="inline_css" id="inline_css" style="display: none;"><?= html($inlineCss ?? '') ?></textarea>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold d-flex align-items-center">
                                <i class="bi bi-filetype-js text-warning me-2"></i>
                                Встроенный JavaScript
                            </label>
                            <div class="mb-2">
                                <small class="text-muted">JavaScript код, который будет выполнен на странице</small>
                            </div>
                            <div id="inline-js-container" class="border rounded">
                                <div id="inline-js-editor" style="height: 200px;"></div>
                            </div>
                            <textarea name="inline_js" id="inline_js" style="display: none;"><?= html($inlineJs ?? '') ?></textarea>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold d-flex align-items-center">
                                <i class="bi bi-file-earmark-css text-success me-2"></i>
                                Внешние CSS файлы
                                <?php if(!empty($systemCss)): ?>
                                    <span class="badge bg-info ms-2" data-bs-toggle="tooltip" title="Системные файлы (нельзя удалить)">Системные</span>
                                <?php endif; ?>
                            </label>
                            
                            <?php if(!empty($systemCss)): ?>
                                <div class="mb-3">
                                    <small class="text-muted d-block mb-2">Системные файлы (автоматически подключаются):</small>
                                    <?php foreach($systemCss as $systemCssFile): ?>
                                        <div class="input-group mb-2">
                                            <input type="text" 
                                                   class="form-control system-asset" 
                                                   value="<?= html($systemCssFile) ?>" 
                                                   readonly
                                                   placeholder="Системный CSS файл">
                                            <span class="input-group-text text-muted bg-light">
                                                <i class="bi bi-lock-fill" data-bs-toggle="tooltip" title="Системный файл"></i>
                                            </span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                            
                            <small class="text-muted d-block mb-2">Дополнительные CSS файлы:</small>
                            <div id="css-files-container">
                                <?php if(!empty($cssFiles)): ?>
                                    <?php foreach($cssFiles as $index => $cssFile): ?>
                                        <?php if(!in_array($cssFile, $systemCss)): ?>
                                            <div class="input-group mb-2 css-file-row">
                                                <input type="text" 
                                                       name="css_files[]" 
                                                       class="form-control" 
                                                       value="<?= html($cssFile) ?>" 
                                                       placeholder="templates/default/front/assets/css/my-block.css">
                                                <button type="button" class="btn btn-outline-danger remove-asset" data-type="css">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                                
                                <div class="input-group mb-2 css-file-row">
                                    <input type="text" 
                                           name="css_files[]" 
                                           class="form-control" 
                                           value="" 
                                           placeholder="templates/default/front/assets/css/my-block.css">
                                    <button type="button" class="btn btn-outline-danger remove-asset" data-type="css">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <button type="button" class="btn btn-outline-primary btn-sm mt-2" id="add-css-file">
                                <i class="bi bi-plus"></i> Добавить CSS файл
                            </button>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold d-flex align-items-center">
                                <i class="bi bi-file-earmark-js text-info me-2"></i>
                                Внешние JavaScript файлы
                                <?php if(!empty($systemJs)): ?>
                                    <span class="badge bg-info ms-2" data-bs-toggle="tooltip" title="Системные файлы (нельзя удалить)">Системные</span>
                                <?php endif; ?>
                            </label>
                            
                            <?php if(!empty($systemJs)): ?>
                                <div class="mb-3">
                                    <small class="text-muted d-block mb-2">Системные файлы (автоматически подключаются):</small>
                                    <?php foreach($systemJs as $systemJsFile): ?>
                                        <div class="input-group mb-2">
                                            <input type="text" 
                                                   class="form-control system-asset" 
                                                   value="<?= html($systemJsFile) ?>" 
                                                   readonly
                                                   placeholder="Системный JS файл">
                                            <span class="input-group-text text-muted bg-light">
                                                <i class="bi bi-lock-fill" data-bs-toggle="tooltip" title="Системный файл"></i>
                                            </span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                            
                            <small class="text-muted d-block mb-2">Дополнительные JavaScript файлы:</small>
                            <div id="js-files-container">
                                <?php if(!empty($jsFiles)): ?>
                                    <?php foreach($jsFiles as $index => $jsFile): ?>
                                        <?php if(!in_array($jsFile, $systemJs)): ?>
                                            <div class="input-group mb-2 js-file-row">
                                                <input type="text" 
                                                       name="js_files[]" 
                                                       class="form-control" 
                                                       value="<?= html($jsFile) ?>" 
                                                       placeholder="templates/default/front/assets/js/my-block.js">
                                                <button type="button" class="btn btn-outline-danger remove-asset" data-type="js">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </div>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                                
                                <div class="input-group mb-2 js-file-row">
                                    <input type="text" 
                                           name="js_files[]" 
                                           class="form-control" 
                                           value="" 
                                           placeholder="templates/default/front/assets/js/my-block.js">
                                    <button type="button" class="btn btn-outline-danger remove-asset" data-type="js">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <button type="button" class="btn btn-outline-primary btn-sm mt-2" id="add-js-file">
                                <i class="bi bi-plus"></i> Добавить JS файл
                            </button>
                        </div>
                    </div>
                </div>

                <div class="border-top px-4 py-3 bg-light">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted small">
                            <i class="bi bi-clock-history me-1"></i>
                            <?php if(isset($block['updated_at'])): ?>
                                Последнее изменение: <?= date('d.m.Y H:i', strtotime($block['updated_at'])) ?>
                            <?php else: ?>
                                Создание нового блока
                            <?php endif; ?>
                        </div>
                        <div class="d-flex gap-2">
                            <a href="<?= ADMIN_URL ?>/html-blocks" class="btn btn-outline-secondary">
                                <i class="bi bi-x-lg me-1"></i> Отмена
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-lg me-1"></i> Сохранить блок
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<?php

    add_admin_js('templates/default/admin/assets/js/controllers/ace.js');
    add_admin_js('templates/default/admin/assets/js/controllers/mode-html.js');
    add_admin_js('templates/default/admin/assets/js/controllers/mode-css.js');
    add_admin_js('templates/default/admin/assets/js/controllers/mode-javascript.js');
    add_admin_js('templates/default/admin/assets/js/controllers/theme-monokai.js');
    
    add_admin_js('templates/default/admin/assets/js/controllers/conditional-fields.js');
    add_admin_js('templates/default/admin/assets/js/controllers/icon-field.js');
?>

<?php ob_start(); ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const inlineCssEditor = ace.edit("inline-css-editor", {
            theme: "ace/theme/monokai",
            mode: "ace/mode/css",
            showPrintMargin: false,
            fontSize: "14px",
            tabSize: 4,
            useSoftTabs: true,
            wrap: true,
            minLines: 8,
            maxLines: 20
        });

        const inlineJsEditor = ace.edit("inline-js-editor", {
            theme: "ace/theme/monokai",
            mode: "ace/mode/javascript",
            showPrintMargin: false,
            fontSize: "14px",
            tabSize: 4,
            useSoftTabs: true,
            wrap: true,
            minLines: 8,
            maxLines: 20
        });

        const initialInlineCss = document.getElementById('inline_css').value;
        inlineCssEditor.setValue(initialInlineCss, -1);
        
        const initialInlineJs = document.getElementById('inline_js').value;
        inlineJsEditor.setValue(initialInlineJs, -1);

        [inlineCssEditor, inlineJsEditor].forEach(ed => {
            ed.session.getUndoManager().reset();
            ed.setOptions({
                enableBasicAutocompletion: true,
                enableLiveAutocompletion: false,
                enableSnippets: false,
                behavioursEnabled: true,
                wrapBehavioursEnabled: true
            });
            ed.session.setUseWrapMode(true);
            ed.session.setTabSize(4);
            ed.session.setUseSoftTabs(true);
        });

        document.getElementById('add-css-file').addEventListener('click', function() {
            addAssetRow('css');
        });

        document.getElementById('add-js-file').addEventListener('click', function() {
            addAssetRow('js');
        });

        function addAssetRow(type) {
            const container = document.getElementById(`${type}-files-container`);
            const newRow = document.createElement('div');
            newRow.className = `input-group mb-2 ${type}-file-row`;
            newRow.innerHTML = `
                <input type="text" name="${type}_files[]" class="form-control" value="" placeholder="templates/default/front/assets/${type}/my-block.${type}">
                <button type="button" class="btn btn-outline-danger remove-asset" data-type="${type}">
                    <i class="bi bi-trash"></i>
                </button>
            `;
            container.appendChild(newRow);
            attachRemoveHandlers();
        }

        function attachRemoveHandlers() {
            document.querySelectorAll('.remove-asset').forEach(button => {
                button.addEventListener('click', function() {
                    const type = this.getAttribute('data-type');
                    const row = this.closest(`.${type}-file-row`);
                    const container = document.getElementById(`${type}-files-container`);
                    
                    if (container.querySelectorAll(`.${type}-file-row`).length > 1) {
                        row.remove();
                    } else {
                        const input = row.querySelector('input');
                        input.value = '';
                    }
                });
            });
        }

        attachRemoveHandlers();

        const form = document.getElementById("blockForm");
        const inlineCssField = document.getElementById("inline_css");
        const inlineJsField = document.getElementById("inline_js");

        form.addEventListener("submit", function(e) {
            inlineCssField.value = inlineCssEditor.getValue();
            inlineJsField.value = inlineJsEditor.getValue();
        });
        document.querySelector('input[name="name"]').focus();
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    });
</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const templateSelect = document.getElementById('block-template-select');
    const templatePreview = document.getElementById('template-preview');
    const previewBtn = document.getElementById('preview-template-btn');
    
    if (templateSelect) {
        templateSelect.addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            if (templatePreview) {
                templatePreview.textContent = selectedOption.text;
            }
        });
    }
    
    if (previewBtn) {
        previewBtn.addEventListener('click', function() {
            alert('Недоступно');
        });
    }
    
    const blockTypeSelect = document.getElementById('block-type-select');
    if (blockTypeSelect) {
        blockTypeSelect.addEventListener('change', function() {
            const blockType = this.value;
            if (blockType !== 'DefaultBlock') {
                fetchAvailableTemplates(blockType);
            }
        });
    }
    
    function fetchAvailableTemplates(blockType) {
        fetch(`/admin/html-blocks/get-block-templates?block_type=${encodeURIComponent(blockType)}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateTemplateSelect(data.templates);
                }
            })
            .catch(error => console.error('Error:', error));
    }
    
    function updateTemplateSelect(templates) {
        const select = document.getElementById('block-template-select');
        if (select) {
            select.innerHTML = '';
            Object.entries(templates).forEach(([key, value]) => {
                const option = document.createElement('option');
                option.value = key;
                option.textContent = value;
                select.appendChild(option);
            });
        }
    }
});
</script>
<?php admin_bottom_js(ob_get_clean()); ?>