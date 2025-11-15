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
            profesional_id: '',
            modalidad: 'presencial',
            fecha_programada: '',
            hora_programada: '',
            direccion_servicio: '',
            sintomas: '',
            observaciones: '',
            telefono_contacto: '',
            urgencia: 'normal',
            metodo_pago_preferido: 'efectivo',
            
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
            await this.cargarProfesionales(servicio.id);
        },

        async cargarProfesionales(servicioId) {
            this.loading = true;
            try {
                const response = await fetch(`/api/profesionales?servicio_id=${servicioId}`);
                if (response.ok) {
                    const data = await response.json();
                    this.profesionales = data.profesionales || data || [];
                }
            } catch (error) {
                console.error('Error:', error);
            } finally {
                this.loading = false;
            }
        },

        async enviarSolicitud() {
            if (!this.validarFormulario()) return;

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

                if (response.ok) {
                    alert('¡Solicitud creada exitosamente!');
                    window.location.href = '/paciente/dashboard';
                } else {
                    const error = await response.json();
                    alert(error.message || 'Error al crear la solicitud');
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
            
            if (tipo === 'ambulancia') {
                if (!this.formData.fecha_programada || !this.formData.hora_programada) {
                    alert('Selecciona fecha y hora para la ambulancia');
                    return false;
                }
                if (!this.formData.origen || !this.formData.destino) {
                    alert('Ingresa dirección de origen y destino');
                    return false;
                }
                if (!this.formData.condicion_paciente) {
                    alert('Describe la condición del paciente');
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
                    <button @click="volver()" class="text-gray-600 hover:text-gray-900 mr-4">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                        </svg>
                    </button>
                    <h1 class="text-xl font-semibold text-gray-900">Nueva Solicitud</h1>
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
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Selecciona un servicio</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <template x-for="servicio in servicios" :key="servicio.id">
                    <div @click="seleccionarServicio(servicio)" class="bg-white rounded-lg shadow-sm p-6 cursor-pointer hover:shadow-md hover:border-indigo-500 border-2 border-transparent transition">
                        <h3 class="text-lg font-semibold text-gray-900 mb-2" x-text="servicio.nombre"></h3>
                        <p class="text-sm text-gray-600 mb-3" x-text="servicio.descripcion"></p>
                        <p class="text-xl font-bold text-indigo-600">$<span x-text="parseInt(servicio.precio_base).toLocaleString('es-CO')"></span></p>
                    </div>
                </template>
            </div>
        </div>

        <!-- Paso 2: Detalles y Programación -->
        <div x-show="paso === 2 && !loading">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Detalles del servicio</h2>
            <div class="bg-white rounded-lg shadow-sm p-6 space-y-6">
                
                <!-- MÉDICO ESPECIALISTA -->
                <template x-if="formData.servicio_tipo === 'medico'">
                    <div class="space-y-6">
                        <div class="bg-blue-50 p-4 rounded-lg">
                            <p class="text-sm text-blue-800"><strong>Importante:</strong> Tu solicitud será enviada al médico para aprobación. Recibirás confirmación una vez sea revisada.</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Especialidad requerida</label>
                            <input type="text" x-model="formData.especialidad" placeholder="Ej: Cardiología, Dermatología, Medicina General" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Profesional (Opcional)</label>
                            <select x-model="formData.profesional_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                                <option value="">Cualquier médico disponible</option>
                                <template x-for="prof in profesionales" :key="prof.id">
                                    <option :value="prof.id" x-text="prof.nombre"></option>
                                </template>
                            </select>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Fecha *</label>
                                <input type="date" x-model="formData.fecha_programada" :min="getFechaMinima()" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Rango horario *</label>
                                <select x-model="formData.rango_horario" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                                    <option value="">Selecciona</option>
                                    <option value="manana">Mañana (8am - 12pm)</option>
                                    <option value="tarde">Tarde (2pm - 6pm)</option>
                                </select>
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Modalidad</label>
                            <div class="grid grid-cols-2 gap-4">
                                <label class="flex items-center p-4 border-2 rounded-lg cursor-pointer" :class="formData.modalidad === 'virtual' ? 'border-indigo-500 bg-indigo-50' : 'border-gray-300'">
                                    <input type="radio" x-model="formData.modalidad" value="virtual" class="mr-2">
                                    <span>Telemedicina</span>
                                </label>
                                <label class="flex items-center p-4 border-2 rounded-lg cursor-pointer" :class="formData.modalidad === 'presencial' ? 'border-indigo-500 bg-indigo-50' : 'border-gray-300'">
                                    <input type="radio" x-model="formData.modalidad" value="presencial" class="mr-2">
                                    <span>Domicilio</span>
                                </label>
                            </div>
                        </div>
                        
                        <div x-show="formData.modalidad === 'presencial'">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Dirección *</label>
                            <input type="text" x-model="formData.direccion_servicio" placeholder="Calle 123 #45-67, Apto 101" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Motivo de consulta / Síntomas *</label>
                            <textarea x-model="formData.sintomas" rows="3" placeholder="Describe tus síntomas o motivo de la consulta..." class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"></textarea>
                        </div>
                    </div>
                </template>

                <!-- AMBULANCIA -->
                <template x-if="formData.servicio_tipo === 'ambulancia'">
                    <div class="space-y-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de ambulancia *</label>
                            <div class="grid grid-cols-2 gap-4">
                                <label class="flex items-center p-4 border-2 rounded-lg cursor-pointer" :class="formData.tipo_ambulancia === 'basica' ? 'border-indigo-500 bg-indigo-50' : 'border-gray-300'">
                                    <input type="radio" x-model="formData.tipo_ambulancia" value="basica" class="mr-2">
                                    <div>
                                        <div class="font-semibold">Básica</div>
                                        <div class="text-xs text-gray-600">Traslado estándar</div>
                                    </div>
                                </label>
                                <label class="flex items-center p-4 border-2 rounded-lg cursor-pointer" :class="formData.tipo_ambulancia === 'medicalizada' ? 'border-indigo-500 bg-indigo-50' : 'border-gray-300'">
                                    <input type="radio" x-model="formData.tipo_ambulancia" value="medicalizada" class="mr-2">
                                    <div>
                                        <div class="font-semibold">Medicalizada</div>
                                        <div class="text-xs text-gray-600">Con equipo médico</div>
                                    </div>
                                </label>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Fecha *</label>
                                <input type="date" x-model="formData.fecha_programada" :min="getFechaMinima()" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Hora exacta *</label>
                                <input type="time" x-model="formData.hora_programada" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de emergencia *</label>
                            <div class="grid grid-cols-2 gap-4">
                                <label class="flex items-center p-4 border-2 rounded-lg cursor-pointer" :class="formData.tipo_emergencia === 'programado' ? 'border-indigo-500 bg-indigo-50' : 'border-gray-300'">
                                    <input type="radio" x-model="formData.tipo_emergencia" value="programado" class="mr-2">
                                    <span>Programado</span>
                                </label>
                                <label class="flex items-center p-4 border-2 rounded-lg cursor-pointer" :class="formData.tipo_emergencia === 'urgente' ? 'border-red-500 bg-red-50' : 'border-gray-300'">
                                    <input type="radio" x-model="formData.tipo_emergencia" value="urgente" class="mr-2">
                                    <span class="text-red-600 font-semibold">Urgente</span>
                                </label>
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Dirección de origen (recogida) *</label>
                            <input type="text" x-model="formData.origen" placeholder="Calle 123 #45-67" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Dirección de destino (entrega) *</label>
                            <input type="text" x-model="formData.destino" placeholder="Hospital XYZ, Calle 456 #78-90" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Condición del paciente *</label>
                            <textarea x-model="formData.condicion_paciente" rows="2" placeholder="Ej: Estable, requiere oxígeno, paciente crítico..." class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"></textarea>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Número de acompañantes</label>
                                <input type="number" x-model="formData.numero_acompanantes" min="0" max="2" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Contacto de emergencia</label>
                                <input type="text" x-model="formData.contacto_emergencia" placeholder="Nombre y teléfono" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Observaciones especiales</label>
                            <textarea x-model="formData.observaciones" rows="2" placeholder="Equipo médico necesario, instrucciones adicionales..." class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"></textarea>
                        </div>
                    </div>
                </template>

                <!-- ENFERMERÍA -->
                <template x-if="formData.servicio_tipo === 'enfermera'">
                    <div class="space-y-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de cuidado *</label>
                            <select x-model="formData.tipo_cuidado" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                                <option value="">Selecciona</option>
                                <option value="cuidado_general">Cuidado general</option>
                                <option value="inyecciones">Aplicación de inyecciones</option>
                                <option value="curaciones">Curaciones</option>
                                <option value="postoperatorio">Post-operatorio</option>
                                <option value="sondas">Manejo de sondas</option>
                                <option value="geriatrico">Cuidado geriátrico</option>
                                <option value="pediatrico">Cuidado pediátrico</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Intensidad horaria *</label>
                            <div class="grid grid-cols-2 gap-4">
                                <label class="flex items-center p-4 border-2 rounded-lg cursor-pointer" :class="formData.intensidad_horaria === '12h' ? 'border-indigo-500 bg-indigo-50' : 'border-gray-300'">
                                    <input type="radio" x-model="formData.intensidad_horaria" value="12h" class="mr-2">
                                    <span>12 horas</span>
                                </label>
                                <label class="flex items-center p-4 border-2 rounded-lg cursor-pointer" :class="formData.intensidad_horaria === '24h' ? 'border-indigo-500 bg-indigo-50' : 'border-gray-300'">
                                    <input type="radio" x-model="formData.intensidad_horaria" value="24h" class="mr-2">
                                    <span>24 horas</span>
                                </label>
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Turno preferido</label>
                            <select x-model="formData.turno" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                                <option value="diurno">Diurno (6am - 6pm)</option>
                                <option value="nocturno">Nocturno (6pm - 6am)</option>
                                <option value="mixto">Mixto</option>
                            </select>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Duración *</label>
                                <select x-model="formData.duracion_tipo" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                                    <option value="dias">Días</option>
                                    <option value="semanas">Semanas</option>
                                    <option value="meses">Meses</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Cantidad *</label>
                                <input type="number" x-model="formData.duracion_cantidad" min="1" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Fecha de inicio *</label>
                            <input type="date" x-model="formData.fecha_programada" :min="getFechaMinima()" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Género preferido</label>
                            <select x-model="formData.genero_preferido" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                                <option value="indistinto">Indistinto</option>
                                <option value="femenino">Femenino</option>
                                <option value="masculino">Masculino</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Dirección del servicio *</label>
                            <input type="text" x-model="formData.direccion_servicio" placeholder="Calle 123 #45-67, Apto 101" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Condición del paciente</label>
                            <textarea x-model="formData.condicion_paciente_detalle" rows="2" placeholder="Movilidad reducida, alzheimer, diabetes, etc..." class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"></textarea>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Necesidades especiales</label>
                            <textarea x-model="formData.necesidades_especiales" rows="2" placeholder="Manejo de sondas, oxígeno, medicación específica..." class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"></textarea>
                        </div>
                    </div>
                </template>

                <!-- VETERINARIA -->
                <template x-if="formData.servicio_tipo === 'veterinario'">
                    <div class="space-y-6">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de mascota *</label>
                                <select x-model="formData.tipo_mascota" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                                    <option value="">Selecciona</option>
                                    <option value="perro">Perro</option>
                                    <option value="gato">Gato</option>
                                    <option value="ave">Ave</option>
                                    <option value="conejo">Conejo</option>
                                    <option value="otro">Otro</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Nombre de la mascota *</label>
                                <input type="text" x-model="formData.nombre_mascota" placeholder="Nombre" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Edad</label>
                                <input type="text" x-model="formData.edad_mascota" placeholder="Ej: 3 años" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Raza / Tamaño</label>
                                <input type="text" x-model="formData.raza_tamano" placeholder="Ej: Golden Retriever / Grande" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Motivo de consulta *</label>
                            <select x-model="formData.motivo_veterinario" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                                <option value="">Selecciona</option>
                                <option value="vacunacion">Vacunación</option>
                                <option value="revision">Revisión general</option>
                                <option value="enfermedad">Enfermedad</option>
                                <option value="emergencia">Emergencia</option>
                                <option value="cirugia">Cirugía</option>
                                <option value="desparasitacion">Desparasitación</option>
                            </select>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Fecha *</label>
                                <input type="date" x-model="formData.fecha_programada" :min="getFechaMinima()" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Rango horario *</label>
                                <select x-model="formData.rango_horario" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                                    <option value="">Selecciona</option>
                                    <option value="manana">Mañana (8am - 12pm)</option>
                                    <option value="tarde">Tarde (2pm - 6pm)</option>
                                </select>
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Modalidad</label>
                            <div class="grid grid-cols-2 gap-4">
                                <label class="flex items-center p-4 border-2 rounded-lg cursor-pointer" :class="formData.modalidad === 'presencial' ? 'border-indigo-500 bg-indigo-50' : 'border-gray-300'">
                                    <input type="radio" x-model="formData.modalidad" value="presencial" class="mr-2">
                                    <span>A domicilio</span>
                                </label>
                                <label class="flex items-center p-4 border-2 rounded-lg cursor-pointer" :class="formData.modalidad === 'consultorio' ? 'border-indigo-500 bg-indigo-50' : 'border-gray-300'">
                                    <input type="radio" x-model="formData.modalidad" value="consultorio" class="mr-2">
                                    <span>En consultorio</span>
                                </label>
                            </div>
                        </div>
                        
                        <div x-show="formData.modalidad === 'presencial'">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Dirección *</label>
                            <input type="text" x-model="formData.direccion_servicio" placeholder="Calle 123 #45-67" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Síntomas (si aplica)</label>
                            <textarea x-model="formData.sintomas" rows="2" placeholder="Describe los síntomas de la mascota..." class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"></textarea>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Historial de vacunas (opcional)</label>
                            <textarea x-model="formData.historial_vacunas" rows="2" placeholder="Últimas vacunas aplicadas..." class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"></textarea>
                        </div>
                    </div>
                </template>

                <!-- LABORATORIO -->
                <template x-if="formData.servicio_tipo === 'laboratorio'">
                    <div class="space-y-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Exámenes solicitados *</label>
                            <div class="border border-gray-300 rounded-lg p-4 space-y-2 max-h-60 overflow-y-auto">
                                <template x-for="examen in examenesDisponibles" :key="examen">
                                    <label class="flex items-center p-2 hover:bg-gray-50 rounded cursor-pointer">
                                        <input type="checkbox" :value="examen" @change="
                                            if ($event.target.checked) {
                                                formData.examenes_solicitados.push(examen);
                                            } else {
                                                formData.examenes_solicitados = formData.examenes_solicitados.filter(e => e !== examen);
                                            }
                                        " class="mr-3">
                                        <span x-text="examen"></span>
                                    </label>
                                </template>
                            </div>
                            <p class="text-sm text-gray-600 mt-2">Seleccionados: <span x-text="formData.examenes_solicitados.length"></span></p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Fecha para toma de muestras *</label>
                            <input type="date" x-model="formData.fecha_programada" :min="getFechaMinima()" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                            <p class="text-xs text-gray-600 mt-1">Se recomienda agendar en horario de mañana si requiere ayuno</p>
                        </div>
                        
                        <div>
                            <label class="flex items-center space-x-3 p-4 bg-yellow-50 rounded-lg">
                                <input type="checkbox" x-model="formData.requiere_ayuno" class="w-5 h-5">
                                <span class="text-sm"><strong>Requiere ayuno</strong> (8-12 horas sin alimentos)</span>
                            </label>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Dirección para toma de muestras *</label>
                            <input type="text" x-model="formData.direccion_servicio" placeholder="Calle 123 #45-67, Apto 101" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Email para recibir resultados *</label>
                            <input type="email" x-model="formData.email_resultados" placeholder="correo@ejemplo.com" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                            <p class="text-xs text-gray-600 mt-1">Los resultados se enviarán en 24-48 horas</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Preparación especial</label>
                            <textarea x-model="formData.preparacion_especial" rows="2" placeholder="Indicaciones especiales del médico..." class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"></textarea>
                        </div>
                    </div>
                </template>

                <!-- Campos comunes a todos -->
                <div class="border-t pt-6 space-y-4">
                    <h3 class="font-semibold text-gray-900">Información de contacto</h3>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Teléfono de contacto *</label>
                            <input type="tel" x-model="formData.telefono_contacto" placeholder="3001234567" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Urgencia</label>
                            <select x-model="formData.urgencia" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                                <option value="normal">Normal</option>
                                <option value="urgente">Urgente</option>
                            </select>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Método de pago preferido</label>
                        <select x-model="formData.metodo_pago_preferido" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                            <option value="efectivo">Efectivo</option>
                            <option value="tarjeta">Tarjeta de crédito/débito</option>
                            <option value="transferencia">Transferencia</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Observaciones adicionales</label>
                        <textarea x-model="formData.observaciones" rows="2" placeholder="Información adicional que consideres importante..." class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"></textarea>
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
