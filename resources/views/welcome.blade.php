@extends('layouts.app')

@section('content')
    {{-- 'scroll-mt-[5rem]' (o la altura de tu nav) compensa el anclaje --}}
    <header id="inicio" class="hero relative flex items-center justify-center h-[90vh] text-center text-white overflow-hidden scroll-mt-[5rem]">
        {{-- El margin-top negativo se eliminó --}}

        {{-- Imagen de fondo con efecto parallax --}}
        <div class="absolute inset-0 w-full h-full bg-cover bg-center"
            style="background-image: url('https://images.unsplash.com/photo-1527786356703-4b100091cd2c?auto=format&fit=crop&w=1920&q=80'); background-attachment: fixed;">
        </div>

        <div class="absolute inset-0 bg-gradient-to-t from-emerald-900/80 via-emerald-800/40 to-transparent"></div>

        <div class="relative z-10 max-w-4xl px-6 animate-fadeInUp">
            <h1 class="text-5xl md:text-7xl font-extrabold leading-tight drop-shadow-2xl">
                Tu Aventura Comienza Aquí
            </h1>

            <p class="text-lg md:text-xl mt-4 text-emerald-50 drop-shadow-md">
                Explora, desconecta y vive la libertad sobre ruedas. Cada viaje, una historia.
            </p>

            <div class="mt-10">
                <a href="#flota"
                    class="inline-flex items-center gap-3 px-10 py-4 bg-emerald-600 text-white font-semibold text-lg rounded-full shadow-xl hover:bg-emerald-700 transition transform hover:scale-105 duration-300 group">
                    Descubre la Flota
                    <svg class="w-5 h-5 transition-transform duration-300 group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                    </svg>
                </a>
            </div>
        </div>
    </header>

    <section id="ventajas" class="py-20 bg-gray-50 scroll-mt-[5rem]">
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

    <section id="flota" class="py-20 bg-white scroll-mt-[5rem]">
        <div class="container mx-auto px-6 text-center">
            <h2 class="text-4xl font-extrabold text-gray-900 mb-4">Nuestra Flota</h2>
            <p class="text-xl text-gray-600 mt-2 mb-12">Autocaravanas equipadas para tu próxima aventura.</p>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-10 mt-12">
                @forelse ($campervans as $campervan)
                <a href="{{ route('campervan.show', $campervan) }}"
                    class="block bg-white rounded-2xl shadow-xl overflow-hidden hover:shadow-2xl transition transform hover:-translate-y-2 duration-300">

                    <img src="{{ $campervan->main_image_path ? asset('storage/'. $campervan->main_image_path) : 'https://placehold.co/600x400/E9D5FF/7C3AED?text=Autocaravana' }}"
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

    <section id="contacto" class="bg-emerald-800 text-white py-20 text-center scroll-mt-[5rem]">
        <h2 class="text-4xl font-extrabold mb-4">¿Listo para tu próxima aventura?</h2>
        <p class="text-xl mb-10 text-emerald-100">Reserva hoy mismo tu camper ideal y empieza tu viaje por carretera con la mejor garantía.</p>

        <a href="{{ route('contact') }}" class="inline-block px-10 py-4 bg-white text-emerald-700 text-lg font-bold rounded-full shadow-2xl hover:bg-gray-100 transition transform hover:scale-105 duration-300">
            ¡Contactar Ahora y Reservar!
        </a>
    </section>
@endsection

