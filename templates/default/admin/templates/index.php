<div class="container-fluid p-0">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0">
            <?php echo bloggy_icon('bs', 'palette', '24', '#000', 'me-2'); ?>
            Управление шаблонами
        </h4>
        <div class="d-flex gap-2">
            <a href="<?php echo ADMIN_URL; ?>/settings/cleanup-backups" class="btn btn-sm btn-outline-danger">
                <?php echo bloggy_icon('bs', 'trash', '14', '#000', 'me-1'); ?> Очистить все резервные копии
            </a>
            <a href="<?php echo ADMIN_URL; ?>/settings?tab=site" class="btn btn-outline-secondary btn-sm">
                <?php echo bloggy_icon('bs', 'gear', '14', '#000', 'me-1'); ?> Настройки шаблонов
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-3 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0">
                    <h6 class="card-title mb-0">Шаблоны</h6>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <?php foreach ($templates as $template) { ?>
                            <a href="#" class="list-group-item list-group-item-action template-selector <?php echo $template['name'] === $currentTemplate ? 'active' : ''; ?>" 
                               data-template="<?php echo $template['name']; ?>">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span>
                                        <?php echo bloggy_icon('bs', 'folder', '16', '#000', 'me-2'); ?>
                                        <?php echo html(ucfirst($template['name'])); ?>
                                    </span>
                                    <?php if ($template['name'] === $currentTemplate) { ?>
                                        <span class="badge bg-success rounded-pill">активен</span>
                                    <?php } ?>
                                </div>
                            </a>
                        <?php } ?>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mt-3">
                <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                    <h6 class="card-title mb-0">Файлы шаблона</h6>
                    <button class="btn btn-sm btn-outline-secondary" id="refreshFiles" title="Обновить список">
                        <?php echo bloggy_icon('bs', 'arrow-clockwise', '14', '#000'); ?>
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush file-list" id="fileList" style="max-height: 500px; overflow-y: auto;">
                        <div class="text-center py-3">
                            <div class="spinner-border spinner-border-sm" role="status">
                                <span class="visually-hidden">Загрузка...</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-9">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                    <h6 class="card-title mb-0" id="currentFile">Выберите файл для редактирования</h6>
                    <div class="d-flex gap-2">
                        <button class="btn btn-sm btn-outline-secondary" id="refreshFile" title="Обновить" style="display: none;">
                            <?php echo bloggy_icon('bs', 'arrow-clockwise', '14', '#000'); ?>
                        </button>
                        <button class="btn btn-sm btn-success" id="saveFile" disabled style="display: none;">
                            <?php echo bloggy_icon('bs', 'check-lg', '14', '#fff', 'me-1'); ?> Сохранить
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div id="editorContainer" style="height: 600px; display: none;">
                        <div id="codeEditor" style="height: 100%;"></div>
                    </div>
                    <div id="editorPlaceholder" class="text-center py-5">
                        <div class="mb-3">
                            <?php echo bloggy_icon('bs', 'code-slash', '48', '#6C6C6C'); ?>
                        </div>
                        <h5 class="text-muted">Выберите файл для редактирования</h5>
                        <p class="text-muted">Файлы шаблона появятся после выбора шаблона</p>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mt-3" id="fileInfo" style="display: none;">
                <div class="card-header bg-white border-0">
                    <h6 class="card-title mb-0">Информация о файле</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <strong>Имя файла:</strong><br>
                            <span id="infoFileName" class="text-muted">-</span>
                        </div>
                        <div class="col-md-4">
                            <strong>Размер:</strong><br>
                            <span id="infoFileSize" class="text-muted">-</span>
                        </div>
                        <div class="col-md-4">
                            <strong>Путь:</strong><br>
                            <span id="infoFilePath" class="text-muted">-</span>
                        </div>
                        <div class="col-md-12 mt-2">
                            <strong>Описание:</strong><br>
                            <span id="infoFileDescription" class="text-muted">Нет описания</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.4.12/ace.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.4.12/ext-language_tools.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.4.12/mode-php.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.4.12/mode-html.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.4.12/mode-css.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/ace/1.4.12/mode-javascript.js"></script>

<?php ob_start(); ?>
<script>
let editor = null;
let currentTemplate = '<?php echo $currentTemplate; ?>';
let currentFile = null;

document.addEventListener('DOMContentLoaded', function() {
    editor = ace.edit("codeEditor");
    editor.setTheme("ace/theme/monokai");
    editor.session.setMode("ace/mode/php");
    editor.setOptions({
        enableBasicAutocompletion: true,
        enableLiveAutocompletion: true,
        enableSnippets: true,
        fontSize: "14px",
        showPrintMargin: false,
        tabSize: 4,
        useSoftTabs: true
    });

    initEventHandlers();
    loadTemplateFiles(currentTemplate);
});

function initEventHandlers() {
    document.querySelectorAll('.template-selector').forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            const template = this.getAttribute('data-template');
            loadTemplateFiles(template);
            document.querySelectorAll('.template-selector').forEach(el => {
                el.classList.remove('active');
            });
            this.classList.add('active');
        });
    });

    document.getElementById('saveFile').addEventListener('click', saveFile);
    document.getElementById('refreshFile').addEventListener('click', function() {
        if (currentFile) {
            loadFileContent(currentTemplate, currentFile);
        }
    });

    document.getElementById('refreshFiles').addEventListener('click', function() {
        loadTemplateFiles(currentTemplate);
    });

    editor.session.on('change', function() {
        document.getElementById('saveFile').disabled = false;
    });
}

