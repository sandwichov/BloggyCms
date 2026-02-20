class CodeBlockAdmin {
    constructor(container = document) {
        this.container = container;
        this.editor = null;
        this.textarea = null;
        
        this.init();
    }

    init() {
        this.initAceEditor();
        this.bindEvents();
        this.setupFormSync();
    }

    initAceEditor() {
        const editorContainer = this.container.querySelector('#code-editor-container');
        this.textarea = this.container.querySelector('#code-editor-textarea');
        
        if (!editorContainer || !this.textarea) {
            console.warn('Code editor container not found');
            return;
        }

        if (typeof ace === 'undefined') {
            console.warn('Ace Editor not loaded');
            this.fallbackToTextarea();
            return;
        }

        try {
            this.editor = ace.edit(editorContainer);
            this.editor.setOptions({
                mode: 'ace/mode/javascript',
                theme: 'ace/theme/monokai',
                fontSize: '14px',
                showPrintMargin: false,
                useSoftTabs: true,
                tabSize: 4,
                wrap: true,
                showLineNumbers: true,
                showGutter: true,
                highlightActiveLine: true
            });

            if (this.textarea.value) {
                this.editor.setValue(this.textarea.value);
            } else {
                this.editor.setValue('// Ваш код здесь...');
            }
            this.editor.clearSelection();
            this.editor.getSession().on('change', () => {
                this.textarea.value = this.editor.getValue();
            });

            this.setupKeybindings();

        } catch (error) {
            console.error('Error initializing Ace Editor:', error);
            this.fallbackToTextarea();
        }
    }

    setupFormSync() {
        const form = this.container.closest('form');
        if (form) {
            form.addEventListener('submit', (e) => {
                this.syncEditorToTextarea();
            });
        }

        const saveButton = document.getElementById('save-post-block-settings');
        if (saveButton) {
            saveButton.addEventListener('click', () => {
                this.syncEditorToTextarea();
            });
        }
    }

    syncEditorToTextarea() {
        if (this.editor && this.textarea) {
            this.textarea.value = this.editor.getValue();
        }
    }

    fallbackToTextarea() {
        const editorContainer = this.container.querySelector('#code-editor-container');
        const textarea = this.container.querySelector('#code-editor-textarea');
        
        if (editorContainer && textarea) {
            textarea.style.display = 'block';
            textarea.style.width = '100%';
            textarea.style.height = '400px';
            textarea.style.fontFamily = 'monospace';
            textarea.style.fontSize = '14px';
            editorContainer.style.display = 'none';
        }
    }

    setupKeybindings() {
        if (!this.editor) return;
        this.editor.commands.addCommand({
            name: 'indent',
            bindKey: 'Tab',
            exec: (editor) => {
                if (editor.getSelectedText()) {
                    editor.indent();
                } else {
                    editor.insert('    ');
                }
            }
        });

        this.editor.commands.addCommand({
            name: 'outdent',
            bindKey: 'Shift-Tab',
            exec: (editor) => {
                editor.blockOutdent();
            }
        });

        this.editor.commands.addCommand({
            name: 'duplicateLine',
            bindKey: { win: 'Ctrl-D', mac: 'Cmd-D' },
            exec: (editor) => {
                const session = editor.getSession();
                const selection = editor.getSelection();
                
                if (selection.isEmpty()) {
                    const cursor = selection.getCursor();
                    const line = session.getLine(cursor.row);
                    session.insert({ row: cursor.row + 1, column: 0 }, line + '\n');
                } else {
                    editor.duplicateSelection();
                }
            }
        });
    }

    bindEvents() {
        const languageSelect = this.container.querySelector('#code-language-select');
        if (languageSelect && this.editor) {
            languageSelect.addEventListener('change', () => {
                this.updateEditorMode();
            });
        }
    }

    updateEditorMode() {
        if (!this.editor) return;

        const languageSelect = this.container.querySelector('#code-language-select');
        const language = languageSelect ? languageSelect.value : 'javascript';
        
        const modeMap = {
            'javascript': 'javascript',
            'typescript': 'typescript',
            'php': 'php',
            'html': 'html',
            'css': 'css',
            'scss': 'scss',
            'python': 'python',
            'java': 'java',
            'cpp': 'c_cpp',
            'csharp': 'csharp',
            'sql': 'sql',
            'json': 'json',
            'xml': 'xml',
            'bash': 'sh',
            'markdown': 'markdown',
            'yaml': 'yaml',
            'dockerfile': 'dockerfile',
            'nginx': 'nginx',
            'plaintext': 'text'
        };

        const mode = modeMap[language] || 'text';
        this.editor.getSession().setMode(`ace/mode/${mode}`);
    }

    destroy() {
        if (this.editor) {
            this.editor.destroy();
        }
    }
}

function initCodeBlockAdmin() {
    const editorContainers = document.querySelectorAll('#code-editor-container');
    
    editorContainers.forEach(container => {
        if (!container.hasAttribute('data-initialized')) {
            new CodeBlockAdmin(container.closest('.modal') || document);
            container.setAttribute('data-initialized', 'true');
        }
    });
}

document.addEventListener('DOMContentLoaded', function() {
    setTimeout(initCodeBlockAdmin, 100);
});

document.addEventListener('shown.bs.modal', function() {
    setTimeout(initCodeBlockAdmin, 300);
});

document.addEventListener('click', function(e) {
    if (e.target.matches('#save-post-block-settings') || 
        e.target.closest('#save-post-block-settings')) {
        const editorContainers = document.querySelectorAll('#code-editor-container');
        editorContainers.forEach(container => {
            if (window.ace) {
                const editor = ace.edit(container);
                const textarea = document.getElementById('code-editor-textarea');
                if (editor && textarea) {
                    textarea.value = editor.getValue();
                }
            }
        });
    }
});

window.CodeBlockAdmin = CodeBlockAdmin;
window.initCodeBlockAdmin = initCodeBlockAdmin;