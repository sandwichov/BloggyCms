(function() {
    window.initPickrField = function(input) {
        if (!input || input.classList.contains('initialized')) return;
        
        try {
            let options = {};
            if (input.dataset.pickrOptions) {
                try {
                    options = JSON.parse(input.dataset.pickrOptions);
                    console.log('Pickr options for', input.id, options);
                } catch (e) {
                    console.error('Failed to parse Pickr options:', e);
                }
            }
            
            if (!input.parentNode) {
                setTimeout(() => initPickrField(input), 100);
                return;
            }
            
            let wrapper = input.closest('.input-group');
            if (!wrapper) {
                wrapper = document.createElement('div');
                wrapper.className = 'input-group';
                wrapper.style.width = 'auto';
                input.parentNode.insertBefore(wrapper, input);
                wrapper.appendChild(input);
            }
            
            const oldWrapper = input.closest('.pickr-color-picker-wrapper');
            if (oldWrapper && oldWrapper !== wrapper.parentNode) {
                oldWrapper.remove();
            }
            
            input.style.width = '130px';
            input.style.borderTopRightRadius = '0';
            input.style.borderBottomRightRadius = '0';
            
            let button = wrapper.querySelector('.pickr-trigger');
            if (!button) {
                button = document.createElement('button');
                button.type = 'button';
                button.className = 'btn pickr-trigger';
                button.innerHTML = '<svg class="icon icon-palette" width="20" height="20" style="fill: currentColor"><use href="' + (window.pickrConfig?.iconsPath || '/templates/default/admin/icons/') + 'bs.svg#palette"></use></svg>';
                button.style.padding = '0.375rem 0.75rem';
                button.style.display = 'inline-flex';
                button.style.alignItems = 'center';
                button.style.justifyContent = 'center';
                button.style.borderTopLeftRadius = '0';
                button.style.borderBottomLeftRadius = '0';
                button.style.borderLeft = 'none';
                
                wrapper.appendChild(button);
            }
            
            setTimeout(() => {
                try {
                    if (!button.isConnected) {
                        console.warn('Pickr: button not in DOM');
                        return;
                    }
                    
                    const pickr = Pickr.create({
                        el: button,
                        theme: 'monolith',
                        default: input.value || '#000000',
                        position: 'bottom-start',
                        useAsButton: true,
                        container: 'body',
                        components: {
                            preview: true,
                            opacity: options.showAlpha || false,
                            hue: true,
                            interaction: {
                                hex: options.showInput !== false,
                                rgba: options.showAlpha || false,
                                input: options.showInput !== false,
                                clear: options.allowEmpty !== false,
                                save: true
                            }
                        },
                        swatches: options.palette || []
                    });
                    
                    input._pickr = pickr;
                    input.classList.add('initialized');
                    
                    function updateButtonColor(color) {
                        if (color) {
                            const hexColor = color.toHEXA().toString();
                            const r = parseInt(hexColor.slice(1,3), 16);
                            const g = parseInt(hexColor.slice(3,5), 16);
                            const b = parseInt(hexColor.slice(5,7), 16);
                            const brightness = (r * 299 + g * 587 + b * 114) / 1000;
                            
                            button.style.backgroundColor = hexColor;
                            button.style.borderColor = hexColor;
                            
                            if (brightness > 128) {
                                button.style.color = '#212529';
                            } else {
                                button.style.color = '#fff';
                            }
                        } else {
                            button.style.backgroundColor = '';
                            button.style.borderColor = '';
                            button.style.color = '';
                        }
                    }
                    
                    input.addEventListener('click', function(e) {
                        e.preventDefault();
                        if (pickr) pickr.show();
                    });
                    
                    pickr.on('save', function(color) {
                        if (color) {
                            input.value = color.toHEXA().toString();
                            updateButtonColor(color);
                        } else {
                            input.value = '';
                            updateButtonColor(null);
                        }
                        
                        input.dispatchEvent(new Event('change', { bubbles: true }));
                        input.dispatchEvent(new Event('input', { bubbles: true }));
                        pickr.hide();
                    });
                    
                    pickr.on('change', function(color) {
                        if (color) {
                            input.value = color.toHEXA().toString();
                            updateButtonColor(color);
                            input.dispatchEvent(new Event('input', { bubbles: true }));
                        }
                    });
                    
                    pickr.on('clear', function() {
                        input.value = '';
                        updateButtonColor(null);
                        input.dispatchEvent(new Event('change', { bubbles: true }));
                        input.dispatchEvent(new Event('input', { bubbles: true }));
                    });
                    
                    if (input.value) {
                        const tempColor = { toHEXA: () => ({ toString: () => input.value }) };
                        updateButtonColor(tempColor);
                    }
                    
                } catch (error) {
                    console.error('Pickr creation error:', error);
                }
            }, 50);
            
        } catch (error) {
            console.error('Pickr init error:', error);
        }
    };

    window.initAllPickrFields = function() {
        if (typeof Pickr === 'undefined') {
            setTimeout(window.initAllPickrFields, 100);
            return;
        }
        
        const inputs = document.querySelectorAll('.pickr-color-picker:not(.initialized)');
        if (inputs.length === 0) return;
        
        inputs.forEach(function(input) {
            setTimeout(() => {
                window.initPickrField(input);
            }, 10);
        });
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', window.initAllPickrFields);
    } else {
        setTimeout(window.initAllPickrFields, 100);
    }
    
    window.addEventListener('load', function() {
        setTimeout(window.initAllPickrFields, 200);
    });
    
})();