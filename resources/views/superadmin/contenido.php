<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Contenido - Especialistas en Casa</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-50" x-data="contenidoApp()" x-init="init()">
    
    <!-- Navbar -->
    <nav class="bg-white shadow-lg sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center space-x-4">
                    <svg class="h-8 w-8 text-indigo-600" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"/>
                    </svg>
                    <span class="text-xl font-bold text-gray-900">Gestión de Contenido</span>
                </div>
                
                <div class="flex items-center space-x-4">
                    <a href="/superadmin/dashboard" class="text-gray-600 hover:text-gray-900">← Dashboard</a>
                    <button @click="logout()" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg text-sm font-medium">Salir</button>
                </div>
            </div>
        </div>
    </nav>

    <!-- Navigation Menu -->
    <div class="bg-white border-b border-gray-200 sticky top-16 z-40">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex space-x-8 overflow-x-auto py-4">
                <a href="/superadmin/dashboard" class="flex items-center space-x-2 px-3 py-2 rounded-lg hover:bg-gray-100 text-gray-700 font-medium whitespace-nowrap">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/></svg>
                    <span>Dashboard</span>
                </a>
                <a href="/superadmin/usuarios" class="flex items-center space-x-2 px-3 py-2 rounded-lg hover:bg-gray-100 text-gray-700 font-medium whitespace-nowrap">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/></svg>
                    <span>Usuarios</span>
                </a>
                <a href="/superadmin/finanzas" class="flex items-center space-x-2 px-3 py-2 rounded-lg hover:bg-gray-100 text-gray-700 font-medium whitespace-nowrap">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z"/></svg>
                    <span>Finanzas</span>
                </a>
                <a href="/superadmin/contenido" class="flex items-center space-x-2 px-3 py-2 rounded-lg bg-indigo-50 text-indigo-700 font-medium whitespace-nowrap">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd"/></svg>
                    <span>Contenido</span>
                </a>
                <a href="/superadmin/configuracion" class="flex items-center space-x-2 px-3 py-2 rounded-lg hover:bg-gray-100 text-gray-700 font-medium whitespace-nowrap">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd"/></svg>
                    <span>Configuración</span>
                </a>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        <!-- Tabs -->
        <div class="mb-6 border-b border-gray-200">
            <nav class="-mb-px flex space-x-8">
                <button @click="tab = 'servicios'" :class="tab === 'servicios' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    Servicios
                </button>
                <button @click="tab = 'banners'" :class="tab === 'banners' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    Banners
                </button>
                <button @click="tab = 'faqs'" :class="tab === 'faqs' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    FAQs
                </button>
                <button @click="tab = 'contenido'" :class="tab === 'contenido' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                    Páginas Estáticas
                </button>
            </nav>
        </div>

        <!-- Mensajes -->
        <div x-show="message" x-transition class="mb-6 p-4 rounded-lg" :class="messageType === 'success' ? 'bg-green-50 text-green-800' : 'bg-red-50 text-red-800'">
            <p x-text="message"></p>
        </div>

        <!-- Tab: Servicios -->
        <div x-show="tab === 'servicios'" x-transition>
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold">Gestión de Servicios</h2>
                <button @click="nuevoServicio()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg font-medium">
                    + Nuevo Servicio
                </button>
            </div>

            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Servicio</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Precio</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Duración</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Estado</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <template x-for="servicio in servicios" :key="servicio.id">
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <div>
                                        <div class="font-medium text-gray-900" x-text="servicio.nombre"></div>
                                        <div class="text-sm text-gray-500" x-text="servicio.descripcion.substring(0, 60) + '...'"></div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-sm">$<span x-text="formatNumber(servicio.precio_min)"></span> - $<span x-text="formatNumber(servicio.precio_max)"></span></td>
                                <td class="px-6 py-4 text-sm" x-text="servicio.duracion_minutos + ' min'"></td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full" :class="servicio.activo ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'" x-text="servicio.activo ? 'Activo' : 'Inactivo'"></span>
                                </td>
                                <td class="px-6 py-4 text-right text-sm space-x-2">
                                    <button @click="editarServicio(servicio)" class="text-indigo-600 hover:text-indigo-900">Editar</button>
                                    <button @click="eliminarServicio(servicio.id)" class="text-red-600 hover:text-red-900">Eliminar</button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Tab: Banners -->
        <div x-show="tab === 'banners'" x-transition>
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold">Gestión de Banners</h2>
                <button @click="nuevoBanner()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg font-medium">
                    + Nuevo Banner
                </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <template x-for="banner in banners" :key="banner.id">
                    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                        <img :src="banner.imagen_url" class="w-full h-48 object-cover">
                        <div class="p-6">
                            <h3 class="font-bold text-lg" x-text="banner.titulo"></h3>
                            <p class="text-gray-600 text-sm mt-1" x-text="banner.subtitulo"></p>
                            <div class="mt-4 flex justify-between items-center">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full" :class="banner.activo ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'" x-text="banner.activo ? 'Activo' : 'Inactivo'"></span>
                                <div class="space-x-2">
                                    <button @click="editarBanner(banner)" class="text-indigo-600 hover:text-indigo-900 text-sm">Editar</button>
                                    <button @click="eliminarBanner(banner.id)" class="text-red-600 hover:text-red-900 text-sm">Eliminar</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- Tab: FAQs -->
        <div x-show="tab === 'faqs'" x-transition>
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold">Preguntas Frecuentes</h2>
                <button @click="nuevaFAQ()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg font-medium">
                    + Nueva FAQ
                </button>
            </div>

            <div class="bg-white rounded-xl shadow-lg divide-y">
                <template x-for="faq in faqs" :key="faq.id">
                    <div class="p-6">
                        <div class="flex justify-between items-start">
                            <div class="flex-1">
                                <div class="flex items-center space-x-2 mb-2">
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full bg-indigo-100 text-indigo-800" x-text="faq.categoria"></span>
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full" :class="faq.activo ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'" x-text="faq.activo ? 'Activa' : 'Inactiva'"></span>
                                </div>
                                <h3 class="font-bold text-lg" x-text="faq.pregunta"></h3>
                                <p class="text-gray-600 mt-2" x-text="faq.respuesta"></p>
                            </div>
                            <div class="ml-4 flex space-x-2">
                                <button @click="editarFAQ(faq)" class="text-indigo-600 hover:text-indigo-900 text-sm">Editar</button>
                                <button @click="eliminarFAQ(faq.id)" class="text-red-600 hover:text-red-900 text-sm">Eliminar</button>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        <!-- Tab: Contenido Estático -->
        <div x-show="tab === 'contenido'" x-transition>
            <h2 class="text-2xl font-bold mb-6">Páginas Estáticas</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <button @click="editarContenido('terminos')" class="bg-white rounded-xl shadow-lg p-6 text-left hover:shadow-xl transition-shadow">
                    <h3 class="font-bold text-lg">Términos y Condiciones</h3>
                    <p class="text-gray-600 text-sm mt-2">Editar términos legales del servicio</p>
                </button>
                <button @click="editarContenido('privacidad')" class="bg-white rounded-xl shadow-lg p-6 text-left hover:shadow-xl transition-shadow">
                    <h3 class="font-bold text-lg">Política de Privacidad</h3>
                    <p class="text-gray-600 text-sm mt-2">Editar política de privacidad</p>
                </button>
                <button @click="editarContenido('sobre_nosotros')" class="bg-white rounded-xl shadow-lg p-6 text-left hover:shadow-xl transition-shadow">
                    <h3 class="font-bold text-lg">Sobre Nosotros</h3>
                    <p class="text-gray-600 text-sm mt-2">Editar información de la empresa</p>
                </button>
            </div>
        </div>

    </div>

    <!-- Modal Servicio -->
    <div x-show="modalServicio" x-transition class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div @click="modalServicio = false" class="fixed inset-0 bg-gray-500 bg-opacity-75"></div>
            
            <div class="relative bg-white rounded-lg max-w-2xl w-full p-6">
                <h3 class="text-2xl font-bold mb-4" x-text="servicioForm.id ? 'Editar Servicio' : 'Nuevo Servicio'"></h3>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Nombre</label>
                        <input type="text" x-model="servicioForm.nombre" class="w-full px-3 py-2 border rounded-lg">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Descripción</label>
                        <textarea x-model="servicioForm.descripcion" rows="3" class="w-full px-3 py-2 border rounded-lg"></textarea>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Precio Mínimo</label>
                            <input type="number" x-model="servicioForm.precio_min" class="w-full px-3 py-2 border rounded-lg">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Precio Máximo</label>
                            <input type="number" x-model="servicioForm.precio_max" class="w-full px-3 py-2 border rounded-lg">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Duración (minutos)</label>
                            <input type="number" x-model="servicioForm.duracion_minutos" class="w-full px-3 py-2 border rounded-lg">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Categoría</label>
                            <input type="text" x-model="servicioForm.categoria" class="w-full px-3 py-2 border rounded-lg">
                        </div>
                    </div>
                    <div class="flex items-center space-x-4">
                        <label class="flex items-center">
                            <input type="checkbox" x-model="servicioForm.activo" class="mr-2">
                            <span class="text-sm">Activo</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" x-model="servicioForm.destacado" class="mr-2">
                            <span class="text-sm">Destacado</span>
                        </label>
                    </div>
                </div>

                <div class="mt-6 flex justify-end space-x-3">
                    <button @click="modalServicio = false" class="px-4 py-2 border rounded-lg hover:bg-gray-50">Cancelar</button>
                    <button @click="guardarServicio()" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">Guardar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Banner -->
    <div x-show="modalBanner" x-transition class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div @click="modalBanner = false" class="fixed inset-0 bg-gray-500 bg-opacity-75"></div>
            
            <div class="relative bg-white rounded-lg max-w-2xl w-full p-6">
                <h3 class="text-2xl font-bold mb-4" x-text="bannerForm.id ? 'Editar Banner' : 'Nuevo Banner'"></h3>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Título</label>
                        <input type="text" x-model="bannerForm.titulo" class="w-full px-3 py-2 border rounded-lg">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Subtítulo</label>
                        <input type="text" x-model="bannerForm.subtitulo" class="w-full px-3 py-2 border rounded-lg">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">URL de Imagen</label>
                        <input type="url" x-model="bannerForm.imagen_url" class="w-full px-3 py-2 border rounded-lg">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Enlace (opcional)</label>
                        <input type="url" x-model="bannerForm.enlace" class="w-full px-3 py-2 border rounded-lg">
                    </div>
                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" x-model="bannerForm.activo" class="mr-2">
                            <span class="text-sm">Activo</span>
                        </label>
                    </div>
                </div>

                <div class="mt-6 flex justify-end space-x-3">
                    <button @click="modalBanner = false" class="px-4 py-2 border rounded-lg hover:bg-gray-50">Cancelar</button>
                    <button @click="guardarBanner()" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">Guardar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal FAQ -->
    <div x-show="modalFAQ" x-transition class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div @click="modalFAQ = false" class="fixed inset-0 bg-gray-500 bg-opacity-75"></div>
            
            <div class="relative bg-white rounded-lg max-w-2xl w-full p-6">
                <h3 class="text-2xl font-bold mb-4" x-text="faqForm.id ? 'Editar FAQ' : 'Nueva FAQ'"></h3>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Pregunta</label>
                        <input type="text" x-model="faqForm.pregunta" class="w-full px-3 py-2 border rounded-lg">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Respuesta</label>
                        <textarea x-model="faqForm.respuesta" rows="4" class="w-full px-3 py-2 border rounded-lg"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Categoría</label>
                        <input type="text" x-model="faqForm.categoria" class="w-full px-3 py-2 border rounded-lg">
                    </div>
                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" x-model="faqForm.activo" class="mr-2">
                            <span class="text-sm">Activa</span>
                        </label>
                    </div>
                </div>

                <div class="mt-6 flex justify-end space-x-3">
                    <button @click="modalFAQ = false" class="px-4 py-2 border rounded-lg hover:bg-gray-50">Cancelar</button>
                    <button @click="guardarFAQ()" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">Guardar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Contenido -->
    <div x-show="modalContenido" x-transition class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div @click="modalContenido = false" class="fixed inset-0 bg-gray-500 bg-opacity-75"></div>
            
            <div class="relative bg-white rounded-lg max-w-4xl w-full p-6">
                <h3 class="text-2xl font-bold mb-4" x-text="contenidoForm.titulo"></h3>
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Título de Página</label>
                        <input type="text" x-model="contenidoForm.titulo" class="w-full px-3 py-2 border rounded-lg">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Contenido HTML</label>
                        <textarea x-model="contenidoForm.contenido" rows="15" class="w-full px-3 py-2 border rounded-lg font-mono text-sm"></textarea>
                        <p class="text-xs text-gray-500 mt-1">Puedes usar HTML básico: &lt;h1&gt;, &lt;h2&gt;, &lt;p&gt;, &lt;ul&gt;, &lt;li&gt;, etc.</p>
                    </div>
                </div>

                <div class="mt-6 flex justify-end space-x-3">
                    <button @click="modalContenido = false" class="px-4 py-2 border rounded-lg hover:bg-gray-50">Cancelar</button>
                    <button @click="guardarContenido()" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">Guardar</button>
                </div>
            </div>
        </div>
    </div>

