// Sistema de Transiciones de Página (Page Transitions)
class PageTransitions {
    constructor(options = {}) {
        this.options = {
            duration: options.duration || 300,
            type: options.type || 'fade', // fade, slide, scale
            showLoadingBar: options.showLoadingBar !== false,
            excludeExternal: options.excludeExternal !== false
        };

        this.isTransitioning = false;
        this.init();
    }

    init() {
        this.createStyles();
        if (this.options.showLoadingBar) {
            this.createLoadingBar();
        }
        this.interceptLinks();
        this.showPageEnter();
    }

    createStyles() {
        if (document.getElementById('page-transitions-styles')) return;

        const styles = document.createElement('style');
        styles.id = 'page-transitions-styles';
        styles.textContent = `
            .page-transition-exit {
                animation: pageExit ${this.options.duration}ms ease-out forwards;
            }
            
            @keyframes pageExit {
                0% { opacity: 1; transform: translateY(0); }
                100% { opacity: 0; transform: translateY(-20px); }
            }

            .page-transition-enter {
                animation: pageEnter ${this.options.duration}ms ease-out forwards;
            }

            @keyframes pageEnter {
                0% { opacity: 0; transform: translateY(20px); }
                100% { opacity: 1; transform: translateY(0); }
            }

            .page-transition-slide-exit {
                animation: slideExit ${this.options.duration}ms ease-out forwards;
            }

            @keyframes slideExit {
                0% { transform: translateX(0); }
                100% { transform: translateX(-100%); }
            }

            .page-transition-slide-enter {
                animation: slideEnter ${this.options.duration}ms ease-out forwards;
            }

            @keyframes slideEnter {
                0% { transform: translateX(100%); }
                100% { transform: translateX(0); }
            }

            .page-transition-scale-exit {
                animation: scaleExit ${this.options.duration}ms ease-out forwards;
            }

            @keyframes scaleExit {
                0% { opacity: 1; transform: scale(1); }
                100% { opacity: 0; transform: scale(0.9); }
            }

            .page-transition-scale-enter {
                animation: scaleEnter ${this.options.duration}ms ease-out forwards;
            }

            @keyframes scaleEnter {
                0% { opacity: 0; transform: scale(1.1); }
                100% { opacity: 1; transform: scale(1); }
            }

            .loading-bar {
                position: fixed;
                top: 0;
                left: 0;
                width: 0;
                height: 3px;
                background: linear-gradient(90deg, #6366f1, #a855f7);
                z-index: 9999;
                transition: width 0.3s ease;
            }

            .loading-bar.active {
                animation: loadingProgress 2s ease-in-out infinite;
            }

            @keyframes loadingProgress {
                0% { width: 0; }
                50% { width: 70%; }
                100% { width: 100%; }
            }
        `;
        document.head.appendChild(styles);
    }

    createLoadingBar() {
        const bar = document.createElement('div');
        bar.className = 'loading-bar';
        bar.id = 'page-loading-bar';
        document.body.appendChild(bar);
        this.loadingBar = bar;
    }

    interceptLinks() {
        document.addEventListener('click', (e) => {
            const link = e.target.closest('a');
            
            if (!link || this.isTransitioning) return;
            
            const href = link.getAttribute('href');
            
            // Skip if no href, external link, or anchor
            if (!href || 
                href.startsWith('#') || 
                href.startsWith('javascript:') ||
                (this.options.excludeExternal && this.isExternalLink(href))) {
                return;
            }

            // Skip if it has target="_blank" or download attribute
            if (link.target === '_blank' || link.hasAttribute('download')) {
                return;
            }

            // Intercept navigation
            e.preventDefault();
            this.navigateTo(href);
        });
    }

    isExternalLink(href) {
        try {
            const url = new URL(href, window.location.href);
            return url.hostname !== window.location.hostname;
        } catch {
            return false;
        }
    }

    async navigateTo(url) {
        if (this.isTransitioning) return;
        
        this.isTransitioning = true;
        
        // Show loading bar
        if (this.loadingBar) {
            this.loadingBar.style.width = '0';
            this.loadingBar.classList.add('active');
        }

        // Exit animation
        await this.animatePageExit();

        // Navigate
        window.location.href = url;
    }

    animatePageExit() {
        return new Promise(resolve => {
            const exitClass = `page-transition-${this.options.type}-exit`;
            document.body.classList.add(exitClass);

            setTimeout(() => {
                resolve();
            }, this.options.duration);
        });
    }

    showPageEnter() {
        const enterClass = `page-transition-${this.options.type}-enter`;
        document.body.classList.add(enterClass);

        setTimeout(() => {
            document.body.classList.remove(enterClass);
            
            // Hide loading bar
            if (this.loadingBar) {
                this.loadingBar.classList.remove('active');
                this.loadingBar.style.width = '100%';
                setTimeout(() => {
                    this.loadingBar.style.width = '0';
                }, 200);
            }
        }, this.options.duration);
    }

    // Manual control
    showLoading() {
        if (this.loadingBar) {
            this.loadingBar.style.width = '0';
            this.loadingBar.classList.add('active');
        }
    }

    hideLoading() {
        if (this.loadingBar) {
            this.loadingBar.classList.remove('active');
            this.loadingBar.style.width = '100%';
            setTimeout(() => {
                this.loadingBar.style.width = '0';
            }, 200);
        }
    }

    setProgress(percent) {
        if (this.loadingBar) {
            this.loadingBar.style.width = `${percent}%`;
        }
    }
}

// Auto-init
document.addEventListener('DOMContentLoaded', () => {
    window.pageTransitions = new PageTransitions({
        type: 'fade',
        duration: 300,
        showLoadingBar: true
    });
});
