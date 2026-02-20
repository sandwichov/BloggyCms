class ListBlockAdmin {
    constructor(container = document) {
        this.container = container;
        this.itemsContainer = this.container.querySelector('#list-items-container');
        this.addButton = this.container.querySelector('#add-list-item');
        this.listTypeSelect = this.container.querySelector('#list-type-select');
        if (this.itemsContainer && !this.itemsContainer.hasAttribute('data-initialized')) {
            this.itemsContainer.setAttribute('data-initialized', 'true');
            this.init();
        }
    }

    init() {
        this.bindEvents();
        this.initSortable();
        this.updateListTypeIndicator();
        this.toggleRemoveButtons();
    }

    bindEvents() {
        if (this.addButton && !this.addButton.hasAttribute('data-event-bound')) {
            this.addButton.addEventListener('click', () => {
                this.addListItem();
            });
            this.addButton.setAttribute('data-event-bound', 'true');
        }

        if (this.itemsContainer && !this.itemsContainer.hasAttribute('data-event-bound')) {
            this.itemsContainer.addEventListener('click', (e) => {
                const removeBtn = e.target.closest('.remove-list-item');
                if (removeBtn) {
                    this.removeListItem(removeBtn.closest('.list-item'));
                }
            });
            this.itemsContainer.setAttribute('data-event-bound', 'true');
        }
        if (this.listTypeSelect && !this.listTypeSelect.hasAttribute('data-event-bound')) {
            this.listTypeSelect.addEventListener('change', () => {
                this.updateListTypeIndicator();
            });
            this.listTypeSelect.setAttribute('data-event-bound', 'true');
        }
    }

    initSortable() {
        if (typeof Sortable !== 'undefined' && this.itemsContainer) {
            try {
                new Sortable(this.itemsContainer, {
                    handle: '.list-item-handle',
                    ghostClass: 'sortable-ghost',
                    chosenClass: 'sortable-chosen',
                    animation: 150,
                    onEnd: () => {
                        this.updateItemsOrder();
                    }
                });
            } catch (e) {}
        }
    }

    addListItem() {
        if (!this.itemsContainer) return;
        
        const newItem = document.createElement('div');
        newItem.className = 'list-item card mb-2';
        newItem.innerHTML = `
            <div class="card-body py-2">
                <div class="row align-items-center">
                    <div class="col-1 text-center">
                        <span class="list-item-handle text-muted">
                            <i class="bi bi-grip-vertical"></i>
                        </span>
                    </div>
                    <div class="col-9">
                        <input type="text" 
                            name="content[items][]" 
                            class="form-control" 
                            value="" 
                            placeholder="Введите текст элемента списка">
                    </div>
                    <div class="col-2 text-end">
                        <button type="button" class="btn btn-danger btn-sm remove-list-item">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;

        this.itemsContainer.appendChild(newItem);
        this.toggleRemoveButtons();
        const input = newItem.querySelector('input');
        if (input) {
            input.focus();
        }
    }

    removeListItem(itemElement) {
        if (!this.itemsContainer) return;
        
        const items = this.itemsContainer.querySelectorAll('.list-item');
        if (items.length > 1) {
            itemElement.remove();
            this.updateItemsOrder();
            this.toggleRemoveButtons();
        }
    }

    updateItemsOrder() {
        if (!this.itemsContainer) return;
        
        const items = this.itemsContainer.querySelectorAll('.list-item');
        
        items.forEach((item, index) => {
            const input = item.querySelector('input[name^="content[items]"]');
            if (input) {
                const newName = input.name.replace(/content\[items\]\[\d+\]/, `content[items][${index}]`);
                input.name = newName;
            }
        });
    }

    toggleRemoveButtons() {
        if (!this.itemsContainer) return;
        
        const items = this.itemsContainer.querySelectorAll('.list-item');
        const removeButtons = this.itemsContainer.querySelectorAll('.remove-list-item');
        
        removeButtons.forEach(button => {
            button.disabled = items.length <= 1;
        });
    }

    updateListTypeIndicator() {
        if (!this.listTypeSelect) return;
        
        const type = this.listTypeSelect.value;
        const indicator = this.listTypeSelect.nextElementSibling;
        
        if (indicator && indicator.classList.contains('form-text')) {
            if (type === 'ul') {
                indicator.innerHTML = '<i class="bi bi-dot text-danger"></i> Маркированный список (точки)';
            } else {
                indicator.innerHTML = '<i class="bi bi-1-circle text-success"></i> Нумерованный список (цифры)';
            }
        }
    }

    static reinitializeAll() {
        const containers = document.querySelectorAll('#list-items-container');
        containers.forEach(container => {
            container.removeAttribute('data-initialized');
            if (container.closest('.modal')) {
                new ListBlockAdmin(container.closest('.modal'));
            } else {
                new ListBlockAdmin();
            }
        });
    }
    
}

document.addEventListener('DOMContentLoaded', function() {
    const listContainer = document.getElementById('list-items-container');
    if (listContainer) {
        new ListBlockAdmin();
    }
});

function initListBlockInModal(modal) {
    if (modal) {
        const listContainer = modal.querySelector('#list-items-container');
        if (listContainer && !listContainer.hasAttribute('data-initialized')) {
            new ListBlockAdmin(modal);
            listContainer.setAttribute('data-initialized', 'true');
        }
    }
}

if (typeof window !== 'undefined') {
    window.ListBlockAdmin = ListBlockAdmin;
    window.initListBlockInModal = initListBlockInModal;
}