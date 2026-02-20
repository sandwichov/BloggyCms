document.addEventListener('DOMContentLoaded', function() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    const confirmLinks = document.querySelectorAll('a[onclick*="confirm"]');
    confirmLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            const confirmText = this.getAttribute('onclick').match(/confirm\('([^']+)'/)?.[1];
            if (confirmText && !confirm(confirmText)) {
                e.preventDefault();
            }
        });
    });
});