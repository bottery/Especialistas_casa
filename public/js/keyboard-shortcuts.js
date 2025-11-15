// Sistema de Atajos de Teclado (Keyboard Shortcuts)
class KeyboardShortcuts {
    constructor() {
        this.shortcuts = new Map();
        this.modalOpen = false;
        this.init();
    }

    init() {
        this.registerDefaultShortcuts();
        this.addEventListeners();
        this.createHelpModal();
    }

    addEventListeners() {
        document.addEventListener('keydown', (e) => {
            // Ignorar si está escribiendo en un input
            if (this.isTyping(e.target)) return;

            const key = this.getKeyCombo(e);
            const shortcut = this.shortcuts.get(key);

            if (shortcut) {
                e.preventDefault();
                shortcut.action();
            }
        });
    }

    isTyping(element) {
        const tagName = element.tagName.toLowerCase();
        return tagName === 'input' || tagName === 'textarea' || element.isContentEditable;
    }

    getKeyCombo(e) {
        const keys = [];
        if (e.ctrlKey || e.metaKey) keys.push('ctrl');
        if (e.altKey) keys.push('alt');
        if (e.shiftKey) keys.push('shift');
        keys.push(e.key.toLowerCase());
        return keys.join('+');
    }

    register(keys, action, description, category = 'General') {
        this.shortcuts.set(keys, { action, description, category, keys });
    }

    registerDefaultShortcuts() {
        // Navegación
        this.register('ctrl+h', () => window.location.href = '/', 'Ir al inicio', 'Navegación');
        this.register('ctrl+b', () => window.history.back(), 'Volver atrás', 'Navegación');
        this.register('ctrl+k', () => this.focusSearch(), 'Buscar', 'Navegación');

        // Acciones
        this.register('ctrl+n', () => this.newRequest(), 'Nueva solicitud', 'Acciones');
        this.register('ctrl+s', (e) => this.saveForm(e), 'Guardar formulario', 'Acciones');
        this.register('ctrl+/', () => this.toggleHelpModal(), 'Mostrar atajos', 'Ayuda');
        this.register('?', () => this.toggleHelpModal(), 'Mostrar atajos', 'Ayuda');
        this.register('escape', () => this.closeModals(), 'Cerrar modal', 'General');

        // Dashboard
        this.register('g+d', () => this.goToDashboard(), 'Ir al dashboard', 'Navegación');
        this.register('g+p', () => this.goToProfile(), 'Ir al perfil', 'Navegación');
    }

    focusSearch() {
        const searchInput = document.querySelector('input[type="text"][placeholder*="Buscar"]') ||
                          document.querySelector('input[type="search"]');
        if (searchInput) {
            searchInput.focus();
            searchInput.select();
        }
    }

    newRequest() {
        const newBtn = document.querySelector('button[onclick*="nuevaSolicitud"]') ||
                      document.querySelector('[href*="nueva-solicitud"]');
        if (newBtn) newBtn.click();
    }

    saveForm(e) {
        e?.preventDefault();
        const submitBtn = document.querySelector('button[type="submit"]');
        if (submitBtn) submitBtn.click();
    }

    goToDashboard() {
        const role = this.detectRole();
        window.location.href = `/${role}/dashboard`;
    }

    goToProfile() {
        window.location.href = '/perfil';
    }

    detectRole() {
        const path = window.location.pathname;
        if (path.includes('admin')) return 'admin';
        if (path.includes('profesional')) return 'profesional';
        return 'paciente';
    }

    closeModals() {
        // Cerrar cualquier modal abierto
        const closeButtons = document.querySelectorAll('[x-show="modalDetalleAbierto"] button, [x-show="modalCalificacionAbierto"] button');
        closeButtons.forEach(btn => {
            if (btn.textContent.includes('×') || btn.querySelector('svg')) {
                btn.click();
            }
        });
    }

    toggleHelpModal() {
        if (this.modalOpen) {
            this.hideHelpModal();
        } else {
            this.showHelpModal();
        }
    }

