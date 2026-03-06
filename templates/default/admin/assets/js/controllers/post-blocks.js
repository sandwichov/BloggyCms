class PostBlocksManager {
    constructor() {
        this.blocksContainer = document.getElementById('post-blocks-container');
        this.blockButtons = document.getElementById('post-block-buttons');
        this.blocksData = [];
        this.currentModal = null;
        this.currentCategory = 'all';
        this.currentSearch = '';
        
        if (!this.blocksContainer || !this.blockButtons) {
            return;
        }
        
        this.ensureAdminUrl();
        
        if (window.initialPostBlocks && Array.isArray(window.initialPostBlocks)) {
            window.initialPostBlocks.forEach((block, index) => {});
        }
        
        this.init();
    }

    ensureAdminUrl() {
        if (typeof window.ADMIN_URL === 'undefined') {
            const path = window.location.pathname;
            if (path.includes('/admin/')) {
                const base = path.split('/admin/')[0];
                window.ADMIN_URL = base + '/admin';
            } else {
                window.ADMIN_URL = '/admin';
            }
        }
    }

    init() {
        this.renderBlockButtons();
        this.loadInitialBlocks();
        this.bindEvents();
        this.initSortable();
    }

    renderBlockButtons() {
        if (!window.availablePostBlocks || Object.keys(window.availablePostBlocks).length === 0) {
            this.blockButtons.innerHTML = '<div class="text-center text-muted py-3">Нет доступных блоков</div>';
            return;
        }

        const categories = {};
        
        Object.entries(window.availablePostBlocks).forEach(([systemName, block]) => {
            const category = block.category || 'other';
            if (!categories[category]) {
                categories[category] = [];
            }
            categories[category].push({
                ...block,
                system_name: systemName
            });
        });

        let html = '';
        
        const categoryOrder = ['basic', 'text', 'media', 'layout', 'advanced', 'other'];
        const sortedCategories = Object.keys(categories).sort((a, b) => {
            const indexA = categoryOrder.indexOf(a);
            const indexB = categoryOrder.indexOf(b);
            return (indexA === -1 ? 999 : indexA) - (indexB === -1 ? 999 : indexB);
        });

        sortedCategories.forEach(category => {
            const categoryBlocks = categories[category];
            const filteredBlocks = this.filterBlocks(categoryBlocks);
            
            if (filteredBlocks.length > 0) {
                html += this.renderCategorySection(category, filteredBlocks);
            }
        });

        this.blockButtons.innerHTML = html || '<div class="text-center text-muted py-2">Блоки не найдены</div>';
        this.bindBlockButtonEvents();
    }

    renderCategorySection(category, blocks) {
        const categoryName = this.getCategoryName(category);
        
        return `
        <div class="block-category-section" data-category="${category}">
            <div class="block-category-title">${categoryName}</div>
            <div class="d-flex flex-wrap gap-1">
                ${blocks.map(block => this.renderBlockButton(block)).join('')}
            </div>
        </div>`;
    }

    renderBlockButton(block) {
        return `
        <button type="button" 
                class="btn btn-sm block-icon-btn add-post-block" 
                data-type="${block.system_name}"
                data-category="${block.category || 'other'}"
                title="${block.name} - ${block.description || ''}"
                data-bs-toggle="tooltip"
                data-bs-placement="bottom">
            <i class="${block.icon}"></i>
        </button>`;
    }

    filterBlocks(blocks) {
        return blocks.filter(block => {
            const categoryMatch = this.currentCategory === 'all' || 
                                block.category === this.currentCategory;
            
            const searchMatch = !this.currentSearch || 
                              block.name.toLowerCase().includes(this.currentSearch.toLowerCase()) ||
                              (block.description && block.description.toLowerCase().includes(this.currentSearch.toLowerCase()));
            
            return categoryMatch && searchMatch;
        });
    }

    getCategoryName(category) {
        const names = {
            'text': '📝 Текст',
            'media': '🖼️ Медиа',
            'layout': '📐 Компоновка',
            'advanced': '⚙️ Расширенные',
            'basic': '🔧 Основные',
            'other': '📦 Другие'
        };
        return names[category] || category;
    }

    bindBlockButtonEvents() {
        const tooltipTriggerList = [].slice.call(
            this.blockButtons.querySelectorAll('[data-bs-toggle="tooltip"]')
        );
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl, {
                delay: { "show": 500, "hide": 100 }
            });
        });

        const categoryFilter = document.getElementById('block-category-filter');
        if (categoryFilter) {
            categoryFilter.addEventListener('change', (e) => {
                this.currentCategory = e.target.value;
                this.renderBlockButtons();
            });
        }

        const searchInput = document.getElementById('block-search');
        if (searchInput) {
            searchInput.addEventListener('input', (e) => {
                this.currentSearch = e.target.value;
                this.renderBlockButtons();
            });
        }

        const clearSearch = document.getElementById('clear-search');
        if (clearSearch) {
            clearSearch.addEventListener('click', () => {
                this.currentSearch = '';
                if (searchInput) searchInput.value = '';
                this.renderBlockButtons();
            });
        }
    }

    loadInitialBlocks() {
        if (window.initialPostBlocks && Array.isArray(window.initialPostBlocks)) {
            this.blocksData = [...window.initialPostBlocks];
            this.renderBlocks();
        }
    }

    bindEvents() {
        document.addEventListener('click', (e) => {
            const addButton = e.target.closest('.add-post-block');
            if (addButton) {
                const blockType = addButton.getAttribute('data-type');
                this.addBlock(blockType);
                return;
            }
            const removeButton = e.target.closest('.remove-post-block');
            if (removeButton) {
                const blockElement = removeButton.closest('.post-block-item');
                const blockId = blockElement?.getAttribute('data-block-id');
                if (blockId) {
                    this.removeBlock(blockId);
                }
                return;
            }
            const editButton = e.target.closest('.edit-post-block');
            if (editButton) {
                const blockElement = editButton.closest('.post-block-item');
                const blockId = blockElement?.getAttribute('data-block-id');
                if (blockId) {
                    this.editBlock(blockId);
                }
                return;
            }
        });
    }

    async addBlock(blockType) {
        const blockId = `block_${Date.now()}_${Math.random().toString(36).substr(2, 9)}`;
        const defaultContent = await this.getDefaultContent(blockType);
        const defaultSettings = await this.getDefaultSettings(blockType);
        
        const newBlock = {
            id: blockId,
            type: blockType,
            content: defaultContent,
            settings: defaultSettings,
            order: this.blocksData.length
        };

        this.blocksData.push(newBlock);
        this.renderBlocks();
        this.editBlock(blockId);
        this.updateHiddenField();
    }

    async getDefaultContent(blockType) {
        try {
            const response = await fetch(`${window.ADMIN_URL}/post-blocks/get-default-content?system_name=${blockType}`);
            if (response.ok) {
                const data = await response.json();
                if (data.success) {
                    return data.content;
                }
            }
        } catch (error) {}
        
        return {};
    }

    async getDefaultSettings(blockType) {
        try {
            const response = await fetch(`${window.ADMIN_URL}/post-blocks/get-default-settings?system_name=${blockType}`);
            if (response.ok) {
                const data = await response.json();
                if (data.success) {
                    return data.settings;
                }
            }
        } catch (error) {}
        
        return {};
    }

    async getBlockPresets(blockType) {
        try {
            const response = await fetch(
                `${window.ADMIN_URL}/post-blocks/get-presets?system_name=${blockType}`
            );
            if (response.ok) {
                const data = await response.json();
                if (data.success) {
                    return data.presets || [];
                }
            }
        } catch (error) {
            
        }
        return [];
    }

    removeBlock(blockId) {
        if (confirm('Удалить этот блок?')) {
            this.blocksData = this.blocksData.filter(block => block.id !== blockId);
            this.renderBlocks();
            this.updateHiddenField();
        }
    }

    editBlock(blockId) {
        const block = this.blocksData.find(b => b.id === blockId);
        if (!block) {
            return;
        }

        const blockInfo = window.availablePostBlocks?.[block.type];
        if (!blockInfo) {
            alert(`Информация о блоке не найдена: ${block.type}`);
            return;
        }

        this.openBlockSettingsModal(block, blockInfo);
    }

    openBlockSettingsModal(block, blockInfo) {
        this.closeCurrentModal();

        const modalId = 'post-block-settings-modal';
        const modal = document.createElement('div');
        modal.id = modalId;
        modal.className = 'modal fade';
        modal.setAttribute('tabindex', '-1');
        modal.innerHTML = `
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="${blockInfo.icon} me-2"></i>
                            ${blockInfo.name}
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body" id="post-block-settings-content">
                        <div class="text-center py-4">
                            <div class="spinner-border" role="status">
                                <span class="visually-hidden">Загрузка...</span>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Отмена</button>
                        <button type="button" class="btn btn-primary" id="save-post-block-settings">
                            <i class="bi bi-check-lg me-1"></i>Сохранить
                        </button>
                    </div>
                </div>
            </div>
        `;

        document.body.appendChild(modal);
        this.currentModal = modal;

        this.loadBlockSettingsForm(block, blockInfo, modal);
        
        const bsModal = new bootstrap.Modal(modal);
        bsModal.show();

        modal.addEventListener('hidden.bs.modal', () => {
            this.closeCurrentModal();
        });
    }

    async loadBlockSettingsForm(block, blockInfo, modal) {
        const content = modal.querySelector('#post-block-settings-content');
        
        try {
            const presets = await this.getBlockPresets(block.type);
            
            const url = `${window.ADMIN_URL}/post-blocks/get-settings-form?` + 
                `system_name=${block.type}` + 
                `&current_settings=${encodeURIComponent(JSON.stringify(block.settings || {}))}` + 
                `&current_content=${encodeURIComponent(JSON.stringify(block.content || {}))}`;
            
            const response = await fetch(url);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            let html = await response.text();
            
            if (presets.length > 0) {
                const presetSelector = this.createPresetSelector(presets, block);
                html = this.insertPresetSelectorIntoForm(html, presetSelector);
            }
            
            content.innerHTML = html;

            this.applySavedBlockData(block);
            
            this.initPresetHandlers(block, presets);
            this.initTabs(content);
            
            setTimeout(() => {
                this.reinitializeAllBlocks();
            }, 150);
            
            this.bindSaveHandler(block.id, modal);
            
        } catch (error) {
            content.innerHTML = `
                <div class="alert alert-danger">
                    <h6>Ошибка загрузки формы</h6>
                    <p class="mb-0">${error.message}</p>
                </div>
            `;
        }
    }

    applySavedBlockData(block) {
        if (!block) return;
        
        if (block.settings) {
            Object.keys(block.settings).forEach(key => {
                const settingInput = document.querySelector(`[name="settings[${key}]"]`);
                if (settingInput) {
                    const value = block.settings[key];
                    
                    if (settingInput.type === 'checkbox') {
                        settingInput.checked = Boolean(value);
                    } else if (settingInput.type === 'radio') {
                        const radio = document.querySelector(`[name="settings[${key}]"][value="${value}"]`);
                        if (radio) radio.checked = true;
                    } else {
                        settingInput.value = value || '';
                    }
                }
            });
        }
        
        if (block.content) {
            Object.keys(block.content).forEach(key => {
                const contentInput = document.querySelector(`[name="content[${key}]"]`);
                if (contentInput) {
                    contentInput.value = block.content[key] || '';
                }
            });
        }
        
        if (block.settings && block.settings.preset_id) {
            const presetSelect = document.getElementById('block-preset-select');
            if (presetSelect) {
                presetSelect.value = block.settings.preset_id;
                
                const selectedOption = presetSelect.options[presetSelect.selectedIndex];
                if (selectedOption) {
                    const template = selectedOption.getAttribute('data-template');
                    if (template) {
                        this.applyPresetTemplate(template);
                    }
                }
            }
        }
    }

    applyPresetTemplate(template) {
        if (template.includes('text-danger')) {
            const customClassInput = document.querySelector('[name="settings[custom_class]"]');
            if (customClassInput && !customClassInput.value) {
                customClassInput.value = 'text-danger';
            }
        } else if (template.includes('text-dark')) {
            const customClassInput = document.querySelector('[name="settings[custom_class]"]');
            if (customClassInput && !customClassInput.value) {
                customClassInput.value = 'text-dark';
            }
        }
    }

    createPresetSelector(presets, currentBlock) {
        const currentPresetId = currentBlock.settings?.preset_id || '';
        
        let options = '<option value="">-- Без пресета (стандартный шаблон) --</option>';
        
        presets.forEach(preset => {
            const selected = currentPresetId == preset.id ? 'selected' : '';
            const template = this.escapeHtml(preset.preset_template || '');
            const name = this.escapeHtml(preset.preset_name);
            
            options += `
                <option value="${preset.id}" 
                        ${selected}
                        data-template="${template}"
                        data-name="${name}">
                    ${name}
                </option>`;
        });
        
        return `
        <div class="mb-4">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <label class="form-label mb-0">Выберите пресет</label>
                <button type="button" class="btn btn-sm btn-outline-secondary" id="reset-preset">
                    <i class="bi bi-arrow-counterclockwise"></i> Сбросить
                </button>
            </div>
            <select class="form-select block-preset-selector" id="block-preset-select">
                ${options}
            </select>
            <div class="form-text">
                Используйте сохраненные шаблоны для быстрой настройки внешнего вида блока
            </div>
        </div>`;
    }

    insertPresetSelectorIntoForm(html, presetSelector) {
        const formMatch = html.match(/<form[^>]*>([\s\S]*?)<\/form>/);
        if (!formMatch) {
            return presetSelector + html;
        }
        
        const formContent = formMatch[1];
        const newFormContent = presetSelector + formContent;
        const newHtml = html.replace(formContent, newFormContent);
        
        return newHtml;
    }

    initPresetHandlers(block, presets) {
        const presetSelect = document.getElementById('block-preset-select');
        const resetPresetBtn = document.getElementById('reset-preset');
        
        if (presetSelect) {
            presetSelect.addEventListener('change', (e) => {
                const selectedOption = e.target.options[e.target.selectedIndex];
                const presetId = e.target.value;
                
                if (presetId) {

                    if (!block.settings) block.settings = {};
                    block.settings.preset_id = presetId;

                    const template = selectedOption.getAttribute('data-template');
                    if (template && template.includes('text-danger')) {
                        const customClassInput = document.querySelector('[name="settings[custom_class]"]');
                        if (customClassInput) {
                            customClassInput.value = 'text-danger';
                        }
                    } else if (template && template.includes('text-dark')) {
                        const customClassInput = document.querySelector('[name="settings[custom_class]"]');
                        if (customClassInput) {
                            customClassInput.value = 'text-dark';
                        }
                    }
                } else {
                    if (block.settings) {
                        delete block.settings.preset_id;
                    }
                }
            });
            
            if (presetSelect.value) {
                presetSelect.dispatchEvent(new Event('change'));
            }
        }
        
        if (resetPresetBtn) {
            resetPresetBtn.addEventListener('click', () => {
                if (presetSelect) {
                    presetSelect.value = '';
                    presetSelect.dispatchEvent(new Event('change'));
                }
            });
        }
    }
    
    showPresetPreview(template, container, contentElement) {
        if (!container || !contentElement) return;
        
        const previewText = template.length > 500 ? 
            template.substring(0, 500) + '...' : 
            template;
        
        contentElement.textContent = previewText;
        container.style.display = 'block';
    }

    hidePresetPreview(container) {
        if (container) {
            container.style.display = 'none';
        }
    }

    escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    reinitializeAllBlocks() {
        if (typeof ListBlockAdmin !== 'undefined' && typeof ListBlockAdmin.reinitializeAll === 'function') {
            ListBlockAdmin.reinitializeAll();
        }
    }

    initTabs(container) {
        const tabTriggers = container.querySelectorAll('[data-bs-toggle="tab"]');
        tabTriggers.forEach(trigger => {
            trigger.addEventListener('click', (e) => {
                e.preventDefault();
                const tab = new bootstrap.Tab(trigger);
                tab.show();
            });
        });
    }

    bindSaveHandler(blockId, modal) {
        const saveButton = document.getElementById('save-post-block-settings');
        if (saveButton) {
            saveButton.onclick = () => {
                this.saveBlockSettings(blockId, modal);
            };
        }
    }

    async saveBlockSettings(blockId, modal) {
        let saveButton = null;
        let originalText = '';
        
        try {
            const block = this.blocksData.find(b => b.id === blockId);
            if (!block) throw new Error('Блок не найден');
            
            const form = modal.querySelector('form');
            if (!form) throw new Error('Форма не найдена');
            
            const formData = new FormData(form);
            const contentData = {};
            const settingsData = {};
            
            for (const [key, value] of formData.entries()) {
                if (key.startsWith('content[')) {
                    const fieldName = key.match(/content\[(.*?)\]/)?.[1];
                    if (fieldName) {
                        contentData[fieldName] = value;
                    }
                } else if (key.startsWith('settings[')) {
                    const fieldName = key.match(/settings\[(.*?)\]/)?.[1];
                    if (fieldName) {
                        settingsData[fieldName] = value;
                    }
                }
            }
            
            const presetSelect = document.getElementById('block-preset-select');
            if (presetSelect && presetSelect.value) {
                settingsData.preset_id = presetSelect.value;
                const selectedOption = presetSelect.options[presetSelect.selectedIndex];
                settingsData.preset_name = selectedOption.getAttribute('data-name') || '';
            }
            
            block.content = { ...block.content, ...contentData };
            block.settings = { ...block.settings, ...settingsData };
            
            const uploadFormData = new FormData(form);
            uploadFormData.append('block_id', blockId);
            uploadFormData.append('block_type', block.type);
            uploadFormData.append('action', 'save_settings');
            uploadFormData.append('content_json', JSON.stringify(block.content));
            uploadFormData.append('settings_json', JSON.stringify(block.settings));
            
            saveButton = modal.querySelector('#save-post-block-settings');
            if (saveButton) {
                originalText = saveButton.innerHTML;
                saveButton.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Сохранение...';
                saveButton.disabled = true;
            }
            
            const response = await fetch(`${window.ADMIN_URL}/post-blocks/upload-block-files`, {
                method: 'POST',
                body: uploadFormData
            });
            
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                const text = await response.text();
                console.error('Non-JSON response:', text.substring(0, 200));
                throw new Error('Сервер вернул не JSON');
            }
            
            const data = await response.json();
            
            if (data.success) {
                if (data.block_data) {
                    block.content = data.block_data.content || block.content;
                    block.settings = data.block_data.settings || block.settings;
                }
                
                const blockElement = document.querySelector(`.post-block-item[data-block-id="${blockId}"]`);
                if (blockElement) {
                    const hasPreset = block.settings && block.settings.preset_id;
                    const typeInfo = blockElement.querySelector('.block-type-info');
                    const existingBadge = typeInfo.querySelector('.badge .bg-success .text-dark');
                    
                    if (hasPreset && block.settings.preset_name) {
                        blockElement.classList.add('has-preset');
                        
                        if (!existingBadge) {
                            const badge = document.createElement('span');
                            badge.className = 'badge bg-success text-dark';
                            badge.title = `Используется пресет: ${this.escapeHtml(block.settings.preset_name)}`;
                            badge.innerHTML = `<i class="bi bi-gear me-1"></i>${this.escapeHtml(block.settings.preset_name)}`;
                            typeInfo.appendChild(badge);
                        } else {
                            existingBadge.title = `Используется пресет: ${this.escapeHtml(block.settings.preset_name)}`;
                            existingBadge.innerHTML = `<i class="bi bi-gear me-1"></i>${this.escapeHtml(block.settings.preset_name)}`;
                        }
                    } else {
                        blockElement.classList.remove('has-preset');
                        if (existingBadge) {
                            existingBadge.remove();
                        }
                    }
                }
                
                this.closeCurrentModal();
                await this.updateBlockPreview(blockId);
                this.updateHiddenField();
                this.showNotification('Настройки блока сохранены', 'success');
            } else {
                throw new Error(data.message || 'Ошибка сохранения');
            }
        } catch (error) {
            console.error('Save error:', error);
            this.showNotification('Ошибка сохранения: ' + error.message, 'error');
        } finally {
            if (saveButton) {
                saveButton.innerHTML = originalText;
                saveButton.disabled = false;
            }
        }
    }

    async updateBlockPreview(blockId) {
        const block = this.blocksData.find(b => b.id === blockId);
        if (!block) return;
        
        await this.loadBlockPreview(block.id, block.type, block.content, block.settings);
    }

    showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show`;
        notification.style.cssText = 'position: fixed; top: 20px; right: 20px; z-index: 1060; min-width: 300px;';
        notification.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(notification);
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 5000);
    }

    closeCurrentModal() {
        if (this.currentModal) {
            const bsModal = bootstrap.Modal.getInstance(this.currentModal);
            if (bsModal) {
                bsModal.hide();
            }
            this.currentModal.remove();
            this.currentModal = null;
            const backdrop = document.querySelector('.modal-backdrop');
            if (backdrop) {
                backdrop.remove();
            }
            
            document.body.classList.remove('modal-open');
            document.body.style.overflow = '';
            document.body.style.paddingRight = '';
        }
    }

    async renderBlocks() {
        this.blocksData.sort((a, b) => a.order - b.order);
        
        if (this.blocksData.length === 0) {
            this.blocksContainer.innerHTML = `
                <div class="text-center text-muted py-5 empty-state">
                    <i class="bi bi-inbox display-4 d-block mb-3 opacity-50"></i>
                    <p class="mb-1">Нет добавленных блоков</p>
                    <small class="text-muted">Добавьте блоки из панели выше для создания контента</small>
                </div>
            `;
            return;
        }

        let html = '';
        
        for (const [index, block] of this.blocksData.entries()) {
            const blockInfo = window.availablePostBlocks?.[block.type];
            if (!blockInfo) continue;

            html += await this.renderBlockWithPreview(block, blockInfo, index);
        }

        this.blocksContainer.innerHTML = html;
        await this.loadAllPreviews();
        
        this.initActionTooltips();
    }

    async renderBlockWithPreview(block, blockInfo, index) {
        const hasPreset = block.settings && block.settings.preset_id && block.settings.preset_name;
        const presetBadge = hasPreset ? 
            `<span class="badge bg-success text-dark" title="Используется пресет: ${this.escapeHtml(block.settings.preset_name)}">
                <i class="bi bi-gear me-1"></i>
                ${this.escapeHtml(block.settings.preset_name)}
            </span>` : '';

        return `
        <div class="post-block-item ${hasPreset ? 'has-preset' : ''}" data-block-id="${block.id}" data-block-type="${block.type}">
            <div class="post-block-item-inner">
                <div class="block-header">
                    <div class="block-order">${index + 1}</div>
                    <div class="block-type-info">
                        <i class="${blockInfo.icon}"></i>
                        <span class="block-type-name">${blockInfo.name}</span>
                        ${presetBadge}
                    </div>
                    <div class="block-actions">
                        <button type="button" class="btn btn-sm btn-outline-primary edit-post-block" 
                                title="Редактировать">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-danger remove-post-block" 
                                title="Удалить">
                            <i class="bi bi-trash"></i>
                        </button>
                        <span class="drag-handle" title="Перетащите для изменения порядка">
                            <i class="bi bi-grip-vertical"></i>
                        </span>
                    </div>
                </div>
                <div class="block-preview-container" id="preview-${block.id}">
                    <div class="preview-loading">
                        <div class="spinner-border spinner-border-sm" role="status">
                            <span class="visually-hidden">Загрузка...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>`;
    }

    async loadAllPreviews() {
        const previewPromises = this.blocksData.map(block => {
            return this.loadBlockPreview(
                block.id, 
                block.type, 
                block.content || {},
                block.settings || {}
            );
        });
        
        await Promise.all(previewPromises);
    }

    async loadBlockPreview(blockId, blockType, content, settings) {
        const previewContainer = document.getElementById(`preview-${blockId}`);
        if (!previewContainer) return;
        
        try {
            const block = this.blocksData.find(b => b.id === blockId);
            
            const normalizedContent = this.normalizeContentData(blockType, content);
            const normalizedSettings = Array.isArray(settings) && settings.length === 0 ? {} : settings;
            
            previewContainer.innerHTML = `
                <div class="preview-loading">
                    <div class="loading-content">
                        <div class="spinner-border spinner-border-sm text-primary" role="status">
                            <span class="visually-hidden">Загрузка превью...</span>
                        </div>
                    </div>
                </div>
            `;
            
            const response = await fetch(`${window.ADMIN_URL}/post-blocks/get-preview`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    block_id: blockId,
                    block_type: blockType,
                    content: normalizedContent,
                    settings: normalizedSettings
                })
            });
            
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data = await response.json();
            
            if (data.success && data.html) {
                previewContainer.innerHTML = data.html;
                previewContainer.classList.add('preview-loaded');
                
                this.initPreviewActions(blockId);
            } else {
                previewContainer.innerHTML = this.renderErrorPreview(blockType, data.message || 'Ошибка загрузки');
            }
        } catch (error) {
            previewContainer.innerHTML = this.renderErrorPreview(blockType, error.message);
        }
    }

    normalizeContentData(blockType, content) {
        if (!content) return {};
        
        const normalized = { ...content };
        
        switch (blockType) {
            case 'TextBlock':
                if (content.content && !content.text) {
                    normalized.text = content.content;
                }
                break;
                
            case 'ImageBlock':
                if (content.image_url && !content.url) {
                    normalized.url = content.image_url;
                }
                if (content.alt_text && !content.alt) {
                    normalized.alt = content.alt_text;
                }
                break;
                
            case 'HeaderBlock':
                break;
                
            default:
                if (!normalized.text) {
                    for (const [key, value] of Object.entries(content)) {
                        if (typeof value === 'string' && value.trim()) {
                            normalized.text = value;
                            break;
                        }
                    }
                }
        }
        
        return normalized;
    }

    initPreviewActions(blockId) {
        const editBtn = document.querySelector(`#preview-${blockId} .preview-edit-btn`);
        if (editBtn) {
            editBtn.onclick = (e) => {
                e.preventDefault();
                e.stopPropagation();
                this.editBlock(blockId);
            };
        }
        
        const addContentBtn = document.querySelector(`#preview-${blockId} .btn-outline-primary`);
        if (addContentBtn && addContentBtn.textContent.includes('Добавить')) {
            addContentBtn.onclick = (e) => {
                e.preventDefault();
                e.stopPropagation();
                this.editBlock(blockId);
            };
        }
    }

    renderErrorPreview(blockType, error) {
        return `
        <div class="alert alert-danger small mb-0">
            <div class="d-flex align-items-center">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <div>
                    <strong>Ошибка превью</strong>
                    <div class="small">${this.escapeHtml(error)}</div>
                </div>
            </div>
        </div>`;
    }

    initActionTooltips() {
        const tooltipTriggerList = [].slice.call(
            this.blocksContainer.querySelectorAll('[title]')
        );
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }

    getBlockPreview(block, blockInfo) {
        const content = block.content;
        if (block.settings && block.settings.preset_name) {
            const presetInfo = `<span class="badge bg-info me-2" title="Используется пресет">📋 ${block.settings.preset_name}</span>`;
            
            if (content.text) {
                return presetInfo + `"${content.text.substring(0, 5000)}${content.text.length > 5000 ? '...' : ''}"`;
            }
            
            if (content.content) {
                return presetInfo + `"${content.content.substring(0, 5000)}${content.content.length > 5000 ? '...' : ''}"`;
            }
            
            return presetInfo + (blockInfo.description || 'Блок с пресетом');
        }

        if (content.text) {
            return `"${content.text.substring(0, 5000)}${content.text.length > 5000 ? '...' : ''}"`;
        }
        
        if (content.content) {
            return `"${content.content.substring(0, 5000)}${content.content.length > 5000 ? '...' : ''}"`;
        }
        
        if (content.items && Array.isArray(content.items)) {
            const itemsText = content.items.map(item => item.text).filter(Boolean).join(', ');
            return `📋 ${itemsText.substring(0, 5000)}${itemsText.length > 5000 ? '...' : ''}`;
        }
        
        if (content.url && block.type.includes('Image')) {
            return `🖼️ ${content.alt || 'Изображение'}`;
        }
        
        return blockInfo.description || 'Блок без содержимого';
    }

    normalizeBlockData(block) {
        const normalized = { ...block };
        
        if (normalized.content) {
            if (normalized.type === 'TextBlock' && normalized.content.content && !normalized.content.text) {
                normalized.content.text = normalized.content.content;
            }
            
            if (normalized.type === 'ImageBlock') {
                if (normalized.content.image_url && !normalized.content.url) {
                    normalized.content.url = normalized.content.image_url;
                }
                if (normalized.content.alt_text && !normalized.content.alt) {
                    normalized.content.alt = normalized.content.alt_text;
                }
            }
        }
        
        if (Array.isArray(normalized.settings) && normalized.settings.length === 0) {
            normalized.settings = {};
        }
        
        return normalized;
    }

    updateHiddenField() {
        const hiddenField = document.getElementById('post_blocks_data');
        if (hiddenField) {
            hiddenField.value = JSON.stringify(this.blocksData);
        }
    }

    getBlocksData() {
        return this.blocksData;
    }

    clearAllBlocks() {
        this.blocksData = [];
        this.renderBlocks();
        this.updateHiddenField();
    }

    initSortable() {
        if (!this.blocksContainer) return;

        this.sortable = new Sortable(this.blocksContainer, {
            animation: 150,
            ghostClass: 'sortable-ghost',
            chosenClass: 'sortable-chosen',
            dragClass: 'sortable-drag',
            handle: '.drag-handle',
            
            delay: 100,
            delayOnTouchOnly: true,
            touchStartThreshold: 3,
            
            onStart: (evt) => {
                evt.item.classList.add('sortable-active');
                document.body.classList.add('post-blocks-dragging');
            },
            
            onEnd: (evt) => {
                evt.item.classList.remove('sortable-active');
                document.body.classList.remove('post-blocks-dragging');
                this.updateBlocksOrder();
            },
            
            onUpdate: () => {
                this.updateBlocksOrder();
            }
        });
    }

    updateBlocksOrder() {
        const blockElements = this.blocksContainer.querySelectorAll('.post-block-item');
        
        blockElements.forEach((element, index) => {
            const blockId = element.getAttribute('data-block-id');
            const block = this.blocksData.find(b => b.id === blockId);
            
            if (block) {
                block.order = index;
                const orderElement = element.querySelector('.block-order');
                if (orderElement) {
                    orderElement.textContent = index + 1;
                }
            }
        });
        
        this.blocksData.sort((a, b) => a.order - b.order);
        
        this.updateHiddenField();
    }

}

