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
            profesional_id: '',
            modalidad: 'presencial',
            fecha_programada: '',
            hora_programada: '',
            direccion_servicio: '',
            sintomas: '',
            observaciones: ''
        },
        servicioSeleccionado: null,

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
                const fechaHora = `${this.formData.fecha_programada} ${this.formData.hora_programada}:00`;
                
                const response = await fetch('/api/solicitudes', {
                    method: 'POST',
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        ...this.formData,
                        fecha_programada: fechaHora
                    })
                });

                if (response.ok) {
                    alert('¡Solicitud creada exitosamente!');
                    window.location.href = '/paciente/dashboard';
                } else {
                    alert('Error al crear la solicitud');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Error al crear la solicitud');
            } finally {
                this.loading = false;
            }
        },

        validarFormulario() {
            if (!this.formData.servicio_id) {
                alert('Selecciona un servicio');
                return false;
            }
            if (!this.formData.fecha_programada) {
                alert('Selecciona una fecha');
                return false;
            }
            if (!this.formData.hora_programada) {
                alert('Selecciona una hora');
                return false;
            }
            if (this.formData.modalidad === 'presencial' && !this.formData.direccion_servicio) {
                alert('Ingresa la dirección para servicio presencial');
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
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Programa tu cita</h2>
            <div class="bg-white rounded-lg shadow-sm p-6 space-y-6">
                <!-- Profesional -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Profesional (Opcional)</label>
                    <select x-model="formData.profesional_id" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                        <option value="">Cualquier profesional disponible</option>
                        <template x-for="prof in profesionales" :key="prof.id">
                            <option :value="prof.id" x-text="prof.nombre"></option>
                        </template>
                    </select>
                </div>

                <!-- Modalidad -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Modalidad</label>
                    <div class="grid grid-cols-3 gap-4">
                        <label class="flex items-center p-4 border-2 rounded-lg cursor-pointer" :class="formData.modalidad === 'virtual' ? 'border-indigo-500 bg-indigo-50' : 'border-gray-300'">
                            <input type="radio" x-model="formData.modalidad" value="virtual" class="mr-2">
                            <span>Virtual</span>
                        </label>
                        <label class="flex items-center p-4 border-2 rounded-lg cursor-pointer" :class="formData.modalidad === 'presencial' ? 'border-indigo-500 bg-indigo-50' : 'border-gray-300'">
                            <input type="radio" x-model="formData.modalidad" value="presencial" class="mr-2">
                            <span>Presencial</span>
                        </label>
                        <label class="flex items-center p-4 border-2 rounded-lg cursor-pointer" :class="formData.modalidad === 'consultorio' ? 'border-indigo-500 bg-indigo-50' : 'border-gray-300'">
                            <input type="radio" x-model="formData.modalidad" value="consultorio" class="mr-2">
                            <span>Consultorio</span>
                        </label>
                    </div>
                </div>

                <!-- Fecha y Hora -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Fecha</label>
                        <input type="date" x-model="formData.fecha_programada" :min="getFechaMinima()" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Hora</label>
                        <input type="time" x-model="formData.hora_programada" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>

                <!-- Dirección (solo si es presencial) -->
                <div x-show="formData.modalidad === 'presencial'">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Dirección del servicio</label>
                    <input type="text" x-model="formData.direccion_servicio" placeholder="Calle 123 #45-67, Apto 101" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                </div>

                <!-- Síntomas -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Síntomas o Motivo de Consulta</label>
                    <textarea x-model="formData.sintomas" rows="3" placeholder="Describe brevemente tus síntomas o motivo de la consulta..." class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"></textarea>
                </div>

                <!-- Observaciones -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Observaciones (Opcional)</label>
                    <textarea x-model="formData.observaciones" rows="2" placeholder="Información adicional que consideres importante..." class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500"></textarea>
                </div>

                <!-- Botones -->
                <div class="flex justify-between pt-4">
                    <button @click="paso = 1" class="px-6 py-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                        Atrás
                    </button>
                    <button @click="paso = 3" class="px-6 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                        Continuar
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
