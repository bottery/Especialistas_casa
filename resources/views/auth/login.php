<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Especialistas en Casa</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="/js/validator.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gradient-to-br from-indigo-100 to-blue-100 min-h-screen flex items-center justify-center p-4">
    
    <div x-data="loginForm()" class="bg-white rounded-2xl shadow-2xl p-8 w-full max-w-md">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Iniciar Sesión</h1>
            <p class="text-gray-600">Accede a tu cuenta de Especialistas en Casa</p>
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
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
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
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
                    placeholder="••••••••"
                >
            </div>

            <div class="flex items-center justify-between">
                <label class="flex items-center">
                    <input type="checkbox" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    <span class="ml-2 text-sm text-gray-600">Recordarme</span>
                </label>
                <a href="#" class="text-sm text-indigo-600 hover:text-indigo-700">¿Olvidaste tu contraseña?</a>
            </div>

            <button 
                type="submit"
                :disabled="loading"
                :class="loading ? 'bg-gray-400' : 'bg-indigo-600 hover:bg-indigo-700'"
                class="w-full text-white py-3 rounded-lg font-semibold transition shadow-lg"
            >
                <span x-show="!loading">Iniciar Sesión</span>
                <span x-show="loading">Cargando...</span>
            </button>
        </form>

        <div class="mt-6 text-center">
            <p class="text-gray-600">
                ¿No tienes cuenta? 
                <a href="/register" class="text-indigo-600 font-semibold hover:text-indigo-700">Regístrate aquí</a>
            </p>
        </div>

        <div class="mt-6">
            <a href="/" class="text-gray-600 hover:text-gray-900 flex items-center justify-center">
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
                        const response = await fetch('/api/login', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify(this.formData)
                        });

                        const data = await response.json();

                        if (data.success) {
                            // Guardar token
                            localStorage.setItem('token', data.access_token);
                            localStorage.setItem('refresh_token', data.refresh_token);
                            localStorage.setItem('usuario', JSON.stringify(data.user));

                            this.success = true;
                            this.message = 'Inicio de sesión exitoso. Redirigiendo...';

                            // Redirigir según el rol
                            setTimeout(() => {
                                const rol = data.user.rol;
                                window.location.href = `/${rol}/dashboard`;
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
