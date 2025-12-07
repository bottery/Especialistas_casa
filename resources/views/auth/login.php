<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - VitaHome</title>
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
<body class="hero-gradient min-h-screen flex items-center justify-center p-4">
    
    <div x-data="loginForm()" class="bg-white rounded-2xl shadow-2xl p-8 w-full max-w-md border border-teal-100">
        <div class="text-center mb-8">
            <a href="<?= url('/') ?>" class="inline-block mb-4">
                <img src="<?= asset('/images/vitahome-icon.svg') ?>" alt="VitaHome" class="h-16 w-16 mx-auto">
            </a>
            <h1 class="text-3xl font-bold gradient-text mb-2">Iniciar Sesión</h1>
            <p class="text-gray-600">Accede a tu cuenta de VitaHome</p>
        </div>

        <!-- Alert -->
        <div x-show="message" x-transition class="mb-6 p-4 rounded-lg" :class="success ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'">
            <p x-text="message"></p>
        </div>

        <form @submit.prevent="login" class="space-y-6">
            <div class="field-container">
                <label class="block text-gray-700 font-medium mb-2">Correo Electrónico</label>
                <input 
                    type="email" 
                    x-model="formData.email"
                    data-validate="required|email"
                    required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent transition"
                    placeholder="tu@email.com"
                >
            </div>

            <div class="field-container">
                <label class="block text-gray-700 font-medium mb-2">Contraseña</label>
                <input 
                    type="password" 
                    x-model="formData.password"
                    data-validate="required|minLength:6"
                    required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-transparent transition"
                    placeholder="••••••••"
                >
            </div>

            <div class="flex items-center justify-between">
                <label class="flex items-center">
                    <input type="checkbox" class="rounded border-gray-300 text-teal-600 focus:ring-teal-500">
                    <span class="ml-2 text-sm text-gray-600">Recordarme</span>
                </label>
                <a href="#" class="text-sm text-teal-600 hover:text-teal-700">¿Olvidaste tu contraseña?</a>
            </div>

            <button 
                type="submit"
                :disabled="loading"
                :class="loading ? 'bg-gray-400' : 'gradient-bg hover:opacity-90'"
                class="w-full text-white py-3 rounded-xl font-semibold transition shadow-lg shadow-teal-500/30"
            >
                <span x-show="!loading">Iniciar Sesión</span>
                <span x-show="loading">Cargando...</span>
            </button>
        </form>

        <div class="mt-6 text-center">
            <p class="text-gray-600">
                ¿No tienes cuenta? 
                <a href="<?= url('/register') ?>" class="text-teal-600 font-semibold hover:text-teal-700">Regístrate aquí</a>
            </p>
        </div>

        <div class="mt-6">
            <a href="<?= url('/') ?>" class="text-gray-600 hover:text-gray-900 flex items-center justify-center">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"/>
                </svg>
                Volver al inicio
            </a>
        </div>
    </div>

    <script>
        function loginForm() {
            return {
                formData: {
                    email: '',
                    password: ''
                },
                message: '',
                success: false,
                loading: false,

                async login() {
                    this.loading = true;
                    this.message = '';

                    try {
                        const response = await fetch(BASE_URL + '/api/login', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify(this.formData)
                        });

                        const data = await response.json();

                        if (data.success) {
                            // Validar que tenemos los datos necesarios
                            if (!data.user || !data.user.rol) {
                                this.success = false;
                                this.message = 'Respuesta del servidor inválida';
                                console.error('Datos recibidos:', data);
                                return;
                            }

                            // Guardar token
                            localStorage.setItem('token', data.access_token);
                            localStorage.setItem('refresh_token', data.refresh_token);
                            localStorage.setItem('usuario', JSON.stringify(data.user));

                            this.success = true;
                            this.message = 'Inicio de sesión exitoso. Redirigiendo...';

                            // Redirigir según el rol
                            setTimeout(() => {
                                const rol = data.user.rol;
                                // Los profesionales van al dashboard unificado
                                const rolesProfesionales = ['medico', 'enfermera', 'veterinario', 'laboratorio', 'ambulancia'];
                                const dashboardRol = rolesProfesionales.includes(rol) ? 'profesional' : rol;
                                window.location.href = BASE_URL + `/${dashboardRol}/dashboard`;
                            }, 1000);
                        } else {
                            this.success = false;
                            this.message = data.message || 'Error al iniciar sesión';
                        }
                    } catch (error) {
                        this.success = false;
                        this.message = 'Error de conexión. Por favor, intenta nuevamente.';
                        console.error('Error:', error);
                    } finally {
                        this.loading = false;
                    }
                }
            }
        }
    </script>

</body>
</html>
