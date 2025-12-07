/**
 * Vista Kanban para Dashboard Admin
 * Gestión visual de solicitudes por estados
 */

class KanbanBoard {
    constructor() {
        this.solicitudes = [];
        this.filtros = {
            especialidad: null,
            profesional: null,
            busqueda: ''
        };
        
        this.columnas = [
            { estado: 'pendiente', titulo: '📋 Pendientes', color: '#fbbf24' },
            { estado: 'asignada', titulo: '👤 Asignadas', color: '#60a5fa' },
            { estado: 'en_camino', titulo: '🚗 En Camino', color: '#a78bfa' },
            { estado: 'en_proceso', titulo: '▶️ En Proceso', color: '#34d399' },
            { estado: 'completada', titulo: '✅ Completadas', color: '#10b981' }
        ];
    }
    
    /**
     * Inicializar Kanban
     */
    async init() {
        this.renderBoard();
        await this.cargarSolicitudes();
        this.iniciarActualizacionAutomatica();
        this.iniciarDragAndDrop();
    }
    
    /**
     * Renderizar tablero Kanban
     */
    renderBoard() {
        const container = document.getElementById('kanban-container');
        if (!container) return;
        
        let html = '<div class="kanban-board">';
        
        this.columnas.forEach(columna => {
            html += `
                <div class="kanban-column" data-estado="${columna.estado}">
                    <div class="kanban-column-header" style="border-left: 4px solid ${columna.color}">
                        <h3>${columna.titulo}</h3>
                        <span class="badge" data-count="${columna.estado}">0</span>
                    </div>
                    <div class="kanban-column-body" id="column-${columna.estado}">
                        <div class="loading-spinner">Cargando...</div>
                    </div>
                </div>
            `;
        });
        
        html += '</div>';
        container.innerHTML = html;
    }
    
    /**
     * Cargar todas las solicitudes
     */
    async cargarSolicitudes() {
        try {
            const response = await fetch((typeof BASE_URL !== 'undefined' ? BASE_URL : '') + '/api/admin/solicitudes/todas');
            if (!response.ok) throw new Error('Error al cargar solicitudes');
            
            const data = await response.json();
            this.solicitudes = data.data || [];
            
            this.renderSolicitudes();
        } catch (error) {
            console.error('Error:', error);
            window.ToastNotification.show('Error al cargar solicitudes', 'error');
        }
    }
    
    /**
     * Renderizar solicitudes en las columnas
     */
    renderSolicitudes() {
        // Limpiar columnas
        this.columnas.forEach(columna => {
            const columnBody = document.getElementById(`column-${columna.estado}`);
            if (columnBody) columnBody.innerHTML = '';
        });
        
        // Aplicar filtros
        const solicitudesFiltradas = this.aplicarFiltros();
        
        // Agrupar por estado
        const porEstado = {};
        this.columnas.forEach(col => porEstado[col.estado] = []);
        
        solicitudesFiltradas.forEach(solicitud => {
            const estado = this.mapearEstado(solicitud.estado);
            if (porEstado[estado]) {
                porEstado[estado].push(solicitud);
            }
        });
        
        // Renderizar en cada columna
        this.columnas.forEach(columna => {
            const columnBody = document.getElementById(`column-${columna.estado}`);
            const badge = document.querySelector(`[data-count="${columna.estado}"]`);
            const solicitudes = porEstado[columna.estado];
            
            if (badge) badge.textContent = solicitudes.length;
            
            if (solicitudes.length === 0) {
                columnBody.innerHTML = '<div class="kanban-empty">No hay solicitudes</div>';
            } else {
                solicitudes.forEach(solicitud => {
                    columnBody.appendChild(this.crearTarjeta(solicitud));
                });
            }
        });
    }
    
