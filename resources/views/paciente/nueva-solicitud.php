<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Solicitud - VitaHome</title>
    <link rel="icon" type="image/svg+xml" href="<?= asset('/images/vitahome-icon.svg') ?>">
    <script>const BASE_URL = '<?= rtrim(BASE_URL, "/") ?>';</script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="<?= asset('/js/auth-interceptor.js') ?>"></script>
    <script src="<?= asset('/js/validator.js') ?>"></script>
    <script src="<?= asset('/js/toast.js') ?>"></script>
    <link rel="stylesheet" href="<?= url('/css/vitahome-brand.css') ?>">
    <link rel="stylesheet" href="<?= url('/css/breadcrumbs.css') ?>">
    <link rel="stylesheet" href="<?= url('/css/progress.css') ?>">
    <style>
        .gradient-bg { background: linear-gradient(135deg, #14b8a6 0%, #1e3a5f 100%); }
        .gradient-text { background: linear-gradient(135deg, #14b8a6 0%, #1e3a5f 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
    </style>
<script>
window.nuevaSolicitudApp = function() {
    return {
        loading: false,
        paso: 1,
        servicios: [],
        profesionales: [],
        especialidadesDisponibles: [],
        mostrarSelectorEspecialidad: false,
        especialidadSeleccionada: null,
        // Configuraci�n de pagos
        configPagos: null,
        // Modal de �xito
        mostrarModalExito: false,
        mensajeExito: '',
        esTransferencia: false,
        datosTransferencia: null,
        formData: {
            servicio_id: '',
            servicio_tipo: '',
            especialidad_solicitada: null,
            modalidad: 'presencial',
            fecha_programada: '',
            hora_programada: '',
            direccion_servicio: '',
            sintomas: '',
            observaciones: '',
            telefono_contacto: '',
            urgencia: 'normal',
            metodo_pago_preferido: '',
            
            // M�dico Especialista
            especialidad: '',
            rango_horario: '',
            
            // Ambulancia
            tipo_ambulancia: 'basica',
            origen: '',
            destino: '',
            tipo_emergencia: 'programado',
            condicion_paciente: '',
            numero_acompanantes: 0,
            contacto_emergencia: '',
            
            // Enfermer�a
            tipo_cuidado: '',
            intensidad_horaria: '12h',
            duracion_tipo: 'dias',
            duracion_cantidad: 1,
            turno: 'diurno',
            genero_preferido: 'indistinto',
            necesidades_especiales: '',
            condicion_paciente_detalle: '',
            
            // Veterinaria
            tipo_mascota: '',
            nombre_mascota: '',
            edad_mascota: '',
            raza_tamano: '',
            motivo_veterinario: '',
            historial_vacunas: '',
            
            // Laboratorio
            examenes_solicitados: [],
            requiere_ayuno: false,
            preparacion_especial: '',
            email_resultados: '',
            
            // Fisioterapia
            tipo_tratamiento: '',
            numero_sesiones: 1,
            frecuencia_sesiones: 'semanal',
            zona_tratamiento: '',
            lesion_condicion: '',
            
            // Psicolog�a
            tipo_sesion_psico: 'individual',
            motivo_consulta_psico: '',
            primera_vez: true,
            observaciones_privadas: '',
            
            // Nutrici�n
            tipo_consulta_nutri: '',
            objetivos_nutri: '',
            peso_actual: '',
            altura_actual: '',
            condiciones_medicas: '',
            incluye_plan_alimenticio: true
        },
        servicioSeleccionado: null,
        examenesDisponibles: [
            'Hemograma completo',
            'Glucosa',
            'Perfil lip�dico',
            'Creatinina',
            '�cido �rico',
            'Transaminasas',
            'TSH (Tiroides)',
            'Examen de orina',
            'Coprol�gico',
            'Ant�geno prost�tico (PSA)',
            'Hemoglobina glicosilada',
            'Vitamina D',
            'Vitamina B12'
        ],

        async init() {
            const token = localStorage.getItem('token');
            if (!token) {
                window.location.href = BASE_URL + '/login';
                return;
            }

            await this.cargarServicios();
            await this.cargarConfigPagos();
        },

        async cargarConfigPagos() {
            try {
                const response = await fetch(BASE_URL + '/api/configuracion/pagos');
                if (response.ok) {
                    const data = await response.json();
                    this.configPagos = data.configuracion || data || null;
                }
            } catch (error) {
                console.error('Error cargando configuraci�n de pagos:', error);
                // Valores por defecto si falla
                this.configPagos = {
                    banco_nombre: 'Bancolombia',
                    banco_tipo_cuenta: 'Ahorros',
                    banco_cuenta: 'Consultar administrador',
                    banco_titular: 'Especialistas en Casa'
                };
            }
        },

        async cargarServicios() {
            this.loading = true;
            try {
                const response = await fetch(BASE_URL + '/api/servicios');
                if (response.ok) {
                    const data = await response.json();
                    this.servicios = data.servicios || data || [];
                }
            } catch (error) {
                console.error('Error:', error);
            } finally {
                this.loading = false;
            }
        },

        obtenerServicioPorTipo(tipo) {
            // Buscar el primer servicio activo del tipo especificado
            return this.servicios.find(s => s.tipo === tipo && s.activo == 1) || 
                   { id: 0, nombre: tipo, tipo: tipo, precio_base: 0 };
        },

        // Validar formato de email
        validarFormatoEmail(email) {
            if (!email) return false;
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        },

        // Validar email y mostrar mensaje
        validarEmailResultados() {
            if (this.formData.email_resultados && !this.validarFormatoEmail(this.formData.email_resultados)) {
                ToastNotification.warning('El formato del email no parece correcto');
            }
        },
        
        // Actualizar servicio cuando cambia la modalidad
        actualizarServicioPorModalidad() {
            if (!this.formData.servicio_tipo) return;
            
            // Buscar un servicio que coincida con tipo Y modalidad
            const servicioConModalidad = this.servicios.find(s => 
                s.tipo === this.formData.servicio_tipo && 
                s.modalidad === this.formData.modalidad && 
                s.activo == 1
            );
            
            if (servicioConModalidad) {
                this.formData.servicio_id = servicioConModalidad.id;
                this.servicioSeleccionado = servicioConModalidad;
            }
        },

        async seleccionarServicio(servicio) {
            // Validar que el servicio existe y tiene un tipo v�lido
            if (!servicio || !servicio.tipo) {
                ToastNotification.error('No hay servicios disponibles de este tipo. Por favor, contacta con el administrador.');
                return;
            }
            
            // Guardar el tipo de servicio
            this.formData.servicio_tipo = servicio.tipo;
            
            // Establecer modalidad por defecto seg�n el tipo de servicio
            if (servicio.tipo === 'ambulancia') {
                this.formData.modalidad = 'traslado'; // Ambulancia siempre es traslado
            } else if (servicio.tipo === 'laboratorio') {
                this.formData.modalidad = 'domicilio'; // Laboratorio a domicilio
            } else if (servicio.tipo === 'enfermera') {
                this.formData.modalidad = 'domicilio'; // Enfermer�a a domicilio
            }
            // Para m�dico y veterinario, mantiene la modalidad que elija el usuario
            
            // Buscar el servicio correcto seg�n la modalidad actual
            const servicioConModalidad = this.servicios.find(s => 
                s.tipo === servicio.tipo && 
                s.modalidad === this.formData.modalidad && 
                s.activo == 1
            );
            
            // Si existe un servicio con esa modalidad, usarlo; si no, usar el primero disponible
            const servicioFinal = servicioConModalidad || servicio;
            
            if (!servicioFinal.id || servicioFinal.id === 0) {
                ToastNotification.error('No hay servicios disponibles. Por favor, contacta con el administrador.');
                return;
            }
            
            this.formData.servicio_id = servicioFinal.id;
            this.servicioSeleccionado = servicioFinal;
            
            // Si es m�dico, cargar especialidades disponibles
            if (servicio.tipo === 'medico') {
                await this.cargarEspecialidades('medico');
                this.mostrarSelectorEspecialidad = true;
            } else {
                this.mostrarSelectorEspecialidad = false;
                this.formData.especialidad_solicitada = null;
                this.paso = 2;
            }
        },

        async cargarEspecialidades(tipo) {
            try {
                const url = tipo ? `${BASE_URL}/api/especialidades?tipo=${tipo}` : `${BASE_URL}/api/especialidades`;
                const response = await fetch(url);
                if (response.ok) {
                    const data = await response.json();
                    this.especialidadesDisponibles = data.especialidades || [];
                } else {
                    ToastNotification.error('Error al cargar especialidades');
                }
            } catch (error) {
                console.error('Error:', error);
                ToastNotification.error('Error de conexi�n al cargar especialidades');
            }
        },

        seleccionarEspecialidad(especialidad) {
            this.formData.especialidad_solicitada = especialidad;
            this.especialidadSeleccionada = especialidad;
            this.mostrarSelectorEspecialidad = false;
            this.paso = 2;
        },

        volverAServicios() {
            this.paso = 1;
            this.mostrarSelectorEspecialidad = false;
            this.servicioSeleccionado = null;
            this.especialidadSeleccionada = null;
        },



        async enviarSolicitud() {
            if (!this.validarFormulario()) return;
            
            // Validar m�todo de pago
            if (!this.formData.metodo_pago_preferido) {
                ToastNotification.warning('Debes seleccionar un m�todo de pago');
                return;
            }

            this.loading = true;
            try {
                const token = localStorage.getItem('token');
                
                // Preparar fecha y hora seg�n tipo de servicio
                let fechaHora = this.formData.fecha_programada;
                if (this.formData.hora_programada) {
                    fechaHora += ` ${this.formData.hora_programada}:00`;
                } else {
                    fechaHora += ' 00:00:00';
                }
                
                // Preparar datos espec�ficos del servicio
                const payload = {
                    ...this.formData,
                    fecha_programada: fechaHora,
                    examenes_solicitados: JSON.stringify(this.formData.examenes_solicitados),
                    requiere_aprobacion: this.formData.servicio_tipo === 'medico'
                };
                
                const response = await fetch(BASE_URL + '/api/solicitudes', {
                    method: 'POST',
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });

                const data = await response.json();
                
                if (response.ok) {
                    // Mostrar modal de �xito
                    this.mensajeExito = data.message;
                    this.esTransferencia = data.metodo_pago === 'transferencia';
                    this.datosTransferencia = data.datos_transferencia || null;
                    this.mostrarModalExito = true;
                } else {
                    // Mostrar error con Toast
                    const errorMsg = data.error || data.message || 'Error desconocido al crear la solicitud';
                    console.error('Error del servidor:', data);
                    ToastNotification.error(errorMsg);
                }
            } catch (error) {
                console.error('Error de conexi�n:', error);
                ToastNotification.error('Error de conexi�n con el servidor');
            } finally {
                this.loading = false;
            }
        },

        irAMisSolicitudes() {
            window.location.href = BASE_URL + '/paciente/dashboard';
        },

        validarFormulario() {
            const tipo = this.formData.servicio_tipo;
            
            // Validaciones comunes
            if (!this.formData.servicio_id) {
                ToastNotification.warning('Selecciona un servicio');
                return false;
            }
            
            // Validaciones por tipo de servicio
            if (tipo === 'medico') {
                if (!this.formData.fecha_programada || !this.formData.rango_horario) {
                    ToastNotification.warning('Selecciona fecha y rango horario');
                    return false;
                }
                if (!this.formData.sintomas) {
                    ToastNotification.warning('Describe los s�ntomas o motivo de consulta');
                    return false;
                }
            }
            
            if (this.servicioSeleccionado.tipo === 'ambulancia') {
                if (!this.formData.fecha_programada || !this.formData.hora_programada) {
                    ToastNotification.warning('Selecciona fecha y hora para la ambulancia');
                    return false;
                }
                if (!this.formData.origen || !this.formData.destino) {
                    ToastNotification.warning('Ingresa direcci�n de origen y destino');
                    return false;
                }
                if (!this.formData.condicion_paciente) {
                    ToastNotification.warning('Describe la condici�n del paciente');
                    return false;
                }
            }
            
            if (tipo === 'enfermera') {
                if (!this.formData.fecha_programada) {
                    ToastNotification.warning('Selecciona la fecha de inicio');
                    return false;
                }
                if (!this.formData.tipo_cuidado || !this.formData.duracion_cantidad) {
                    ToastNotification.warning('Completa tipo de cuidado y duraci�n');
                    return false;
                }
                if (!this.formData.direccion_servicio) {
                    ToastNotification.warning('Ingresa la direcci�n donde se prestar� el servicio');
                    return false;
                }
            }
            
            if (tipo === 'veterinario') {
                if (!this.formData.fecha_programada || !this.formData.rango_horario) {
                    ToastNotification.warning('Selecciona fecha y rango horario');
                    return false;
                }
                if (!this.formData.tipo_mascota || !this.formData.nombre_mascota) {
                    ToastNotification.warning('Ingresa informaci�n de la mascota');
                    return false;
                }
                if (this.formData.modalidad === 'presencial' && !this.formData.direccion_servicio) {
                    ToastNotification.warning('Ingresa la direcci�n para servicio a domicilio');
                    return false;
                }
            }
            
            if (tipo === 'laboratorio') {
                if (!this.formData.fecha_programada) {
                    ToastNotification.warning('Selecciona fecha para toma de muestras');
                    return false;
                }
                if (this.formData.examenes_solicitados.length === 0) {
                    ToastNotification.warning('Selecciona al menos un examen');
                    return false;
                }
                if (!this.formData.direccion_servicio) {
                    ToastNotification.warning('Ingresa direcci�n para toma de muestras');
                    return false;
                }
                if (!this.formData.email_resultados) {
                    ToastNotification.warning('Ingresa email para recibir resultados');
                    return false;
                }
                // Validar formato de email
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(this.formData.email_resultados)) {
                    ToastNotification.error('El formato del email no es v�lido. Ejemplo: usuario@correo.com');
                    return false;
                }
            }
            
            // Validaci�n de tel�fono (com�n)
            if (!this.formData.telefono_contacto) {
                ToastNotification.warning('Ingresa un tel�fono de contacto');
                return false;
            }
            
            return true;
        },

        volver() {
            if (this.paso > 1) {
                this.paso--;
            } else {
                window.location.href = BASE_URL + '/paciente/dashboard';
            }
        },

        getFechaMinima() {
            const hoy = new Date();
            hoy.setDate(hoy.getDate() + 1); // M�nimo ma�ana
            return hoy.toISOString().split('T')[0];
        }
    }
}
</script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-50" x-data="nuevaSolicitudApp()" x-init="init()">
    <!-- Header -->
    <nav class="bg-white shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <h1 class="text-xl font-bold text-gray-900">Nueva Solicitud</h1>
                </div>
                <div class="flex items-center">
                    <button @click="window.location.href = BASE_URL + '/paciente/dashboard'" class="text-gray-600 hover:text-gray-900">
                        Volver al Dashboard
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <!-- Breadcrumbs -->
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
        <nav class="breadcrumb">
            <div class="breadcrumb-item">
                <svg class="breadcrumb-icon" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/>
                </svg>
                <a href="<?= url('/paciente/dashboard') ?>">Inicio</a>
            </div>
            <span class="breadcrumb-separator">/</span>
            <div class="breadcrumb-item active">Nueva Solicitud</div>
        </nav>
    </div>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Indicador de pasos mejorado -->
        <div class="progress-steps mb-8">
            <div class="progress-step" :class="paso === 1 ? 'active' : paso > 1 ? 'completed' : ''">
                <div class="progress-step-circle">
                    <template x-if="paso > 1">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                    </template>
                    <template x-if="paso <= 1">
                        <span>1</span>
                    </template>
                </div>
                <div class="progress-step-label">Seleccionar Servicio</div>
            </div>

            <div class="progress-step" :class="paso === 2 ? 'active' : paso > 2 ? 'completed' : ''">
                <div class="progress-step-circle">
                    <template x-if="paso > 2">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                    </template>
                    <template x-if="paso <= 2">
                        <span>2</span>
                    </template>
                </div>
                <div class="progress-step-label">Detalles</div>
            </div>

            <div class="progress-step" :class="paso === 3 ? 'active' : paso > 3 ? 'completed' : ''">
                <div class="progress-step-circle">
                    <template x-if="paso > 3">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                    </template>
                    <template x-if="paso <= 3">
                        <span>3</span>
                    </template>
                </div>
                <div class="progress-step-label">Confirmar</div>
            </div>
        </div>

        <!-- Antiguo indicador (oculto) -->
        <div class="mb-8 hidden">
            <div class="flex items-center justify-center">
                <div class="flex items-center">
                    <div class="flex items-center relative">
                        <div :class="paso >= 1 ? 'gradient-bg' : 'bg-gray-300'" class="rounded-full h-10 w-10 flex items-center justify-center text-white font-semibold">1</div>
                        <span class="ml-2 text-sm font-medium" :class="paso >= 1 ? 'text-teal-600' : 'text-gray-500'">Servicio</span>
                    </div>
                    <div class="w-24 h-1 mx-4" :class="paso >= 2 ? 'gradient-bg' : 'bg-gray-300'"></div>
                    <div class="flex items-center">
                        <div :class="paso >= 2 ? 'gradient-bg' : 'bg-gray-300'" class="rounded-full h-10 w-10 flex items-center justify-center text-white font-semibold">2</div>
                        <span class="ml-2 text-sm font-medium" :class="paso >= 2 ? 'text-teal-600' : 'text-gray-500'">Detalles</span>
                    </div>
                    <div class="w-24 h-1 mx-4" :class="paso >= 3 ? 'gradient-bg' : 'bg-gray-300'"></div>
                    <div class="flex items-center">
                        <div :class="paso >= 3 ? 'gradient-bg' : 'bg-gray-300'" class="rounded-full h-10 w-10 flex items-center justify-center text-white font-semibold">3</div>
                        <span class="ml-2 text-sm font-medium" :class="paso >= 3 ? 'text-teal-600' : 'text-gray-500'">Confirmar</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Loading -->
        <div x-show="loading" class="flex justify-center py-12">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-teal-600"></div>
        </div>

        <!-- Paso 1: Seleccionar Servicio -->
        <div x-show="paso === 1 && !loading && !mostrarSelectorEspecialidad">
            <h2 class="text-2xl font-bold text-gray-900 mb-6 text-center">�Qu� tipo de servicio necesitas?</h2>
            
            <!-- Mensaje cuando no hay servicios -->
            <div x-show="servicios.length === 0" class="mb-6 p-6 bg-yellow-50 border-2 border-yellow-200 rounded-xl max-w-2xl mx-auto">
                <div class="flex items-start gap-4">
                    <div class="flex-shrink-0">
                        <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-yellow-900 mb-2">?? No hay servicios disponibles</h3>
                        <p class="text-yellow-800 mb-2">Actualmente no hay servicios registrados en el sistema.</p>
                        <p class="text-yellow-700 text-sm">Por favor, contacta con el administrador para que agregue los servicios disponibles.</p>
                    </div>
                </div>
            </div>
            
            <!-- Categor�as principales - Grid compacto -->
            <div class="grid grid-cols-2 md:grid-cols-3 gap-4 max-w-3xl mx-auto"
                 :class="servicios.length === 0 ? 'opacity-50 pointer-events-none' : ''">
                <!-- M�DICOS -->
                <div @click="seleccionarServicio(obtenerServicioPorTipo('medico'))" 
                     class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-6 cursor-pointer hover:shadow-lg hover:scale-105 transition-all duration-200 border-2 border-transparent hover:border-blue-500">
                    <div class="flex flex-col items-center text-center">
                        <div class="bg-blue-600 p-4 rounded-full mb-3">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <h3 class="font-bold text-gray-900 text-lg mb-1">M�dico</h3>
                        <p class="text-xs text-gray-600">Consulta especialista</p>
                    </div>
                </div>
                
                <!-- ENFERMER�A -->
                <div @click="seleccionarServicio(obtenerServicioPorTipo('enfermera'))" 
                     class="bg-gradient-to-br from-pink-50 to-pink-100 rounded-xl p-6 cursor-pointer hover:shadow-lg hover:scale-105 transition-all duration-200 border-2 border-transparent hover:border-pink-500">
                    <div class="flex flex-col items-center text-center">
                        <div class="bg-pink-600 p-4 rounded-full mb-3">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                            </svg>
                        </div>
                        <h3 class="font-bold text-gray-900 text-lg mb-1">Enfermer�a</h3>
                        <p class="text-xs text-gray-600">Cuidados a domicilio</p>
                    </div>
                </div>
                
                <!-- AMBULANCIA -->
                <div @click="seleccionarServicio(obtenerServicioPorTipo('ambulancia'))" 
                     class="bg-gradient-to-br from-red-50 to-red-100 rounded-xl p-6 cursor-pointer hover:shadow-lg hover:scale-105 transition-all duration-200 border-2 border-transparent hover:border-red-500">
                    <div class="flex flex-col items-center text-center">
                        <div class="bg-red-600 p-4 rounded-full mb-3">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </div>
                        <h3 class="font-bold text-gray-900 text-lg mb-1">Ambulancia</h3>
                        <p class="text-xs text-gray-600">Traslado m�dico</p>
                    </div>
                </div>
                
                <!-- VETERINARIA -->
                <div @click="seleccionarServicio(obtenerServicioPorTipo('veterinario'))" 
                     class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl p-6 cursor-pointer hover:shadow-lg hover:scale-105 transition-all duration-200 border-2 border-transparent hover:border-green-500">
                    <div class="flex flex-col items-center text-center">
                        <div class="bg-green-600 p-4 rounded-full mb-3">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5"/>
                            </svg>
                        </div>
                        <h3 class="font-bold text-gray-900 text-lg mb-1">Veterinaria</h3>
                        <p class="text-xs text-gray-600">Para tu mascota</p>
                    </div>
                </div>
                
                <!-- LABORATORIO -->
                <div @click="seleccionarServicio(obtenerServicioPorTipo('laboratorio'))" 
                     class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl p-6 cursor-pointer hover:shadow-lg hover:scale-105 transition-all duration-200 border-2 border-transparent hover:border-purple-500">
                    <div class="flex flex-col items-center text-center">
                        <div class="bg-purple-600 p-4 rounded-full mb-3">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                            </svg>
                        </div>
                        <h3 class="font-bold text-gray-900 text-lg mb-1">Laboratorio</h3>
                        <p class="text-xs text-gray-600">Ex�menes a domicilio</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Paso 1.5: Selector de Especialidad M�dica -->
        <div x-show="mostrarSelectorEspecialidad && !loading">
            <div class="max-w-4xl mx-auto">
                <!-- Breadcrumb -->
                <div class="mb-6 flex items-center gap-2 text-sm">
                    <button @click="volverAServicios()" class="text-blue-600 hover:text-blue-700 font-medium flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                        Volver a servicios
                    </button>
                </div>

                <h2 class="text-2xl font-bold text-gray-900 mb-3 text-center">�Qu� especialidad necesitas?</h2>
                <p class="text-gray-600 text-center mb-8">Selecciona la especialidad que mejor se ajuste a tu necesidad</p>
                
                <!-- Grid de Especialidades -->
                <div x-show="especialidadesDisponibles.length === 0" class="text-center py-12">
                    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto mb-4"></div>
                    <p class="text-gray-500">Cargando especialidades...</p>
                </div>

                <div x-show="especialidadesDisponibles.length > 0" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                    <template x-for="esp in especialidadesDisponibles" :key="esp">
                        <div @click="seleccionarEspecialidad(esp)" 
                             class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl p-5 cursor-pointer hover:shadow-xl hover:scale-105 transition-all duration-200 border-2 border-transparent hover:border-blue-500 group">
                            <div class="flex flex-col items-center text-center">
                                <div class="bg-blue-600 group-hover:bg-blue-700 p-3 rounded-full mb-3 transition-colors">
                                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                </div>
                                <h3 class="font-bold text-gray-900 text-base mb-1 group-hover:text-blue-700 transition-colors" x-text="esp"></h3>
                                <p class="text-xs text-gray-600">Click para seleccionar</p>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Opci�n de Medicina General -->
                <div class="mt-6 p-4 bg-gradient-to-r from-green-50 to-emerald-50 rounded-xl border-2 border-green-200">
                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0">
                            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h4 class="font-bold text-green-900 mb-1">�No est�s seguro de qu� especialidad necesitas?</h4>
                            <p class="text-sm text-green-800 mb-3">Un m�dico general puede evaluar tu caso y referirte a la especialidad correcta si es necesario.</p>
                            <button @click="seleccionarEspecialidad('Medicina General')" 
                                    class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium text-sm transition-colors">
                                Continuar con Medicina General
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Bot�n volver -->
                <div class="mt-6 text-center">
                    <button @click="volverAServicios()" 
                            class="px-6 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg font-medium transition-colors">
                        ? Volver a Servicios
                    </button>
                </div>
            </div>
        </div>

        <!-- Paso 2: Detalles y Programaci�n -->
        <div x-show="paso === 2 && !loading">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Detalles del servicio</h2>
            <div class="bg-white rounded-lg shadow-sm p-6 space-y-6">
                
                <!-- M�DICO ESPECIALISTA -->
                <template x-if="formData.servicio_tipo === 'medico'">
                    <div class="space-y-4">
                        <div class="bg-blue-50 border-l-4 border-blue-500 p-3 rounded flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-blue-900">Especialidad seleccionada:</p>
                                <p class="text-lg font-bold text-blue-700" x-text="formData.especialidad_solicitada || especialidadSeleccionada"></p>
                            </div>
                            <button @click="mostrarSelectorEspecialidad = true; paso = 1;" type="button" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                Cambiar
                            </button>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="field-container">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Fecha *</label>
                                <input type="date" x-model="formData.fecha_programada" data-validate="required|futureDate" :min="getFechaMinima()" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Horario *</label>
                                <div class="grid grid-cols-2 gap-2">
                                    <label class="flex items-center justify-center p-2 border-2 rounded-lg cursor-pointer text-sm" :class="formData.rango_horario === 'manana' ? 'border-blue-500 bg-blue-50 font-semibold' : 'border-gray-300'">
                                        <input type="radio" x-model="formData.rango_horario" value="manana" class="sr-only">
                                        <span>?? Ma�ana</span>
                                    </label>
                                    <label class="flex items-center justify-center p-2 border-2 rounded-lg cursor-pointer text-sm" :class="formData.rango_horario === 'tarde' ? 'border-blue-500 bg-blue-50 font-semibold' : 'border-gray-300'">
                                        <input type="radio" x-model="formData.rango_horario" value="tarde" class="sr-only">
                                        <span>?? Tarde</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Modalidad</label>
                            <div class="grid grid-cols-3 gap-3">
                                <label class="flex items-center p-3 border-2 rounded-lg cursor-pointer" :class="formData.modalidad === 'virtual' ? 'border-blue-500 bg-blue-50' : 'border-gray-300'">
                                    <input type="radio" x-model="formData.modalidad" value="virtual" @change="actualizarServicioPorModalidad()" class="mr-2">
                                    <span class="text-sm">?? Virtual</span>
                                </label>
                                <label class="flex items-center p-3 border-2 rounded-lg cursor-pointer" :class="formData.modalidad === 'presencial' ? 'border-blue-500 bg-blue-50' : 'border-gray-300'">
                                    <input type="radio" x-model="formData.modalidad" value="presencial" @change="actualizarServicioPorModalidad()" class="mr-2">
                                    <span class="text-sm">?? Domicilio</span>
                                </label>
                                <label class="flex items-center p-3 border-2 rounded-lg cursor-pointer" :class="formData.modalidad === 'consultorio' ? 'border-blue-500 bg-blue-50' : 'border-gray-300'">
                                    <input type="radio" x-model="formData.modalidad" value="consultorio" @change="actualizarServicioPorModalidad()" class="mr-2">
                                    <span class="text-sm">?? Consultorio</span>
                                </label>
                            </div>
                        </div>
                        
                        <div x-show="formData.modalidad === 'presencial'" x-transition>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Direcci�n *</label>
                            <input type="text" x-model="formData.direccion_servicio" placeholder="Calle 123 #45-67, Apto 101" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">S�ntomas / Motivo *</label>
                            <textarea x-model="formData.sintomas" rows="2" placeholder="Describe brevemente..." class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm"></textarea>
                        </div>
                    </div>
                </template>

                <!-- AMBULANCIA -->
                <template x-if="formData.servicio_tipo === 'ambulancia'">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de ambulancia *</label>
                            <div class="grid grid-cols-2 gap-3">
                                <label class="flex items-center p-3 border-2 rounded-lg cursor-pointer" :class="formData.tipo_ambulancia === 'basica' ? 'border-red-500 bg-red-50' : 'border-gray-300'">
                                    <input type="radio" x-model="formData.tipo_ambulancia" value="basica" class="sr-only">
                                    <div class="text-center w-full">
                                        <div class="font-semibold text-sm">?? B�sica</div>
                                        <div class="text-xs text-gray-600">Traslado est�ndar</div>
                                    </div>
                                </label>
                                <label class="flex items-center p-3 border-2 rounded-lg cursor-pointer" :class="formData.tipo_ambulancia === 'medicalizada' ? 'border-red-500 bg-red-50' : 'border-gray-300'">
                                    <input type="radio" x-model="formData.tipo_ambulancia" value="medicalizada" class="sr-only">
                                    <div class="text-center w-full">
                                        <div class="font-semibold text-sm">?? Medicalizada</div>
                                        <div class="text-xs text-gray-600">Con equipo m�dico</div>
                                    </div>
                                </label>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1">Fecha *</label>
                                <input type="date" x-model="formData.fecha_programada" :min="getFechaMinima()" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Hora *</label>
                                <input type="time" x-model="formData.hora_programada" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 text-sm">
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Urgencia *</label>
                            <div class="grid grid-cols-2 gap-3">
                                <label class="flex items-center justify-center p-3 border-2 rounded-lg cursor-pointer text-sm" :class="formData.tipo_emergencia === 'programado' ? 'border-red-500 bg-red-50 font-semibold' : 'border-gray-300'">
                                    <input type="radio" x-model="formData.tipo_emergencia" value="programado" class="sr-only">
                                    <span>?? Programado</span>
                                </label>
                                <label class="flex items-center justify-center p-3 border-2 rounded-lg cursor-pointer text-sm" :class="formData.tipo_emergencia === 'urgente' ? 'border-red-600 bg-red-100 font-bold text-red-700' : 'border-gray-300'">
                                    <input type="radio" x-model="formData.tipo_emergencia" value="urgente" class="sr-only">
                                    <span>?? Urgente</span>
                                </label>
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">?? Origen (recogida) *</label>
                            <input type="text" x-model="formData.origen" placeholder="Direcci�n de recogida" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 text-sm">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">?? Destino (entrega) *</label>
                            <input type="text" x-model="formData.destino" placeholder="Hospital o cl�nica" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 text-sm">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Condici�n del paciente *</label>
                            <textarea x-model="formData.condicion_paciente" rows="2" placeholder="Estable, requiere ox�geno, cr�tico..." class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 text-sm"></textarea>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Acompa�antes</label>
                                <input type="number" x-model="formData.numero_acompanantes" min="0" max="2" placeholder="0-2" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Contacto emergencia</label>
                                <input type="text" x-model="formData.contacto_emergencia" placeholder="Nombre - Tel" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 text-sm">
                            </div>
                        </div>
                    </div>
                </template>

                <!-- ENFERMER�A -->
                <template x-if="formData.servicio_tipo === 'enfermera'">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de cuidado *</label>
                            <select x-model="formData.tipo_cuidado" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 text-sm">
                                <option value="">Selecciona</option>
                                <option value="cuidado_general">?? Cuidado general</option>
                                <option value="inyecciones">?? Inyecciones</option>
                                <option value="curaciones">?? Curaciones</option>
                                <option value="postoperatorio">?? Post-operatorio</option>
                                <option value="sondas">?? Manejo de sondas</option>
                                <option value="geriatrico">?? Geri�trico</option>
                                <option value="pediatrico">?? Pedi�trico</option>
                            </select>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Intensidad *</label>
                                <div class="grid grid-cols-2 gap-2">
                                    <label class="flex items-center justify-center p-2 border-2 rounded-lg cursor-pointer text-sm" :class="formData.intensidad_horaria === '12h' ? 'border-pink-500 bg-pink-50 font-semibold' : 'border-gray-300'">
                                        <input type="radio" x-model="formData.intensidad_horaria" value="12h" class="sr-only">
                                        <span>12h</span>
                                    </label>
                                    <label class="flex items-center justify-center p-2 border-2 rounded-lg cursor-pointer text-sm" :class="formData.intensidad_horaria === '24h' ? 'border-pink-500 bg-pink-50 font-semibold' : 'border-gray-300'">
                                        <input type="radio" x-model="formData.intensidad_horaria" value="24h" class="sr-only">
                                        <span>24h</span>
                                    </label>
                                </div>
                            </div>
                            <div x-show="formData.intensidad_horaria === '12h'" x-transition>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Turno</label>
                                <select x-model="formData.turno" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 text-sm">
                                    <option value="diurno">?? Diurno</option>
                                    <option value="nocturno">?? Nocturno</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-3 gap-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Duraci�n *</label>
                                <select x-model="formData.duracion_tipo" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 text-sm">
                                    <option value="dias">D�as</option>
                                    <option value="semanas">Semanas</option>
                                    <option value="meses">Meses</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Cantidad *</label>
                                <input type="number" x-model="formData.duracion_cantidad" min="1" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">G�nero</label>
                                <select x-model="formData.genero_preferido" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 text-sm">
                                    <option value="indistinto">Indistinto</option>
                                    <option value="femenino">? Femenino</option>
                                    <option value="masculino">? Masculino</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Fecha inicio *</label>
                                <input type="date" x-model="formData.fecha_programada" :min="getFechaMinima()" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Direcci�n *</label>
                                <input type="text" x-model="formData.direccion_servicio" placeholder="Direcci�n" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 text-sm">
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Condici�n del paciente</label>
                            <textarea x-model="formData.condicion_paciente_detalle" rows="2" placeholder="Movilidad, condiciones m�dicas..." class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 text-sm"></textarea>
                        </div>
                    </div>
                </template>

                <!-- VETERINARIA -->
                <template x-if="formData.servicio_tipo === 'veterinario'">
                    <div class="space-y-4">
                        <div class="grid grid-cols-3 gap-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Tipo mascota *</label>
                                <select x-model="formData.tipo_mascota" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 text-sm">
                                    <option value="">Selecciona</option>
                                    <option value="perro">?? Perro</option>
                                    <option value="gato">?? Gato</option>
                                    <option value="ave">?? Ave</option>
                                    <option value="conejo">?? Conejo</option>
                                    <option value="otro">?? Otro</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nombre *</label>
                                <input type="text" x-model="formData.nombre_mascota" placeholder="Nombre" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Edad / Raza</label>
                                <input type="text" x-model="formData.edad_mascota" placeholder="3 a�os / Labrador" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 text-sm">
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Motivo de consulta *</label>
                            <select x-model="formData.motivo_veterinario" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 text-sm">
                                <option value="">Selecciona</option>
                                <option value="vacunacion">?? Vacunaci�n</option>
                                <option value="revision">?? Revisi�n general</option>
                                <option value="enfermedad">?? Enfermedad</option>
                                <option value="emergencia">?? Emergencia</option>
                                <option value="cirugia">?? Cirug�a</option>
                                <option value="desparasitacion">?? Desparasitaci�n</option>
                            </select>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Fecha *</label>
                                <input type="date" x-model="formData.fecha_programada" :min="getFechaMinima()" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Horario</label>
                                <div class="grid grid-cols-2 gap-2">
                                    <label class="flex items-center justify-center p-2 border-2 rounded-lg cursor-pointer text-sm" :class="formData.rango_horario === 'manana' ? 'border-green-500 bg-green-50 font-semibold' : 'border-gray-300'">
                                        <input type="radio" x-model="formData.rango_horario" value="manana" class="sr-only">
                                        <span>?? AM</span>
                                    </label>
                                    <label class="flex items-center justify-center p-2 border-2 rounded-lg cursor-pointer text-sm" :class="formData.rango_horario === 'tarde' ? 'border-green-500 bg-green-50 font-semibold' : 'border-gray-300'">
                                        <input type="radio" x-model="formData.rango_horario" value="tarde" class="sr-only">
                                        <span>?? PM</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Direcci�n *</label>
                            <input type="text" x-model="formData.direccion_servicio" placeholder="Calle 123 #45-67" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 text-sm">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">S�ntomas (si aplica)</label>
                            <textarea x-model="formData.sintomas" rows="2" placeholder="Describe los s�ntomas de la mascota..." class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 text-sm"></textarea>
                        </div>
                    </div>
                </template>

                <!-- LABORATORIO -->
                <template x-if="formData.servicio_tipo === 'laboratorio'">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Ex�menes solicitados * <span class="text-xs text-purple-600" x-text="'(' + formData.examenes_solicitados.length + ' seleccionados)'"></span></label>
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-2 border border-gray-300 rounded-lg p-3 max-h-48 overflow-y-auto">
                                <template x-for="examen in examenesDisponibles" :key="examen">
                                    <label class="flex items-center p-2 hover:bg-purple-50 rounded cursor-pointer text-sm">
                                        <input type="checkbox" :value="examen" @change="
                                            if ($event.target.checked) {
                                                formData.examenes_solicitados.push(examen);
                                            } else {
                                                formData.examenes_solicitados = formData.examenes_solicitados.filter(e => e !== examen);
                                            }
                                        " class="mr-2">
                                        <span x-text="examen" class="text-xs"></span>
                                    </label>
                                </template>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Fecha toma de muestras *</label>
                                <input type="date" x-model="formData.fecha_programada" :min="getFechaMinima()" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Email resultados *</label>
                                <div class="relative">
                                    <input type="email" 
                                           x-model="formData.email_resultados" 
                                           placeholder="correo@ejemplo.com" 
                                           @blur="validarEmailResultados()"
                                           :class="{'border-red-500 bg-red-50': formData.email_resultados && !validarFormatoEmail(formData.email_resultados), 'border-green-500 bg-green-50': formData.email_resultados && validarFormatoEmail(formData.email_resultados)}"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 text-sm pr-10">
                                    <span x-show="formData.email_resultados && validarFormatoEmail(formData.email_resultados)" 
                                          class="absolute right-3 top-1/2 -translate-y-1/2 text-green-600">?</span>
                                    <span x-show="formData.email_resultados && !validarFormatoEmail(formData.email_resultados)" 
                                          class="absolute right-3 top-1/2 -translate-y-1/2 text-red-600">?</span>
                                </div>
                                <p x-show="formData.email_resultados && !validarFormatoEmail(formData.email_resultados)" 
                                   class="text-xs text-red-600 mt-1">Formato inv�lido. Ej: usuario@correo.com</p>
                            </div>
                        </div>
                        
                        <div>
                            <label class="flex items-center space-x-2 p-3 bg-yellow-50 rounded-lg border border-yellow-200">
                                <input type="checkbox" x-model="formData.requiere_ayuno" class="w-4 h-4">
                                <span class="text-sm">?? <strong>Requiere ayuno</strong> (8-12 horas)</span>
                            </label>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Direcci�n para toma de muestras *</label>
                            <input type="text" x-model="formData.direccion_servicio" placeholder="Calle 123 #45-67, Apto 101" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 text-sm">
                        </div>
                    </div>
                </template>

                <!-- Campos comunes a todos -->
                <div class="border-t pt-4 space-y-3">
                    <h3 class="font-semibold text-gray-900 text-sm">Informaci�n de contacto</h3>
                    
                    <div class="grid grid-cols-3 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tel�fono *</label>
                            <input type="tel" x-model="formData.telefono_contacto" placeholder="3001234567" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Urgencia</label>
                            <select x-model="formData.urgencia" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 text-sm">
                                <option value="normal">?? Normal</option>
                                <option value="urgente">?? Urgente</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">M�todo pago *</label>
                            <select x-model="formData.metodo_pago_preferido" @change="formData.metodo_pago_preferido === 'transferencia' && (paso3_mostrar_instrucciones = true)" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 text-sm">
                                <option value="">Selecciona</option>
                                <option value="transferencia">?? Transferencia (Requiere confirmaci�n)</option>
                            </select>
                        </div>
                    </div>
                    
                    <!-- Instrucciones de transferencia -->
                    <div x-show="formData.metodo_pago_preferido === 'transferencia'" x-transition class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mt-3">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-yellow-800">Pago por transferencia</h3>
                                <div class="mt-2 text-sm text-yellow-700">
                                    <p class="mb-2">Al confirmar, deber�s:</p>
                                    <ol class="list-decimal list-inside space-y-1 ml-2">
                                        <li>Realizar la transferencia a la cuenta indicada</li>
                                        <li>Enviar captura de pantalla al WhatsApp: <strong>+57 300 123 4567</strong></li>
                                        <li>Esperar confirmaci�n del administrador</li>
                                        <li>El profesional aceptar� tu solicitud una vez confirmado el pago</li>
                                    </ol>
                                    <p class="mt-2 text-xs">?? Tiempo estimado de confirmaci�n: 1-2 horas en horario h�bil</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Botones -->
                <div class="flex justify-between pt-4 border-t">
                    <button @click="paso = 1" class="px-6 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                        Atr�s
                    </button>
                    <button @click="paso = 3" class="px-6 py-2 gradient-bg text-white rounded-lg hover:opacity-90">
                        Continuar a confirmaci�n
                    </button>
                </div>
            </div>
        </div>

        <!-- Paso 3: Confirmaci�n -->
        <div x-show="paso === 3 && !loading">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Confirma tu solicitud</h2>
            <div class="bg-white rounded-lg shadow-sm p-6 space-y-4">
                <!-- Servicio y Precio -->
                <div class="border-b pb-4">
                    <h3 class="font-semibold text-gray-900 mb-2">?? Servicio</h3>
                    <p class="text-gray-700" x-text="servicioSeleccionado?.nombre"></p>
                    <p x-show="formData.especialidad_solicitada" class="text-sm text-purple-600 mt-1">
                        Especialidad: <span x-text="formData.especialidad_solicitada"></span>
                    </p>
                    <p class="text-2xl font-bold text-teal-600 mt-2">$<span x-text="parseInt(servicioSeleccionado?.precio_base || 0).toLocaleString('es-CO')"></span></p>
                </div>

                <!-- Detalles seg�n tipo de servicio -->
                <div class="border-b pb-4">
                    <h3 class="font-semibold text-gray-900 mb-2">?? Detalles de la Cita</h3>
                    
                    <!-- Fecha y hora/horario -->
                    <p class="text-sm text-gray-600">
                        <strong>Fecha:</strong> <span x-text="formData.fecha_programada"></span>
                        <template x-if="formData.hora_programada">
                            <span> a las <span x-text="formData.hora_programada"></span></span>
                        </template>
                        <template x-if="formData.rango_horario && !formData.hora_programada">
                            <span> - <span x-text="formData.rango_horario === 'manana' ? 'Ma�ana' : formData.rango_horario === 'tarde' ? 'Tarde' : 'Noche'"></span></span>
                        </template>
                    </p>
                    
                    <p class="text-sm text-gray-600"><strong>Modalidad:</strong> <span x-text="formData.modalidad === 'traslado' ? '?? Traslado' : formData.modalidad === 'domicilio' ? '?? Domicilio' : formData.modalidad === 'virtual' ? '?? Virtual' : '?? Consultorio'"></span></p>
                    <p x-show="formData.direccion_servicio" class="text-sm text-gray-600"><strong>?? Direcci�n:</strong> <span x-text="formData.direccion_servicio"></span></p>
                    <p class="text-sm text-gray-600"><strong>?? Tel�fono:</strong> <span x-text="formData.telefono_contacto"></span></p>
                </div>

                <!-- Detalles espec�ficos por tipo de servicio -->
                <div class="border-b pb-4">
                    <!-- M�DICO -->
                    <template x-if="formData.servicio_tipo === 'medico'">
                        <div>
                            <h3 class="font-semibold text-gray-900 mb-2">?? Motivo de Consulta</h3>
                            <p class="text-sm text-gray-600" x-text="formData.sintomas || 'No especificado'"></p>
                        </div>
                    </template>
                    
                    <!-- AMBULANCIA -->
                    <template x-if="formData.servicio_tipo === 'ambulancia'">
                        <div>
                            <h3 class="font-semibold text-gray-900 mb-2">?? Detalles del Traslado</h3>
                            <p class="text-sm text-gray-600"><strong>Tipo:</strong> <span class="capitalize" x-text="formData.tipo_ambulancia"></span></p>
                            <p class="text-sm text-gray-600"><strong>Origen:</strong> <span x-text="formData.origen"></span></p>
                            <p class="text-sm text-gray-600"><strong>Destino:</strong> <span x-text="formData.destino"></span></p>
                            <p class="text-sm text-gray-600"><strong>Condici�n paciente:</strong> <span x-text="formData.condicion_paciente"></span></p>
                            <p x-show="formData.numero_acompanantes > 0" class="text-sm text-gray-600"><strong>Acompa�antes:</strong> <span x-text="formData.numero_acompanantes"></span></p>
                        </div>
                    </template>
                    
                    <!-- ENFERMER�A -->
                    <template x-if="formData.servicio_tipo === 'enfermera'">
                        <div>
                            <h3 class="font-semibold text-gray-900 mb-2">?? Detalles del Cuidado</h3>
                            <p class="text-sm text-gray-600"><strong>Tipo:</strong> <span x-text="formData.tipo_cuidado?.replace('_', ' ')"></span></p>
                            <p class="text-sm text-gray-600"><strong>Intensidad:</strong> <span x-text="formData.intensidad_horaria"></span><template x-if="formData.intensidad_horaria === '12h'"> - Turno <span x-text="formData.turno"></span></template></p>
                            <p class="text-sm text-gray-600"><strong>Duraci�n:</strong> <span x-text="formData.duracion_cantidad"></span> <span x-text="formData.duracion_tipo"></span></p>
                            <p x-show="formData.condicion_paciente_detalle" class="text-sm text-gray-600"><strong>Condici�n:</strong> <span x-text="formData.condicion_paciente_detalle"></span></p>
                        </div>
                    </template>
                    
                    <!-- VETERINARIA -->
                    <template x-if="formData.servicio_tipo === 'veterinario'">
                        <div>
                            <h3 class="font-semibold text-gray-900 mb-2">?? Datos de la Mascota</h3>
                            <p class="text-sm text-gray-600"><strong>Mascota:</strong> <span x-text="formData.nombre_mascota"></span> (<span x-text="formData.tipo_mascota"></span>)</p>
                            <p x-show="formData.edad_mascota" class="text-sm text-gray-600"><strong>Edad/Raza:</strong> <span x-text="formData.edad_mascota"></span></p>
                            <p class="text-sm text-gray-600"><strong>Motivo:</strong> <span x-text="formData.motivo_veterinario"></span></p>
                            <p x-show="formData.sintomas" class="text-sm text-gray-600"><strong>S�ntomas:</strong> <span x-text="formData.sintomas"></span></p>
                        </div>
                    </template>
                    
                    <!-- LABORATORIO -->
                    <template x-if="formData.servicio_tipo === 'laboratorio'">
                        <div>
                            <h3 class="font-semibold text-gray-900 mb-2">?? Ex�menes Solicitados</h3>
                            <div class="flex flex-wrap gap-1 mb-2">
                                <template x-for="examen in formData.examenes_solicitados" :key="examen">
                                    <span class="inline-block bg-purple-100 text-purple-700 text-xs px-2 py-1 rounded" x-text="examen"></span>
                                </template>
                            </div>
                            <p x-show="formData.requiere_ayuno" class="text-sm text-orange-600">?? Requiere ayuno</p>
                            <p class="text-sm text-gray-600"><strong>Email resultados:</strong> <span x-text="formData.email_resultados"></span></p>
                        </div>
                    </template>
                </div>
                
                <!-- M�todo de Pago -->
                <div class="border-b pb-4">
                    <h3 class="font-semibold text-gray-900 mb-2">?? M�todo de Pago</h3>
                    <div x-show="formData.metodo_pago_preferido === 'transferencia'" class="space-y-2">
                        <p class="text-sm text-gray-600">?? <strong>Transferencia Bancaria</strong></p>
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 mt-2">
                            <p class="text-xs font-semibold text-blue-900 mb-2">?? Datos para transferencia:</p>
                            <template x-if="configPagos">
                                <div>
                                    <p class="text-xs text-blue-800">Banco: <strong x-text="configPagos.banco_nombre || 'Bancolombia'"></strong></p>
                                    <p class="text-xs text-blue-800">Tipo: <strong x-text="configPagos.banco_tipo_cuenta || 'Ahorros'"></strong></p>
                                    <p class="text-xs text-blue-800">Cuenta: <strong x-text="configPagos.banco_cuenta || 'Consultar'"></strong></p>
                                    <p class="text-xs text-blue-800">Titular: <strong x-text="configPagos.banco_titular || 'Especialistas en Casa'"></strong></p>
                                </div>
                            </template>
                            <template x-if="!configPagos">
                                <p class="text-xs text-blue-800">Cargando datos bancarios...</p>
                            </template>
                        </div>
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3 mt-2">
                            <p class="text-xs font-semibold text-yellow-900 mb-1">?? Importante:</p>
                            <p class="text-xs text-yellow-800">Despu�s de confirmar, sube tu comprobante desde <strong>"Mis Solicitudes"</strong>.</p>
                            <p class="text-xs text-yellow-800 mt-1">Tu solicitud quedar� en estado <strong>"Pendiente de Pago"</strong> hasta confirmar.</p>
                        </div>
                    </div>
                    <div x-show="formData.metodo_pago_preferido === 'efectivo'" class="text-sm text-gray-600">
                        ?? <strong>Pago en Efectivo</strong> - Pagar�s directamente al profesional
                    </div>
                </div>

                <!-- Botones -->
                <div class="flex justify-between pt-4">
                    <button @click="paso = 2" class="px-6 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                        Atr�s
                    </button>
                    <button @click="enviarSolicitud()" :disabled="loading" class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:opacity-50">
                        <span x-show="!loading">? Confirmar Solicitud</span>
                        <span x-show="loading">Procesando...</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Modal de �xito -->
        <div x-show="mostrarModalExito" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" style="background-color: rgba(0,0,0,0.5);">
            <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full overflow-hidden transform transition-all"
                 x-show="mostrarModalExito"
                 x-transition:enter="ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-90"
                 x-transition:enter-end="opacity-100 scale-100">
                
                <!-- Header con �cono -->
                <div class="bg-gradient-to-r from-green-500 to-emerald-600 px-6 py-8 text-center">
                    <div class="mx-auto w-20 h-20 bg-white rounded-full flex items-center justify-center mb-4 shadow-lg">
                        <svg class="w-12 h-12 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold text-white">�Solicitud Creada!</h2>
                </div>
                
                <!-- Contenido -->
                <div class="px-6 py-6">
                    <p class="text-gray-700 text-center mb-4" x-text="mensajeExito"></p>
                    
                    <!-- Info de transferencia -->
                    <template x-if="esTransferencia">
                        <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 mb-4">
                            <div class="flex items-start gap-3">
                                <div class="flex-shrink-0">
                                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </div>
                                <div>
                                    <h4 class="font-semibold text-blue-900 mb-1">Siguiente paso:</h4>
                                    <p class="text-sm text-blue-800">Realiza la transferencia seg�n los datos bancarios mostrados y sube tu comprobante desde <strong>"Mis Solicitudes"</strong>.</p>
                                </div>
                            </div>
                        </div>
                    </template>
                    
                    <!-- Datos bancarios si existen -->
                    <template x-if="esTransferencia && datosTransferencia">
                        <div class="bg-gray-50 rounded-xl p-4 mb-4">
                            <h4 class="font-semibold text-gray-800 mb-3 flex items-center gap-2">
                                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                </svg>
                                Datos para transferencia
                            </h4>
                            <div class="space-y-2 text-sm">
                                <p x-show="datosTransferencia.banco_nombre"><span class="text-gray-500">Banco:</span> <span class="font-medium" x-text="datosTransferencia.banco_nombre"></span></p>
                                <p x-show="datosTransferencia.banco_cuenta"><span class="text-gray-500">Cuenta:</span> <span class="font-medium" x-text="datosTransferencia.banco_cuenta"></span></p>
                                <p x-show="datosTransferencia.banco_tipo_cuenta"><span class="text-gray-500">Tipo:</span> <span class="font-medium" x-text="datosTransferencia.banco_tipo_cuenta"></span></p>
                                <p x-show="datosTransferencia.banco_titular"><span class="text-gray-500">Titular:</span> <span class="font-medium" x-text="datosTransferencia.banco_titular"></span></p>
                            </div>
                        </div>
                    </template>
                </div>
                
                <!-- Footer -->
                <div class="px-6 pb-6">
                    <button @click="irAMisSolicitudes()" class="w-full py-3 bg-gradient-to-r from-blue-600 to-indigo-600 text-white font-semibold rounded-xl hover:from-blue-700 hover:to-indigo-700 transition-all shadow-lg">
                        Ir a Mis Solicitudes
                    </button>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
