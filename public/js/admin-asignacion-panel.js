/**
 * Panel de administración: Asignación de profesionales a solicitudes
 * Admin/Superadmin: Asignar profesionales ordenados por calificación
 */

class AdminAsignacionPanel {
    constructor() {
        this.solicitudesPendientes = [];
        this.profesionalesDisponibles = [];
        this.solicitudSeleccionada = null;
        this.init();
    }
    
    async init() {
        await this.cargarSolicitudesPendientes();
        this.renderizarSolicitudes();
        this.setupAutoRefresh();
    }
    
    /**
     * Cargar solicitudes pendientes de asignación
     */
    async cargarSolicitudesPendientes() {
        try {
            const response = await fetch('/api/admin/solicitudes/pendientes', {
                credentials: 'include'
            });
            
            if (response.ok) {
                const data = await response.json();
                this.solicitudesPendientes = data.solicitudes || [];
            }
        } catch (error) {
            console.error('Error:', error);
        }
    }
    
    /**
     * Renderizar lista de solicitudes
     */
    renderizarSolicitudes() {
        const container = document.getElementById('tablaSolicitudesPendientes');
        if (!container) return;
        
        if (this.solicitudesPendientes.length === 0) {
            container.innerHTML = `
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    No hay solicitudes pendientes de asignación
                </div>
            `;
            return;
        }
        
        const html = `
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Paciente</th>
                            <th>Servicio</th>
                            <th>Modalidad</th>
                            <th>Fecha Programada</th>
                            <th>Monto</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${this.solicitudesPendientes.map(sol => this.renderizarFilaSolicitud(sol)).join('')}
                    </tbody>
                </table>
            </div>
        `;
        
        container.innerHTML = html;
    }
    
    /**
     * Renderizar fila de solicitud
     */
    renderizarFilaSolicitud(solicitud) {
        return `
            <tr>
                <td>#${solicitud.id}</td>
                <td>
                    <div><strong>${solicitud.paciente_nombre}</strong></div>
                    <small class="text-muted">${solicitud.paciente_email}</small>
                </td>
                <td>
                    <div>${solicitud.servicio_nombre}</div>
                    <small class="badge bg-secondary">${solicitud.servicio_tipo}</small>
                </td>
                <td><span class="badge bg-info">${solicitud.modalidad}</span></td>
                <td>${this.formatearFecha(solicitud.fecha_programada)}</td>
                <td><strong>$${Number(solicitud.monto_total).toLocaleString('es-CO')}</strong></td>
                <td>
                    <button class="btn btn-sm btn-primary" 
                            onclick="adminAsignacion.abrirModalAsignacion(${solicitud.id}, '${solicitud.servicio_tipo}', '${solicitud.modalidad}')">
                        <i class="fas fa-user-plus me-1"></i>Asignar Profesional
                    </button>
                </td>
            </tr>
        `;
    }
    
