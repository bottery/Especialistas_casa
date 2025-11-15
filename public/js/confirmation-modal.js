/**
 * Sistema de Modal de Confirmación
 * Reemplaza window.confirm() con modales personalizados
 */

class ConfirmationModal {
    constructor() {
        this.modalContainer = null;
        this.currentResolve = null;
        this.init();
    }

    init() {
        // Crear el contenedor del modal si no existe
        if (!document.getElementById('confirmation-modal-root')) {
            this.modalContainer = document.createElement('div');
            this.modalContainer.id = 'confirmation-modal-root';
            document.body.appendChild(this.modalContainer);
        }
    }

    /**
     * Muestra un modal de confirmación
     * @param {Object} options - Opciones de configuración
     * @param {string} options.title - Título del modal
     * @param {string} options.message - Mensaje del modal
     * @param {string} options.confirmText - Texto del botón confirmar (default: "Confirmar")
     * @param {string} options.cancelText - Texto del botón cancelar (default: "Cancelar")
     * @param {string} options.type - Tipo de modal: 'danger', 'warning', 'info' (default: 'warning')
     * @param {boolean} options.requireReason - Si requiere un campo de texto para motivo
     * @param {string} options.reasonPlaceholder - Placeholder del campo de motivo
     * @returns {Promise<{confirmed: boolean, reason: string|null}>}
     */
    show(options = {}) {
        return new Promise((resolve) => {
            this.currentResolve = resolve;

            const {
                title = '¿Estás seguro?',
                message = 'Esta acción no se puede deshacer.',
                confirmText = 'Confirmar',
                cancelText = 'Cancelar',
                type = 'warning',
                requireReason = false,
                reasonPlaceholder = 'Escribe el motivo...'
            } = options;

            // Configuración de colores según tipo
            const typeConfig = {
                danger: {
                    bgColor: 'bg-red-100',
                    iconColor: 'text-red-600',
                    buttonColor: 'bg-red-600 hover:bg-red-700',
                    icon: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>'
                },
                warning: {
                    bgColor: 'bg-yellow-100',
                    iconColor: 'text-yellow-600',
                    buttonColor: 'bg-yellow-600 hover:bg-yellow-700',
                    icon: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>'
                },
                info: {
                    bgColor: 'bg-blue-100',
                    iconColor: 'text-blue-600',
                    buttonColor: 'bg-blue-600 hover:bg-blue-700',
                    icon: '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>'
                }
            };

            const config = typeConfig[type] || typeConfig.warning;

            const modalHTML = `
                <div class="confirmation-modal-overlay fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4" style="animation: fadeIn 0.2s ease-out;">
                    <div class="confirmation-modal-content bg-white rounded-xl shadow-2xl max-w-md w-full p-6" style="animation: slideUp 0.3s ease-out;">
                        <div class="flex items-start mb-4">
                            <div class="${config.bgColor} rounded-full p-3 mr-4">
                                <svg class="w-6 h-6 ${config.iconColor}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    ${config.icon}
                                </svg>
                            </div>
                            <div class="flex-1">
                                <h3 class="text-lg font-semibold text-gray-900 mb-2">${title}</h3>
                                <p class="text-gray-600 text-sm">${message}</p>
                            </div>
                        </div>

                        ${requireReason ? `
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Motivo *</label>
                                <textarea 
                                    id="confirmation-reason-input"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-${type === 'danger' ? 'red' : type === 'warning' ? 'yellow' : 'blue'}-500 focus:border-transparent text-sm resize-none"
                                    rows="3"
                                    placeholder="${reasonPlaceholder}"
                                ></textarea>
                                <p class="text-xs text-red-600 mt-1 hidden" id="reason-error">El motivo es obligatorio</p>
                            </div>
                        ` : ''}

                        <div class="flex justify-end space-x-3">
                            <button 
                                id="modal-cancel-btn"
                                class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition font-medium text-sm"
                            >
                                ${cancelText}
                            </button>
                            <button 
                                id="modal-confirm-btn"
                                class="${config.buttonColor} px-4 py-2 rounded-lg text-white transition font-medium text-sm"
                            >
                                ${confirmText}
                            </button>
                        </div>
                    </div>
                </div>

                <style>
                    @keyframes fadeIn {
                        from { opacity: 0; }
                        to { opacity: 1; }
                    }
                    @keyframes slideUp {
                        from { transform: translateY(20px); opacity: 0; }
                        to { transform: translateY(0); opacity: 1; }
                    }
                </style>
            `;

            this.modalContainer.innerHTML = modalHTML;

            // Event listeners
            const overlay = this.modalContainer.querySelector('.confirmation-modal-overlay');
            const cancelBtn = document.getElementById('modal-cancel-btn');
            const confirmBtn = document.getElementById('modal-confirm-btn');
            const reasonInput = requireReason ? document.getElementById('confirmation-reason-input') : null;
            const reasonError = requireReason ? document.getElementById('reason-error') : null;

            const handleCancel = () => {
                this.close();
                resolve({ confirmed: false, reason: null });
            };

            const handleConfirm = () => {
                if (requireReason) {
                    const reason = reasonInput.value.trim();
                    if (reason.length === 0) {
                        reasonError.classList.remove('hidden');
                        reasonInput.classList.add('border-red-500');
                        reasonInput.focus();
                        return;
                    }
                    this.close();
                    resolve({ confirmed: true, reason });
                } else {
                    this.close();
                    resolve({ confirmed: true, reason: null });
                }
            };

            cancelBtn.addEventListener('click', handleCancel);
            confirmBtn.addEventListener('click', handleConfirm);
            
            // Cerrar al hacer click fuera del modal
            overlay.addEventListener('click', (e) => {
                if (e.target === overlay) {
                    handleCancel();
                }
            });

            // Cerrar con tecla ESC
            const handleEscape = (e) => {
                if (e.key === 'Escape') {
                    handleCancel();
                    document.removeEventListener('keydown', handleEscape);
                }
            };
            document.addEventListener('keydown', handleEscape);

            // Enter para confirmar si no requiere motivo
            if (!requireReason) {
                const handleEnter = (e) => {
                    if (e.key === 'Enter') {
                        handleConfirm();
                        document.removeEventListener('keydown', handleEnter);
                    }
                };
                document.addEventListener('keydown', handleEnter);
            }

            // Focus en input de motivo si existe
            if (reasonInput) {
                reasonInput.focus();
            }
        });
    }