    /**
     * Crear tarjeta de solicitud
     */
    crearTarjeta(solicitud) {
        const div = document.createElement('div');
        div.className = 'kanban-card';
        div.draggable = true;
        div.dataset.solicitudId = solicitud.id;
        
        const prioridad = solicitud.urgente ? 'urgente' : 'normal';
        const tiempoTranscurrido = this.calcularTiempoTranscurrido(solicitud.fecha_creacion);
        
        div.innerHTML = `
            <div class="kanban-card-header">
                <span class="badge badge-${prioridad}">#${solicitud.id}</span>
                <span class="kanban-card-time">${tiempoTranscurrido}</span>
            </div>
            
            <div class="kanban-card-body">
                <h4>${solicitud.servicio_nombre}</h4>
                <p class="paciente-info">
                    <i class="fas fa-user"></i>
                    ${solicitud.paciente_nombre}
                </p>
                
                ${solicitud.profesional_nombre ? `
                    <p class="profesional-info">
                        <i class="fas fa-user-md"></i>
                        ${solicitud.profesional_nombre}
                    </p>
                ` : ''}
                
                ${solicitud.especialidad_solicitada ? `
                    <span class="badge badge-especialidad">
                        ${solicitud.especialidad_solicitada}
                    </span>
                ` : ''}
                
                ${solicitud.tiempo_estimado_llegada ? `
                    <p class="tiempo-estimado">
                        <i class="fas fa-clock"></i>
                        Llegada en ~${solicitud.tiempo_estimado_llegada} min
                    </p>
                ` : ''}
            </div>
            
            <div class="kanban-card-footer">
                <button class="btn-icon" onclick="kanbanBoard.verDetalle(${solicitud.id})" title="Ver detalle">
                    <i class="fas fa-eye"></i>
                </button>
                
                ${!solicitud.profesional_id && solicitud.estado === 'pendiente' ? `
                    <button class="btn-icon btn-primary" onclick="kanbanBoard.asignarProfesional(${solicitud.id})" title="Asignar">
                        <i class="fas fa-user-plus"></i>
                    </button>
                ` : ''}
                
                <button class="btn-icon" onclick="kanbanBoard.verUbicacion(${solicitud.id})" title="Ver ubicación">
                    <i class="fas fa-map-marker-alt"></i>
                </button>
            </div>
        `;
        
        // Agregar evento de drag
        div.addEventListener('dragstart', (e) => this.onDragStart(e));
        
        return div;
    }
    
    /**
     * Aplicar filtros
     */
    aplicarFiltros() {
        return this.solicitudes.filter(solicitud => {
            // Filtro por especialidad
            if (this.filtros.especialidad && solicitud.especialidad_solicitada !== this.filtros.especialidad) {
                return false;
            }
            
            // Filtro por profesional
            if (this.filtros.profesional && solicitud.profesional_id != this.filtros.profesional) {
                return false;
            }
            
            // Filtro por búsqueda
            if (this.filtros.busqueda) {
                const busqueda = this.filtros.busqueda.toLowerCase();
                const textoCompleto = `
                    ${solicitud.id}
                    ${solicitud.paciente_nombre}
                    ${solicitud.profesional_nombre || ''}
                    ${solicitud.servicio_nombre}
                    ${solicitud.especialidad_solicitada || ''}
                `.toLowerCase();
                
                if (!textoCompleto.includes(busqueda)) {
                    return false;
                }
            }
            
            return true;
        });
    }
    
    /**
     * Mapear estado de BD a estado de Kanban
     */
    mapearEstado(estadoBD) {
        const mapa = {
            'pendiente': 'pendiente',
            'pago_pendiente': 'pendiente',
            'pago_confirmado': 'pendiente',
            'asignada': 'asignada',
            'aceptada': 'asignada',
            'en_camino': 'en_camino',
            'en_proceso': 'en_proceso',
            'completada': 'completada',
            'finalizada': 'completada'
        };
        
        return mapa[estadoBD] || 'pendiente';
    }
    
