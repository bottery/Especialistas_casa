/**
 * Panel de administración: Gestión de pagos por transferencia
 * Admin/Superadmin: Aprobar o rechazar pagos pendientes
 */

class AdminPagosPanel {
    constructor() {
        this.pagosPendientes = [];
        this.init();
    }
    
    async init() {
        await this.cargarPagosPendientes();
        this.renderizarTabla();
        this.setupAutoRefresh();
    }
    
    /**
     * Cargar pagos pendientes desde API
     */
    async cargarPagosPendientes() {
        try {
            const response = await fetch('/api/admin/pagos/pendientes', {
                credentials: 'include'
            });
            
            if (response.ok) {
                const data = await response.json();
                this.pagosPendientes = data.pagos || [];
            } else {
                throw new Error('Error al cargar pagos');
            }
        } catch (error) {
            console.error('Error:', error);
            if (window.Toast) {
                window.Toast.show('Error al cargar pagos pendientes', 'error');
            }
        }
    }
    
    /**
     * Renderizar tabla de pagos
     */
    renderizarTabla() {
        const container = document.getElementById('tablaPagosPendientes');
        if (!container) return;
        
        if (this.pagosPendientes.length === 0) {
            container.innerHTML = `
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    No hay pagos pendientes de confirmación
                </div>
            `;
            return;
        }
        
        const html = `
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID Pago</th>
                            <th>Paciente</th>
                            <th>Servicio</th>
                            <th>Monto</th>
                            <th>Fecha Pago</th>
                            <th>Estado</th>
                            <th>Comprobante</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${this.pagosPendientes.map(pago => this.renderizarFilaPago(pago)).join('')}
                    </tbody>
                </table>
            </div>
        `;
        
        container.innerHTML = html;
    }
    
    /**
     * Renderizar una fila de pago
     */
    renderizarFilaPago(pago) {
        const estadoBadge = pago.estado_pago === 'comprobante_subido' 
            ? '<span class="badge bg-warning">Comprobante subido</span>'
            : '<span class="badge bg-secondary">Pendiente comprobante</span>';
        
        const tieneComprobante = pago.comprobante_imagen != null;
        
        return `
            <tr>
                <td>#${pago.pago_id}</td>
                <td>
                    <div><strong>${pago.paciente_nombre}</strong></div>
                    <small class="text-muted">${pago.paciente_email}</small>
                </td>
                <td>${pago.servicio_nombre}</td>
                <td><strong>$${Number(pago.monto).toLocaleString('es-CO')}</strong></td>
                <td>${this.formatearFecha(pago.fecha_pago)}</td>
                <td>${estadoBadge}</td>
                <td class="text-center">
                    ${tieneComprobante ? `
                        <button class="btn btn-sm btn-info" onclick="adminPagos.verComprobante('${pago.comprobante_imagen}')">
                            <i class="fas fa-file-image"></i> Ver
                        </button>
                    ` : '<span class="text-muted">Sin comprobante</span>'}
                </td>
                <td>
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-primary" 
                                onclick="adminPagos.verDetalle(${pago.pago_id})"
                                title="Ver detalle">
                            <i class="fas fa-eye"></i>
                        </button>
                        ${tieneComprobante ? `
                            <button class="btn btn-outline-success" 
                                    onclick="adminPagos.aprobarPago(${pago.pago_id})"
                                    title="Aprobar">
                                <i class="fas fa-check"></i>
                            </button>
                            <button class="btn btn-outline-danger" 
                                    onclick="adminPagos.rechazarPago(${pago.pago_id})"
                                    title="Rechazar">
                                <i class="fas fa-times"></i>
                            </button>
                        ` : ''}
                    </div>
                </td>
            </tr>
        `;
    }
    
