document.addEventListener('DOMContentLoaded', function() {
    
    const userDropdowns = document.querySelectorAll('.tg-user-dropdown');
    
    userDropdowns.forEach(dropdown => {
        const btn = dropdown.querySelector('.tg-user-btn');
        const menu = dropdown.querySelector('.tg-user-menu');
        
        if (!btn || !menu) return;
        
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            document.querySelectorAll('.tg-user-dropdown.tg-open').forEach(d => {
                if (d !== dropdown) {
                    d.classList.remove('tg-open');
                }
            });
            
            dropdown.classList.toggle('tg-open');
        });
        
        document.addEventListener('click', function(e) {
            if (!dropdown.contains(e.target)) {
                dropdown.classList.remove('tg-open');
            }
        });
    });
    
    const profileParents = document.querySelectorAll('.tg-profile-parent');
    
    profileParents.forEach(parent => {
        parent.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const parentItem = this.closest('.tg-profile-item');
            const wasOpen = parentItem.classList.contains('tg-open');
            
            const parentContainer = parentItem.parentElement;
            if (parentContainer) {
                parentContainer.querySelectorAll(':scope > .tg-profile-item.tg-open').forEach(item => {
                    if (item !== parentItem) {
                        item.classList.remove('tg-open');
                    }
                });
            }
            
            parentItem.classList.toggle('tg-open');
        });
    });
    
    const mobileToggle = document.querySelector('.tg-mobile-toggle');
    if (mobileToggle) {
        const menu = document.querySelector('.tg-nav .tg-menu');
        
        mobileToggle.addEventListener('click', function() {
            menu.classList.toggle('tg-active');
            this.classList.toggle('tg-active');
            
            if (menu.classList.contains('tg-active')) {
                document.body.style.overflow = 'hidden';
            } else {
                document.body.style.overflow = '';
                menu.querySelectorAll('.tg-menu-item.tg-open').forEach(item => {
                    item.classList.remove('tg-open');
                });
            }
        });
        
        document.addEventListener('click', function(e) {
            if (window.innerWidth <= 768 && 
                menu.classList.contains('tg-active') && 
                !menu.contains(e.target) && 
                !mobileToggle.contains(e.target)) {
                menu.classList.remove('tg-active');
                mobileToggle.classList.remove('tg-active');
                document.body.style.overflow = '';
            }
        });
    }

    window.addEventListener('resize', function() {
        if (window.innerWidth > 768) {
            const menu = document.querySelector('.tg-nav .tg-menu.tg-active');
            const toggle = document.querySelector('.tg-mobile-toggle.tg-active');
            
            if (menu) {
                menu.classList.remove('tg-active');
                document.body.style.overflow = '';
            }
            if (toggle) {
                toggle.classList.remove('tg-active');
            }
        }
    });
});