    /**
     * Calcular tiempo transcurrido
     */
    calcularTiempoTranscurrido(fechaCreacion) {
        const ahora = new Date();
        const fecha = new Date(fechaCreacion);
        const diffMs = ahora - fecha;
        const diffMins = Math.floor(diffMs / 60000);
        
        if (diffMins < 60) {
            return `${diffMins}m`;
        } else if (diffMins < 1440) {
            return `${Math.floor(diffMins / 60)}h`;
        } else {
            return `${Math.floor(diffMins / 1440)}d`;
        }
    }
    
    /**
     * Drag and Drop
     */
    iniciarDragAndDrop() {
        // Permitir drop en columnas
        document.querySelectorAll('.kanban-column-body').forEach(column => {
            column.addEventListener('dragover', (e) => this.onDragOver(e));
            column.addEventListener('drop', (e) => this.onDrop(e));
        });
    }
    
    onDragStart(e) {
        e.dataTransfer.effectAllowed = 'move';
        e.dataTransfer.setData('solicitudId', e.target.dataset.solicitudId);
        e.target.classList.add('dragging');
    }
    
    onDragOver(e) {
        e.preventDefault();
        e.dataTransfer.dropEffect = 'move';
    }
    
    async onDrop(e) {
        e.preventDefault();
        
        const solicitudId = e.dataTransfer.getData('solicitudId');
        const nuevoEstado = e.target.closest('.kanban-column').dataset.estado;
        
        // Remover clase dragging
        document.querySelector('.dragging')?.classList.remove('dragging');
        
        await this.cambiarEstado(solicitudId, nuevoEstado);
    }
    
    /**
     * Cambiar estado de solicitud
     */
    async cambiarEstado(solicitudId, nuevoEstado) {
        try {
            const estadoBD = this.mapearEstadoInverso(nuevoEstado);
            
            const response = await fetch(`${typeof BASE_URL !== 'undefined' ? BASE_URL : ''}/api/admin/solicitudes/${solicitudId}/estado`, {
                method: 'PATCH',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ estado: estadoBD })
            });
            
            if (!response.ok) throw new Error('Error al cambiar estado');
            
            window.ToastNotification.show('Estado actualizado correctamente', 'success');
            await this.cargarSolicitudes();
            
        } catch (error) {
            console.error('Error:', error);
            window.ToastNotification.show('Error al cambiar estado', 'error');
        }
    }
    
    mapearEstadoInverso(estadoKanban) {
        const mapa = {
            'pendiente': 'pendiente',
            'asignada': 'asignada',
            'en_camino': 'en_camino',
            'en_proceso': 'en_proceso',
            'completada': 'completada'
        };
        
        return mapa[estadoKanban] || 'pendiente';
    }
    
    /**
     * Ver detalle de solicitud
     */
    verDetalle(solicitudId) {
        // Trigger evento para abrir modal de detalle
        window.dispatchEvent(new CustomEvent('ver-detalle-solicitud', { 
            detail: { solicitudId } 
        }));
    }
    
    /**
     * Asignar profesional
     */
    asignarProfesional(solicitudId) {
        window.dispatchEvent(new CustomEvent('asignar-profesional', { 
            detail: { solicitudId } 
        }));
    }
    
    /**
     * Ver ubicación
     */
    verUbicacion(solicitudId) {
        const solicitud = this.solicitudes.find(s => s.id == solicitudId);
        if (solicitud && solicitud.direccion) {
            window.open(`https://www.google.com/maps/search/?api=1&query=${encodeURIComponent(solicitud.direccion)}`);
        }
    }
    
    /**
     * Actualización automática cada 30 segundos
     */
    iniciarActualizacionAutomatica() {
        setInterval(() => {
            this.cargarSolicitudes();
        }, 30000);
    }
    
    /**
     * Aplicar filtro
     */
    aplicarFiltro(tipo, valor) {
        this.filtros[tipo] = valor;
        this.renderSolicitudes();
    }
}

// Instancia global
// Instancia global (se inicializará manualmente desde Alpine)
let kanbanBoard;

// NO inicializar automáticamente, se hará desde el tab Alpine
// La instancia se crea cuando el usuario hace clic en el tab Kanban
