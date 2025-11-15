/**
 * Utilidad para gestión de pagos por transferencia
 * Frontend para pacientes: Mostrar QR, subir comprobante
 */

class TransferenciaPago {
    constructor() {
        this.modal = null;
        this.pagoId = null;
        this.solicitudId = null;
    }
    
    /**
     * Mostrar modal con datos de transferencia y QR
     * @param {Object} datosPago - Información del pago y transferencia
     */
    mostrarModalTransferencia(datosPago) {
        this.pagoId = datosPago.pago_id;
        this.solicitudId = datosPago.solicitud_id;
        
        const datos = datosPago.datos_transferencia;
        
        const modalHTML = `
            <div class="modal fade" id="modalTransferencia" tabindex="-1" data-bs-backdrop="static">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title">
                                <i class="fas fa-money-bill-transfer me-2"></i>
                                Completa tu transferencia
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Monto a transferir:</strong> $${Number(datosPago.monto_total).toLocaleString('es-CO')} COP
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card mb-3">
                                        <div class="card-header bg-light">
                                            <strong>Datos bancarios</strong>
                                        </div>
                                        <div class="card-body">
                                            ${datos.banco_nombre ? `<p class="mb-2"><strong>Banco:</strong> ${datos.banco_nombre}</p>` : ''}
                                            ${datos.banco_tipo_cuenta ? `<p class="mb-2"><strong>Tipo:</strong> ${datos.banco_tipo_cuenta}</p>` : ''}
                                            ${datos.banco_cuenta ? `
                                                <p class="mb-2">
                                                    <strong>Cuenta:</strong> 
                                                    <span id="numeroCuenta">${datos.banco_cuenta}</span>
                                                    <button class="btn btn-sm btn-outline-secondary ms-2" onclick="window.transferenciaPago.copiarCuenta()">
                                                        <i class="fas fa-copy"></i>
                                                    </button>
                                                </p>
                                            ` : ''}
                                            ${datos.banco_titular ? `<p class="mb-0"><strong>Titular:</strong> ${datos.banco_titular}</p>` : ''}
                                        </div>
                                    </div>
                                    
                                    ${datos.instrucciones ? `
                                        <div class="alert alert-warning">
                                            <small>${datos.instrucciones}</small>
                                        </div>
                                    ` : ''}
                                </div>
                                
                                <div class="col-md-6 text-center">
                                    ${datos.qr_imagen ? `
                                        <div class="card">
                                            <div class="card-header bg-light">
                                                <strong>Código QR</strong>
                                            </div>
                                            <div class="card-body">
                                                <img src="${datos.qr_imagen}" 
                                                     alt="QR Transferencia" 
                                                     class="img-fluid" 
                                                     style="max-width: 250px;">
                                                <p class="text-muted small mt-2">
                                                    Escanea este código con tu app bancaria
                                                </p>
                                            </div>
                                        </div>
                                    ` : `
                                        <div class="alert alert-secondary">
                                            <i class="fas fa-qrcode fa-3x mb-2"></i>
                                            <p>Código QR no disponible</p>
                                        </div>
                                    `}
                                </div>
                            </div>
                            
                            <hr class="my-4">
                            
                            <h6 class="mb-3">Sube tu comprobante de pago</h6>
                            
                            <form id="formComprobante" enctype="multipart/form-data">
                                <div class="mb-3">
                                    <label class="form-label">Número de referencia (opcional)</label>
                                    <input type="text" 
                                           class="form-control" 
                                           id="numeroReferencia" 
                                           name="numero_referencia"
                                           placeholder="Ej: 123456789">
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label">Comprobante de pago *</label>
                                    <input type="file" 
                                           class="form-control" 
                                           id="archivoComprobante" 
                                           name="comprobante"
                                           accept="image/jpeg,image/png,image/jpg,application/pdf"
                                           required>
                                    <div class="form-text">
                                        Formatos aceptados: JPG, PNG, PDF (máx. 10MB)
                                    </div>
                                </div>
                                
                                <div id="previewComprobante" class="mb-3" style="display: none;">
                                    <img id="imgPreview" class="img-thumbnail" style="max-height: 200px;">
                                </div>
                                
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    <strong>Importante:</strong> Tu solicitud será activada una vez el administrador confirme tu pago.
                                </div>
                            </form>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                Cancelar
                            </button>
                            <button type="button" class="btn btn-primary" id="btnEnviarComprobante">
                                <i class="fas fa-upload me-2"></i>
                                Enviar comprobante
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
        
        // Eliminar modal anterior si existe
        const existingModal = document.getElementById('modalTransferencia');
        if (existingModal) {
            existingModal.remove();
        }
        
        // Insertar modal
        document.body.insertAdjacentHTML('beforeend', modalHTML);
        
        // Inicializar modal
        this.modal = new bootstrap.Modal(document.getElementById('modalTransferencia'));
        this.modal.show();
        
        // Event listeners
        this.setupEventListeners();
    }
    
    /**
     * Configurar event listeners
     */
    setupEventListeners() {
        // Preview de imagen
        document.getElementById('archivoComprobante').addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (file && file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    document.getElementById('imgPreview').src = e.target.result;
                    document.getElementById('previewComprobante').style.display = 'block';
                };
                reader.readAsDataURL(file);
            } else {
                document.getElementById('previewComprobante').style.display = 'none';
            }
        });
        
        // Enviar comprobante
        document.getElementById('btnEnviarComprobante').addEventListener('click', () => {
            this.enviarComprobante();
        });
    }
    
    /**
     * Copiar número de cuenta al portapapeles
     */
    copiarCuenta() {
        const numeroCuenta = document.getElementById('numeroCuenta').textContent;
        navigator.clipboard.writeText(numeroCuenta).then(() => {
            if (window.Toast) {
                window.Toast.show('Número de cuenta copiado', 'success');
            } else {
                alert('Número de cuenta copiado');
            }
        });
    }
    
    /**
     * Enviar comprobante al servidor
     */
    async enviarComprobante() {
        const form = document.getElementById('formComprobante');
        const archivo = document.getElementById('archivoComprobante').files[0];
        const numeroReferencia = document.getElementById('numeroReferencia').value;
        
        if (!archivo) {
            if (window.Toast) {
                window.Toast.show('Por favor selecciona el comprobante', 'error');
            } else {
                alert('Por favor selecciona el comprobante');
            }
            return;
        }
        
        // Validar tamaño (10MB)
        if (archivo.size > 10 * 1024 * 1024) {
            if (window.Toast) {
                window.Toast.show('El archivo es demasiado grande. Máximo 10MB', 'error');
            } else {
                alert('El archivo es demasiado grande. Máximo 10MB');
            }
            return;
        }
        
        const btn = document.getElementById('btnEnviarComprobante');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Enviando...';
        
        try {
            const formData = new FormData();
            formData.append('comprobante', archivo);
            if (numeroReferencia) {
                formData.append('numero_referencia', numeroReferencia);
            }
            
            const response = await fetch(`/api/pagos/${this.pagoId}/comprobante`, {
                method: 'POST',
                body: formData,
                credentials: 'include'
            });
            
            const data = await response.json();
            
            if (response.ok) {
                if (window.Toast) {
                    window.Toast.show(data.message || 'Comprobante enviado exitosamente', 'success');
                } else {
                    alert(data.message || 'Comprobante enviado exitosamente');
                }
                
                this.modal.hide();
                
                // Redirigir a mis solicitudes
                setTimeout(() => {
                    window.location.href = '/mis-solicitudes';
                }, 1500);
            } else {
                throw new Error(data.error || 'Error al enviar comprobante');
            }
        } catch (error) {
            console.error('Error:', error);
            if (window.Toast) {
                window.Toast.show(error.message, 'error');
            } else {
                alert('Error: ' + error.message);
            }
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-upload me-2"></i>Enviar comprobante';
        }
    }
    
    /**
     * Verificar estado de pago
     */
    async verificarEstadoPago(pagoId) {
        try {
            const response = await fetch(`/api/admin/pagos/${pagoId}`, {
                credentials: 'include'
            });
            
            if (response.ok) {
                const data = await response.json();
                return data.estado;
            }
        } catch (error) {
            console.error('Error verificando estado:', error);
        }
        return null;
    }
}

// Inicializar globalmente
if (typeof window !== 'undefined') {
    window.TransferenciaPago = TransferenciaPago;
    window.transferenciaPago = new TransferenciaPago();
}
