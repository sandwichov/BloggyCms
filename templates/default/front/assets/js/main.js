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

document.addEventListener('DOMContentLoaded', function() {
    const showMoreButtons = document.querySelectorAll('.tg-show-more-btn');
    
    showMoreButtons.forEach(button => {
        button.addEventListener('click', function() {
            const month = this.dataset.month;
            const monthContainer = document.querySelector(`.tg-month-posts[data-month="${month}"]`);
            
            if (monthContainer) {
                const hiddenPosts = monthContainer.querySelectorAll('.tg-archive-post-item.tg-hidden');
                hiddenPosts.forEach(post => {
                    post.classList.remove('tg-hidden');
                });
                
                this.style.display = 'none';
            }
        });
    });
    
    document.querySelectorAll('.tg-month-posts').forEach(container => {
        const posts = container.querySelectorAll('.tg-archive-post-item');
        if (posts.length > 3) {
            for (let i = 3; i < posts.length; i++) {
                posts[i].classList.add('tg-hidden');
            }
        }
    });
});

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('tg-register-form');
    if (!form) return;
    
    const usernameInput = document.getElementById('username');
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const confirmInput = document.getElementById('password_confirm');
    const usernameStatus = document.getElementById('username-status');
    const emailStatus = document.getElementById('email-status');
    const passwordStrength = document.getElementById('password-strength');
    const passwordMatch = document.getElementById('password-match');
    
    function checkUsername() {
        const username = usernameInput.value;
        
        if (username.length === 0) {
            usernameStatus.innerHTML = '';
            return;
        }
        
        if (username.length < 3) {
            usernameStatus.innerHTML = '<span class="tg-status-error">Too short</span>';
            return;
        }
        
        if (!/^[a-zA-Z0-9_]+$/.test(username)) {
            usernameStatus.innerHTML = '<span class="tg-status-error">Invalid characters</span>';
            return;
        }
        
        usernameStatus.innerHTML = '<span class="tg-status-valid">Available</span>';
    }
    
    function checkEmail() {
        const email = emailInput.value;
        
        if (email.length === 0) {
            emailStatus.innerHTML = '';
            return;
        }
        
        if (!email.includes('@') || !email.includes('.')) {
            emailStatus.innerHTML = '<span class="tg-status-error">Invalid format</span>';
            return;
        }
        
        emailStatus.innerHTML = '<span class="tg-status-valid">Valid</span>';
    }
    
    function checkPasswordStrength() {
        const password = passwordInput.value;
        const reqLength = document.getElementById('req-length');
        
        if (password.length === 0) {
            passwordStrength.innerHTML = '';
            reqLength.classList.remove('valid', 'invalid');
            return;
        }
        
        if (password.length >= 6) {
            reqLength.classList.add('valid');
            reqLength.classList.remove('invalid');
            reqLength.querySelector('svg').style.color = '#31b131';
        } else {
            reqLength.classList.add('invalid');
            reqLength.classList.remove('valid');
            reqLength.querySelector('svg').style.color = '#dc3545';
        }
        
        let strength = 'weak';
        if (password.length >= 8) strength = 'medium';
        if (password.length >= 10 && /[A-Z]/.test(password) && /[0-9]/.test(password)) strength = 'strong';
        
        const strengthText = strength === 'weak' ? 'Weak' : strength === 'medium' ? 'Medium' : 'Strong';
        const strengthColor = strength === 'weak' ? '#dc3545' : strength === 'medium' ? '#f1c40f' : '#31b131';
        
        passwordStrength.innerHTML = `<span class="tg-status" style="color: ${strengthColor};">${strengthText}</span>`;
    }
    
    function checkPasswordMatch() {
        const password = passwordInput.value;
        const confirm = confirmInput.value;
        
        if (confirm.length === 0) {
            passwordMatch.innerHTML = '';
            return;
        }
        
        if (password === confirm) {
            passwordMatch.innerHTML = '<span class="tg-status-valid">✓ Match</span>';
        } else {
            passwordMatch.innerHTML = '<span class="tg-status-error">✗ Don\'t match</span>';
        }
    }
    
    function togglePassword(inputId, buttonId) {
        const input = document.getElementById(inputId);
        const button = document.getElementById(buttonId);
        
        button.addEventListener('click', function() {
            if (input.type === 'password') {
                input.type = 'text';
                button.innerHTML = '<svg class="icon icon-eye-slash" width="16" height="16" style="fill: currentColor"><use href="/templates/default/admin/icons/bs.svg#eye-slash"></use></svg>';
            } else {
                input.type = 'password';
                button.innerHTML = '<svg class="icon icon-eye" width="16" height="16" style="fill: currentColor"><use href="/templates/default/admin/icons/bs.svg#eye"></use></svg>';
            }
        });
    }
    
    if (usernameInput) {
        usernameInput.addEventListener('input', checkUsername);
        usernameInput.addEventListener('blur', checkUsername);
    }
    
    if (emailInput) {
        emailInput.addEventListener('input', checkEmail);
        emailInput.addEventListener('blur', checkEmail);
    }
    
    if (passwordInput) {
        passwordInput.addEventListener('input', function() {
            checkPasswordStrength();
            checkPasswordMatch();
        });
    }
    
    if (confirmInput) {
        confirmInput.addEventListener('input', checkPasswordMatch);
    }
    
    togglePassword('password', 'toggle-password');
    togglePassword('password_confirm', 'toggle-confirm-password');
    
    if (form) {
        form.addEventListener('submit', function(e) {
            const username = usernameInput.value;
            const password = passwordInput.value;
            const confirm = confirmInput.value;
            const terms = document.getElementById('terms').checked;
            
            let isValid = true;
            let errorMessage = '';
            
            if (username.length < 3) {
                isValid = false;
                errorMessage = 'Username must be at least 3 characters';
            } else if (password.length < 6) {
                isValid = false;
                errorMessage = 'Password must be at least 6 characters';
            } else if (password !== confirm) {
                isValid = false;
                errorMessage = 'Passwords do not match';
            } else if (!terms) {
                isValid = false;
                errorMessage = 'You must agree to the Terms of Service';
            }
            
            if (!isValid) {
                e.preventDefault();
                alert(errorMessage);
                return false;
            }
            
            const submitBtn = document.getElementById('submit-btn');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<svg class="icon icon-hourglass tg-mr-1" width="16" height="16" style="fill: currentColor"><use href="/templates/default/admin/icons/bs.svg#hourglass"></use></svg> Создаю аккаунт...';
            submitBtn.disabled = true;
            
            setTimeout(() => {
                if (submitBtn.disabled) {
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                }
            }, 5000);
        });
    }
    
    if (usernameInput.value) checkUsername();
    if (emailInput.value) checkEmail();
    if (passwordInput.value) checkPasswordStrength();
    checkPasswordMatch();
});