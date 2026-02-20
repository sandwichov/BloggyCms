class MyUploadAdapter {
    constructor(loader) {
        this.loader = loader;
    }

    upload() {
        return this.loader.file
            .then(file => new Promise((resolve, reject) => {
                const formData = new FormData();
                formData.append('upload', file);

                fetch('<?= BASE_URL ?>/admin/pages/upload-image', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        reject(data.error.message);
                    } else {
                        resolve({
                            default: data.url
                        });
                    }
                })
                .catch(error => {
                    reject('Upload failed');
                });
            }));
    }

    abort() {}
}

function MyCustomUploadAdapterPlugin(editor) {
    editor.plugins.get('FileRepository').createUploadAdapter = (loader) => {
        return new MyUploadAdapter(loader);
    };
}

ClassicEditor
    .create(document.querySelector('#content'), {
        extraPlugins: [MyCustomUploadAdapterPlugin],
        toolbar: ['heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', '|', 'outdent', 'indent', '|', 'blockQuote', 'insertTable', 'mediaEmbed', 'imageUpload', 'undo', 'redo'],
        heading: {
            options: [
                { model: 'paragraph', title: 'Paragraph', class: 'ck-heading__paragraph' },
                { model: 'heading1', view: 'h1', title: 'Heading 1', class: 'ck-heading__heading1' },
                { model: 'heading2', view: 'h2', title: 'Heading 2', class: 'ck-heading__heading2' },
                { model: 'heading3', view: 'h3', title: 'Heading 3', class: 'ck-heading__heading3' }
            ]
        }
    })
    .then(editor => {
        const form = document.querySelector('form');
        form.addEventListener('submit', () => {
            document.getElementById('content-input').value = editor.getData();
        });
    })
    .catch(error => {
        console.error(error);
    });