class BlockReinitializer {
    static reinitializeAll() {
        if (typeof ListBlockAdmin !== 'undefined' && typeof ListBlockAdmin.reinitializeAll === 'function') {
            ListBlockAdmin.reinitializeAll();
        }
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const blocksContainer = document.getElementById('post-blocks-container');
    const blockButtons = document.getElementById('post-block-buttons');
    
    if (!blocksContainer || !blockButtons) {
        return;
    }
    
    if (!window.availablePostBlocks || Object.keys(window.availablePostBlocks).length === 0) {
        return;
    }
    
    try {
        window.postBlocksManager = new PostBlocksManager();
    } catch (error) {}
    
    document.addEventListener('show.bs.modal', function() {
        setTimeout(() => {
            BlockReinitializer.reinitializeAll();
        }, 100);
    });
});

document.addEventListener('DOMContentLoaded', function() {
    setTimeout(() => {
        if (window.postBlocksManager) {
            window.postBlocksManager.blocksData = window.postBlocksManager.blocksData.map(block => {
                return window.postBlocksManager.normalizeBlockData(block);
            });

            window.postBlocksManager.updateHiddenField();
            
            window.postBlocksManager.renderBlocks();
        }
    }, 1500);
});

if (typeof module !== 'undefined' && module.exports) {
    module.exports = { PostBlocksManager, BlockReinitializer };
}