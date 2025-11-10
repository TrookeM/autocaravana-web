<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Campers - Tu Aventura Comienza Aquí')</title>

    <!-- ✅ 1. CARGA VITE (CON app.js) PRIMERO, EN EL HEAD -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    
    <!-- ✅ 2. CARGA LOS ESTILOS DE LIVEWIRE -->
    @livewireStyles
</head>

<body class="font-sans antialiased bg-white text-gray-800">

    <nav id="navbar" class="fixed top-0 left-0 right-0 z-50 transition-all duration-300 h-[5rem]">
        <div class="max-w-7xl mx-auto px-6 h-full">
            <div class="flex justify-between items-center h-full">
                {{-- Logo --}}
                <a href="/" id="nav-logo">
                    <img src="{{ asset('images/logo.png') }}"
                         alt="Logo de Campers"
                         class="h-18 w-auto">
                </a>

                {{-- Navegación para Escritorio --}}
                <div class="hidden md:flex items-center nav-group gap-2">
                    <a href="/#ventajas" class="nav-button">Por qué elegirnos</a>
                    <a href="/#flota" class="nav-button">Nuestra flota</a>
                    <a href="{{ route('contact') }}" class="nav-button">Contacto</a>
                </div>

                {{-- Botón de Contacto para Escritorio --}}
                <div class="hidden md:block">
                    <a href="{{ route('contact') }}" class="inline-block px-5 py-2 bg-emerald-600 text-white font-semibold text-sm rounded-full shadow-md hover:bg-emerald-700 transition transform hover:scale-105 duration-300">
                        Reservar Ahora
                    </a>
                </div>

                {{-- Botón del Menú Móvil --}}
                <div class="md:hidden">
                    <button id="mobile-menu-button" class="focus:outline-none" aria-controls="mobile-menu" aria-expanded="false" aria-label="Abrir menú">
                        <span class="bar"></span>
                        <span class="bar"></span>
                        <span class="bar"></span>
                    </button>
                </div>
            </div>
        </div>

        {{-- Menú Móvil --}}
        <div id="mobile-menu" class="hidden md:hidden bg-white shadow-xl absolute top-full left-0 w-full">
            <a href="/#ventajas" class="block py-3 px-6 text-gray-700 hover:bg-gray-50">Por qué elegirnos</a>
            <a href="/#flota" class="block py-3 px-6 text-gray-700 hover:bg-gray-50">Nuestra flota</a>
            <a href="{{ route('contact') }}" class="block py-3 px-6 text-gray-700 hover:bg-gray-50 border-b">Contacto</a>
            <div class="p-4"><a href="{{ route('contact') }}" class="w-full text-center inline-block px-5 py-3 bg-emerald-600 text-white font-semibold rounded-lg shadow-md hover:bg-emerald-700 transition duration-300">Reservar Ahora</a></div>
        </div>
    </nav>

    <main>
        @yield('content')
    </main>

    <footer class="bg-emerald-900 text-emerald-50 py-12">
        <div class="max-w-7xl mx-auto px-6 grid md:grid-cols-4 gap-10">

            {{-- Columna 1: Sobre Nosotros --}}
            <div>
                <h3 class="text-xl font-bold mb-4 border-b border-emerald-700 pb-2">Campers</h3>
                <p class="text-sm text-emerald-100">
                    En Campers ofrecemos experiencias únicas sobre ruedas. Calidad, libertad y aventura garantizadas en cada viaje.
                </p>
            </div>

            {{-- Columna 2: Enlaces Rápidos --}}
            <div>
                <h3 class="text-xl font-bold mb-4 border-b border-emerald-700 pb-2">Enlaces</h3>
                <ul class="space-y-3 text-sm">
                    <li><a href="/" class="hover:text-white transition duration-200">Inicio</a></li>
                    <li><a href="/#ventajas" class="hover:text-white transition duration-200">Por qué elegirnos</a></li>
                    <li><a href="/#flota" class="hover:text-white transition duration-200">Nuestra flota</a></li>
                    <li><a href="{{ route('contact') }}" class="hover:text-white transition duration-200">Contacto y Reserva</a></li>
                </ul>
            </div>

            {{-- Columna 3: Contacto --}}
            <div>
                <h3 class="text-xl font-bold mb-4 border-b border-emerald-700 pb-2">Contacto</h3>
                <ul class="text-sm text-emerald-100 space-y-3">
                    <li class="flex items-center"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.772-1.549a1 1 0 011.06-.54l4.435.74A1 1 0 0118 16.847V17a1 1 0 01-1 1h-1.153a1 1 0 01-.986-.836l-.74-4.435a1 1 0 01.54-1.06l1.548-.773a11.037 11.037 0 00-6.105-6.105l-.772 1.549a1 1 0 01-1.06.54l-4.435-.74A1 1 0 013.153 3H2z" />
                        </svg> +34 600 123 456</li>
                    <li class="flex items-center"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z" />
                            <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z" />
                        </svg> info@campers.es</li>
                    <li class="flex items-center"><svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-2" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 1110.63 8.35c-.244.372-.61.737-.997 1.026C13.627 14.62 10 17 10 17s-3.627-2.38-4.686-3.57C4.66 12.137 4.295 11.772 4.05 11.4A7 7 0 015.05 4.05zm5.45 6.45a2 2 0 10-4 0 2 2 0 004 0z" clip-rule="evenodd" />
                        </svg> Madrid, España</li>
                </ul>
            </div>

            {{-- Columna 4: Síguenos --}}
            <div>
                <h3 class="text-xl font-bold mb-4 border-b border-emerald-700 pb-2">Síguenos</h3>
                <div class="flex space-x-6 text-2xl">
                    <a href="#" class="hover:text-white transition duration-200" aria-label="Facebook"><svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.789c0-2.527 1.547-3.918 3.793-3.918 1.087 0 2.037.193 2.308.28v2.66h-1.55c-1.22 0-1.458.583-1.458 1.432V12h3.046l-.497 3.033h-2.549V21.878C18.343 21.128 22 16.991 22 12z" />
                        </svg></a>
                    <a href="#" class="hover:text-white transition duration-200" aria-label="Instagram"><svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12 2C6.477 2 2 6.477 2 12s4.477 10 10 10 10-4.477 10-10S17.523 2 12 2zm4 4c1.105 0 2 .895 2 2s-.895 2-2 2-2-.895-2-2 .895-2 2-2zm-4 4c2.761 0 5 2.239 5 5s-2.239 5-5 5-5-2.239-5-5 2.239-5 5-5zm0 2a3 3 0 100 6 3 3 0 000-6z" />
                        </svg></a>
                    <a href="#" class="hover:text-white transition duration-200" aria-label="TikTok"><svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M12.44 2.008C12.016 2.016 11.583 2.006 11.162 2.05c-.046-.01-.093-.018-.14-.025-.008-.002-.015-.005-.023-.007C10.74 1.99 10.59 2 10.435 2H3.5C2.122 2 1 3.122 1 4.5v15C1 20.878 2.122 22 3.5 22h17c1.378 0 2.5-1.122 2.5-2.5v-13C23 4.122 21.878 3 20.5 3H16.14c-.16 0-.315-.01-.465.01c-.008.002-.015.005-.023.007-.047.007-.094.015-.14.025C15.82 2.006 15.387 2.016 14.962 2.008l-.022.001c-.13-.008-.26-.008-.39-.008H12.44zm-.006 1.5c.348-.008.694.002 1.04.025.2.014.402.04.603.078.08.016.16.033.24.053.07.018.14.038.21.06l.004.002c.07.022.14.045.21.07.07.025.14.053.21.083.07.03.14.06.21.092.07.032.14.066.21.103.07.037.14.077.21.12c.07.043.14.09.2.138.06.048.12.1.18.155.06.055.12.113.18.173.06.06.12.123.18.188.06.065.12.13.18.2.06.07.12.14.17.21.05.07.1.145.15.22.05.075.1.15.15.23.05.08.1.16.15.24.05.08.1.165.15.25.05.085.1.17.14.26.04.09.08.18.12.27.04.095.08.19.12.29.04.1.08.2.11.3.03.1.06.2.09.31.03.1.06.21.08.32.02.1.04.2.06.31.02.1.03.21.04.32.01.1.02.2.02.31V9.5h-2.s5V8c0-.28-.22-.5-.5-.5H13c-.28 0-.5.22-.5.5v2.5H10c-.28 0-.5.22-.5.5v2c0 .28.22.5.5.5h2.5V17c0 .28.22.5.5.5h2.5c.28 0 .5-.22.5-.5v-2.5h2.5c.28 0 .5-.22.5-.5v-2c0-.28-.22-.5-.5-.5h-2.5V8c0-1.87-1.427-3.418-3.235-3.492z" />
                        </svg></a>
                </div>
            </div>
        </div>

        <div class="text-center text-emerald-200 text-sm mt-10 border-t border-emerald-800 pt-6">
            © {{ date('Y') }} Campers. Todos los derechos reservados.
        </div>
    </footer>

    <!-- ✅ 3. CARGA LOS SCRIPTS DE LIVEWIRE AL FINAL DEL BODY, DESPUÉS DE VITE -->
    @livewireScripts
</body>
</html>