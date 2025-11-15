// Sistema de Date Range Picker con Flatpickr
class DateRangePicker {
    constructor() {
        this.flatpickrLoaded = false;
        this.instances = new Map();
    }

    async ensureFlatpickr() {
        if (this.flatpickrLoaded) return;
        
        return new Promise((resolve, reject) => {
            if (typeof flatpickr !== 'undefined') {
                this.flatpickrLoaded = true;
                resolve();
                return;
            }

            // Cargar CSS
            const link = document.createElement('link');
            link.rel = 'stylesheet';
            link.href = 'https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css';
            document.head.appendChild(link);

            // Cargar tema dark
            const linkDark = document.createElement('link');
            linkDark.rel = 'stylesheet';
            linkDark.href = 'https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/dark.css';
            linkDark.media = '(prefers-color-scheme: dark)';
            document.head.appendChild(linkDark);

            // Cargar JS
            const script = document.createElement('script');
            script.src = 'https://cdn.jsdelivr.net/npm/flatpickr';
            script.onload = () => {
                // Cargar idioma español
                const scriptES = document.createElement('script');
                scriptES.src = 'https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js';
                scriptES.onload = () => {
                    this.flatpickrLoaded = true;
                    resolve();
                };
                scriptES.onerror = () => reject(new Error('No se pudo cargar el idioma'));
                document.head.appendChild(scriptES);
            };
            script.onerror = () => reject(new Error('No se pudo cargar Flatpickr'));
            document.head.appendChild(script);
        });
    }

    async initRangePicker(inputSelector, options = {}) {
        await this.ensureFlatpickr();

        const input = document.querySelector(inputSelector);
        if (!input) {
            console.error('Input not found:', inputSelector);
            return null;
        }

        const config = {
            mode: 'range',
            locale: flatpickr.l10ns.es,
            dateFormat: 'd/m/Y',
            altInput: true,
            altFormat: 'j F, Y',
            minDate: options.minDate || null,
            maxDate: options.maxDate || null,
            defaultDate: options.defaultDate || null,
            onChange: options.onChange || function(selectedDates) {
                if (selectedDates.length === 2) {
                    console.log('Rango seleccionado:', selectedDates);
                }
            },
            onClose: options.onClose || function(selectedDates) {
                if (selectedDates.length === 2) {
                    console.log('Rango cerrado:', selectedDates);
                }
            }
        };

        const instance = flatpickr(input, config);
        this.instances.set(inputSelector, instance);
        
        return instance;
    }

    async initSinglePicker(inputSelector, options = {}) {
        await this.ensureFlatpickr();

        const input = document.querySelector(inputSelector);
        if (!input) {
            console.error('Input not found:', inputSelector);
            return null;
        }

        const config = {
            locale: flatpickr.l10ns.es,
            dateFormat: 'd/m/Y',
            altInput: true,
            altFormat: 'j F, Y',
            minDate: options.minDate || null,
            maxDate: options.maxDate || null,
            defaultDate: options.defaultDate || null,
            enableTime: options.enableTime || false,
            time_24hr: true,
            onChange: options.onChange || null,
            onClose: options.onClose || null
        };

        const instance = flatpickr(input, config);
        this.instances.set(inputSelector, instance);
        
        return instance;
    }

    async createFilterDateRange(containerSelector, onFilterChange) {
        await this.ensureFlatpickr();

        const container = document.querySelector(containerSelector);
        if (!container) {
            console.error('Container not found:', containerSelector);
            return;
        }

        // Crear HTML
        container.innerHTML = `
            <div class="date-range-filter">
                <div class="flex flex-wrap gap-2 items-center">
                    <div class="relative flex-1 min-w-[200px]">
                        <input type="text" 
                               id="date-range-input" 
                               placeholder="Seleccionar rango de fechas"
                               class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-indigo-500 dark:bg-gray-800 dark:text-white">
                        <svg class="absolute right-3 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    
                    <!-- Botones de acceso rápido -->
                    <div class="flex gap-1">
                        <button onclick="window.dateRangePicker.setQuickRange('today')" 
                                class="px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 dark:text-gray-300 transition">
                            Hoy
                        </button>
                        <button onclick="window.dateRangePicker.setQuickRange('week')" 
                                class="px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 dark:text-gray-300 transition">
                            Esta semana
                        </button>
                        <button onclick="window.dateRangePicker.setQuickRange('month')" 
                                class="px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 dark:text-gray-300 transition">
                            Este mes
                        </button>
                        <button onclick="window.dateRangePicker.clearRange()" 
                                class="px-3 py-2 text-sm text-red-600 dark:text-red-400 border border-red-300 dark:border-red-600 rounded-lg hover:bg-red-50 dark:hover:bg-red-900 transition">
                            Limpiar
                        </button>
                    </div>
                </div>
            </div>
        `;

        // Inicializar picker
        const instance = await this.initRangePicker('#date-range-input', {
            onChange: (selectedDates) => {
                if (selectedDates.length === 2 && onFilterChange) {
                    onFilterChange(selectedDates[0], selectedDates[1]);
                }
            }
        });

        this.currentRangeInstance = instance;
    }

    setQuickRange(period) {
        if (!this.currentRangeInstance) return;

        const today = new Date();
        let start, end;

        switch (period) {
            case 'today':
                start = end = today;
                break;
            case 'week':
                start = new Date(today);
                start.setDate(today.getDate() - today.getDay()); // Inicio de semana
                end = new Date(today);
                end.setDate(start.getDate() + 6); // Fin de semana
                break;
            case 'month':
                start = new Date(today.getFullYear(), today.getMonth(), 1);
                end = new Date(today.getFullYear(), today.getMonth() + 1, 0);
                break;
        }

        this.currentRangeInstance.setDate([start, end]);
    }

    clearRange() {
        if (!this.currentRangeInstance) return;
        this.currentRangeInstance.clear();
    }

    getInstance(selector) {
        return this.instances.get(selector);
    }

    destroyInstance(selector) {
        const instance = this.instances.get(selector);
        if (instance) {
            instance.destroy();
            this.instances.delete(selector);
        }
    }

    destroyAll() {
        this.instances.forEach(instance => instance.destroy());
        this.instances.clear();
    }

    formatDate(date) {
        return date.toLocaleDateString('es-ES', {
            year: 'numeric',
            month: '2-digit',
            day: '2-digit'
        });
    }
}

// Instancia global
window.dateRangePicker = new DateRangePicker();
