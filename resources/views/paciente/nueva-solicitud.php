<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Solicitud - Especialistas en Casa</title>
    <script src="https://cdn.tailwindcss.com"></script>
<script>
window.nuevaSolicitudApp = function() {
    return {
        loading: false,
        paso: 1,
        servicios: [],
        profesionales: [],
        formData: {
            servicio_id: '',
            servicio_tipo: '',
            modalidad: 'presencial',
            fecha_programada: '',
            hora_programada: '',
            direccion_servicio: '',
            sintomas: '',
            observaciones: '',
            telefono_contacto: '',
            urgencia: 'normal',
            metodo_pago_preferido: '',
            
            // Médico Especialista
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
            
            // Enfermería
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
            
            // Psicología
            tipo_sesion_psico: 'individual',
            motivo_consulta_psico: '',
            primera_vez: true,
            observaciones_privadas: '',
            
            // Nutrición
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
            'Perfil lipídico',
            'Creatinina',
            'Ácido úrico',
            'Transaminasas',
            'TSH (Tiroides)',
            'Examen de orina',
            'Coprológico',
            'Antígeno prostático (PSA)',
            'Hemoglobina glicosilada',
            'Vitamina D',
            'Vitamina B12'
        ],

        async init() {
            const token = localStorage.getItem('token');
            if (!token) {
                window.location.href = '/login';
                return;
            }

            await this.cargarServicios();
        },

        async cargarServicios() {
            this.loading = true;
            try {
                const response = await fetch('/api/servicios');
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

        async seleccionarServicio(servicio) {
            this.formData.servicio_id = servicio.id;
            this.formData.servicio_tipo = servicio.tipo;
            this.servicioSeleccionado = servicio;
            this.paso = 2;
        },



        async enviarSolicitud() {
            if (!this.validarFormulario()) return;
            
            // Validar método de pago
            if (!this.formData.metodo_pago_preferido) {
                ToastNotification.warning('Debes seleccionar un método de pago');
                return;
            }

            this.loading = true;
            try {
                const token = localStorage.getItem('token');
                
                // Preparar fecha y hora según tipo de servicio
                let fechaHora = this.formData.fecha_programada;
                if (this.formData.hora_programada) {
                    fechaHora += ` ${this.formData.hora_programada}:00`;
                } else {
                    fechaHora += ' 00:00:00';
                }
                
                // Preparar datos específicos del servicio
                const payload = {
                    ...this.formData,
                    fecha_programada: fechaHora,
                    examenes_solicitados: JSON.stringify(this.formData.examenes_solicitados),
                    requiere_aprobacion: this.formData.servicio_tipo === 'medico'
                };
                
                const response = await fetch('/api/solicitudes', {
                    method: 'POST',
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });

                const data = await response.json();
                
                if (response.ok) {
                    // Mostrar mensaje personalizado según método de pago
                    if (data.metodo_pago === 'transferencia') {
                        alert(`✅ ${data.message}\n\n📋 Datos para transferencia:\nBanco: Bancolombia\nCuenta Ahorros: 1234-5678-9012\nTitular: Especialistas en Casa SAS\nNIT: 900.123.456-7\n\n📱 Envía el comprobante al WhatsApp: +57 300 123 4567`);
                    } else {
                        alert(`✅ ${data.message}`);
                    }
                    
                    // Redirigir después de 2 segundos
                    setTimeout(() => {
                        window.location.href = '/paciente/dashboard';
                    }, 2000);
                } else {
                    alert(data.message || 'Error al crear la solicitud');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error al crear la solicitud');
            } finally {
                this.loading = false;
            }
        },

        validarFormulario() {
            const tipo = this.formData.servicio_tipo;
            
            // Validaciones comunes
            if (!this.formData.servicio_id) {
                alert('Selecciona un servicio');
                return false;
            }
            
            // Validaciones por tipo de servicio
            if (tipo === 'medico') {
                if (!this.formData.fecha_programada || !this.formData.rango_horario) {
                    alert('Selecciona fecha y rango horario');
                    return false;
                }
                if (!this.formData.sintomas) {
                    alert('Describe los síntomas o motivo de consulta');
                    return false;
                }
            }
            
            if (this.servicioSeleccionado.tipo === 'ambulancia') {
                if (!this.formData.fecha_programada || !this.formData.hora_programada) {
                    ToastNotification.warning('Selecciona fecha y hora para la ambulancia');
                    return false;
                }
                if (!this.formData.direccion_origen || !this.formData.direccion_destino) {
                    ToastNotification.warning('Ingresa dirección de origen y destino');
                    return false;
                }
                if (!this.formData.condicion_paciente) {
                    ToastNotification.warning('Describe la condición del paciente');
                    return false;
                }
            }
            
            if (tipo === 'enfermera') {
                if (!this.formData.fecha_programada) {
                    alert('Selecciona la fecha de inicio');
                    return false;
                }
                if (!this.formData.tipo_cuidado || !this.formData.duracion_cantidad) {
                    alert('Completa tipo de cuidado y duración');
                    return false;
                }
                if (!this.formData.direccion_servicio) {
                    alert('Ingresa la dirección donde se prestará el servicio');
                    return false;
                }
            }
            
            if (tipo === 'veterinario') {
                if (!this.formData.fecha_programada || !this.formData.rango_horario) {
                    alert('Selecciona fecha y rango horario');
                    return false;
                }
                if (!this.formData.tipo_mascota || !this.formData.nombre_mascota) {
                    alert('Ingresa información de la mascota');
                    return false;
                }
                if (this.formData.modalidad === 'presencial' && !this.formData.direccion_servicio) {
                    alert('Ingresa la dirección para servicio a domicilio');
                    return false;
                }
            }
            
            if (tipo === 'laboratorio') {
                if (!this.formData.fecha_programada) {
                    alert('Selecciona fecha para toma de muestras');
                    return false;
                }
                if (this.formData.examenes_solicitados.length === 0) {
                    alert('Selecciona al menos un examen');
                    return false;
                }
                if (!this.formData.direccion_servicio) {
                    alert('Ingresa dirección para toma de muestras');
                    return false;
                }
                if (!this.formData.email_resultados) {
                    alert('Ingresa email para recibir resultados');
                    return false;
                }
            }
            
            // Validación de teléfono (común)
            if (!this.formData.telefono_contacto) {
                alert('Ingresa un teléfono de contacto');
                return false;
            }
            
            return true;
        },

        volver() {
            if (this.paso > 1) {
                this.paso--;
            } else {
                window.location.href = '/paciente/dashboard';
            }
        },

        getFechaMinima() {
            const hoy = new Date();
            hoy.setDate(hoy.getDate() + 1); // Mínimo mañana
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
                    <button @click="volver()" class="text-gray-600 hover:text-gray-900 mr-4" title="Volver">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </button>
                    <h1 class="text-xl font-semibold text-gray-900">Nueva Solicitud</h1>
                </div>
                
                <div class="flex items-center">
                    <button @click="window.location.href='/paciente/dashboard'" class="text-gray-600 hover:text-indigo-600 transition" title="Ir al inicio">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Indicador de pasos -->
        <div class="mb-8">
            <div class="flex items-center justify-center">
                <div class="flex items-center">
                    <div class="flex items-center relative">
                        <div :class="paso >= 1 ? 'bg-indigo-600' : 'bg-gray-300'" class="rounded-full h-10 w-10 flex items-center justify-center text-white font-semibold">1</div>
                        <span class="ml-2 text-sm font-medium" :class="paso >= 1 ? 'text-indigo-600' : 'text-gray-500'">Servicio</span>
                    </div>
                    <div class="w-24 h-1 mx-4" :class="paso >= 2 ? 'bg-indigo-600' : 'bg-gray-300'"></div>
                    <div class="flex items-center">
                        <div :class="paso >= 2 ? 'bg-indigo-600' : 'bg-gray-300'" class="rounded-full h-10 w-10 flex items-center justify-center text-white font-semibold">2</div>
                        <span class="ml-2 text-sm font-medium" :class="paso >= 2 ? 'text-indigo-600' : 'text-gray-500'">Detalles</span>
                    </div>
                    <div class="w-24 h-1 mx-4" :class="paso >= 3 ? 'bg-indigo-600' : 'bg-gray-300'"></div>
                    <div class="flex items-center">
                        <div :class="paso >= 3 ? 'bg-indigo-600' : 'bg-gray-300'" class="rounded-full h-10 w-10 flex items-center justify-center text-white font-semibold">3</div>
                        <span class="ml-2 text-sm font-medium" :class="paso >= 3 ? 'text-indigo-600' : 'text-gray-500'">Confirmar</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Loading -->
        <div x-show="loading" class="flex justify-center py-12">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600"></div>
        </div>

        <!-- Paso 1: Seleccionar Servicio -->
        <div x-show="paso === 1 && !loading">
            <h2 class="text-2xl font-bold text-gray-900 mb-6 text-center">¿Qué tipo de servicio necesitas?</h2>
            
            <!-- Categorías principales - Grid compacto -->
            <div class="grid grid-cols-2 md:grid-cols-3 gap-4 max-w-3xl mx-auto">
                <!-- MÉDICOS -->
                <div @click="seleccionarServicio({id: 1, nombre: 'Consulta Médica', tipo: 'medico', precio_base: 80000})" 
                     class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-xl p-6 cursor-pointer hover:shadow-lg hover:scale-105 transition-all duration-200 border-2 border-transparent hover:border-blue-500">
                    <div class="flex flex-col items-center text-center">
                        <div class="bg-blue-600 p-4 rounded-full mb-3">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <h3 class="font-bold text-gray-900 text-lg mb-1">Médico</h3>
                        <p class="text-xs text-gray-600">Consulta especialista</p>
                    </div>
                </div>
                
                <!-- ENFERMERÍA -->
                <div @click="seleccionarServicio({id: 2, nombre: 'Servicio de Enfermería', tipo: 'enfermera', precio_base: 120000})" 
                     class="bg-gradient-to-br from-pink-50 to-pink-100 rounded-xl p-6 cursor-pointer hover:shadow-lg hover:scale-105 transition-all duration-200 border-2 border-transparent hover:border-pink-500">
                    <div class="flex flex-col items-center text-center">
                        <div class="bg-pink-600 p-4 rounded-full mb-3">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                            </svg>
                        </div>
                        <h3 class="font-bold text-gray-900 text-lg mb-1">Enfermería</h3>
                        <p class="text-xs text-gray-600">Cuidados a domicilio</p>
                    </div>
                </div>
                
                <!-- AMBULANCIA -->
                <div @click="seleccionarServicio({id: 3, nombre: 'Servicio de Ambulancia', tipo: 'ambulancia', precio_base: 200000})" 
                     class="bg-gradient-to-br from-red-50 to-red-100 rounded-xl p-6 cursor-pointer hover:shadow-lg hover:scale-105 transition-all duration-200 border-2 border-transparent hover:border-red-500">
                    <div class="flex flex-col items-center text-center">
                        <div class="bg-red-600 p-4 rounded-full mb-3">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </div>
                        <h3 class="font-bold text-gray-900 text-lg mb-1">Ambulancia</h3>
                        <p class="text-xs text-gray-600">Traslado médico</p>
                    </div>
                </div>
                
                <!-- VETERINARIA -->
                <div @click="seleccionarServicio({id: 4, nombre: 'Consulta Veterinaria', tipo: 'veterinario', precio_base: 70000})" 
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
                <div @click="seleccionarServicio({id: 5, nombre: 'Exámenes de Laboratorio', tipo: 'laboratorio', precio_base: 50000})" 
                     class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-xl p-6 cursor-pointer hover:shadow-lg hover:scale-105 transition-all duration-200 border-2 border-transparent hover:border-purple-500">
                    <div class="flex flex-col items-center text-center">
                        <div class="bg-purple-600 p-4 rounded-full mb-3">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
                            </svg>
                        </div>
                        <h3 class="font-bold text-gray-900 text-lg mb-1">Laboratorio</h3>
                        <p class="text-xs text-gray-600">Exámenes a domicilio</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Paso 2: Detalles y Programación -->
        <div x-show="paso === 2 && !loading">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Detalles del servicio</h2>
            <div class="bg-white rounded-lg shadow-sm p-6 space-y-6">
                
                <!-- MÉDICO ESPECIALISTA -->
                <template x-if="formData.servicio_tipo === 'medico'">
                    <div class="space-y-4">
                        <div class="bg-blue-50 border-l-4 border-blue-500 p-3 rounded">
                            <p class="text-sm text-blue-800">Tu solicitud será enviada al médico para aprobación</p>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Especialidad</label>
                                <input type="text" x-model="formData.especialidad" placeholder="Medicina General, Cardiología..." class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm">
                                <p class="text-xs text-gray-500 mt-1">El administrador asignará un profesional disponible</p>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Fecha *</label>
                                <input type="date" x-model="formData.fecha_programada" :min="getFechaMinima()" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Horario *</label>
                                <div class="grid grid-cols-2 gap-2">
                                    <label class="flex items-center justify-center p-2 border-2 rounded-lg cursor-pointer text-sm" :class="formData.rango_horario === 'manana' ? 'border-blue-500 bg-blue-50 font-semibold' : 'border-gray-300'">
                                        <input type="radio" x-model="formData.rango_horario" value="manana" class="sr-only">
                                        <span>🌅 Mañana</span>
                                    </label>
                                    <label class="flex items-center justify-center p-2 border-2 rounded-lg cursor-pointer text-sm" :class="formData.rango_horario === 'tarde' ? 'border-blue-500 bg-blue-50 font-semibold' : 'border-gray-300'">
                                        <input type="radio" x-model="formData.rango_horario" value="tarde" class="sr-only">
                                        <span>🌆 Tarde</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Modalidad</label>
                            <div class="grid grid-cols-2 gap-3">
                                <label class="flex items-center p-3 border-2 rounded-lg cursor-pointer" :class="formData.modalidad === 'virtual' ? 'border-blue-500 bg-blue-50' : 'border-gray-300'">
                                    <input type="radio" x-model="formData.modalidad" value="virtual" class="mr-2">
                                    <span class="text-sm">💻 Telemedicina</span>
                                </label>
                                <label class="flex items-center p-3 border-2 rounded-lg cursor-pointer" :class="formData.modalidad === 'presencial' ? 'border-blue-500 bg-blue-50' : 'border-gray-300'">
                                    <input type="radio" x-model="formData.modalidad" value="presencial" class="mr-2">
                                    <span class="text-sm">🏠 Domicilio</span>
                                </label>
                            </div>
                        </div>
                        
                        <div x-show="formData.modalidad === 'presencial'" x-transition>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Dirección *</label>
                            <input type="text" x-model="formData.direccion_servicio" placeholder="Calle 123 #45-67, Apto 101" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 text-sm">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Síntomas / Motivo *</label>
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
                                        <div class="font-semibold text-sm">🚐 Básica</div>
                                        <div class="text-xs text-gray-600">Traslado estándar</div>
                                    </div>
                                </label>
                                <label class="flex items-center p-3 border-2 rounded-lg cursor-pointer" :class="formData.tipo_ambulancia === 'medicalizada' ? 'border-red-500 bg-red-50' : 'border-gray-300'">
                                    <input type="radio" x-model="formData.tipo_ambulancia" value="medicalizada" class="sr-only">
                                    <div class="text-center w-full">
                                        <div class="font-semibold text-sm">🚑 Medicalizada</div>
                                        <div class="text-xs text-gray-600">Con equipo médico</div>
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
                                    <span>📅 Programado</span>
                                </label>
                                <label class="flex items-center justify-center p-3 border-2 rounded-lg cursor-pointer text-sm" :class="formData.tipo_emergencia === 'urgente' ? 'border-red-600 bg-red-100 font-bold text-red-700' : 'border-gray-300'">
                                    <input type="radio" x-model="formData.tipo_emergencia" value="urgente" class="sr-only">
                                    <span>🚨 Urgente</span>
                                </label>
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">📍 Origen (recogida) *</label>
                            <input type="text" x-model="formData.origen" placeholder="Dirección de recogida" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 text-sm">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">🏥 Destino (entrega) *</label>
                            <input type="text" x-model="formData.destino" placeholder="Hospital o clínica" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 text-sm">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Condición del paciente *</label>
                            <textarea x-model="formData.condicion_paciente" rows="2" placeholder="Estable, requiere oxígeno, crítico..." class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 text-sm"></textarea>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Acompañantes</label>
                                <input type="number" x-model="formData.numero_acompanantes" min="0" max="2" placeholder="0-2" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Contacto emergencia</label>
                                <input type="text" x-model="formData.contacto_emergencia" placeholder="Nombre - Tel" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 text-sm">
                            </div>
                        </div>
                    </div>
                </template>

                <!-- ENFERMERÍA -->
                <template x-if="formData.servicio_tipo === 'enfermera'">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de cuidado *</label>
                            <select x-model="formData.tipo_cuidado" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 text-sm">
                                <option value="">Selecciona</option>
                                <option value="cuidado_general">💊 Cuidado general</option>
                                <option value="inyecciones">💉 Inyecciones</option>
                                <option value="curaciones">🩹 Curaciones</option>
                                <option value="postoperatorio">🏥 Post-operatorio</option>
                                <option value="sondas">🔬 Manejo de sondas</option>
                                <option value="geriatrico">👴 Geriátrico</option>
                                <option value="pediatrico">👶 Pediátrico</option>
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
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Turno</label>
                                <select x-model="formData.turno" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 text-sm">
                                    <option value="diurno">☀️ Diurno</option>
                                    <option value="nocturno">🌙 Nocturno</option>
                                    <option value="mixto">🔄 Mixto</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-3 gap-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Duración *</label>
                                <select x-model="formData.duracion_tipo" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 text-sm">
                                    <option value="dias">Días</option>
                                    <option value="semanas">Semanas</option>
                                    <option value="meses">Meses</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Cantidad *</label>
                                <input type="number" x-model="formData.duracion_cantidad" min="1" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Género</label>
                                <select x-model="formData.genero_preferido" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 text-sm">
                                    <option value="indistinto">Indistinto</option>
                                    <option value="femenino">♀ Femenino</option>
                                    <option value="masculino">♂ Masculino</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Fecha inicio *</label>
                                <input type="date" x-model="formData.fecha_programada" :min="getFechaMinima()" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Dirección *</label>
                                <input type="text" x-model="formData.direccion_servicio" placeholder="Dirección" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 text-sm">
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Condición del paciente</label>
                            <textarea x-model="formData.condicion_paciente_detalle" rows="2" placeholder="Movilidad, condiciones médicas..." class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-500 text-sm"></textarea>
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
                                    <option value="perro">🐕 Perro</option>
                                    <option value="gato">🐈 Gato</option>
                                    <option value="ave">🦜 Ave</option>
                                    <option value="conejo">🐰 Conejo</option>
                                    <option value="otro">🐾 Otro</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Nombre *</label>
                                <input type="text" x-model="formData.nombre_mascota" placeholder="Nombre" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 text-sm">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Edad / Raza</label>
                                <input type="text" x-model="formData.edad_mascota" placeholder="3 años / Labrador" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 text-sm">
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Motivo de consulta *</label>
                            <select x-model="formData.motivo_veterinario" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 text-sm">
                                <option value="">Selecciona</option>
                                <option value="vacunacion">💉 Vacunación</option>
                                <option value="revision">🔍 Revisión general</option>
                                <option value="enfermedad">🤒 Enfermedad</option>
                                <option value="emergencia">🚨 Emergencia</option>
                                <option value="cirugia">⚕️ Cirugía</option>
                                <option value="desparasitacion">💊 Desparasitación</option>
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
                                        <span>🌅 AM</span>
                                    </label>
                                    <label class="flex items-center justify-center p-2 border-2 rounded-lg cursor-pointer text-sm" :class="formData.rango_horario === 'tarde' ? 'border-green-500 bg-green-50 font-semibold' : 'border-gray-300'">
                                        <input type="radio" x-model="formData.rango_horario" value="tarde" class="sr-only">
                                        <span>🌆 PM</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Modalidad</label>
                            <div class="grid grid-cols-2 gap-2">
                                <label class="flex items-center justify-center p-2 border-2 rounded-lg cursor-pointer text-sm" :class="formData.modalidad === 'presencial' ? 'border-green-500 bg-green-50 font-semibold' : 'border-gray-300'">
                                    <input type="radio" x-model="formData.modalidad" value="presencial" class="sr-only">
                                    <span>🏠 Domicilio</span>
                                </label>
                                <label class="flex items-center justify-center p-2 border-2 rounded-lg cursor-pointer text-sm" :class="formData.modalidad === 'consultorio' ? 'border-green-500 bg-green-50 font-semibold' : 'border-gray-300'">
                                    <input type="radio" x-model="formData.modalidad" value="consultorio" class="sr-only">
                                    <span>🏥 Consultorio</span>
                                </label>
                            </div>
                        </div>
                        
                        <div x-show="formData.modalidad === 'presencial'">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Dirección *</label>
                            <input type="text" x-model="formData.direccion_servicio" placeholder="Calle 123 #45-67" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 text-sm">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Síntomas (si aplica)</label>
                            <textarea x-model="formData.sintomas" rows="2" placeholder="Describe los síntomas de la mascota..." class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 text-sm"></textarea>
                        </div>
                    </div>
                </template>

                <!-- LABORATORIO -->
                <template x-if="formData.servicio_tipo === 'laboratorio'">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Exámenes solicitados * <span class="text-xs text-purple-600" x-text="'(' + formData.examenes_solicitados.length + ' seleccionados)'"></span></label>
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
                                <input type="email" x-model="formData.email_resultados" placeholder="correo@ejemplo.com" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 text-sm">
                            </div>
                        </div>
                        
                        <div>
                            <label class="flex items-center space-x-2 p-3 bg-yellow-50 rounded-lg border border-yellow-200">
                                <input type="checkbox" x-model="formData.requiere_ayuno" class="w-4 h-4">
                                <span class="text-sm">⚠️ <strong>Requiere ayuno</strong> (8-12 horas)</span>
                            </label>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Dirección para toma de muestras *</label>
                            <input type="text" x-model="formData.direccion_servicio" placeholder="Calle 123 #45-67, Apto 101" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 text-sm">
                        </div>
                    </div>
                </template>

                <!-- Campos comunes a todos -->
                <div class="border-t pt-4 space-y-3">
                    <h3 class="font-semibold text-gray-900 text-sm">Información de contacto</h3>
                    
                    <div class="grid grid-cols-3 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Teléfono *</label>
                            <input type="tel" x-model="formData.telefono_contacto" placeholder="3001234567" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Urgencia</label>
                            <select x-model="formData.urgencia" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 text-sm">
                                <option value="normal">⏱️ Normal</option>
                                <option value="urgente">🚨 Urgente</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Método pago *</label>
                            <select x-model="formData.metodo_pago_preferido" @change="formData.metodo_pago_preferido === 'transferencia' && (paso3_mostrar_instrucciones = true)" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 text-sm">
                                <option value="">Selecciona</option>
                                <option value="pse">💳 PSE (Pago inmediato con MercadoPago)</option>
                                <option value="transferencia">🏦 Transferencia (Requiere confirmación)</option>
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
                                    <p class="mb-2">Al confirmar, deberás:</p>
                                    <ol class="list-decimal list-inside space-y-1 ml-2">
                                        <li>Realizar la transferencia a la cuenta indicada</li>
                                        <li>Enviar captura de pantalla al WhatsApp: <strong>+57 300 123 4567</strong></li>
                                        <li>Esperar confirmación del administrador</li>
                                        <li>El profesional aceptará tu solicitud una vez confirmado el pago</li>
                                    </ol>
                                    <p class="mt-2 text-xs">⏱️ Tiempo estimado de confirmación: 1-2 horas en horario hábil</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Botones -->
                <div class="flex justify-between pt-4 border-t">
                    <button @click="paso = 1" class="px-6 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                        Atrás
                    </button>
                    <button @click="paso = 3" class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                        Continuar a confirmación
                    </button>
                </div>
            </div>
        </div>

        <!-- Paso 3: Confirmación -->
        <div x-show="paso === 3 && !loading">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Confirma tu solicitud</h2>
            <div class="bg-white rounded-lg shadow-sm p-6 space-y-4">
                <div class="border-b pb-4">
                    <h3 class="font-semibold text-gray-900 mb-2">Servicio</h3>
                    <p class="text-gray-700" x-text="servicioSeleccionado?.nombre"></p>
                    <p class="text-2xl font-bold text-indigo-600 mt-2">$<span x-text="parseInt(servicioSeleccionado?.precio_base || 0).toLocaleString('es-CO')"></span></p>
                </div>

                <div class="border-b pb-4">
                    <h3 class="font-semibold text-gray-900 mb-2">Detalles</h3>
                    <p class="text-sm text-gray-600"><strong>Modalidad:</strong> <span x-text="formData.modalidad"></span></p>
                    <p class="text-sm text-gray-600"><strong>Fecha:</strong> <span x-text="formData.fecha_programada"></span> a las <span x-text="formData.hora_programada"></span></p>
                    <p x-show="formData.direccion_servicio" class="text-sm text-gray-600"><strong>Dirección:</strong> <span x-text="formData.direccion_servicio"></span></p>
                </div>

                <div class="border-b pb-4">
                    <h3 class="font-semibold text-gray-900 mb-2">Síntomas</h3>
                    <p class="text-sm text-gray-600" x-text="formData.sintomas || 'No especificado'"></p>
                </div>
                
                <div class="border-b pb-4">
                    <h3 class="font-semibold text-gray-900 mb-2">Método de Pago</h3>
                    <div x-show="formData.metodo_pago_preferido === 'pse'">
                        <p class="text-sm text-gray-600">💳 <strong>PSE con MercadoPago</strong></p>
                        <p class="text-xs text-green-600 mt-1">✓ Pago inmediato - Tu solicitud será confirmada automáticamente</p>
                    </div>
                    <div x-show="formData.metodo_pago_preferido === 'transferencia'" class="space-y-2">
                        <p class="text-sm text-gray-600">🏦 <strong>Transferencia Bancaria</strong></p>
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-3 mt-2">
                            <p class="text-xs font-semibold text-blue-900 mb-1">📋 Datos para transferencia:</p>
                            <p class="text-xs text-blue-800">Banco: <strong>Bancolombia</strong></p>
                            <p class="text-xs text-blue-800">Tipo: <strong>Ahorros</strong></p>
                            <p class="text-xs text-blue-800">Cuenta: <strong>1234-5678-9012</strong></p>
                            <p class="text-xs text-blue-800">Titular: <strong>Especialistas en Casa SAS</strong></p>
                            <p class="text-xs text-blue-800">NIT: <strong>900.123.456-7</strong></p>
                        </div>
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3 mt-2">
                            <p class="text-xs font-semibold text-yellow-900 mb-1">⚠️ Importante:</p>
                            <p class="text-xs text-yellow-800">Envía el comprobante al WhatsApp <strong>+57 300 123 4567</strong> con tu nombre completo.</p>
                            <p class="text-xs text-yellow-800 mt-1">Tu solicitud quedará en estado <strong>"Pendiente de Pago"</strong> hasta confirmar.</p>
                        </div>
                    </div>
                </div>

                <!-- Botones -->
                <div class="flex justify-between pt-4">
                    <button @click="paso = 2" class="px-6 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                        Atrás
                    </button>
                    <button @click="enviarSolicitud()" :disabled="loading" class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 disabled:opacity-50">
                        <span x-show="!loading">Confirmar Solicitud</span>
                        <span x-show="loading">Procesando...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