    createHelpModal() {
        const modal = document.createElement('div');
        modal.id = 'shortcuts-help-modal';
        modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden';
        modal.style.backdropFilter = 'blur(4px)';
        
        modal.innerHTML = `
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl max-w-2xl w-full mx-4 max-h-[80vh] overflow-hidden">
                <div class="bg-gradient-to-r from-indigo-600 to-purple-600 px-6 py-4 flex justify-between items-center">
                    <h3 class="text-xl font-bold text-white flex items-center gap-2">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z"/>
                        </svg>
                        Atajos de Teclado
                    </h3>
                    <button onclick="window.keyboardShortcuts.hideHelpModal()" class="text-white hover:text-gray-200">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <div class="p-6 overflow-y-auto max-h-[calc(80vh-80px)]">
                    ${this.generateShortcutsHTML()}
                </div>
            </div>
        `;

        document.body.appendChild(modal);

        // Cerrar al hacer click fuera
        modal.addEventListener('click', (e) => {
            if (e.target === modal) this.hideHelpModal();
        });
    }

    generateShortcutsHTML() {
        const categories = {};
        
        this.shortcuts.forEach(shortcut => {
            if (!categories[shortcut.category]) {
                categories[shortcut.category] = [];
            }
            categories[shortcut.category].push(shortcut);
        });

        let html = '';
        for (const [category, shortcuts] of Object.entries(categories)) {
            html += `
                <div class="mb-6">
                    <h4 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase mb-3">${category}</h4>
                    <div class="space-y-2">
                        ${shortcuts.map(s => `
                            <div class="flex justify-between items-center py-2 px-3 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700">
                                <span class="text-gray-700 dark:text-gray-300">${s.description}</span>
                                <div class="flex gap-1">
                                    ${s.keys.split('+').map(k => `
                                        <kbd class="px-2 py-1 text-xs font-semibold text-gray-800 dark:text-gray-200 bg-gray-100 dark:bg-gray-600 border border-gray-300 dark:border-gray-500 rounded shadow-sm">
                                            ${this.formatKey(k)}
                                        </kbd>
                                    `).join('<span class="text-gray-400 mx-1">+</span>')}
                                </div>
                            </div>
                        `).join('')}
                    </div>
                </div>
            `;
        }

        return html;
    }

    formatKey(key) {
        const keyMap = {
            'ctrl': '⌃',
            'cmd': '⌘',
            'alt': '⌥',
            'shift': '⇧',
            'escape': 'Esc',
            'arrowup': '↑',
            'arrowdown': '↓',
            'arrowleft': '←',
            'arrowright': '→',
            'enter': '↵'
        };
        return keyMap[key.toLowerCase()] || key.toUpperCase();
    }

    showHelpModal() {
        const modal = document.getElementById('shortcuts-help-modal');
        if (modal) {
            modal.classList.remove('hidden');
            this.modalOpen = true;
        }
    }

    hideHelpModal() {
        const modal = document.getElementById('shortcuts-help-modal');
        if (modal) {
            modal.classList.add('hidden');
            this.modalOpen = false;
        }
    }
}

// Inicializar
window.keyboardShortcuts = new KeyboardShortcuts();

// Mostrar indicador visual de ayuda
document.addEventListener('DOMContentLoaded', () => {
    const helpIndicator = document.createElement('div');
    helpIndicator.className = 'fixed bottom-4 left-4 bg-gray-800 text-white text-xs px-3 py-2 rounded-lg shadow-lg opacity-75 hover:opacity-100 transition z-30';
    helpIndicator.innerHTML = 'Presiona <kbd class="bg-gray-700 px-1 rounded">?</kbd> para ver atajos';
    helpIndicator.style.cursor = 'pointer';
    helpIndicator.onclick = () => window.keyboardShortcuts.toggleHelpModal();
    
    // Mostrar durante 5 segundos al cargar
    document.body.appendChild(helpIndicator);
    setTimeout(() => {
        helpIndicator.style.opacity = '0';
        setTimeout(() => helpIndicator.remove(), 300);
    }, 5000);
});
