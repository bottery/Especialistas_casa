// Vista de Calendario para Citas con FullCalendar.js
class CalendarView {
    constructor(containerId, options = {}) {
        this.containerId = containerId;
        this.options = {
            apiEndpoint: options.apiEndpoint || '/api/citas',
            locale: options.locale || 'es',
            initialView: options.initialView || 'dayGridMonth',
            editable: options.editable || false,
            onEventClick: options.onEventClick || (() => {}),
            onDateClick: options.onDateClick || (() => {}),
            onEventDrop: options.onEventDrop || (() => {})
        };

        this.calendar = null;
        this.events = [];
        this.fullCalendarLoaded = false;
    }

    async ensureFullCalendar() {
        if (this.fullCalendarLoaded) return;

        return new Promise((resolve, reject) => {
            // Cargar CSS
            const link = document.createElement('link');
            link.rel = 'stylesheet';
            link.href = 'https://cdn.jsdelivr.net/npm/fullcalendar@6.1.9/index.global.min.css';
            document.head.appendChild(link);

            // Cargar JS
            const script = document.createElement('script');
            script.src = 'https://cdn.jsdelivr.net/npm/fullcalendar@6.1.9/index.global.min.js';
            script.onload = () => {
                // Cargar idioma español
                const scriptES = document.createElement('script');
                scriptES.src = 'https://cdn.jsdelivr.net/npm/fullcalendar@6.1.9/locales/es.global.min.js';
                scriptES.onload = () => {
                    this.fullCalendarLoaded = true;
                    resolve();
                };
                scriptES.onerror = () => reject(new Error('Failed to load FullCalendar locale'));
                document.head.appendChild(scriptES);
            };
            script.onerror = () => reject(new Error('Failed to load FullCalendar'));
            document.head.appendChild(script);
        });
    }

    async init() {
        await this.ensureFullCalendar();
        
        const container = document.getElementById(this.containerId);
        if (!container) {
            console.error('[Calendar] Container not found:', this.containerId);
            return;
        }

        // Crear estructura
        container.innerHTML = `
            <div class="calendar-wrapper bg-white dark:bg-gray-800 rounded-xl shadow-lg p-6">
                <div class="calendar-header mb-4 flex justify-between items-center">
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Mi Calendario</h2>
                    <div class="flex gap-2">
                        <button onclick="window.calendarView.changeView('dayGridMonth')" 
                                class="px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded hover:bg-gray-50 dark:hover:bg-gray-700 dark:text-gray-300">
                            Mes
                        </button>
                        <button onclick="window.calendarView.changeView('timeGridWeek')" 
                                class="px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded hover:bg-gray-50 dark:hover:bg-gray-700 dark:text-gray-300">
                            Semana
                        </button>
                        <button onclick="window.calendarView.changeView('timeGridDay')" 
                                class="px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded hover:bg-gray-50 dark:hover:bg-gray-700 dark:text-gray-300">
                            Día
                        </button>
                        <button onclick="window.calendarView.changeView('listWeek')" 
                                class="px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded hover:bg-gray-50 dark:hover:bg-gray-700 dark:text-gray-300">
                            Lista
                        </button>
                    </div>
                </div>
                <div id="calendar-render"></div>
            </div>
        `;

        // Inicializar FullCalendar
        this.initCalendar();
        
        // Cargar eventos
        await this.loadEvents();
    }

