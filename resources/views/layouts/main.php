<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="VitaHome - Servicios médicos especializados a tu alcance">
    <title><?= $title ?? 'VitaHome - Especialistas en Casa' ?></title>
    <link rel="icon" type="image/svg+xml" href="<?= asset('/images/vitahome-icon.svg') ?>">
    
    <!-- TailwindCSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="<?= url('/css/vitahome-brand.css') ?>">
    
    <!-- Configuración de Tailwind -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#14b8a6',
                        secondary: '#1e3a5f'
                    }
                }
            }
        }
    </script>
    
    <style>
        [x-cloak] { display: none !important; }
        .gradient-bg { background: linear-gradient(135deg, #14b8a6 0%, #1e3a5f 100%); }
        .gradient-text { background: linear-gradient(135deg, #14b8a6 0%, #1e3a5f 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
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
                    <a href="<?= url('/') ?>" class="flex items-center space-x-3">
                        <img src="<?= asset('/images/vitahome-icon.svg') ?>" alt="VitaHome" class="h-10 w-10">
                        <span class="text-2xl font-bold gradient-text">VitaHome</span>
                    </a>
                </div>
                
                <div class="flex items-center space-x-4">
                    <a href="<?= url('/login') ?>" class="text-gray-700 hover:text-teal-600 transition">Iniciar Sesión</a>
                    <a href="<?= url('/register') ?>" class="gradient-bg text-white px-4 py-2 rounded-lg hover:opacity-90 transition">Registrarse</a>
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
                    <div class="flex items-center space-x-3 mb-4">
                        <img src="<?= asset('/images/vitahome-icon.svg') ?>" alt="VitaHome" class="h-8 w-8 filter brightness-0 invert">
                        <span class="text-lg font-semibold">VitaHome</span>
                    </div>
                    <p class="text-gray-300">Servicios médicos especializados en la comodidad de tu hogar.</p>
                </div>
                <div>
                    <h3 class="text-lg font-semibold mb-4">Enlaces Rápidos</h3>
                    <ul class="space-y-2">
                        <li><a href="<?= url('/servicios') ?>" class="text-gray-300 hover:text-teal-400 transition">Servicios</a></li>
                        <li><a href="<?= url('/nosotros') ?>" class="text-gray-300 hover:text-teal-400 transition">Nosotros</a></li>
                        <li><a href="<?= url('/contacto') ?>" class="text-gray-300 hover:text-teal-400 transition">Contacto</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-lg font-semibold mb-4">Contacto</h3>
                    <ul class="space-y-2 text-gray-300">
                        <li>Email: info@vitahome.com</li>
                        <li>Teléfono: +57 300 123 4567</li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-gray-700 mt-8 pt-8 text-center text-gray-400">
                <p>&copy; 2025 VitaHome. Todos los derechos reservados.</p>
            </div>
        </div>
    </footer>

</body>
</html>
