class CookieConsent {
    static init(options) {
        if (this.getConsentStatus()) {
            return;
        }
        
        this.showConsentBar(options);
    }

    static showConsentBar(options) {
        const bar = document.getElementById('cookie-consent-bar');
        if (!bar) return;

        bar.classList.add(options.position || 'bottom');
        bar.classList.add(options.theme || 'light');
        
        bar.style.display = 'flex';
        setTimeout(() => {
            bar.style.opacity = '1';
            bar.style.transform = 'translateY(0)';
        }, 100);

        document.querySelector('.cookie-btn-accept')?.addEventListener('click', () => {
            this.setConsent(true, options);
            this.hideConsentBar(bar);
        });

        document.querySelector('.cookie-btn-reject')?.addEventListener('click', () => {
            this.setConsent(false, options);
            this.hideConsentBar(bar);
        });
    }

    static hideConsentBar(bar) {
        bar.style.opacity = '0';
        setTimeout(() => {
            bar.style.display = 'none';
        }, 300);
    }

    static setConsent(accepted, options) {
        const expiryDate = new Date();
        expiryDate.setDate(expiryDate.getDate() + (options.expiryDays || 30));
        
        document.cookie = `cookie_consent=${accepted ? 'accepted' : 'rejected'}; expires=${expiryDate.toUTCString()}; path=/; SameSite=Lax`;
        
        if (accepted) {
            this.loadAcceptedScripts(options);
        }
    }

    static getConsentStatus() {
        const cookies = document.cookie.split(';');
        for (let cookie of cookies) {
            const [name, value] = cookie.trim().split('=');
            if (name === 'cookie_consent') {
                return value;
            }
        }
        return null;
    }

    static loadAcceptedScripts(options) {
        if (options.enableAnalytics) {
        }
        
        if (options.enableMarketing) {
        }
    }
}