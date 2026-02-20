class DocsForm {
    constructor() {
        this.form = document.getElementById('docs-form');
        if (!this.form) return;

        this.init();
    }

    init() {
        this.initSlugGeneration();
        this.initCharacterCounters();
        this.initFormValidation();
    }

    initSlugGeneration() {
        const titleInput = document.querySelector('input[name="title"]');
        const slugInput = document.querySelector('input[name="slug"]');
        
        if (!titleInput || !slugInput) return;

        titleInput.addEventListener('blur', () => {
            if (!slugInput.value) {
                this.generateSlug(titleInput.value).then(slug => {
                    slugInput.value = slug;
                });
            }
        });

        slugInput.addEventListener('input', (e) => {
            let slug = e.target.value;
            slug = slug.toLowerCase()
                .replace(/[^a-z0-9а-яё\s-]/g, '')
                .replace(/\s+/g, '-')
                .replace(/-+/g, '-')
                .replace(/^-|-$/g, '');
            
            e.target.value = slug;
        });
    }

    async generateSlug(text) {
        return text.toLowerCase()
            .replace(/[^a-z0-9а-яё\s-]/g, '')
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-')
            .replace(/^-|-$/g, '');
    }

    initCharacterCounters() {
        const counters = {
            'meta_title': 60,
            'meta_description': 160,
            'excerpt': 255
        };

        Object.entries(counters).forEach(([fieldName, maxLength]) => {
            const field = document.querySelector(`[name="${fieldName}"]`);
            if (!field) return;

            const counterId = `${fieldName}-counter`;
            let counterElement = document.getElementById(counterId);
            
            if (!counterElement) {
                counterElement = document.createElement('div');
                counterElement.id = counterId;
                counterElement.className = 'form-text text-end';
                field.parentNode.appendChild(counterElement);
            }

            const updateCounter = () => {
                const length = field.value.length;
                counterElement.textContent = `${length} / ${maxLength} символов`;
                counterElement.className = `form-text text-end ${length > maxLength ? 'text-danger' : 'text-muted'}`;
            };

            field.addEventListener('input', updateCounter);
            updateCounter();
        });
    }

    initFormValidation() {
        this.form.addEventListener('submit', (e) => {
            const title = document.querySelector('input[name="title"]');
            const content = document.querySelector('textarea[name="content"]');
            
            let isValid = true;
            let errorMessage = '';

            if (!title.value.trim()) {
                isValid = false;
                errorMessage += '• Заголовок статьи обязателен\n';
                title.classList.add('is-invalid');
            } else {
                title.classList.remove('is-invalid');
            }

            if (!content.value.trim()) {
                isValid = false;
                errorMessage += '• Содержание статьи обязательно\n';
                content.classList.add('is-invalid');
            } else {
                content.classList.remove('is-invalid');
            }

            if (!isValid) {
                e.preventDefault();
                alert('Пожалуйста, исправьте следующие ошибки:\n\n' + errorMessage);
                return false;
            }

            const submitButton = this.form.querySelector('button[type="submit"]');
            if (submitButton) {
                submitButton.disabled = true;
                submitButton.innerHTML = '<i class="bi bi-hourglass-split me-1"></i> Сохранение...';
            }

            return true;
        });
    }

    previewArticle() {
        const formData = new FormData(this.form);
        const previewWindow = window.open('', '_blank');
        previewWindow.document.write('<h1>Предпросмотр статьи</h1>');
        previewWindow.document.write('<p>Функция предпросмотра в разработке...</p>');
    }
}

document.addEventListener('DOMContentLoaded', () => {
    window.docsForm = new DocsForm();
});