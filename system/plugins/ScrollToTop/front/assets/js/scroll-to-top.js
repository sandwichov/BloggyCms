const ScrollToTop = {
    init(options = {}) {
        this.button = document.getElementById('scroll-to-top');
        this.showAfter = options.showAfter || 300;
        this.animationSpeed = options.animationSpeed || 800;
        
        if (this.button) {
            window.addEventListener('scroll', () => this.toggleButton());
            this.button.addEventListener('click', (e) => this.scrollToTop(e));
        }
    },
    
    toggleButton() {
        if (window.pageYOffset > this.showAfter) {
            this.button.classList.add('visible');
        } else {
            this.button.classList.remove('visible');
        }
    },
    
    scrollToTop(e) {
        e.preventDefault();
        
        const start = window.pageYOffset;
        const startTime = performance.now();
        
        const animate = (currentTime) => {
            const timeElapsed = currentTime - startTime;
            const progress = Math.min(timeElapsed / this.animationSpeed, 1);
            
            window.scrollTo(0, start * (1 - this.easeInOutQuad(progress)));
            
            if (progress < 1) {
                requestAnimationFrame(animate);
            }
        };
        
        requestAnimationFrame(animate);
    },
    
    easeInOutQuad(t) {
        return t < 0.5 ? 2 * t * t : -1 + (4 - 2 * t) * t;
    }
};