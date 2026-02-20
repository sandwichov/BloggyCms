class ImageBlockAdmin {
    constructor(container = document) {
        this.container = container;
        this.init();
    }

    init() {
        this.bindEvents();
    }

    bindEvents() {
        this.container.addEventListener('change', (e) => {
            if (e.target.classList.contains('image-file-input')) {
                this.previewImage(e.target);
            }
        });
    }

    previewImage(fileInput) {
        const file = fileInput.files[0];
        const previewContainer = this.container.querySelector('.new-image-preview');
        const previewImage = this.container.querySelector('.preview-image');
        
        if (!file || !previewContainer || !previewImage) return;
        if (!file.type.startsWith('image/')) {
            alert('Пожалуйста, выберите файл изображения');
            fileInput.value = '';
            return;
        }

        if (file.size > 5 * 1024 * 1024) {
            alert('Файл слишком большой. Максимальный размер: 5MB');
            fileInput.value = '';
            return;
        }

        const reader = new FileReader();
        
        reader.onload = (e) => {
            previewImage.src = e.target.result;
            previewContainer.style.display = 'block';
        };
        
        reader.readAsDataURL(file);
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const containers = document.querySelectorAll('.modal, body');
    
    containers.forEach(container => {
        const hasFileInputs = container.querySelector('.image-file-input');
        
        if (hasFileInputs && !container.hasAttribute('data-image-admin-initialized')) {
            new ImageBlockAdmin(container);
            container.setAttribute('data-image-admin-initialized', 'true');
        }
    });
});

document.addEventListener('shown.bs.modal', function() {
    setTimeout(() => {
        const modal = document.querySelector('.modal.show');
        if (modal && modal.querySelector('.image-file-input')) {
            new ImageBlockAdmin(modal);
        }
    }, 100);
});

window.ImageBlockAdmin = ImageBlockAdmin;