    initCalendar() {
        const calendarEl = document.getElementById('calendar-render');
        if (!calendarEl) return;

        this.calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: this.options.initialView,
            locale: this.options.locale,
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: ''
            },
            buttonText: {
                today: 'Hoy',
                month: 'Mes',
                week: 'Semana',
                day: 'Día',
                list: 'Lista'
            },
            editable: this.options.editable,
            droppable: this.options.editable,
            eventDrop: (info) => this.handleEventDrop(info),
            eventClick: (info) => this.handleEventClick(info),
            dateClick: (info) => this.handleDateClick(info),
            events: this.events,
            height: 'auto',
            eventTimeFormat: {
                hour: '2-digit',
                minute: '2-digit',
                hour12: false
            },
            slotLabelFormat: {
                hour: '2-digit',
                minute: '2-digit',
                hour12: false
            },
            dayMaxEvents: 3,
            eventColor: '#6366f1',
            eventDisplay: 'block'
        });

        this.calendar.render();
    }

    async loadEvents() {
        try {
            const token = localStorage.getItem('token');
            const response = await fetch(this.options.apiEndpoint, {
                headers: { 'Authorization': `Bearer ${token}` }
            });

            if (!response.ok) throw new Error('Failed to load events');

            const data = await response.json();
            this.events = this.transformEvents(data.citas || []);
            
            if (this.calendar) {
                this.calendar.removeAllEvents();
                this.calendar.addEventSource(this.events);
            }

        } catch (error) {
            console.error('[Calendar] Load events error:', error);
        }
    }

    transformEvents(citas) {
        return citas.map(cita => ({
            id: cita.id,
            title: cita.servicio || 'Cita',
            start: cita.fecha_programada || cita.fecha_servicio,
            end: cita.fecha_fin,
            color: this.getEventColor(cita.estado),
            extendedProps: {
                estado: cita.estado,
                paciente: cita.paciente_nombre,
                profesional: cita.profesional_nombre,
                direccion: cita.direccion,
                notas: cita.notas
            }
        }));
    }

    getEventColor(estado) {
        const colors = {
            'pendiente': '#f59e0b',       // Amber
            'asignado': '#3b82f6',        // Blue
            'en_proceso': '#8b5cf6',      // Purple
            'completado': '#10b981',      // Green
            'cancelado': '#ef4444',       // Red
            'rechazado': '#6b7280'        // Gray
        };
        return colors[estado] || '#6366f1';
    }

    handleEventClick(info) {
        const event = info.event;
        const props = event.extendedProps;

        // Crear modal de detalle
        const modal = `
            <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" id="event-detail-modal">
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-2xl max-w-md w-full mx-4 p-6">
                    <div class="flex justify-between items-start mb-4">
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white">${event.title}</h3>
                        <button onclick="document.getElementById('event-detail-modal').remove()" 
                                class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                    
                    <div class="space-y-3">
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Fecha y Hora</p>
                            <p class="text-gray-900 dark:text-white font-medium">
                                ${event.start.toLocaleDateString('es-ES', { 
                                    weekday: 'long', 
                                    year: 'numeric', 
                                    month: 'long', 
                                    day: 'numeric' 
                                })}
                                <br>
                                ${event.start.toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' })}
                            </p>
                        </div>
                        
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Estado</p>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium" 
                                  style="background-color: ${event.backgroundColor}20; color: ${event.backgroundColor}">
                                ${props.estado}
                            </span>
                        </div>
                        
                        ${props.paciente ? `
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Paciente</p>
                            <p class="text-gray-900 dark:text-white">${props.paciente}</p>
                        </div>
                        ` : ''}
                        
                        ${props.profesional ? `
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Profesional</p>
                            <p class="text-gray-900 dark:text-white">${props.profesional}</p>
                        </div>
                        ` : ''}
                        
                        ${props.direccion ? `
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Dirección</p>
                            <p class="text-gray-900 dark:text-white">${props.direccion}</p>
                        </div>
                        ` : ''}
                        
                        ${props.notas ? `
                        <div>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Notas</p>
                            <p class="text-gray-900 dark:text-white">${props.notas}</p>
                        </div>
                        ` : ''}
                    </div>
                    
                    <div class="mt-6 flex gap-2">
                        <button onclick="window.location.href='/solicitud/${event.id}'" 
                                class="flex-1 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                            Ver Detalles
                        </button>
                        <button onclick="document.getElementById('event-detail-modal').remove()" 
                                class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700 dark:text-gray-300 transition">
                            Cerrar
                        </button>
                    </div>
                </div>
            </div>
        `;

        document.body.insertAdjacentHTML('beforeend', modal);

        // Callback
        this.options.onEventClick(event);
    }

    handleDateClick(info) {
        console.log('[Calendar] Date clicked:', info.dateStr);
        this.options.onDateClick(info);
    }

    async handleEventDrop(info) {
        const event = info.event;
        
        try {
            const token = localStorage.getItem('token');
            const response = await fetch(`${this.options.apiEndpoint}/${event.id}/reschedule`, {
                method: 'PUT',
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    fecha_programada: event.start.toISOString()
                })
            });

            if (!response.ok) throw new Error('Failed to reschedule');

            if (window.ToastNotification) {
                new ToastNotification('Cita reprogramada con éxito', 'success').show();
            }

            this.options.onEventDrop(event);

        } catch (error) {
            console.error('[Calendar] Event drop error:', error);
            info.revert();
            
            if (window.ToastNotification) {
                new ToastNotification('Error al reprogramar la cita', 'error').show();
            }
        }
    }

    changeView(viewName) {
        if (this.calendar) {
            this.calendar.changeView(viewName);
        }
    }

    goToDate(date) {
        if (this.calendar) {
            this.calendar.gotoDate(date);
        }
    }

    refresh() {
        this.loadEvents();
    }

    addEvent(event) {
        if (this.calendar) {
            this.calendar.addEvent(event);
        }
    }

    removeEvent(eventId) {
        if (this.calendar) {
            const event = this.calendar.getEventById(eventId);
            if (event) event.remove();
        }
    }
}

// Uso: window.calendarView = new CalendarView('calendar-container', { ... });
