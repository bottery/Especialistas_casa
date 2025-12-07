<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VitaHome - Especialistas en Casa</title>
    <meta name="description" content="VitaHome - Servicios médicos especializados en la comodidad de tu hogar">
    <link rel="icon" type="image/svg+xml" href="<?= asset('/images/vitahome-icon.svg') ?>">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="<?= url('/css/vitahome-brand.css') ?>">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'vitahome': {
                            'teal': {
                                50: '#f0fdfa',
                                100: '#ccfbf1',
                                200: '#99f6e4',
                                300: '#5eead4',
                                400: '#2dd4bf',
                                500: '#14b8a6',
                                600: '#0d9488',
                                700: '#0f766e',
                            },
                            'navy': {
                                50: '#eff6ff',
                                100: '#dbeafe',
                                600: '#1e3a5f',
                                700: '#1e3a8a',
                                800: '#1e2d4d',
                                900: '#172554',
                            }
                        }
                    }
                }
            }
        }
    </script>
    <style>
        .gradient-text {
            background: linear-gradient(135deg, #14b8a6 0%, #1e3a5f 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .gradient-bg {
            background: linear-gradient(135deg, #14b8a6 0%, #1e3a5f 100%);
        }
        .hero-gradient {
            background: linear-gradient(135deg, #f0fdfa 0%, #eff6ff 100%);
        }
    </style>
</head>
<body class="hero-gradient min-h-screen">
    
    <!-- Navbar -->
    <nav class="bg-white/90 backdrop-blur-md shadow-lg sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-20">
                <div class="flex items-center">
                    <a href="<?= url('/') ?>" class="flex items-center space-x-3">
                        <img src="<?= asset('/images/vitahome-icon.svg') ?>" alt="VitaHome" class="h-12 w-12">
                        <div>
                            <span class="text-2xl font-bold gradient-text">VitaHome</span>
                            <span class="block text-xs text-gray-500 -mt-1">Especialistas en Casa</span>
                        </div>
                    </a>
                </div>
                
                <div class="flex items-center space-x-4">
                    <a href="#servicios" class="hidden md:block text-gray-700 hover:text-vitahome-teal-600 transition font-medium">Servicios</a>
                    <a href="#nosotros" class="hidden md:block text-gray-700 hover:text-vitahome-teal-600 transition font-medium">Nosotros</a>
                    <a href="<?= url('/login') ?>" class="text-vitahome-navy-600 hover:text-vitahome-teal-600 transition font-medium">Iniciar Sesión</a>
                    <a href="<?= url('/register') ?>" class="gradient-bg text-white px-6 py-2.5 rounded-xl hover:opacity-90 transition font-medium shadow-lg shadow-vitahome-teal-500/30">
                        Registrarse
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
        <div class="grid md:grid-cols-2 gap-12 items-center">
            <div>
                <div class="inline-flex items-center bg-vitahome-teal-100 text-vitahome-teal-700 px-4 py-2 rounded-full text-sm font-medium mb-6">
                    <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    Servicio Certificado de Salud
                </div>
                <h1 class="text-5xl lg:text-6xl font-bold text-vitahome-navy-900 mb-6 leading-tight">
                    Atención Médica 
                    <span class="gradient-text">en tu Hogar</span>
                </h1>
                <p class="text-xl text-gray-600 mb-8 leading-relaxed">
                    Solicita consultas médicas, veterinarias, servicios de enfermería, laboratorio y más, 
                    desde la comodidad de tu casa con profesionales certificados.
                </p>
                <div class="flex flex-wrap gap-4">
                    <a href="<?= url('/register') ?>" class="gradient-bg text-white px-8 py-4 rounded-xl hover:opacity-90 transition font-semibold shadow-lg shadow-vitahome-teal-500/30 text-lg inline-flex items-center">
                        Comenzar Ahora
                        <svg class="w-5 h-5 ml-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd"/>
                        </svg>
                    </a>
                    <a href="#servicios" class="bg-white text-vitahome-navy-600 px-8 py-4 rounded-xl hover:bg-gray-50 transition font-semibold shadow-lg text-lg border-2 border-vitahome-navy-600/20 inline-flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd"/>
                        </svg>
                        Ver Servicios
                    </a>
                </div>
            </div>
            <div class="hidden md:block">
                <div class="bg-white rounded-3xl shadow-2xl p-8 border border-vitahome-teal-100">
                    <!-- Logo grande central -->
                    <div class="flex justify-center mb-8">
                        <img src="<?= asset('/images/vitahome-icon.svg') ?>" alt="VitaHome" class="h-32 w-32">
                    </div>
                    <div class="grid grid-cols-2 gap-6">
                        <div class="text-center p-4 rounded-xl bg-vitahome-teal-50">
                            <div class="text-3xl font-bold gradient-text">+500</div>
                            <div class="text-gray-600 text-sm mt-1">Profesionales</div>
                        </div>
                        <div class="text-center p-4 rounded-xl bg-vitahome-teal-50">
                            <div class="text-3xl font-bold gradient-text">+10k</div>
                            <div class="text-gray-600 text-sm mt-1">Pacientes</div>
                        </div>
                        <div class="text-center p-4 rounded-xl bg-vitahome-teal-50">
                            <div class="text-3xl font-bold gradient-text">24/7</div>
                            <div class="text-gray-600 text-sm mt-1">Disponibilidad</div>
                        </div>
                        <div class="text-center p-4 rounded-xl bg-vitahome-teal-50">
                            <div class="text-3xl font-bold gradient-text">4.9★</div>
                            <div class="text-gray-600 text-sm mt-1">Calificación</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Servicios Section -->
    <section id="servicios" class="bg-white py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <span class="inline-block bg-vitahome-teal-100 text-vitahome-teal-700 px-4 py-2 rounded-full text-sm font-medium mb-4">
                    Servicios Profesionales
                </span>
                <h2 class="text-4xl font-bold text-vitahome-navy-900 mb-4">Nuestros Servicios</h2>
                <p class="text-xl text-gray-600 max-w-2xl mx-auto">Atención médica completa adaptada a tus necesidades, con profesionales certificados</p>
            </div>

            <div class="grid md:grid-cols-3 gap-8">
                <!-- Servicio 1: Consulta Médica -->
                <div class="group bg-gradient-to-br from-vitahome-teal-50 to-white rounded-2xl p-8 hover:shadow-xl transition-all duration-300 border border-vitahome-teal-100 hover:border-vitahome-teal-300 hover:-translate-y-1">
                    <div class="gradient-bg w-16 h-16 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                        <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-vitahome-navy-900 mb-4">Consulta Médica</h3>
                    <p class="text-gray-600 mb-6">Atención médica general virtual o presencial con profesionales certificados.</p>
                    <ul class="space-y-3 text-gray-600">
                        <li class="flex items-center">
                            <svg class="w-5 h-5 text-vitahome-teal-500 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                            Virtual o presencial
                        </li>
                        <li class="flex items-center">
                            <svg class="w-5 h-5 text-vitahome-teal-500 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                            Recetas digitales
                        </li>
                        <li class="flex items-center">
                            <svg class="w-5 h-5 text-vitahome-teal-500 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                            Historia clínica
                        </li>
                    </ul>
                </div>

                <!-- Servicio 2: Laboratorio -->
                <div class="group bg-gradient-to-br from-vitahome-teal-50 to-white rounded-2xl p-8 hover:shadow-xl transition-all duration-300 border border-vitahome-teal-100 hover:border-vitahome-teal-300 hover:-translate-y-1">
                    <div class="gradient-bg w-16 h-16 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                        <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M7 3a1 1 0 000 2h6a1 1 0 100-2H7zM4 7a1 1 0 011-1h10a1 1 0 110 2H5a1 1 0 01-1-1zM2 11a2 2 0 012-2h12a2 2 0 012 2v4a2 2 0 01-2 2H4a2 2 0 01-2-2v-4z"/>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-vitahome-navy-900 mb-4">Laboratorio</h3>
                    <p class="text-gray-600 mb-6">Toma de muestras a domicilio con resultados rápidos y confiables.</p>
                    <ul class="space-y-3 text-gray-600">
                        <li class="flex items-center">
                            <svg class="w-5 h-5 text-vitahome-teal-500 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                            A domicilio
                        </li>
                        <li class="flex items-center">
                            <svg class="w-5 h-5 text-vitahome-teal-500 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                            Resultados digitales
                        </li>
                        <li class="flex items-center">
                            <svg class="w-5 h-5 text-vitahome-teal-500 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                            Múltiples exámenes
                        </li>
                    </ul>
                </div>

                <!-- Servicio 3: Enfermería -->
                <div class="group bg-gradient-to-br from-vitahome-teal-50 to-white rounded-2xl p-8 hover:shadow-xl transition-all duration-300 border border-vitahome-teal-100 hover:border-vitahome-teal-300 hover:-translate-y-1">
                    <div class="gradient-bg w-16 h-16 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                        <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-vitahome-navy-900 mb-4">Enfermería</h3>
                    <p class="text-gray-600 mb-6">Cuidados de enfermería profesional en la comodidad de tu hogar.</p>
                    <ul class="space-y-3 text-gray-600">
                        <li class="flex items-center">
                            <svg class="w-5 h-5 text-vitahome-teal-500 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                            Curaciones
                        </li>
                        <li class="flex items-center">
                            <svg class="w-5 h-5 text-vitahome-teal-500 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                            Inyectología
                        </li>
                        <li class="flex items-center">
                            <svg class="w-5 h-5 text-vitahome-teal-500 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                            Cuidado continuo
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Más servicios -->
            <div class="grid md:grid-cols-4 gap-6 mt-8">
                <div class="bg-white rounded-xl p-6 border border-gray-200 hover:border-vitahome-teal-300 hover:shadow-lg transition-all text-center group">
                    <div class="w-12 h-12 gradient-bg rounded-xl flex items-center justify-center mx-auto mb-4 group-hover:scale-110 transition-transform">
                        <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10.5 3.5a2.5 2.5 0 00-5 0V4h5v-.5zM6 4V3.5A3.5 3.5 0 0113 3.5V4h.5A1.5 1.5 0 0115 5.5v9a1.5 1.5 0 01-1.5 1.5h-9A1.5 1.5 0 013 14.5v-9A1.5 1.5 0 014.5 4H5z"/>
                        </svg>
                    </div>
                    <h4 class="font-semibold text-vitahome-navy-900">Veterinaria</h4>
                    <p class="text-sm text-gray-500 mt-1">Cuidado para mascotas</p>
                </div>
                <div class="bg-white rounded-xl p-6 border border-gray-200 hover:border-vitahome-teal-300 hover:shadow-lg transition-all text-center group">
                    <div class="w-12 h-12 gradient-bg rounded-xl flex items-center justify-center mx-auto mb-4 group-hover:scale-110 transition-transform">
                        <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M8 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM15 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z"/>
                            <path d="M3 4a1 1 0 00-1 1v10a1 1 0 001 1h1.05a2.5 2.5 0 014.9 0H10a1 1 0 001-1v-5h3.05a2.5 2.5 0 014.9 0H19a1 1 0 001-1v-4a1 1 0 00-1-1h-8a1 1 0 00-1 1v3H4V5a1 1 0 00-1-1z"/>
                        </svg>
                    </div>
                    <h4 class="font-semibold text-vitahome-navy-900">Ambulancia</h4>
                    <p class="text-sm text-gray-500 mt-1">Traslados seguros</p>
                </div>
                <div class="bg-white rounded-xl p-6 border border-gray-200 hover:border-vitahome-teal-300 hover:shadow-lg transition-all text-center group">
                    <div class="w-12 h-12 gradient-bg rounded-xl flex items-center justify-center mx-auto mb-4 group-hover:scale-110 transition-transform">
                        <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5 2a1 1 0 011 1v1h1a1 1 0 010 2H6v1a1 1 0 01-2 0V6H3a1 1 0 010-2h1V3a1 1 0 011-1zm0 10a1 1 0 011 1v1h1a1 1 0 110 2H6v1a1 1 0 11-2 0v-1H3a1 1 0 110-2h1v-1a1 1 0 011-1zM12 2a1 1 0 01.967.744L14.146 7.2 17.5 9.134a1 1 0 010 1.732l-3.354 1.935-1.18 4.455a1 1 0 01-1.933 0L9.854 12.8 6.5 10.866a1 1 0 010-1.732l3.354-1.935 1.18-4.455A1 1 0 0112 2z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <h4 class="font-semibold text-vitahome-navy-900">Terapia Física</h4>
                    <p class="text-sm text-gray-500 mt-1">Rehabilitación</p>
                </div>
                <div class="bg-white rounded-xl p-6 border border-gray-200 hover:border-vitahome-teal-300 hover:shadow-lg transition-all text-center group">
                    <div class="w-12 h-12 gradient-bg rounded-xl flex items-center justify-center mx-auto mb-4 group-hover:scale-110 transition-transform">
                        <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 2a1 1 0 00-1 1v1a1 1 0 002 0V3a1 1 0 00-1-1zM4 4h3a3 3 0 006 0h3a2 2 0 012 2v9a2 2 0 01-2 2H4a2 2 0 01-2-2V6a2 2 0 012-2zm2.5 7a1.5 1.5 0 100-3 1.5 1.5 0 000 3zm2.45 4a2.5 2.5 0 10-4.9 0h4.9zM12 9a1 1 0 100 2h3a1 1 0 100-2h-3zm-1 4a1 1 0 011-1h2a1 1 0 110 2h-2a1 1 0 01-1-1z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <h4 class="font-semibold text-vitahome-navy-900">Psicología</h4>
                    <p class="text-sm text-gray-500 mt-1">Salud mental</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Nosotros / Por qué elegirnos -->
    <section id="nosotros" class="hero-gradient py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <span class="inline-block bg-white text-vitahome-teal-700 px-4 py-2 rounded-full text-sm font-medium mb-4 shadow-sm">
                    ¿Por qué VitaHome?
                </span>
                <h2 class="text-4xl font-bold text-vitahome-navy-900 mb-4">Tu salud, nuestra prioridad</h2>
                <p class="text-xl text-gray-600 max-w-2xl mx-auto">Combinamos tecnología y profesionalismo para brindarte la mejor atención médica en casa</p>
            </div>

            <div class="grid md:grid-cols-3 gap-8">
                <div class="bg-white rounded-2xl p-8 shadow-lg border border-vitahome-teal-100">
                    <div class="w-14 h-14 bg-vitahome-teal-100 rounded-xl flex items-center justify-center mb-6">
                        <svg class="w-7 h-7 text-vitahome-teal-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-vitahome-navy-900 mb-3">Profesionales Certificados</h3>
                    <p class="text-gray-600">Todos nuestros especialistas están verificados y cuentan con certificaciones actualizadas.</p>
                </div>

                <div class="bg-white rounded-2xl p-8 shadow-lg border border-vitahome-teal-100">
                    <div class="w-14 h-14 bg-vitahome-teal-100 rounded-xl flex items-center justify-center mb-6">
                        <svg class="w-7 h-7 text-vitahome-teal-600" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-vitahome-navy-900 mb-3">Disponibilidad 24/7</h3>
                    <p class="text-gray-600">Atención disponible las 24 horas, los 7 días de la semana para emergencias y consultas.</p>
                </div>

                <div class="bg-white rounded-2xl p-8 shadow-lg border border-vitahome-teal-100">
                    <div class="w-14 h-14 bg-vitahome-teal-100 rounded-xl flex items-center justify-center mb-6">
                        <svg class="w-7 h-7 text-vitahome-teal-600" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-vitahome-navy-900 mb-3">Calificación Excelente</h3>
                    <p class="text-gray-600">4.9 estrellas promedio basado en miles de reseñas de pacientes satisfechos.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="gradient-bg py-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="max-w-3xl mx-auto">
                <img src="<?= asset('/images/vitahome-icon.svg') ?>" alt="VitaHome" class="h-20 w-20 mx-auto mb-8 filter brightness-0 invert">
                <h2 class="text-4xl font-bold text-white mb-4">¿Listo para comenzar?</h2>
                <p class="text-xl text-white/80 mb-8">Únete a miles de personas que ya confían en VitaHome para su atención médica en casa</p>
                <div class="flex flex-wrap justify-center gap-4">
                    <a href="<?= url('/register') ?>" class="bg-white text-vitahome-teal-600 px-8 py-4 rounded-xl hover:bg-gray-100 transition font-semibold text-lg shadow-lg inline-flex items-center">
                        Crear Cuenta Gratis
                        <svg class="w-5 h-5 ml-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd"/>
                        </svg>
                    </a>
                    <a href="<?= url('/login') ?>" class="bg-transparent text-white border-2 border-white/50 px-8 py-4 rounded-xl hover:bg-white/10 transition font-semibold text-lg inline-flex items-center">
                        Ya tengo cuenta
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-vitahome-navy-900 text-white py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-4 gap-8 mb-12">
                <div>
                    <div class="flex items-center space-x-3 mb-6">
                        <img src="<?= asset('/images/vitahome-icon.svg') ?>" alt="VitaHome" class="h-10 w-10 filter brightness-0 invert">
                        <span class="text-xl font-bold">VitaHome</span>
                    </div>
                    <p class="text-gray-400 leading-relaxed">Servicios médicos especializados en la comodidad de tu hogar. Tu salud, nuestra prioridad.</p>
                </div>
                <div>
                    <h3 class="text-lg font-semibold mb-4">Servicios</h3>
                    <ul class="space-y-3 text-gray-400">
                        <li><a href="#" class="hover:text-vitahome-teal-400 transition">Médicos</a></li>
                        <li><a href="#" class="hover:text-vitahome-teal-400 transition">Enfermería</a></li>
                        <li><a href="#" class="hover:text-vitahome-teal-400 transition">Laboratorio</a></li>
                        <li><a href="#" class="hover:text-vitahome-teal-400 transition">Veterinaria</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-lg font-semibold mb-4">Empresa</h3>
                    <ul class="space-y-3 text-gray-400">
                        <li><a href="#" class="hover:text-vitahome-teal-400 transition">Nosotros</a></li>
                        <li><a href="#" class="hover:text-vitahome-teal-400 transition">Trabaja con nosotros</a></li>
                        <li><a href="#" class="hover:text-vitahome-teal-400 transition">Términos</a></li>
                        <li><a href="#" class="hover:text-vitahome-teal-400 transition">Privacidad</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-lg font-semibold mb-4">Contacto</h3>
                    <ul class="space-y-3 text-gray-400">
                        <li class="flex items-center">
                            <svg class="w-5 h-5 mr-2 text-vitahome-teal-400" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"/>
                                <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"/>
                            </svg>
                            info@vitahome.com
                        </li>
                        <li class="flex items-center">
                            <svg class="w-5 h-5 mr-2 text-vitahome-teal-400" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z"/>
                            </svg>
                            +57 300 123 4567
                        </li>
                        <li class="flex items-center">
                            <svg class="w-5 h-5 mr-2 text-vitahome-teal-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                            </svg>
                            Bogotá, Colombia
                        </li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-gray-800 pt-8 flex flex-col md:flex-row justify-between items-center">
                <p class="text-gray-400">&copy; 2025 VitaHome. Todos los derechos reservados.</p>
                <div class="flex space-x-4 mt-4 md:mt-0">
                    <a href="#" class="w-10 h-10 rounded-full bg-gray-800 flex items-center justify-center hover:bg-vitahome-teal-600 transition">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z"/></svg>
                    </a>
                    <a href="#" class="w-10 h-10 rounded-full bg-gray-800 flex items-center justify-center hover:bg-vitahome-teal-600 transition">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12.017 0C5.396 0 .029 5.367.029 11.987c0 5.079 3.158 9.417 7.618 11.162-.105-.949-.199-2.403.041-3.439.219-.937 1.406-5.957 1.406-5.957s-.359-.72-.359-1.781c0-1.663.967-2.911 2.168-2.911 1.024 0 1.518.769 1.518 1.688 0 1.029-.653 2.567-.992 3.992-.285 1.193.6 2.165 1.775 2.165 2.128 0 3.768-2.245 3.768-5.487 0-2.861-2.063-4.869-5.008-4.869-3.41 0-5.409 2.562-5.409 5.199 0 1.033.394 2.143.889 2.741.099.12.112.225.085.345-.09.375-.293 1.199-.334 1.363-.053.225-.172.271-.401.165-1.495-.69-2.433-2.878-2.433-4.646 0-3.776 2.748-7.252 7.92-7.252 4.158 0 7.392 2.967 7.392 6.923 0 4.135-2.607 7.462-6.233 7.462-1.214 0-2.354-.629-2.758-1.379l-.749 2.848c-.269 1.045-1.004 2.352-1.498 3.146 1.123.345 2.306.535 3.55.535 6.607 0 11.985-5.365 11.985-11.987C23.97 5.39 18.592.026 11.985.026L12.017 0z"/></svg>
                    </a>
                    <a href="#" class="w-10 h-10 rounded-full bg-gray-800 flex items-center justify-center hover:bg-vitahome-teal-600 transition">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/></svg>
                    </a>
                </div>
            </div>
        </div>
    </footer>

</body>
</html>
