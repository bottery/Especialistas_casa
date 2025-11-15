// Sistema de Ayuda Contextual (Tooltips, Tours, Help Icons)
class ContextualHelp {
    constructor() {
        this.tooltips = [];
        this.tourActive = false;
        this.currentTourStep = 0;
        this.tourSteps = [];
        this.init();
    }

    init() {
        this.createStyles();
        this.initTooltips();
    }

    createStyles() {
        if (document.getElementById('contextual-help-styles')) return;

        const styles = document.createElement('style');
        styles.id = 'contextual-help-styles';
        styles.textContent = `
            .help-icon {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                width: 18px;
                height: 18px;
                border-radius: 50%;
                background: #6366f1;
                color: white;
                font-size: 12px;
                font-weight: bold;
                cursor: help;
                transition: all 0.2s;
                margin-left: 4px;
            }
            .help-icon:hover {
                background: #4f46e5;
                transform: scale(1.1);
            }
            .tooltip {
                position: absolute;
                z-index: 9999;
                background: #1f2937;
                color: white;
                padding: 8px 12px;
                border-radius: 8px;
                font-size: 13px;
                max-width: 250px;
                box-shadow: 0 4px 6px rgba(0,0,0,0.1);
                pointer-events: none;
                opacity: 0;
                transition: opacity 0.2s;
            }
            .tooltip.show {
                opacity: 1;
            }
            .tooltip-arrow {
                position: absolute;
                width: 8px;
                height: 8px;
                background: #1f2937;
                transform: rotate(45deg);
            }
            .tooltip[data-position="top"] .tooltip-arrow {
                bottom: -4px;
                left: 50%;
                margin-left: -4px;
            }
            .tooltip[data-position="bottom"] .tooltip-arrow {
                top: -4px;
                left: 50%;
                margin-left: -4px;
            }
            .tooltip[data-position="left"] .tooltip-arrow {
                right: -4px;
                top: 50%;
                margin-top: -4px;
            }
            .tooltip[data-position="right"] .tooltip-arrow {
                left: -4px;
                top: 50%;
                margin-top: -4px;
            }
            .tour-overlay {
                position: fixed;
                inset: 0;
                background: rgba(0,0,0,0.5);
                z-index: 9998;
                backdrop-filter: blur(2px);
            }
            .tour-spotlight {
                position: absolute;
                border: 3px solid #6366f1;
                border-radius: 8px;
                box-shadow: 0 0 0 9999px rgba(0,0,0,0.5);
                z-index: 9999;
                pointer-events: none;
                transition: all 0.3s;
            }
            .tour-popup {
                position: absolute;
                z-index: 10000;
                background: white;
                border-radius: 12px;
                padding: 20px;
                max-width: 350px;
                box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            }
            .tour-popup h3 {
                font-size: 18px;
                font-weight: 600;
                margin-bottom: 8px;
                color: #1f2937;
            }
            .tour-popup p {
                font-size: 14px;
                color: #6b7280;
                margin-bottom: 16px;
            }
            .tour-buttons {
                display: flex;
                justify-content: space-between;
                align-items: center;
            }
            .tour-progress {
                font-size: 12px;
                color: #9ca3af;
            }
        `;
        document.head.appendChild(styles);
    }

    // Tooltips
    initTooltips() {
        document.addEventListener('mouseenter', (e) => {
            const target = e.target.closest('[data-tooltip]');
            if (target) {
                this.showTooltip(target);
            }
        }, true);

        document.addEventListener('mouseleave', (e) => {
            const target = e.target.closest('[data-tooltip]');
            if (target) {
                this.hideTooltip(target);
            }
        }, true);
    }

    showTooltip(element) {
        const text = element.getAttribute('data-tooltip');
        const position = element.getAttribute('data-tooltip-position') || 'top';

        const tooltip = document.createElement('div');
        tooltip.className = 'tooltip';
        tooltip.setAttribute('data-position', position);
        tooltip.innerHTML = `
            ${text}
            <div class="tooltip-arrow"></div>
        `;
        
        document.body.appendChild(tooltip);
        element._tooltip = tooltip;

        // Position
        const rect = element.getBoundingClientRect();
        const tooltipRect = tooltip.getBoundingClientRect();

        let top, left;

        switch (position) {
            case 'top':
                top = rect.top - tooltipRect.height - 8;
                left = rect.left + (rect.width - tooltipRect.width) / 2;
                break;
            case 'bottom':
                top = rect.bottom + 8;
                left = rect.left + (rect.width - tooltipRect.width) / 2;
                break;
            case 'left':
                top = rect.top + (rect.height - tooltipRect.height) / 2;
                left = rect.left - tooltipRect.width - 8;
                break;
            case 'right':
                top = rect.top + (rect.height - tooltipRect.height) / 2;
                left = rect.right + 8;
                break;
        }

        tooltip.style.top = `${top}px`;
        tooltip.style.left = `${left}px`;

        setTimeout(() => tooltip.classList.add('show'), 10);
    }

