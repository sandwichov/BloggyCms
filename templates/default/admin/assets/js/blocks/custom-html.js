(function() {
    'use strict';
    
    function initAceEditor() {
        const editorContainer = document.getElementById('html-editor-container');
        const textarea = document.getElementById('html-editor-textarea');
        
        if (!editorContainer || !textarea) {
            return;
        }

        if (typeof ace === 'undefined') {
            console.warn('Ace Editor not loaded');
            textarea.style.display = 'block';
            textarea.style.width = '100%';
            textarea.style.height = '400px';
            textarea.style.fontFamily = 'monospace';
            editorContainer.style.display = 'none';
            return;
        }

        try {

            const editor = ace.edit(editorContainer);
            
            editor.setOptions({
                mode: 'ace/mode/html',
                theme: 'ace/theme/monokai',
                fontSize: '14px',
                showPrintMargin: false,
                useSoftTabs: true,
                tabSize: 2,
                wrap: false,
                showLineNumbers: true,
                showGutter: true,
                highlightActiveLine: true
            });

            if (textarea.value) {
                editor.setValue(textarea.value);
            } else {
                editor.setValue('<!-- Вставьте ваш HTML код здесь -->');
            }
            editor.clearSelection();

            editor.getSession().on('change', () => {
                textarea.value = editor.getValue();
            });

            editor.commands.addCommand({
                name: 'indent',
                bindKey: 'Tab',
                exec: (ed) => {
                    if (ed.getSelectedText()) {
                        ed.indent();
                    } else {
                        ed.insert('  ');
                    }
                }
            });

            editor.commands.addCommand({
                name: 'outdent',
                bindKey: 'Shift-Tab',
                exec: (ed) => {
                    ed.blockOutdent();
                }
            });

        } catch (error) {
            textarea.style.display = 'block';
            textarea.style.width = '100%';
            textarea.style.height = '400px';
            textarea.style.fontFamily = 'monospace';
            editorContainer.style.display = 'none';
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(initAceEditor, 100);
    });

    document.addEventListener('shown.bs.modal', function() {
        setTimeout(initAceEditor, 300);
    });

    document.addEventListener('click', function(e) {
        if (e.target.matches('#save-post-block-settings') || 
            e.target.closest('#save-post-block-settings')) {
            const editorContainer = document.getElementById('html-editor-container');
            const textarea = document.getElementById('html-editor-textarea');
            
            if (editorContainer && textarea && window.ace) {
                try {
                    const editor = ace.edit(editorContainer);
                    if (editor) {
                        textarea.value = editor.getValue();
                    }
                } catch (error) {

                }
            }
        }
    });

})();