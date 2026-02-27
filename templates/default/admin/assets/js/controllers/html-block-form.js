document.addEventListener('DOMContentLoaded', function() {
    initHtmlBlockForm();
});

function initHtmlBlockForm() {
    initAceEditors();
    initAssetHandlers();
    initFormSubmit();
    initTooltips();
    focusNameField();
}

function initAceEditors() {
    if (typeof ace === 'undefined') {
        console.error('Ace editor not loaded!');
        return;
    }
    
    initCssEditor();
    initJsEditor();
    initHtmlEditor();
}

function initCssEditor() {
    const cssEditorElement = document.getElementById('inline-css-editor');
    if (!cssEditorElement) return;
    
    window.inlineCssEditor = ace.edit("inline-css-editor", {
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
    
    const inlineCssField = document.getElementById('inline_css');
    if (inlineCssField) {
        window.inlineCssEditor.setValue(inlineCssField.value || '', -1);
    }
    
    configureAceEditor(window.inlineCssEditor, false);
}

function initJsEditor() {
    const jsEditorElement = document.getElementById('inline-js-editor');
    if (!jsEditorElement) return;
    
    window.inlineJsEditor = ace.edit("inline-js-editor", {
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
    
    const inlineJsField = document.getElementById('inline_js');
    if (inlineJsField) {
        window.inlineJsEditor.setValue(inlineJsField.value || '', -1);
    }
    
    configureAceEditor(window.inlineJsEditor, false);
}

function initHtmlEditor() {
    const htmlEditorElement = document.getElementById('default-block-html-editor');
    if (!htmlEditorElement) return;
    
    window.defaultBlockHtmlEditor = ace.edit("default-block-html-editor", {
        theme: "ace/theme/monokai",
        mode: "ace/mode/html",
        showPrintMargin: false,
        fontSize: "14px",
        tabSize: 4,
        useSoftTabs: true,
        wrap: true,
        minLines: 20,
        maxLines: 40
    });
    
    window.defaultBlockHtmlEditor.session.setUseWrapMode(true);
    
    const htmlTextarea = document.getElementById('default-block-html');
    if (htmlTextarea) {
        window.defaultBlockHtmlEditor.setValue(htmlTextarea.value || '', -1);
        window.defaultBlockHtmlEditor.session.getUndoManager().reset();
    }
    
    configureAceEditor(window.defaultBlockHtmlEditor, true);
}

function configureAceEditor(editor, enableCompletions = false) {
    if (!editor) return;
    
    editor.setOptions({
        enableBasicAutocompletion: enableCompletions,
        enableLiveAutocompletion: enableCompletions,
        enableSnippets: enableCompletions,
        behavioursEnabled: true,
        wrapBehavioursEnabled: true
    });
    
    editor.session.setUseWrapMode(true);
    editor.session.setTabSize(4);
    editor.session.setUseSoftTabs(true);
    editor.session.getUndoManager().reset();
}

function initAssetHandlers() {
    document.getElementById('add-css-file')?.addEventListener('click', () => addAssetRow('css'));
    document.getElementById('add-js-file')?.addEventListener('click', () => addAssetRow('js'));
    attachRemoveHandlers();
}

function addAssetRow(type) {
    const container = document.getElementById(`${type}-files-container`);
    if (!container) return;
    
    const newRow = document.createElement('div');
    newRow.className = `input-group mb-2 ${type}-file-row`;
    newRow.innerHTML = `
        <input type="text" name="${type}_files[]" class="form-control" value="" placeholder="templates/default/front/assets/${type}/my-block.${type}">
        <button type="button" class="btn btn-outline-danger remove-asset" data-type="${type}">
            <svg class="icon icon-trash" width="16" height="16" style="fill: #000">
                <use href="/templates/default/admin/icons/bs.svg#trash"></use>
            </svg>
        </button>
    `;
    container.appendChild(newRow);
    attachRemoveHandlers();
}

function attachRemoveHandlers() {
    document.querySelectorAll('.remove-asset').forEach(button => {
        button.removeEventListener('click', handleRemoveAsset);
        button.addEventListener('click', handleRemoveAsset);
    });
}

function handleRemoveAsset(e) {
    const button = e.currentTarget;
    const type = button.getAttribute('data-type');
    const row = button.closest(`.${type}-file-row`);
    const container = document.getElementById(`${type}-files-container`);
    
    if (!row || !container) return;
    
    if (container.querySelectorAll(`.${type}-file-row`).length > 1) {
        row.remove();
    } else {
        const input = row.querySelector('input');
        if (input) input.value = '';
    }
}

function initFormSubmit() {
    const form = document.getElementById("blockForm");
    if (!form) return;
    
    form.addEventListener("submit", function(e) {
        saveEditorValues();
    });
}

function saveEditorValues() {
    if (window.inlineCssEditor) {
        const inlineCssField = document.getElementById('inline_css');
        if (inlineCssField) {
            inlineCssField.value = window.inlineCssEditor.getValue();
        }
    }
    
    if (window.inlineJsEditor) {
        const inlineJsField = document.getElementById('inline_js');
        if (inlineJsField) {
            inlineJsField.value = window.inlineJsEditor.getValue();
        }
    }
    
    if (window.defaultBlockHtmlEditor) {
        const defaultBlockHtmlField = document.getElementById('default-block-html');
        if (defaultBlockHtmlField) {
            defaultBlockHtmlField.value = window.defaultBlockHtmlEditor.getValue();
        }
    }
}

function initTooltips() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
}

function focusNameField() {
    document.querySelector('input[name="name"]')?.focus();
}