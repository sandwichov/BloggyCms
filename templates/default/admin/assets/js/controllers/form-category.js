document.addEventListener('DOMContentLoaded', function() {
    const passwordProtectedCheckbox = document.getElementById('password_protected');
    const passwordField = document.querySelector('.password-field');
    
    if (passwordProtectedCheckbox && passwordField) {
        passwordProtectedCheckbox.addEventListener('change', function() {
            passwordField.style.display = this.checked ? 'block' : 'none';
            if (!this.checked) {
                document.getElementById('password').value = '';
            }
        });
    }

    const nameInput = document.querySelector('input[name="name"]');
    const slugInput = document.querySelector('input[name="slug"]');
    
    if (nameInput && slugInput) {
        nameInput.addEventListener('blur', function() {
            if (!slugInput.value) {
                const slug = this.value.toLowerCase()
                    .replace(/[^\w\u0400-\u04FF]+/g, '-')
                    .replace(/^-+|-+$/g, '');
                slugInput.value = slug;
            }
        });
    }

    function setupCharacterCounter(selector, maxLength) {
        const input = document.querySelector(selector);
        if (!input) return;
        
        const counter = document.createElement('div');
        counter.className = 'form-text text-end';
        input.parentNode.appendChild(counter);
        
        function updateCounter() {
            const length = input.value.length;
            counter.textContent = `${length}/${maxLength}`;
            counter.className = `form-text text-end ${length > maxLength ? 'text-danger' : 'text-muted'}`;
        }
        
        input.addEventListener('input', updateCounter);
        updateCounter();
    }

    setupCharacterCounter('input[name="meta_title"]', 60);
    setupCharacterCounter('textarea[name="meta_description"]', 160);

    const imageInput = document.getElementById('image');
    if (imageInput) {
        imageInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (!file) return;
            
            const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            if (!allowedTypes.includes(file.type)) {
                alert('Допустимы только изображения в форматах JPG, PNG, GIF или WebP');
                this.value = '';
                return;
            }
            
            const maxSize = 5 * 1024 * 1024;
            if (file.size > maxSize) {
                alert('Файл слишком большой. Максимальный размер: 5MB');
                this.value = '';
                return;
            }
            
            const reader = new FileReader();
            reader.onload = function(e) {
                const oldPreview = imageInput.parentNode.querySelector('.image-preview');
                if (oldPreview) {
                    oldPreview.remove();
                }
                
                const previewDiv = document.createElement('div');
                previewDiv.className = 'image-preview mt-2 text-center';
                previewDiv.innerHTML = `
                    <img src="${e.target.result}" class="img-thumbnail" style="max-height: 150px;" alt="Превью">
                    <div class="form-text mt-1">Предпросмотр нового изображения</div>
                `;
                imageInput.parentNode.appendChild(previewDiv);
            };
            reader.readAsDataURL(file);
        });
    }

    const deleteImageCheckbox = document.querySelector('input[name="delete_image"]');
    if (deleteImageCheckbox) {
        deleteImageCheckbox.addEventListener('change', function() {
            const currentImage = document.querySelector('.card-body img');
            if (currentImage) {
                currentImage.style.opacity = this.checked ? '0.3' : '1';
            }
        });
    }
});