    /**
     * Abrir modal para asignar profesional
     */
    async abrirModalAsignacion(solicitudId, servicioTipo, modalidad) {
        this.solicitudSeleccionada = solicitudId;
        
        // Cargar profesionales disponibles
        await this.cargarProfesionalesDisponibles(servicioTipo, modalidad);
        
        const solicitud = this.solicitudesPendientes.find(s => s.id === solicitudId);
        
        const modalHTML = `
            <div class="modal fade" id="modalAsignarProfesional" tabindex="-1" data-bs-backdrop="static">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title">
                                <i class="fas fa-user-md me-2"></i>
                                Asignar Profesional - Solicitud #${solicitudId}
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="alert alert-info mb-4">
                                <strong>Paciente:</strong> ${solicitud.paciente_nombre}<br>
                                <strong>Servicio:</strong> ${solicitud.servicio_nombre} (${solicitud.modalidad})<br>
                                <strong>Fecha:</strong> ${this.formatearFecha(solicitud.fecha_programada)}
                            </div>
                            
                            <h6 class="mb-3">
                                <i class="fas fa-trophy text-warning me-2"></i>
                                Profesionales Disponibles (ordenados por calificación)
                            </h6>
                            
                            <div id="listaProfesionales">
                                ${this.renderizarProfesionales()}
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        const existing = document.getElementById('modalAsignarProfesional');
        if (existing) existing.remove();
        
        document.body.insertAdjacentHTML('beforeend', modalHTML);
        const modal = new bootstrap.Modal(document.getElementById('modalAsignarProfesional'));
        modal.show();
    }
    
    /**
     * Cargar profesionales disponibles con calificaciones
     */
    async cargarProfesionalesDisponibles(servicioTipo, modalidad) {
        try {
            const params = new URLSearchParams({
                servicio_tipo: servicioTipo,
                modalidad: modalidad
            });
            
            const response = await fetch(`/api/admin/profesionales/disponibles?${params}`, {
                credentials: 'include'
            });
            
            if (response.ok) {
                const data = await response.json();
                this.profesionalesDisponibles = data.profesionales || [];
            }
        } catch (error) {
            console.error('Error:', error);
        }
    }
    
    /**
     * Renderizar lista de profesionales
     */
    renderizarProfesionales() {
        if (this.profesionalesDisponibles.length === 0) {
            return `
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    No hay profesionales disponibles para este servicio
                </div>
            `;
        }
        
        return this.profesionalesDisponibles.map((prof, index) => {
            const ranking = index + 1;
            const estrellasHTML = this.generarEstrellas(prof.calificacion_promedio);
            const badgeColor = ranking === 1 ? 'bg-warning' : ranking === 2 ? 'bg-secondary' : 'bg-bronze';
            
            return `
                <div class="card mb-3 shadow-sm profesional-card" 
                     onclick="adminAsignacion.seleccionarProfesional(${prof.id})"
                     style="cursor: pointer; transition: transform 0.2s;"
                     onmouseover="this.style.transform='scale(1.02)'"
                     onmouseout="this.style.transform='scale(1)'">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-1 text-center">
                                <h2 class="mb-0">
                                    <span class="badge ${badgeColor}" style="font-size: 1.2rem;">
                                        #${ranking}
                                    </span>
                                </h2>
                            </div>
                            <div class="col-md-2 text-center">
                                ${prof.foto_perfil ? 
                                    `<img src="${prof.foto_perfil}" class="rounded-circle" style="width: 70px; height: 70px; object-fit: cover;">` :
                                    `<div class="rounded-circle bg-secondary text-white d-inline-flex align-items-center justify-content-center" style="width: 70px; height: 70px; font-size: 1.5rem;">
                                        ${prof.nombre.charAt(0)}
                                    </div>`
                                }
                            </div>
                            <div class="col-md-4">
                                <h5 class="mb-1">${prof.nombre}</h5>
                                <p class="mb-1 text-muted">${prof.especialidad || 'Profesional de salud'}</p>
                                <p class="mb-0">
                                    <small class="text-muted">
                                        <i class="fas fa-briefcase me-1"></i>${prof.experiencia_anos} años de experiencia
                                    </small>
                                </p>
                            </div>
                            <div class="col-md-2 text-center">
                                <div class="mb-2">
                                    ${estrellasHTML}
                                </div>
                                <div><strong>${prof.calificacion_promedio.toFixed(1)}</strong>/5</div>
                                <small class="text-muted">${prof.total_calificaciones} opiniones</small>
                            </div>
                            <div class="col-md-2 text-center">
                                <p class="mb-1">
                                    <i class="fas fa-check-circle text-success me-1"></i>
                                    <strong>${prof.servicios_completados}</strong> servicios
                                </p>
                                <p class="mb-0">
                                    <i class="fas fa-clock text-info me-1"></i>
                                    ${prof.servicios_activos} activos
                                </p>
                            </div>
                            <div class="col-md-1 text-center">
                                <button class="btn btn-success btn-sm" onclick="event.stopPropagation(); adminAsignacion.confirmarAsignacion(${prof.id}, '${prof.nombre}')">
                                    <i class="fas fa-check"></i> Asignar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }).join('');
    }
    
    /**
     * Generar estrellas de calificación
     */
    generarEstrellas(calificacion) {
        const fullStars = Math.floor(calificacion);
        const hasHalfStar = calificacion % 1 >= 0.5;
        const emptyStars = 5 - fullStars - (hasHalfStar ? 1 : 0);
        
        let html = '';
        
        for (let i = 0; i < fullStars; i++) {
            html += '<i class="fas fa-star text-warning"></i>';
        }
        
        if (hasHalfStar) {
            html += '<i class="fas fa-star-half-alt text-warning"></i>';
        }
        
        for (let i = 0; i < emptyStars; i++) {
            html += '<i class="far fa-star text-warning"></i>';
        }
        
        return html;
    }
    
    /**
     * Seleccionar profesional (highlight)
     */
    seleccionarProfesional(profesionalId) {
        document.querySelectorAll('.profesional-card').forEach(card => {
            card.classList.remove('border-primary');
            card.style.borderWidth = '1px';
        });
        
        event.currentTarget.classList.add('border-primary');
        event.currentTarget.style.borderWidth = '3px';
    }
    
    /**
     * Confirmar asignación
     */
    async confirmarAsignacion(profesionalId, profesionalNombre) {
        if (!confirm(`¿Asignar este servicio a ${profesionalNombre}?`)) return;
        
        try {
            const response = await fetch(`/api/admin/solicitudes/${this.solicitudSeleccionada}/asignar`, {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                credentials: 'include',
                body: JSON.stringify({ profesional_id: profesionalId })
            });
            
            const data = await response.json();
            
            if (response.ok) {
                if (window.Toast) {
                    window.Toast.show(data.message || 'Profesional asignado exitosamente', 'success');
                } else {
                    alert(data.message);
                }
                
                // Cerrar modal
                const modal = bootstrap.Modal.getInstance(document.getElementById('modalAsignarProfesional'));
                if (modal) modal.hide();
                
                // Recargar lista
                await this.cargarSolicitudesPendientes();
                this.renderizarSolicitudes();
            } else {
                throw new Error(data.error || 'Error al asignar profesional');
            }
        } catch (error) {
            console.error('Error:', error);
            if (window.Toast) {
                window.Toast.show(error.message, 'error');
            } else {
                alert('Error: ' + error.message);
            }
        }
    }
    
    /**
     * Formatear fecha
     */
    formatearFecha(fecha) {
        if (!fecha) return 'N/A';
        const d = new Date(fecha);
        return d.toLocaleString('es-CO', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }
    
    /**
     * Auto-refresh cada 30 segundos
     */
    setupAutoRefresh() {
        setInterval(async () => {
            await this.cargarSolicitudesPendientes();
            this.renderizarSolicitudes();
        }, 30000);
    }
}

// CSS adicional para badge bronze
const style = document.createElement('style');
style.textContent = `
    .bg-bronze {
        background-color: #cd7f32 !important;
        color: white !important;
    }
`;
document.head.appendChild(style);

// Auto-inicializar
if (typeof window !== 'undefined') {
    window.AdminAsignacionPanel = AdminAsignacionPanel;
    
    document.addEventListener('DOMContentLoaded', () => {
        if (document.getElementById('tablaSolicitudesPendientes')) {
            window.adminAsignacion = new AdminAsignacionPanel();
        }
    });
}
