class GalleryBlockAdmin {
    constructor(containerId = 'gallery-items-container') {
        this.itemsContainer = document.getElementById(containerId);
        this.itemCounter = 0;
        
        if (!this.itemsContainer) {
            console.warn('Gallery container not found:', containerId);
            return;
        }
        
        this.init();
    }

    init() {
        this.bindEvents();
        this.initSortable();
        this.updateRemoveButtons();
        this.updateItemCounter();
    }

    bindEvents() {
        const addButton = document.getElementById('add-gallery-item');
        if (addButton) {
            addButton.addEventListener('click', () => {
                this.addGalleryItem();
            });
        } else {
            console.warn('Add gallery item button not found');
        }

        this.itemsContainer.addEventListener('click', (e) => {
            const removeButton = e.target.closest('.remove-gallery-item');
            if (removeButton) {
                this.removeGalleryItem(removeButton);
            }
        });

        this.itemsContainer.addEventListener('change', (e) => {
            if (e.target.classList.contains('gallery-image-input')) {
                this.handleImagePreview(e.target);
            }
        });
    }

    addGalleryItem() {
        const newIndex = this.getItemCount();
        
        const newItemHtml = this.getGalleryItemHtml(newIndex);
        
        const tempDiv = document.createElement('div');
        tempDiv.innerHTML = newItemHtml.trim();
        const newItem = tempDiv.firstElementChild;
        
        this.itemsContainer.appendChild(newItem);
        this.updateRemoveButtons();
        this.updateItemCounter();
    }

    removeGalleryItem(button) {
        const item = button.closest('.gallery-item');
        if (item && this.itemsContainer.children.length > 1) {
            if (confirm('Удалить это изображение из галереи?')) {
                item.remove();
                this.renumberItems();
                this.updateRemoveButtons();
                this.updateItemCounter();
            }
        }
    }

    updateRemoveButtons() {
        const removeButtons = this.itemsContainer.querySelectorAll('.remove-gallery-item');
        const shouldDisable = this.itemsContainer.children.length === 1;
        
        removeButtons.forEach(button => {
            button.disabled = shouldDisable;
        });
    }

    renumberItems() {
        const items = this.itemsContainer.querySelectorAll('.gallery-item');
        
        items.forEach((item, index) => {
            const contentInputs = item.querySelectorAll('input[name^="content[images]"], textarea[name^="content[images]"]');
            contentInputs.forEach(input => {
                const oldName = input.name;
                const newName = oldName.replace(/content\[images\]\[\d+\]/, `content[images][${index}]`);
                input.name = newName;
            });

            const removeCheckbox = item.querySelector('input[type="checkbox"][name^="remove_gallery_image_"]');
            if (removeCheckbox) {
                removeCheckbox.name = `remove_gallery_image_${index}`;
                removeCheckbox.id = `removeImage${index}`;
                
                const label = item.querySelector(`label[for^="removeImage"]`);
                if (label) {
                    label.htmlFor = `removeImage${index}`;
                }
            }

            const fileInput = item.querySelector('.gallery-image-input');
            if (fileInput) {
                fileInput.name = `gallery_image_${index}`;
            }
        });
    }

    handleImagePreview(input) {
        const file = input.files[0];
        if (!file) return;

        const previewContainer = input.closest('.gallery-item').querySelector('.new-image-preview');
        const previewImg = previewContainer.querySelector('.preview-image');
        const reader = new FileReader();
        
        reader.onload = (e) => {
            previewImg.src = e.target.result;
            previewContainer.style.display = 'block';
            const currentPreview = input.closest('.gallery-item').querySelector('.current-image-preview');
            if (currentPreview) {
                currentPreview.style.display = 'none';
            }
        };
        
        reader.readAsDataURL(file);
    }

