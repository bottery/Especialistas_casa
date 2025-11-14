<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Especialistas en Casa - Servicios médicos especializados a tu alcance">
    <title><?= $title ?? 'Especialistas en Casa' ?></title>
    
    <!-- TailwindCSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Configuración de Tailwind -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#4F46E5',
                        secondary: '#10B981'
                    }
                }
            }
        }
    </script>
    
    <style>
        [x-cloak] { display: none !important; }
    </style>
    
    <!-- Alpine.js para interactividad -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-50 min-h-screen">
    
    <!-- Navbar -->
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="/" class="flex items-center space-x-2">
                        <svg class="h-8 w-8 text-primary" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                        </svg>
                        <span class="text-2xl font-bold text-gray-900">Especialistas en Casa</span>
                    </a>
                </div>
                
                <div class="flex items-center space-x-4">
                    <a href="/login" class="text-gray-700 hover:text-primary transition">Iniciar Sesión</a>
                    <a href="/register" class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition">Registrarse</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Contenido -->
    <main>
        <?= $content ?? '' ?>
    </main>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white mt-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div>
                    <h3 class="text-lg font-semibold mb-4">Especialistas en Casa</h3>
                    <p class="text-gray-300">Servicios médicos especializados en la comodidad de tu hogar.</p>
                </div>
                <div>
                    <h3 class="text-lg font-semibold mb-4">Enlaces Rápidos</h3>
                    <ul class="space-y-2">
                        <li><a href="/servicios" class="text-gray-300 hover:text-white transition">Servicios</a></li>
                        <li><a href="/nosotros" class="text-gray-300 hover:text-white transition">Nosotros</a></li>
                        <li><a href="/contacto" class="text-gray-300 hover:text-white transition">Contacto</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-lg font-semibold mb-4">Contacto</h3>
                    <ul class="space-y-2 text-gray-300">
                        <li>Email: info@especialistasencasa.com</li>
                        <li>Teléfono: +57 300 123 4567</li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-gray-700 mt-8 pt-8 text-center text-gray-400">
                <p>&copy; 2025 Especialistas en Casa. Todos los derechos reservados.</p>
            </div>
        </div>
    </footer>

</body>
</html>
