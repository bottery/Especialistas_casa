<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Especialistas en Casa</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-indigo-50 to-blue-50 min-h-screen">
    
    <!-- Navbar -->
    <nav class="bg-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="/" class="flex items-center space-x-2">
                        <svg class="h-8 w-8 text-indigo-600" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
                            <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"/>
                        </svg>
                        <span class="text-2xl font-bold text-gray-900">Especialistas en Casa</span>
                    </a>
                </div>
                
                <div class="flex items-center space-x-4">
                    <a href="/login" class="text-gray-700 hover:text-indigo-600 transition font-medium">Iniciar Sesión</a>
                    <a href="/register" class="bg-indigo-600 text-white px-6 py-2 rounded-lg hover:bg-indigo-700 transition font-medium shadow-md">Registrarse</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
        <div class="grid md:grid-cols-2 gap-12 items-center">
            <div>
                <h1 class="text-5xl font-bold text-gray-900 mb-6 leading-tight">
                    Atención Médica Especializada 
                    <span class="text-indigo-600">en tu Hogar</span>
                </h1>
                <p class="text-xl text-gray-600 mb-8">
                    Solicita consultas médicas, veterinarias, servicios de enfermería, laboratorio y más, 
                    desde la comodidad de tu casa.
                </p>
                <div class="flex space-x-4">
                    <a href="/register" class="bg-indigo-600 text-white px-8 py-4 rounded-lg hover:bg-indigo-700 transition font-semibold shadow-lg text-lg">
                        Comenzar Ahora
                    </a>
                    <a href="#servicios" class="bg-white text-indigo-600 px-8 py-4 rounded-lg hover:bg-gray-50 transition font-semibold shadow-lg text-lg border-2 border-indigo-600">
                        Ver Servicios
                    </a>
                </div>
            </div>
            <div class="hidden md:block">
                <img src="/assets/images/hero-doctor.svg" alt="Médico" class="w-full" onerror="this.style.display='none'">
                <div class="bg-white rounded-2xl shadow-2xl p-8">
                    <div class="grid grid-cols-2 gap-6">
                        <div class="text-center">
                            <div class="text-3xl font-bold text-indigo-600">+500</div>
                            <div class="text-gray-600">Profesionales</div>
                        </div>
                        <div class="text-center">
                            <div class="text-3xl font-bold text-indigo-600">+10k</div>
                            <div class="text-gray-600">Pacientes</div>
                        </div>
                        <div class="text-center">
                            <div class="text-3xl font-bold text-indigo-600">24/7</div>
                            <div class="text-gray-600">Disponibilidad</div>
                        </div>
                        <div class="text-center">
                            <div class="text-3xl font-bold text-indigo-600">4.9★</div>
                            <div class="text-gray-600">Calificación</div>
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
                <h2 class="text-4xl font-bold text-gray-900 mb-4">Nuestros Servicios</h2>
                <p class="text-xl text-gray-600">Atención médica completa adaptada a tus necesidades</p>
            </div>

            <div class="grid md:grid-cols-3 gap-8">
                <!-- Servicio 1 -->
                <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl p-8 hover:shadow-xl transition">
                    <div class="bg-indigo-600 w-16 h-16 rounded-full flex items-center justify-center mb-6">
                        <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-4">Consulta Médica</h3>
                    <p class="text-gray-600 mb-4">Atención médica general virtual o presencial con profesionales certificados.</p>
                    <ul class="space-y-2 text-gray-600">
                        <li class="flex items-center">
                            <svg class="w-5 h-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                            Virtual o presencial
                        </li>
                        <li class="flex items-center">
                            <svg class="w-5 h-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                            Recetas digitales
                        </li>
                    </ul>
                </div>

                <!-- Servicio 2 -->
                <div class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-xl p-8 hover:shadow-xl transition">
                    <div class="bg-green-600 w-16 h-16 rounded-full flex items-center justify-center mb-6">
                        <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
                            <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm9.707 5.707a1 1 0 00-1.414-1.414L9 12.586l-1.293-1.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-4">Laboratorio</h3>
                    <p class="text-gray-600 mb-4">Toma de muestras a domicilio con resultados rápidos y confiables.</p>
                    <ul class="space-y-2 text-gray-600">
                        <li class="flex items-center">
                            <svg class="w-5 h-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                            A domicilio
                        </li>
                        <li class="flex items-center">
                            <svg class="w-5 h-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                            Resultados digitales
                        </li>
                    </ul>
                </div>

                <!-- Servicio 3 -->
                <div class="bg-gradient-to-br from-purple-50 to-pink-50 rounded-xl p-8 hover:shadow-xl transition">
                    <div class="bg-purple-600 w-16 h-16 rounded-full flex items-center justify-center mb-6">
                        <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-4">Enfermería</h3>
                    <p class="text-gray-600 mb-4">Cuidados de enfermería profesional en la comodidad de tu hogar.</p>
                    <ul class="space-y-2 text-gray-600">
                        <li class="flex items-center">
                            <svg class="w-5 h-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                            Curaciones
                        </li>
                        <li class="flex items-center">
                            <svg class="w-5 h-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                            Inyectología
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="bg-indigo-600 py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-4xl font-bold text-white mb-4">¿Listo para comenzar?</h2>
            <p class="text-xl text-indigo-100 mb-8">Regístrate ahora y recibe atención médica de calidad</p>
            <a href="/register" class="bg-white text-indigo-600 px-8 py-4 rounded-lg hover:bg-gray-100 transition font-semibold text-lg shadow-lg inline-block">
                Crear Cuenta Gratis
            </a>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid md:grid-cols-4 gap-8">
                <div>
                    <h3 class="text-lg font-semibold mb-4">Especialistas en Casa</h3>
                    <p class="text-gray-400">Servicios médicos especializados en la comodidad de tu hogar.</p>
                </div>
                <div>
                    <h3 class="text-lg font-semibold mb-4">Servicios</h3>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="#" class="hover:text-white transition">Médicos</a></li>
                        <li><a href="#" class="hover:text-white transition">Enfermería</a></li>
                        <li><a href="#" class="hover:text-white transition">Laboratorio</a></li>
                        <li><a href="#" class="hover:text-white transition">Veterinaria</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-lg font-semibold mb-4">Empresa</h3>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="#" class="hover:text-white transition">Nosotros</a></li>
                        <li><a href="#" class="hover:text-white transition">Trabaja con nosotros</a></li>
                        <li><a href="#" class="hover:text-white transition">Términos</a></li>
                        <li><a href="#" class="hover:text-white transition">Privacidad</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="text-lg font-semibold mb-4">Contacto</h3>
                    <ul class="space-y-2 text-gray-400">
                        <li>info@especialistasencasa.com</li>
                        <li>+57 300 123 4567</li>
                        <li>Bogotá, Colombia</li>
                    </ul>
                </div>
            </div>
            <div class="border-t border-gray-800 mt-8 pt-8 text-center text-gray-400">
                <p>&copy; 2025 Especialistas en Casa. Todos los derechos reservados.</p>
            </div>
        </div>
    </footer>

</body>
</html>