function loadTemplateFiles(template) {
    const fileList = document.getElementById('fileList');
    fileList.innerHTML = '<div class="text-center py-3"><div class="spinner-border spinner-border-sm" role="status"></div></div>';

    fetch(`<?php echo ADMIN_URL; ?>/templates/get-files?template=${template}`)
        .then(response => response.json())
        .then(files => {
            renderFileList(files, template);
            currentTemplate = template;
        })
        .catch(error => {
            fileList.innerHTML = '<div class="text-center py-3 text-danger">Ошибка загрузки файлов</div>';
        });
}

function renderFileList(files, template) {
    const fileList = document.getElementById('fileList');
    
    if (files.length === 0) {
        fileList.innerHTML = `
            <div class="text-center py-5">
                <div class="mb-3">
                    <?php echo bloggy_icon('bs', 'file-earmark-x', '48', '#6C6C6C'); ?>
                </div>
                <h5 class="text-muted">Шаблонные файлы не найдены</h5>
                <p class="text-muted">Добавьте комментарии "Template Name" в PHP файлы шаблона</p>
            </div>
        `;
        return;
    }

    let html = '';
    files.forEach(file => {
        html += `
            <a href="#" class="list-group-item list-group-item-action file-item" 
               data-file="${file.path}" 
               data-template="${template}">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="flex-grow-1">
                        <div class="d-flex align-items-center mb-2">
                            <?php echo bloggy_icon('bs', 'file-code', '16', '#0d6efd', 'me-3'); ?>
                            <div>
                                <div class="file-name fw-semibold">${file.name}</div>
                                ${file.description ? `<div class="file-description">${file.description}</div>` : ''}
                            </div>
                        </div>
                        <div class="file-path">${file.path}</div>
                    </div>
                    <div class="file-size">${file.size}</div>
                </div>
            </a>
        `;
    });
    
    fileList.innerHTML = html;

    document.querySelectorAll('.file-item').forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            const filePath = this.getAttribute('data-file');
            const templateName = this.getAttribute('data-template');
            
            loadFileContent(templateName, filePath);
            
            document.querySelectorAll('.file-item').forEach(el => {
                el.classList.remove('active');
            });
            this.classList.add('active');
        });
    });
}

function getFileIcon(filename) {
    return 'code';
}

function loadFileContent(template, filePath) {
    currentFile = filePath;
    document.getElementById('editorContainer').style.display = 'block';
    document.getElementById('editorPlaceholder').style.display = 'none';
    document.getElementById('fileInfo').style.display = 'block';
    document.getElementById('refreshFile').style.display = 'inline-block';
    document.getElementById('saveFile').style.display = 'inline-block';
    const fileName = filePath.split('/').pop();
    document.getElementById('currentFile').textContent = `Редактирование: ${fileName}`;
    document.getElementById('saveFile').disabled = true;
    editor.setValue('// Загрузка файла...', -1);
    const extension = fileName.split('.').pop().toLowerCase();
    setEditorMode(extension);

    const url = `<?php echo ADMIN_URL; ?>/templates/get-file?template=${template}&file=${encodeURIComponent(filePath)}`;

    fetch(url)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok: ' + response.status);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                editor.setValue(data.content, -1);
                editor.session.getUndoManager().reset();
                updateFileInfo(data.info, filePath);
            } else {
                editor.setValue('// Ошибка загрузки файла: ' + data.error, -1);
                showToast('Ошибка загрузки файла: ' + data.error, 'danger');
            }
        })
        .catch(error => {
            editor.setValue('// Ошибка загрузки файла: ' + error.message, -1);
            showToast('Ошибка загрузки файла: ' + error.message, 'danger');
        });
}

function setEditorMode(extension) {
    const modes = {
        'php': 'php',
        'html': 'html',
        'css': 'css',
        'js': 'javascript',
        'json': 'json',
        'xml': 'xml',
        'txt': 'text'
    };
    
    const mode = modes[extension] || 'text';
    editor.session.setMode(`ace/mode/${mode}`);
}

function updateFileInfo(info, filePath) {
    document.getElementById('infoFileName').textContent = info.name;
    document.getElementById('infoFileSize').textContent = info.size;
    document.getElementById('infoFilePath').textContent = filePath;
    document.getElementById('infoFileDescription').textContent = info.description || 'Нет описания';
}

function saveFile() {
    if (!currentFile || !currentTemplate) return;

    const content = editor.getValue();
    
    fetch(`<?php echo ADMIN_URL; ?>/templates/save`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            template: currentTemplate,
            file: currentFile,
            content: content
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('saveFile').disabled = true;
            showToast(data.message || 'Файл успешно сохранен', 'success');
        } else {
            showToast('Ошибка сохранения: ' + data.error, 'danger');
        }
    })
    .catch(error => {
        showToast('Ошибка сохранения', 'danger');
    });
}

function showToast(message, type) {
    const toastEl = document.getElementById('toast');
    if (toastEl) {
        const toast = new bootstrap.Toast(toastEl);
        const toastBody = toastEl.querySelector('.toast-body');
        toastBody.textContent = message;
        toastEl.className = 'toast align-items-center border-0';
        toastEl.classList.add(`bg-${type}`);
        
        toast.show();
    } else {
        alert(message);
    }
}
</script>
<?php admin_bottom_js(ob_get_clean()); ?>