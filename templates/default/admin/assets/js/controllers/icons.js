function filterIcons(query) {
    query = query.toLowerCase();
    document.querySelectorAll(".icon-item").forEach(item => {
        const iconId = item.getAttribute("data-icon-id").toLowerCase();
        item.style.display = iconId.includes(query) ? "" : "none";
    });
}

function copyIconCode(code) {
    const textarea = document.createElement('textarea');
    textarea.value = code;
    textarea.style.position = 'fixed';
    textarea.style.opacity = '0';
    textarea.style.pointerEvents = 'none';
    document.body.appendChild(textarea);
    textarea.select();
    textarea.setSelectionRange(0, 99999);
    
    try {
        document.execCommand('copy');
        if (window.notificationSystem) {
            window.notificationSystem.showNotification('Код иконки скопирован в буфер обмена', 'success');
        }
    } catch (err) {
        console.error('Ошибка копирования:', err);
        if (window.notificationSystem) {
            window.notificationSystem.showNotification('Не удалось скопировать код', 'danger');
        }
    }
    
    document.body.removeChild(textarea);
}

document.addEventListener("DOMContentLoaded", function() {
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    if (tooltipTriggerList.length) {
        const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
    }

    const activeTab = localStorage.getItem("activeIconTab");
    if (activeTab) {
        const tabElement = document.querySelector(activeTab);
        if (tabElement) {
            const tab = new bootstrap.Tab(tabElement);
            tab.show();
        }
    }
    
    document.querySelectorAll('#iconTabs button').forEach(button => {
        button.addEventListener("click", function() {
            localStorage.setItem("activeIconTab", `#${this.id}`);
        });
    });
});