// Dark Mode Toggle
class DarkMode {
    constructor() {
        this.theme = localStorage.getItem('theme') || 'light';
        this.init();
    }

    init() {
        this.applyTheme();
        this.addToggleListener();
    }

    applyTheme() {
        document.documentElement.setAttribute('data-theme', this.theme);
        localStorage.setItem('theme', this.theme);
    }

    toggle() {
        this.theme = this.theme === 'light' ? 'dark' : 'light';
        this.applyTheme();
    }

    addToggleListener() {
        document.addEventListener('DOMContentLoaded', () => {
            const toggleBtn = document.getElementById('theme-toggle');
            if (toggleBtn) {
                toggleBtn.addEventListener('click', () => this.toggle());
            }
        });
    }

    isDark() {
        return this.theme === 'dark';
    }
}

// Instancia global
window.darkMode = new DarkMode();
