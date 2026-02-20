class DocsController {
    constructor() {
        this.initTooltips();
        this.initSearch();
        this.initFilters();
    }

    initTooltips() {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    }

    initSearch() {
        const searchInput = document.querySelector('input[name="search"]');
        if (!searchInput) return;

        let debounceTimer;
        searchInput.addEventListener('input', (e) => {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                this.performSearch(e.target.value);
            }, 300);
        });

        searchInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                this.submitSearchForm();
            }
        });
    }

    initFilters() {
        const filterSelects = document.querySelectorAll('select[name="category"], select[name="status"]');
        filterSelects.forEach(select => {
            select.addEventListener('change', () => {
                this.submitSearchForm();
            });
        });
    }

    performSearch(query) {}

    submitSearchForm() {
        const form = document.querySelector('form[method="get"]');
        if (form) {
            form.submit();
        }
    }

    updateCategoryOrder(order) {
        return fetch(`${ADMIN_URL}/docs/categories/reorder`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(order)
        })
        .then(response => response.json());
    }

    quickToggleStatus(articleId) {
        if (!confirm('Изменить статус статьи?')) return;

        fetch(`${ADMIN_URL}/docs/toggle-status/${articleId}`, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (response.ok) {
                location.reload();
            } else {
                alert('Ошибка при изменении статуса');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Ошибка при изменении статуса');
        });
    }
}

document.addEventListener('DOMContentLoaded', () => {
    window.docsController = new DocsController();
});