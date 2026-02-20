class ConditionalFields {
    constructor(formSelector = 'form') {
        this.form = document.querySelector(formSelector);
        this.conditionalFields = [];
        this.init();
    }
    
    init() {
        if (!this.form) return;
        this.findConditionalFields();
        this.bindEvents();
        this.evaluateAllConditions();
    }
    
    findConditionalFields() {
        this.conditionalFields = Array.from(
            document.querySelectorAll('[data-conditional="true"]')
        );
    }
    
    bindEvents() {
        this.form.addEventListener('change', (e) => {
            this.evaluateAllConditions();
        });
        
        this.form.addEventListener('input', (e) => {
            if (e.target.type === 'text' || e.target.type === 'textarea' || e.target.type === 'number') {
                this.evaluateAllConditions();
            }
        });
        
        this.form.addEventListener('submit', (e) => {
            this.handleFormSubmit();
        });
    }
    
    evaluateAllConditions() {
        const formData = this.getFormData();
        
        this.conditionalFields.forEach(field => {
            const condition = field.dataset.condition;
            const isVisible = this.evaluateCondition(condition, formData);
            
            this.toggleField(field, isVisible);
        });
    }
    
    getFormData() {
        const formData = new FormData(this.form);
        const data = {};
        
        for (let [key, value] of formData.entries()) {
            if (key.startsWith('settings[')) {
                const match = key.match(/settings\[([^\]]+)\]/);
                if (match) {
                    data[match[1]] = this.normalizeValue(value);
                }
            } else {
                data[key] = this.normalizeValue(value);
            }
        }
        
        const checkboxes = this.form.querySelectorAll('input[type="checkbox"]');
        checkboxes.forEach(checkbox => {
            const name = this.getFieldName(checkbox);
            if (!(name in data)) {
                data[name] = false;
            }
        });
        
        const fileInputs = this.form.querySelectorAll('input[type="file"]');
        fileInputs.forEach(input => {
            const name = this.getFieldName(input);
            if (name.endsWith('_file')) {
                const baseName = name.replace('_file', '');
                if (input.files && input.files.length > 0) {
                    data[baseName] = true;
                }
                else {
                    const hiddenField = this.findHiddenFieldForFile(input, baseName);
                    if (hiddenField && hiddenField.value) {
                        data[baseName] = true;
                    } else {
                        data[baseName] = false;
                    }
                }
            }
        });

        const removeCheckboxes = this.form.querySelectorAll('input[type="checkbox"][name^="remove_"]');
        removeCheckboxes.forEach(checkbox => {
            const name = this.getFieldName(checkbox);
            if (name.startsWith('remove_')) {
                const baseName = name.replace('remove_', '');
                if (checkbox.checked) {
                    data[baseName] = false;
                }
            }
        });
        
        return data;
    }

    findHiddenFieldForFile(fileInput, baseName) {
        const container = fileInput.closest('.image-field') || fileInput.parentElement;
        if (container) {
            const hiddenField = container.querySelector(`input[type="hidden"][name="settings[${baseName}]"], 
                                                       input[type="hidden"][name="${baseName}"]`);
            return hiddenField;
        }
        return null;
    }
    
    getFieldName(input) {
        let name = input.name;
        if (name.startsWith('settings[')) {
            const match = name.match(/settings\[([^\]]+)\]/);
            return match ? match[1] : name;
        }
        return name;
    }
    
    normalizeValue(value) {
        if (value === 'true') return true;
        if (value === 'false') return false;
        if (value === '') return null;
        if (!isNaN(value) && value !== '') return parseFloat(value);
        return value;
    }
    
    evaluateCondition(condition, formData) {
        const equalityMatch = condition.match(/^field:(\w+)\s*([!=<>]+)\s*(.+)$/);
        if (equalityMatch) {
            const [, fieldName, operator, expectedValue] = equalityMatch;
            const actualValue = formData[fieldName];
            return this.compareValues(actualValue, operator, expectedValue);
        }
        
        const truthyMatch = condition.match(/^field:(\w+)$/);
        if (truthyMatch) {
            const [, fieldName] = truthyMatch;
            const actualValue = formData[fieldName];
            return !!actualValue;
        }
        
        const inMatch = condition.match(/^field:(\w+)\s+in\s+(.+)$/);
        if (inMatch) {
            const [, fieldName, valuesString] = inMatch;
            const expectedValues = valuesString.split(',').map(v => this.normalizeValue(v.trim()));
            const actualValue = formData[fieldName];
            return expectedValues.includes(actualValue);
        }
        
        return true;
    }
    
    compareValues(actual, operator, expected) {
        const normalizedExpected = this.normalizeValue(expected);
        const normalizedActual = this.normalizeValue(actual);
        
        switch (operator) {
            case '=':
            case '==':
                return normalizedActual == normalizedExpected;
            case '!=':
                return normalizedActual != normalizedExpected;
            case '>':
                return normalizedActual > normalizedExpected;
            case '<':
                return normalizedActual < normalizedExpected;
            case '>=':
                return normalizedActual >= normalizedExpected;
            case '<=':
                return normalizedActual <= normalizedExpected;
            default:
                return false;
        }
    }
    
    toggleField(field, isVisible) {
        if (isVisible) {
            field.classList.remove('d-none');
            const inputs = field.querySelectorAll('input, select, textarea');
            inputs.forEach(input => {
                input.disabled = false;
            });
        } else {
            field.classList.add('d-none');
        }
    }
    
    handleFormSubmit() {
        const formData = this.getFormData();
        
        this.conditionalFields.forEach(field => {
            const condition = field.dataset.condition;
            const isVisible = this.evaluateCondition(condition, formData);
            
            if (!isVisible) {
                const inputs = field.querySelectorAll('input, select, textarea');
                inputs.forEach(input => {
                    input.disabled = true;
                });
            }
        });
        
        setTimeout(() => {}, 100);
    }
}

document.addEventListener('DOMContentLoaded', () => {
    new ConditionalFields('form[method="post"]');
    new ConditionalFields('form[method="get"]');
});