    initSortable() {
        if (typeof Sortable === 'undefined') {
            console.warn('Sortable.js not loaded');
            return;
        }

        try {
            this.sortable = new Sortable(this.itemsContainer, {
                handle: '.gallery-item-handle',
                animation: 150,
                ghostClass: 'sortable-ghost',
                chosenClass: 'sortable-chosen',
                dragClass: 'sortable-drag',
                
                onEnd: (evt) => {
                    this.renumberItems();
                }
            });
        } catch (error) {
            console.error('Error initializing Sortable:', error);
        }
    }

    getGalleryItemHtml(index) {
        return `
        <div class="gallery-item card mb-3">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-1 text-center">
                        <span class="gallery-item-handle text-muted">
                            <i class="bi bi-grip-vertical"></i>
                        </span>
                    </div>
                    <div class="col-8">
                        <!-- Поле для загрузки файла -->
                        <div class="mb-3">
                            <label class="form-label small">Загрузить изображение *</label>
                            <input type="file" 
                                   name="gallery_image_${index}" 
                                   class="form-control form-control-sm gallery-image-input" 
                                   accept="image/*"
                                   required>
                            <div class="form-text small">
                                Форматы: JPG, PNG, GIF, WebP. Макс. размер: 5MB
                            </div>
                        </div>

                        <!-- Скрытое поле для URL -->
                        <input type="hidden" 
                               name="content[images][${index}][image_url]" 
                               class="gallery-image-url" 
                               value="">

                        <!-- Alt текст -->
                        <div class="mb-3">
                            <label class="form-label small">Alt текст *</label>
                            <input type="text" 
                                   name="content[images][${index}][alt_text]" 
                                   class="form-control form-control-sm" 
                                   value="" 
                                   placeholder="Описание изображения"
                                   required>
                        </div>

                        <!-- Подпись -->
                        <div class="mb-2">
                            <label class="form-label small">Подпись</label>
                            <input type="text" 
                                   name="content[images][${index}][caption]" 
                                   class="form-control form-control-sm" 
                                   value="" 
                                   placeholder="Необязательная подпись">
                        </div>

                        <!-- Предпросмотр нового изображения -->
                        <div class="new-image-preview mt-2" style="display: none;">
                            <div class="border rounded p-2 bg-light">
                                <img src="" alt="Предпросмотр" class="img-thumbnail preview-image" style="max-height: 60px;">
                            </div>
                        </div>
                    </div>
                    <div class="col-2 text-end">
                        <button type="button" class="btn btn-danger btn-sm remove-gallery-item">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>`;
    }

    getItemCount() {
        return this.itemsContainer.children.length;
    }

    updateItemCounter() {
        const count = this.getItemCount();
    }
}

document.addEventListener('DOMContentLoaded', function() {
    
    const galleryContainers = document.querySelectorAll('[id^="gallery-items-container"]');
    
    if (galleryContainers.length > 0) {
        galleryContainers.forEach((container, index) => {
            const containerId = container.id;
            
            window[`galleryBlockAdmin${index}`] = new GalleryBlockAdmin(containerId);
        });
    } else {
        const alternativeContainers = document.querySelectorAll('.gallery-items-container');
        if (alternativeContainers.length > 0) {
            alternativeContainers.forEach((container, index) => {
                window[`galleryBlockAdmin${index}`] = new GalleryBlockAdmin();
            });
        }
    }
});

document.addEventListener('show.bs.modal', function() {
    setTimeout(() => {
        const galleryContainer = document.getElementById('gallery-items-container');
        if (galleryContainer && !window.galleryBlockAdminInitialized) {
            window.galleryBlockAdmin = new GalleryBlockAdmin();
            window.galleryBlockAdminInitialized = true;
        }
    }, 100);
});

window.reinitializeGalleryBlock = function() {
    const galleryContainer = document.getElementById('gallery-items-container');
    if (galleryContainer) {
        window.galleryBlockAdmin = new GalleryBlockAdmin();
    }
};

window.GalleryBlockAdmin = GalleryBlockAdmin;