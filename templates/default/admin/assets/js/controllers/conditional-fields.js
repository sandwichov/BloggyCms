class ConditionalFields {
    constructor(formSelector = 'form') {
        this.form = document.querySelector(formSelector);
        this.conditionalFields = [];
        this.dependentGroups = new Map();
        this.init();
    }
    
    init() {
        if (!this.form) return;
        this.findConditionalFields();
        this.findDependentGroups();
        this.bindEvents();
        this.evaluateAllConditions();
        this.addStyles();
    }
    
    findConditionalFields() {
        this.conditionalFields = Array.from(
            document.querySelectorAll('[data-conditional="true"]')
        );
    }
    
    findDependentGroups() {
        const groups = document.querySelectorAll('.dependent-group-container');
        groups.forEach(group => {
            const fields = group.querySelectorAll('[data-conditional="true"]');
            if (fields.length > 0) {
                this.dependentGroups.set(group, fields);
            }
        });
    }
    
    bindEvents() {
        this.form.addEventListener('change', () => {
            this.evaluateAllConditions();
        });
        
        this.form.addEventListener('input', (e) => {
            if (e.target.type === 'text' || e.target.type === 'textarea' || e.target.type === 'number') {
                this.evaluateAllConditions();
            }
        });
    }
    
    evaluateAllConditions() {
        const formData = this.getFormData();
        
        this.conditionalFields.forEach(field => {
            const condition = field.dataset.condition;
            const isVisible = this.evaluateCondition(condition, formData);
            this.toggleField(field, isVisible);
        });
        
        this.checkGroupsVisibility();
    }
    
    checkGroupsVisibility() {
        this.dependentGroups.forEach((fields, group) => {
            let hasVisibleFields = false;
            
            fields.forEach(field => {
                if (!field.classList.contains('d-none') && !field.classList.contains('field-hidden')) {
                    hasVisibleFields = true;
                }
            });
            
            if (hasVisibleFields) {
                group.classList.remove('d-none');
                group.classList.remove('group-hidden');
            } else {
                group.classList.add('d-none');
                group.classList.add('group-hidden');
            }
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
        
        return data;
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
            field.classList.remove('field-hidden');
            field.classList.add('field-conditional-visible');
            
            const inputs = field.querySelectorAll('input, select, textarea');
            inputs.forEach(input => {
                input.disabled = false;
                input.removeAttribute('tabindex', '-1');
            });
        } else {
            field.classList.add('d-none');
            field.classList.add('field-hidden');
            field.classList.remove('field-conditional-visible');
            
            const inputs = field.querySelectorAll('input, select, textarea');
            inputs.forEach(input => {
                input.disabled = true;
                input.setAttribute('tabindex', '-1');
            });
        }
    }
    
    addStyles() {
        if (document.getElementById('conditional-fields-styles')) return;
        
        const style = document.createElement('style');
        style.id = 'conditional-fields-styles';
        style.textContent = `
            .field-conditional-visible {
                position: relative;
            }
            
            .dependent-group-container {
                position: relative;
                transition: opacity 0.2s ease;
            }
            
            .dependent-group-container.group-hidden,
            .dependent-group-container.d-none {
                display: none !important;
            }
            
            @keyframes fadeIn {
                from {
                    opacity: 0;
                    transform: translateY(-5px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
            
            .field-conditional-visible {
                animation: fadeIn 0.2s ease-out;
            }
        `;
        
        document.head.appendChild(style);
    }
}

document.addEventListener('DOMContentLoaded', () => {
    new ConditionalFields('form[method="post"]');
    new ConditionalFields('form[method="get"]');
});