<script>
window.contenidoApp = function() {
    return {
        tab: 'servicios',
        message: '',
        messageType: 'success',
        
        servicios: [],
        banners: [],
        faqs: [],
        
        modalServicio: false,
        modalBanner: false,
        modalFAQ: false,
        modalContenido: false,
        
        servicioForm: {},
        bannerForm: {},
        faqForm: {},
        contenidoForm: {},

        async init() {
            const token = localStorage.getItem('token');
            if (!token) {
                window.location.href = '/login';
                return;
            }
            await this.cargarServicios();
            await this.cargarBanners();
            await this.cargarFAQs();
        },

        async cargarServicios() {
            try {
                const token = localStorage.getItem('token');
                const response = await fetch('/api/contenido/servicios', {
                    headers: { 'Authorization': `Bearer ${token}` }
                });
                if (response.ok) {
                    const data = await response.json();
                    this.servicios = data.servicios || [];
                }
            } catch (error) {
                console.error('Error:', error);
            }
        },

        nuevoServicio() {
            this.servicioForm = { activo: 1, destacado: 0, duracion_minutos: 60, categoria: 'general' };
            this.modalServicio = true;
        },

        editarServicio(servicio) {
            this.servicioForm = { ...servicio, activo: !!servicio.activo, destacado: !!servicio.destacado };
            this.modalServicio = true;
        },

        async guardarServicio() {
            try {
                const token = localStorage.getItem('token');
                const response = await fetch('/api/contenido/servicio', {
                    method: 'POST',
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(this.servicioForm)
                });

                if (response.ok) {
                    this.showMessage('Servicio guardado correctamente', 'success');
                    this.modalServicio = false;
                    await this.cargarServicios();
                } else {
                    this.showMessage('Error al guardar servicio', 'error');
                }
            } catch (error) {
                this.showMessage('Error al guardar servicio', 'error');
            }
        },

        async eliminarServicio(id) {
            if (!confirm('¿Eliminar este servicio?')) return;
            
            try {
                const token = localStorage.getItem('token');
                const response = await fetch('/api/contenido/servicio', {
                    method: 'DELETE',
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ id })
                });

                if (response.ok) {
                    this.showMessage('Servicio eliminado', 'success');
                    await this.cargarServicios();
                }
            } catch (error) {
                this.showMessage('Error al eliminar servicio', 'error');
            }
        },

        async cargarBanners() {
            try {
                const token = localStorage.getItem('token');
                const response = await fetch('/api/contenido/banners', {
                    headers: { 'Authorization': `Bearer ${token}` }
                });
                if (response.ok) {
                    const data = await response.json();
                    this.banners = data.banners || [];
                }
            } catch (error) {
                console.error('Error:', error);
            }
        },

        nuevoBanner() {
            this.bannerForm = { activo: 1, orden: 0 };
            this.modalBanner = true;
        },

        editarBanner(banner) {
            this.bannerForm = { ...banner, activo: !!banner.activo };
            this.modalBanner = true;
        },

        async guardarBanner() {
            try {
                const token = localStorage.getItem('token');
                const response = await fetch('/api/contenido/banner', {
                    method: 'POST',
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(this.bannerForm)
                });

                if (response.ok) {
                    this.showMessage('Banner guardado correctamente', 'success');
                    this.modalBanner = false;
                    await this.cargarBanners();
                }
            } catch (error) {
                this.showMessage('Error al guardar banner', 'error');
            }
        },

        async eliminarBanner(id) {
            if (!confirm('¿Eliminar este banner?')) return;
            
            try {
                const token = localStorage.getItem('token');
                const response = await fetch('/api/contenido/banner', {
                    method: 'DELETE',
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ id })
                });

                if (response.ok) {
                    this.showMessage('Banner eliminado', 'success');
                    await this.cargarBanners();
                }
            } catch (error) {
                this.showMessage('Error al eliminar banner', 'error');
            }
        },

        async cargarFAQs() {
            try {
                const token = localStorage.getItem('token');
                const response = await fetch('/api/contenido/faqs', {
                    headers: { 'Authorization': `Bearer ${token}` }
                });
                if (response.ok) {
                    const data = await response.json();
                    this.faqs = data.faqs || [];
                }
            } catch (error) {
                console.error('Error:', error);
            }
        },

        nuevaFAQ() {
            this.faqForm = { activo: 1, categoria: 'general', orden: 0 };
            this.modalFAQ = true;
        },

        editarFAQ(faq) {
            this.faqForm = { ...faq, activo: !!faq.activo };
            this.modalFAQ = true;
        },

        async guardarFAQ() {
            try {
                const token = localStorage.getItem('token');
                const response = await fetch('/api/contenido/faq', {
                    method: 'POST',
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(this.faqForm)
                });

                if (response.ok) {
                    this.showMessage('FAQ guardada correctamente', 'success');
                    this.modalFAQ = false;
                    await this.cargarFAQs();
                }
            } catch (error) {
                this.showMessage('Error al guardar FAQ', 'error');
            }
        },

        async eliminarFAQ(id) {
            if (!confirm('¿Eliminar esta FAQ?')) return;
            
            try {
                const token = localStorage.getItem('token');
                const response = await fetch('/api/contenido/faq', {
                    method: 'DELETE',
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ id })
                });

                if (response.ok) {
                    this.showMessage('FAQ eliminada', 'success');
                    await this.cargarFAQs();
                }
            } catch (error) {
                this.showMessage('Error al eliminar FAQ', 'error');
            }
        },

        async editarContenido(tipo) {
            try {
                const token = localStorage.getItem('token');
                const response = await fetch(`/api/contenido/contenido?tipo=${tipo}`, {
                    headers: { 'Authorization': `Bearer ${token}` }
                });
                
                if (response.ok) {
                    const data = await response.json();
                    this.contenidoForm = data.contenido || { tipo, titulo: '', contenido: '' };
                    this.modalContenido = true;
                }
            } catch (error) {
                this.showMessage('Error al cargar contenido', 'error');
            }
        },

        async guardarContenido() {
            try {
                const token = localStorage.getItem('token');
                const response = await fetch('/api/contenido/contenido', {
                    method: 'POST',
                    headers: {
                        'Authorization': `Bearer ${token}`,
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(this.contenidoForm)
                });

                if (response.ok) {
                    this.showMessage('Contenido guardado correctamente', 'success');
                    this.modalContenido = false;
                }
            } catch (error) {
                this.showMessage('Error al guardar contenido', 'error');
            }
        },

        formatNumber(num) {
            return new Intl.NumberFormat('es-CO').format(num || 0);
        },

        showMessage(msg, type = 'success') {
            this.message = msg;
            this.messageType = type;
            setTimeout(() => { this.message = ''; }, 5000);
        },

        logout() {
            localStorage.removeItem('token');
            localStorage.removeItem('usuario');
            window.location.href = '/login';
        }
    }
}
</script>

</body>
</html>