    hideTooltip(element) {
        if (element._tooltip) {
            element._tooltip.remove();
            delete element._tooltip;
        }
    }

    // Help Icons
    addHelpIcon(element, helpText) {
        const icon = document.createElement('span');
        icon.className = 'help-icon';
        icon.textContent = '?';
        icon.setAttribute('data-tooltip', helpText);
        icon.setAttribute('data-tooltip-position', 'right');
        
        if (typeof element === 'string') {
            element = document.querySelector(element);
        }
        
        if (element) {
            element.appendChild(icon);
        }
    }

    // Tours guiados
    startTour(steps) {
        if (this.tourActive) return;

        this.tourSteps = steps;
        this.currentTourStep = 0;
        this.tourActive = true;

        // Crear overlay
        const overlay = document.createElement('div');
        overlay.className = 'tour-overlay';
        overlay.id = 'tour-overlay';
        document.body.appendChild(overlay);

        // Crear spotlight
        const spotlight = document.createElement('div');
        spotlight.className = 'tour-spotlight';
        spotlight.id = 'tour-spotlight';
        document.body.appendChild(spotlight);

        // Crear popup
        const popup = document.createElement('div');
        popup.className = 'tour-popup';
        popup.id = 'tour-popup';
        document.body.appendChild(popup);

        this.showTourStep();
    }

    showTourStep() {
        const step = this.tourSteps[this.currentTourStep];
        if (!step) {
            this.endTour();
            return;
        }

        const element = document.querySelector(step.target);
        if (!element) {
            console.error('Tour target not found:', step.target);
            this.nextTourStep();
            return;
        }

        // Scroll to element
        element.scrollIntoView({ behavior: 'smooth', block: 'center' });

        setTimeout(() => {
            // Position spotlight
            const rect = element.getBoundingClientRect();
            const spotlight = document.getElementById('tour-spotlight');
            spotlight.style.top = `${rect.top - 8}px`;
            spotlight.style.left = `${rect.left - 8}px`;
            spotlight.style.width = `${rect.width + 16}px`;
            spotlight.style.height = `${rect.height + 16}px`;

            // Position popup
            const popup = document.getElementById('tour-popup');
            popup.innerHTML = `
                <h3>${step.title}</h3>
                <p>${step.content}</p>
                <div class="tour-buttons">
                    <div class="tour-progress">
                        ${this.currentTourStep + 1} de ${this.tourSteps.length}
                    </div>
                    <div class="flex gap-2">
                        ${this.currentTourStep > 0 ? '<button onclick="window.contextualHelp.prevTourStep()" class="px-3 py-1 text-sm border border-gray-300 rounded hover:bg-gray-50">Anterior</button>' : ''}
                        ${this.currentTourStep < this.tourSteps.length - 1 ? '<button onclick="window.contextualHelp.nextTourStep()" class="px-3 py-1 text-sm bg-indigo-600 text-white rounded hover:bg-indigo-700">Siguiente</button>' : '<button onclick="window.contextualHelp.endTour()" class="px-3 py-1 text-sm bg-green-600 text-white rounded hover:bg-green-700">Finalizar</button>'}
                        <button onclick="window.contextualHelp.endTour()" class="px-3 py-1 text-sm text-gray-600 hover:text-gray-900">Salir</button>
                    </div>
                </div>
            `;

            // Position popup below or above element
            const popupRect = popup.getBoundingClientRect();
            const spaceBelow = window.innerHeight - rect.bottom;
            const spaceAbove = rect.top;

            if (spaceBelow > popupRect.height + 20) {
                popup.style.top = `${rect.bottom + 20}px`;
            } else if (spaceAbove > popupRect.height + 20) {
                popup.style.top = `${rect.top - popupRect.height - 20}px`;
            } else {
                popup.style.top = `${window.innerHeight / 2 - popupRect.height / 2}px`;
            }

            popup.style.left = `${rect.left}px`;
        }, 500);
    }

    nextTourStep() {
        this.currentTourStep++;
        this.showTourStep();
    }

    prevTourStep() {
        this.currentTourStep--;
        this.showTourStep();
    }

    endTour() {
        this.tourActive = false;
        this.currentTourStep = 0;
        this.tourSteps = [];

        document.getElementById('tour-overlay')?.remove();
        document.getElementById('tour-spotlight')?.remove();
        document.getElementById('tour-popup')?.remove();
    }
}

// Instancia global
window.contextualHelp = new ContextualHelp();

// Ejemplo de uso:
// window.contextualHelp.addHelpIcon('#formulario-label', 'Complete este campo con su información');
// window.contextualHelp.startTour([
//   { target: '#btn-nueva-solicitud', title: 'Nueva Solicitud', content: 'Haz click aquí para crear una nueva solicitud' },
//   { target: '#tabla-solicitudes', title: 'Tus Solicitudes', content: 'Aquí verás todas tus solicitudes' }
// ]);
