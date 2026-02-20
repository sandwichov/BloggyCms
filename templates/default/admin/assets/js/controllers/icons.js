function filterIcons(query) {
    query = query.toLowerCase();
    document.querySelectorAll(".icon-item").forEach(item => {
        const iconId = item.getAttribute("data-icon-id").toLowerCase();
        item.style.display = iconId.includes(query) ? "" : "none";
    });
}

document.addEventListener("DOMContentLoaded", function() {
    const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
    const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));

    const activeTab = localStorage.getItem("activeIconTab");
    if (activeTab) {
        const tab = new bootstrap.Tab(document.querySelector(activeTab));
        tab.show();
    }
});

document.querySelectorAll('#iconTabs button').forEach(button => {
    button.addEventListener("click", function() {
        localStorage.setItem("activeIconTab", `#${this.id}`);
    });
});