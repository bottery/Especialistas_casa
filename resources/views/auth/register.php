<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - VitaHome</title>
    <link rel="icon" type="image/svg+xml" href="<?= asset('/images/vitahome-icon.svg') ?>">
    <script>const BASE_URL = '<?= rtrim(BASE_URL, "/") ?>';</script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="<?= asset('/js/validator.js') ?>"></script>
    <link rel="stylesheet" href="<?= url('/css/vitahome-brand.css') ?>">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        .gradient-bg { background: linear-gradient(135deg, #14b8a6 0%, #1e3a5f 100%); }
        .gradient-text { background: linear-gradient(135deg, #14b8a6 0%, #1e3a5f 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
        .hero-gradient { background: linear-gradient(135deg, #f0fdfa 0%, #eff6ff 100%); }
    </style>
</head>
<body class="hero-gradient min-h-screen">
    
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-2xl w-full space-y-8 bg-white p-10 rounded-2xl shadow-2xl border border-teal-100">
            <!-- Logo y título -->
            <div class="text-center">
                <a href="<?= url('/') ?>" class="inline-block mb-4">
                    <img src="<?= asset('/images/vitahome-icon.svg') ?>" alt="VitaHome" class="h-16 w-16 mx-auto">
                </a>
                <h2 class="text-3xl font-extrabold gradient-text">Crear Cuenta</h2>
                <p class="mt-2 text-sm text-gray-600">
                    ¿Ya tienes cuenta? 
                    <a href="<?= url('/login') ?>" class="font-medium text-teal-600 hover:text-teal-500">Inicia sesión</a>
                </p>
            </div>

            <!-- Formulario de registro -->
            <div x-data="registerForm()">
                <!-- Mensajes de error/éxito -->
                <div x-show="message" x-transition class="mb-4 p-4 rounded-lg" :class="messageType === 'success' ? 'bg-green-50 text-green-800 border border-green-200' : 'bg-red-50 text-red-800 border border-red-200'">
                    <p x-text="message"></p>
                </div>

                <form @submit.prevent="submitForm" class="space-y-6">
                    
                    <!-- Información Personal -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-semibold text-gray-900 border-b pb-2">Información Personal</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="field-container">
                                <label for="nombre" class="block text-sm font-medium text-gray-700">Nombre *</label>
                                <input type="text" id="nombre" x-model="formData.nombre" data-validate="required|minLength:2" required
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-teal-500 focus:border-teal-500">
                            </div>

                            <div class="field-container">
                                <label for="apellido" class="block text-sm font-medium text-gray-700">Apellido *</label>
                                <input type="text" id="apellido" x-model="formData.apellido" data-validate="required|minLength:2" required
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-teal-500 focus:border-teal-500">
                            </div>
                        </div>

                        <div class="field-container">
                            <label for="email" class="block text-sm font-medium text-gray-700">Correo Electrónico *</label>
                            <input type="email" id="email" x-model="formData.email" data-validate="required|email" required
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-teal-500 focus:border-teal-500">
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="field-container">
                                <label for="telefono" class="block text-sm font-medium text-gray-700">Teléfono *</label>
                                <input type="tel" id="telefono" x-model="formData.telefono" data-validate="required|phone" required
                                    placeholder="+57 300 123 4567"
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-teal-500 focus:border-teal-500">
                            </div>

                            <div class="field-container">
                                <label for="documento" class="block text-sm font-medium text-gray-700">Documento de Identidad *</label>
                                <input type="text" id="documento" x-model="formData.documento" data-validate="required|minLength:6" required
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-teal-500 focus:border-teal-500">
                            </div>
                        </div>
                    </div>

                    <!-- Dirección -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-semibold text-gray-900 border-b pb-2">Dirección</h3>
                        
                        <div>
                            <label for="direccion" class="block text-sm font-medium text-gray-700">Dirección Completa *</label>
                            <input type="text" id="direccion" x-model="formData.direccion" required
                                placeholder="Calle 123 #45-67"
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-teal-500 focus:border-teal-500">
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="ciudad" class="block text-sm font-medium text-gray-700">Ciudad *</label>
                                <input type="text" id="ciudad" x-model="formData.ciudad" required
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-teal-500 focus:border-teal-500">
                            </div>

                            <div>
                                <label for="codigo_postal" class="block text-sm font-medium text-gray-700">Código Postal</label>
                                <input type="text" id="codigo_postal" x-model="formData.codigo_postal"
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-teal-500 focus:border-teal-500">
                            </div>
                        </div>
                    </div>

                    <!-- Contraseña -->
                    <div class="space-y-4">
                        <h3 class="text-lg font-semibold text-gray-900 border-b pb-2">Seguridad</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="password" class="block text-sm font-medium text-gray-700">Contraseña *</label>
                                <input type="password" id="password" x-model="formData.password" required
                                    minlength="8"
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-teal-500 focus:border-teal-500">
                                <p class="mt-1 text-xs text-gray-500">Mínimo 8 caracteres</p>
                            </div>

                            <div>
                                <label for="password_confirm" class="block text-sm font-medium text-gray-700">Confirmar Contraseña *</label>
                                <input type="password" id="password_confirm" x-model="formData.password_confirm" required
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-teal-500 focus:border-teal-500">
                            </div>
                        </div>
                    </div>

                    <!-- Términos y condiciones -->
                    <div class="flex items-start">
                        <input type="checkbox" id="terms" x-model="formData.terms" required
                            class="h-4 w-4 text-teal-600 focus:ring-teal-500 border-gray-300 rounded mt-1">
                        <label for="terms" class="ml-2 block text-sm text-gray-900">
                            Acepto los <a href="#" class="text-teal-600 hover:text-teal-500">términos y condiciones</a> 
                            y la <a href="#" class="text-teal-600 hover:text-teal-500">política de privacidad</a>
                        </label>
                    </div>

                    <!-- Botón de envío -->
                    <div>
                        <button type="submit" :disabled="loading"
                            class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white gradient-bg hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500 disabled:opacity-50 disabled:cursor-not-allowed">
                            <span x-show="!loading">Crear Cuenta</span>
                            <span x-show="loading" class="flex items-center">
                                <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Procesando...
                            </span>
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>

    <script>
        function registerForm() {
            return {
                loading: false,
                message: '',
                messageType: 'error',
                formData: {
                    nombre: '',
                    apellido: '',
                    email: '',
                    telefono: '',
                    documento: '',
                    direccion: '',
                    ciudad: '',
                    codigo_postal: '',
                    rol: 'paciente',
                    password: '',
                    password_confirm: '',
                    terms: false
                },

                async submitForm() {
                    this.message = '';
                    
                    // Validar contraseñas
                    if (this.formData.password !== this.formData.password_confirm) {
                        this.message = 'Las contraseñas no coinciden';
                        this.messageType = 'error';
                        return;
                    }

                    if (this.formData.password.length < 8) {
                        this.message = 'La contraseña debe tener al menos 8 caracteres';
                        this.messageType = 'error';
                        return;
                    }

                    this.loading = true;

                    try {
                        const response = await fetch(BASE_URL + '/api/register', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify(this.formData)
                        });

                        const data = await response.json();

                        if (response.ok) {
                            this.message = data.message || '¡Registro exitoso! Serás redirigido al login...';
                            this.messageType = 'success';
                            
                            // Redirigir al login después de 2 segundos
                            setTimeout(() => {
                                window.location.href = BASE_URL + '/login';
                            }, 2000);
                        } else {
                            this.message = data.message || 'Error al registrar usuario';
                            this.messageType = 'error';
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        this.message = 'Error de conexión. Por favor, intenta nuevamente.';
                        this.messageType = 'error';
                    } finally {
                        this.loading = false;
                    }
                }
            }
        }
    </script>

</body>
</html>