    close() {
        if (this.modalContainer) {
            this.modalContainer.innerHTML = '';
        }
    }

    /**
     * Helper para confirmación de eliminación
     */
    async confirmDelete(itemName = 'este elemento') {
        return await this.show({
            title: '¿Eliminar?',
            message: `¿Estás seguro que deseas eliminar ${itemName}? Esta acción no se puede deshacer.`,
            confirmText: 'Eliminar',
            cancelText: 'Cancelar',
            type: 'danger'
        });
    }

    /**
     * Helper para confirmación de rechazo
     */
    async confirmReject(itemName = 'esta solicitud') {
        return await this.show({
            title: '¿Rechazar?',
            message: `¿Estás seguro que deseas rechazar ${itemName}?`,
            confirmText: 'Rechazar',
            cancelText: 'Cancelar',
            type: 'danger',
            requireReason: true,
            reasonPlaceholder: 'Explica el motivo del rechazo...'
        });
    }

    /**
     * Helper para confirmación de cancelación
     */
    async confirmCancel(itemName = 'esta acción') {
        return await this.show({
            title: '¿Cancelar?',
            message: `¿Estás seguro que deseas cancelar ${itemName}?`,
            confirmText: 'Sí, cancelar',
            cancelText: 'No',
            type: 'warning',
            requireReason: true,
            reasonPlaceholder: 'Explica el motivo de la cancelación...'
        });
    }
}

// Instancia global
window.ConfirmModal = new ConfirmationModal();