    /**
     * Ver comprobante en modal
     */
    verComprobante(imagenPath) {
        const modalHTML = `
            <div class="modal fade" id="modalComprobante" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Comprobante de Pago</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body text-center">
                            ${imagenPath.endsWith('.pdf') ? 
                                `<embed src="${imagenPath}" type="application/pdf" width="100%" height="600px">` :
                                `<img src="${imagenPath}" class="img-fluid" alt="Comprobante">`
                            }
                        </div>
                        <div class="modal-footer">
                            <a href="${imagenPath}" download class="btn btn-primary">
                                <i class="fas fa-download me-2"></i>Descargar
                            </a>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        const existing = document.getElementById('modalComprobante');
        if (existing) existing.remove();
        
        document.body.insertAdjacentHTML('beforeend', modalHTML);
        const modal = new bootstrap.Modal(document.getElementById('modalComprobante'));
        modal.show();
    }
    
    /**
     * Ver detalle completo del pago
     */
    async verDetalle(pagoId) {
        try {
            const response = await fetch(`/api/admin/pagos/${pagoId}`, {
                credentials: 'include'
            });
            
            if (!response.ok) throw new Error('Error al cargar detalle');
            
            const pago = await response.json();
            
            const modalHTML = `
                <div class="modal fade" id="modalDetallePago" tabindex="-1">
                    <div class="modal-dialog modal-xl">
                        <div class="modal-content">
                            <div class="modal-header bg-primary text-white">
                                <h5 class="modal-title">Detalle de Pago #${pago.id}</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6 class="mb-3">Información del Paciente</h6>
                                        <p><strong>Nombre:</strong> ${pago.paciente_nombre}</p>
                                        <p><strong>Email:</strong> ${pago.paciente_email}</p>
                                        <p><strong>Teléfono:</strong> ${pago.paciente_telefono || 'N/A'}</p>
                                        <p><strong>Documento:</strong> ${pago.paciente_documento || 'N/A'}</p>
                                    </div>
                                    <div class="col-md-6">
                                        <h6 class="mb-3">Información del Servicio</h6>
                                        <p><strong>Servicio:</strong> ${pago.servicio_nombre}</p>
                                        <p><strong>Tipo:</strong> ${pago.servicio_tipo}</p>
                                        <p><strong>Modalidad:</strong> ${pago.modalidad}</p>
                                        <p><strong>Fecha programada:</strong> ${this.formatearFecha(pago.fecha_programada)}</p>
                                    </div>
                                </div>
                                
                                <hr>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6 class="mb-3">Datos del Pago</h6>
                                        <p><strong>Monto:</strong> $${Number(pago.monto).toLocaleString('es-CO')} COP</p>
                                        <p><strong>Método:</strong> Transferencia Bancaria</p>
                                        <p><strong>Estado:</strong> ${pago.estado}</p>
                                        <p><strong>Fecha:</strong> ${this.formatearFecha(pago.fecha_pago)}</p>
                                        ${pago.numero_referencia ? `<p><strong>Referencia:</strong> ${pago.numero_referencia}</p>` : ''}
                                    </div>
                                    <div class="col-md-6">
                                        ${pago.comprobante_imagen ? `
                                            <h6 class="mb-3">Comprobante</h6>
                                            <div class="text-center">
                                                <img src="${pago.comprobante_imagen}" 
                                                     class="img-thumbnail" 
                                                     style="max-height: 300px;"
                                                     onclick="adminPagos.verComprobante('${pago.comprobante_imagen}')">
                                                <p class="mt-2">
                                                    <button class="btn btn-sm btn-info" 
                                                            onclick="adminPagos.verComprobante('${pago.comprobante_imagen}')">
                                                        Ver en grande
                                                    </button>
                                                </p>
                                            </div>
                                        ` : '<p class="text-muted">Sin comprobante</p>'}
                                    </div>
                                </div>
                                
                                ${pago.observaciones ? `
                                    <hr>
                                    <h6>Observaciones</h6>
                                    <p>${pago.observaciones}</p>
                                ` : ''}
                            </div>
                            <div class="modal-footer">
                                ${pago.estado === 'comprobante_subido' || pago.estado === 'pendiente' ? `
                                    <button class="btn btn-success" onclick="adminPagos.aprobarPago(${pago.id})">
                                        <i class="fas fa-check me-2"></i>Aprobar Pago
                                    </button>
                                    <button class="btn btn-danger" onclick="adminPagos.rechazarPago(${pago.id})">
                                        <i class="fas fa-times me-2"></i>Rechazar Pago
                                    </button>
                                ` : ''}
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            const existing = document.getElementById('modalDetallePago');
            if (existing) existing.remove();
            
            document.body.insertAdjacentHTML('beforeend', modalHTML);
            const modal = new bootstrap.Modal(document.getElementById('modalDetallePago'));
            modal.show();
            
        } catch (error) {
            console.error('Error:', error);
            if (window.Toast) {
                window.Toast.show('Error al cargar detalle del pago', 'error');
            }
        }
    }
    
    /**
     * Aprobar pago
     */
    async aprobarPago(pagoId) {
        const observaciones = prompt('Observaciones (opcional):');
        
        if (observaciones === null) return; // Usuario canceló
        
        if (!confirm('¿Confirmar aprobación de este pago?')) return;
        
        try {
            const response = await fetch(`/api/admin/pagos/${pagoId}/aprobar`, {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                credentials: 'include',
                body: JSON.stringify({ observaciones })
            });
            
            const data = await response.json();
            
            if (response.ok) {
                if (window.Toast) {
                    window.Toast.show(data.message || 'Pago aprobado exitosamente', 'success');
                } else {
                    alert(data.message);
                }
                
                // Cerrar modales
                const modals = document.querySelectorAll('.modal');
                modals.forEach(m => {
                    const modal = bootstrap.Modal.getInstance(m);
                    if (modal) modal.hide();
                });
                
                // Recargar lista
                await this.cargarPagosPendientes();
                this.renderizarTabla();
            } else {
                throw new Error(data.error || 'Error al aprobar pago');
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
     * Rechazar pago
     */
    async rechazarPago(pagoId) {
        const motivo = prompt('Motivo del rechazo (requerido):');
        
        if (!motivo || motivo.trim() === '') {
            alert('Debes ingresar un motivo para rechazar el pago');
            return;
        }
        
        const observaciones = prompt('Observaciones adicionales (opcional):');
        
        if (!confirm('¿Confirmar rechazo de este pago?')) return;
        
        try {
            const response = await fetch(`/api/admin/pagos/${pagoId}/rechazar`, {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                credentials: 'include',
                body: JSON.stringify({ motivo, observaciones })
            });
            
            const data = await response.json();
            
            if (response.ok) {
                if (window.Toast) {
                    window.Toast.show(data.message || 'Pago rechazado', 'success');
                } else {
                    alert(data.message);
                }
                
                // Cerrar modales
                const modals = document.querySelectorAll('.modal');
                modals.forEach(m => {
                    const modal = bootstrap.Modal.getInstance(m);
                    if (modal) modal.hide();
                });
                
                // Recargar lista
                await this.cargarPagosPendientes();
                this.renderizarTabla();
            } else {
                throw new Error(data.error || 'Error al rechazar pago');
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
            await this.cargarPagosPendientes();
            this.renderizarTabla();
        }, 30000);
    }
}

// Auto-inicializar si existe el contenedor
if (typeof window !== 'undefined') {
    window.AdminPagosPanel = AdminPagosPanel;
    
    document.addEventListener('DOMContentLoaded', () => {
        if (document.getElementById('tablaPagosPendientes')) {
            window.adminPagos = new AdminPagosPanel();
        }
    });
}
