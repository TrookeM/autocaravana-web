<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Campers - Tu Aventura Comienza Aquí</title>
    {{-- Asegúrate de que los assets de Tailwind y JS estén compilados con Vite --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased bg-white text-gray-800 pt-20"> {{-- pt-20 para compensar el navbar fijo --}}

    {{-- NAVBAR --}}
    <nav class="fixed top-0 left-0 w-full bg-white/80 backdrop-blur-md shadow-lg z-50 transition duration-300">
        <div class="max-w-7xl mx-auto flex items-center justify-between px-6 py-4">
            <a href="#inicio" class="flex items-center gap-2">
                <img src="{{ asset('images/logo.png') }}" alt="Logo" class="w-8 h-8"> <span class="text-xl font-extrabold text-emerald-700">Campers</span>
            </a>

            <ul class="hidden md:flex items-center gap-8 text-gray-700 font-medium">
                <li><a href="#inicio" class="hover:text-emerald-700 transition duration-200">Inicio</a></li>
                <li><a href="#flota" class="hover:text-emerald-700 transition duration-200">Flota</a></li>
                <li><a href="#ventajas" class="hover:text-emerald-700 transition duration-200">Ventajas</a></li>
                <li><a href="{{ route('contact') }}" class="hover:text-emerald-700 transition duration-200">Contacto</a></li>
            </ul>

            {{-- Aquí también usamos la ruta con nombre 'contact' --}}
            <a href="{{ route('contact') }}" class="hidden md:inline-block px-5 py-2 bg-emerald-600 text-white rounded-full font-semibold hover:bg-emerald-700 transition duration-200 shadow-md">
                Reservar
            </a>

            <button class="md:hidden text-gray-700 hover:text-emerald-700 transition">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7" />
                </svg>
            </button>
        </div>
    </nav>


    <!-- HERO -->
    <header id="inicio" class="hero relative flex items-center justify-center h-[90vh] text-center text-white overflow-hidden -mt-20">
        <img src="https://images.unsplash.com/photo-1527786356703-4b100091cd2c?auto=format&fit=crop&w=1920&q=80"
            alt="Autocaravana en paisaje natural"
            class="absolute inset-0 w-full h-full object-cover opacity-70">

        <div class="absolute inset-0 bg-gradient-to-t from-emerald-900/80 via-emerald-800/40 to-transparent"></div>

        <div class="relative z-10 max-w-3xl px-6 animate-fadeInUp">
            <h1 class="text-5xl md:text-6xl font-extrabold drop-shadow-2xl">
                Tu Aventura Comienza Aquí
            </h1>

            <p class="text-lg md:text-xl mt-4 text-emerald-50 drop-shadow-md">
                Explora, desconecta y vive la libertad sobre ruedas.
            </p>

            <div class="mt-8">
                <a href="#flota"
                    class="inline-block px-8 py-3 bg-emerald-600 text-white font-semibold rounded-full shadow-xl hover:bg-emerald-700 transition transform hover:scale-105 duration-300">
                    Descubre la Flota
                </a>
            </div>
        </div>
    </header>


    <!-- ¿POR QUÉ ELEGIRNOS? / VENTAJAS -->
    <section id="ventajas" class="py-20 bg-gray-50">
        <div class="max-w-6xl mx-auto px-6 text-center">
            <h2 class="text-4xl font-extrabold text-gray-900 mb-4">¿Por qué elegirnos?</h2>
            <p class="text-xl text-gray-600 mb-12">Tu compañero ideal para la carretera.</p>

            <div class="grid md:grid-cols-3 gap-8">
                {{-- Tarjeta 1: Flexibilidad --}}
                <div class="p-8 bg-white rounded-2xl shadow-xl border border-gray-100 hover:shadow-2xl transition transform hover:-translate-y-2 duration-300">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-12 h-12 text-emerald-600 mx-auto mb-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-.75a4.5 4.5 0 014.5-4.5h3.375M21 16.5v.75m-4.5-4.5h-1.5m-1.5 0h-1.5" />
                    </svg>
                    <h3 class="mt-4 text-xl font-bold text-gray-900">Flexibilidad Total</h3>
                    <p class="mt-3 text-gray-600">Elige tus fechas, tu ruta y tu ritmo. Libertad sin límites.</p>
                </div>

                {{-- Tarjeta 2: Vehículos Revisados --}}
                <div class="p-8 bg-white rounded-2xl shadow-xl border border-gray-100 hover:shadow-2xl transition transform hover:-translate-y-2 duration-300">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-12 h-12 text-emerald-600 mx-auto mb-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                    </svg>
                    <h3 class="mt-4 text-xl font-bold text-gray-900">Vehículos Revisados</h3>
                    <p class="mt-3 text-gray-600">Cada camper es revisada y equipada con todo lo necesario.</p>
                </div>

                {{-- Tarjeta 3: Soporte en Ruta --}}
                <div class="p-8 bg-white rounded-2xl shadow-xl border border-gray-100 hover:shadow-2xl transition transform hover:-translate-y-2 duration-300">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-12 h-12 text-emerald-600 mx-auto mb-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                    </svg>
                    <h3 class="mt-4 text-xl font-bold text-gray-900">Asistencia 24/7</h3>
                    <p class="mt-3 text-gray-600">Te acompañamos en todo momento para que viajes sin preocupaciones.</p>
                </div>
            </div>
        </div>
    </section>


    <!-- NUESTRA FLOTA -->
    <section id="flota" class="py-20 bg-white">
        <div class="container mx-auto px-6 text-center">
            <h2 class="text-4xl font-extrabold text-gray-900 mb-4">Nuestra Flota</h2>
            <p class="text-xl text-gray-600 mt-2 mb-12">Autocaravanas equipadas para tu próxima aventura.</p>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-10 mt-12">
                @forelse ($campervans as $campervan)
                <a href="{{ route('campervan.show', $campervan) }}"
                    class="block bg-white rounded-2xl shadow-xl overflow-hidden hover:shadow-2xl transition transform hover:-translate-y-2 duration-300">

                    <img src="{{ $campervan->main_image_path ? asset('storage/' . $campervan->main_image_path) : 'https://placehold.co/600x400/E9D5FF/7C3AED?text=Autocaravana' }}"
                        alt="{{ $campervan->name }}" class="w-full h-56 object-cover">

                    <div class="p-6 text-left">
                        <h3 class="text-2xl font-bold text-gray-800">{{ $campervan->name }}</h3>
                        <p class="text-gray-600 mt-2">
                            {!! \Illuminate\Support\Str::limit($campervan->description ?? 'Esta autocaravana de lujo está equipada con todas las comodidades. Perfecta para una familia de 4, incluye cocina completa, baño con ducha y una cama king-size. ¡Aventuras inolvidables garantizadas!', 100) !!}
                        </p>

                        <div class="mt-4 pt-4 border-t border-gray-100 flex justify-between items-center">
                            <p class="text-gray-500">Desde:</p>
                            <span class="text-3xl font-extrabold text-emerald-600">
                                {{ number_format($campervan->price_per_night, 2) }}€
                                <span class="text-base font-medium text-gray-500">/ noche</span>
                            </span>
                        </div>
                    </div>
                </a>
                @empty
                <p class="text-gray-500 col-span-full">No hay autocaravanas disponibles en este momento.</p>
                @endforelse
            </div>
        </div>
    </section>


    <!-- CTA FINAL / CONTACTO -->
    <section id="contacto" class="bg-emerald-800 text-white py-20 text-center">
        <h2 class="text-4xl font-extrabold mb-4">¿Listo para tu próxima aventura?</h2>
        <p class="text-xl mb-10 text-emerald-100">Reserva hoy mismo tu camper ideal y empieza tu viaje por carretera con la mejor garantía.</p>

        {{-- ENLACE CORREGIDO: Ahora apunta a la ruta con nombre 'contact' --}}
        <a href="{{ route('contact') }}" class="inline-block px-10 py-4 bg-white text-emerald-700 text-lg font-bold rounded-full shadow-2xl hover:bg-gray-100 transition transform hover:scale-105 duration-300">
            ¡Contactar Ahora y Reservar!
        </a>
    </section>


    <!-- FOOTER -->
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
                    <li><a href="#inicio" class="hover:text-white transition duration-200">Inicio</a></li>
                    <li><a href="#ventajas" class="hover:text-white transition duration-200">Por qué elegirnos</a></li>
                    <li><a href="#flota" class="hover:text-white transition duration-200">Nuestra flota</a></li>
                    <li><a href="#contacto" class="hover:text-white transition duration-200">Contacto y Reserva</a></li>
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
                            <path d="M12.44 2.008C12.016 2.016 11.583 2.006 11.162 2.05c-.046-.01-.093-.018-.14-.025-.008-.002-.015-.005-.023-.007C10.74 1.99 10.59 2 10.435 2H3.5C2.122 2 1 3.122 1 4.5v15C1 20.878 2.122 22 3.5 22h17c1.378 0 2.5-1.122 2.5-2.5v-13C23 4.122 21.878 3 20.5 3H16.14c-.16 0-.315-.01-.465.01c-.008.002-.015.005-.023.007-.047.007-.094.015-.14.025C15.82 2.006 15.387 2.016 14.962 2.008l-.022.001c-.13-.008-.26-.008-.39-.008H12.44zm-.006 1.5c.348-.008.694.002 1.04.025.2.014.402.04.603.078.08.016.16.033.24.053.07.018.14.038.21.06l.004.002c.07.022.14.045.21.07.07.025.14.053.21.083.07.03.14.06.21.092.07.032.14.066.21.103.07.037.14.077.21.12c.07.043.14.09.2.138.06.048.12.1.18.155.06.055.12.113.18.173.06.06.12.123.18.188.06.065.12.13.18.2.06.07.12.14.17.21.05.07.1.145.15.22.05.075.1.15.15.23.05.08.1.16.15.24.05.08.1.165.15.25.05.085.1.17.14.26.04.09.08.18.12.27.04.095.08.19.12.29.04.1.08.2.11.3.03.1.06.2.09.31.03.1.06.21.08.32.02.1.04.2.06.31.02.1.03.21.04.32.01.1.02.2.02.31V9.5h-2.5V8c0-.28-.22-.5-.5-.5H13c-.28 0-.5.22-.5.5v2.5H10c-.28 0-.5.22-.5.5v2c0 .28.22.5.5.5h2.5V17c0 .28.22.5.5.5h2.5c.28 0 .5-.22.5-.5v-2.5h2.5c.28 0 .5-.22.5-.5v-2c0-.28-.22-.5-.5-.5h-2.5V8c0-1.87-1.427-3.418-3.235-3.492z" />
                        </svg></a>
                </div>
            </div>
        </div>

        <div class="text-center text-emerald-200 text-sm mt-10 border-t border-emerald-800 pt-6">
            © {{ date('Y') }} Campers. Todos los derechos reservados.
        </div>
    </footer>


    {{-- Script para animaciones o funcionalidad extra --}}
    <script>
        // Script simple para cambiar el color del navbar al hacer scroll (opcional)
        window.addEventListener('scroll', function() {
            const nav = document.querySelector('nav');
            if (window.scrollY > 50) {
                nav.classList.add('bg-white', 'shadow-xl', 'bg-opacity-95');
                nav.classList.remove('bg-white/80', 'shadow-sm');
            } else {
                nav.classList.remove('bg-white', 'shadow-xl', 'bg-opacity-95');
                nav.classList.add('bg-white/80', 'shadow-sm');
            }
        });
    </script>
</body>

</html>