<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Alquiler de Autocaravanas - Tu Aventura</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-100 font-sans">

    {{-- NAVEGACIÓN --}}
    <nav class="nav-primary">
        <div class="container mx-auto px-4 py-4">
            <h1 class="text-2xl font-bold text-gray-800">Autocaravanas</h1>
        </div>
    </nav>

    {{-- SECCIÓN HÉROE --}}
    <header class="hero-section">
        <img src="https://images.unsplash.com/photo-1527786356703-4b100091cd2c?auto=format&fit=crop&w=1920&q=80"
            alt="Autocaravana en un paisaje montañoso"
            class="absolute inset-0 w-full h-full object-cover opacity-50 z-0">

        <div class="container mx-auto px-4 py-32 relative z-10 text-center">
            <h1 class="text-5xl md:text-6xl font-extrabold mb-10 drop-shadow-lg"> Tu Aventura Comienza Aquí
            </h1>
            <a href="#flota" class="btn-primary text-lg">
                Ver la Flota
            </a>
        </div>
    </header>

    {{-- SECCIÓN CÓMO FUNCIONA --}}
    <section class="bg-white py-20">
        <div class="container mx-auto px-4 text-center">
            <h2 class="text-3xl font-semibold text-gray-700 mb-12">Reserva en 3 Simples Pasos</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-12">

                @foreach ([
                ['icon' => 'search', 'title' => '1. Elige tu Autocaravana', 'text' => 'Explora nuestra flota y encuentra el vehículo perfecto.'],
                ['icon' => 'calendar', 'title' => '2. Selecciona tus Fechas', 'text' => 'Usa nuestro calendario interactivo para comprobar disponibilidad.'],
                ['icon' => 'check', 'title' => '3. Confirma y Disfruta', 'text' => 'Rellena tus datos, recibe confirmación y prepárate para viajar.']
                ] as $step)
                <div class="card-feature">
                    <div class="icon-circle">
                        <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            @if($step['icon'] === 'search')
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            @elseif($step['icon'] === 'calendar')
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            @else
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12 12 0 0012 21.694a12 12 0 008.618-3.04A11.955 11.955 0 0112 5.944z"></path>
                            @endif
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold mb-2">{{ $step['title'] }}</h3>
                    <p class="text-gray-600">{{ $step['text'] }}</p>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- SECCIÓN FLOTA --}}
    <div id="flota" class="container mx-auto px-4 py-20">
        <h2 class="text-3xl font-semibold text-gray-700 mb-6">Nuestra Flota</h2>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            @forelse ($campervans as $campervan)
            <a href="{{ route('campervan.show', $campervan) }}" class="product-card">
                <img src="{{ $campervan->main_image_path ? asset('storage/' . $campervan->main_image_path) : 'https://placehold.co/600x400/E9D5FF/7C3AED?text=Autocaravana' }}"
                    alt="{{ $campervan->name }}" class="card-main-image">
                <div class="p-6">
                    <h3 class="text-xl font-bold mb-2">{{ $campervan->name }}</h3>
                    <div class="text-gray-600 mb-4 h-16">
                        {!! Str::limit($campervan->description, 100) !!}
                    </div>
                    <div class="flex justify-between items-center">
                        <p class="text-lg text-gray-500">Desde:</p>
                        <div class="text-2xl font-bold text-emerald-600">
                            {{ number_format($campervan->price_per_night, 2) }}€
                            <span class="text-sm font-normal text-gray-500">/ noche</span>
                        </div>
                    </div>
                </div>
            </a>
            @empty
            <p class="text-gray-500">No hay autocaravanas disponibles.</p>
            @endforelse
        </div>
    </div>
</body>

</html>