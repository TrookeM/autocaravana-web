<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Alquiler de Autocaravanas</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    @livewireStyles
</head>

<body class="bg-gray-100 font-sans">

    <nav class="bg-white shadow-md">
        <div class="container mx-auto px-4 py-4">
            <h1 class="text-2xl font-bold text-gray-800">Autocaravanas</h1>
        </div>
    </nav>

    <div class="container mx-auto px-4 py-8">
        <h2 class="text-3xl font-semibold text-gray-700 mb-6">Nuestra Flota</h2>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">

            @forelse ($campervans as $campervan)
            
            {{-- CAMBIO CLAVE: El contenedor principal ahora es el enlace <a> --}}
            <a href="{{ route('campervan.show', $campervan) }}" 
               class="bg-white rounded-lg shadow-lg overflow-hidden block hover:shadow-2xl transition duration-300 cursor-pointer"> 

                @php
                    // Define la ruta de la imagen principal.
                    // Asume que 'card-main-image' en app.css usa object-contain.
                    $imagePath = $campervan->main_image_path 
                        ? asset('storage/' . $campervan->main_image_path) 
                        : 'https://via.placeholder.com/600x400.png?text=Autocaravana';
                @endphp
                
                <img src="{{ $imagePath }}" alt="{{ $campervan->name }}" class="card-main-image">

                <div class="p-6">
                    <h3 class="text-xl font-bold mb-2">{{ $campervan->name }}</h3>

                    <div class="text-gray-600 mb-4">
                        {{-- Asegúrate de que Str::limit esté disponible o usa el paquete Illuminate\Support\Str --}}
                        {!! Str::limit($campervan->description, 100) !!} 
                    </div>

                    {{-- Formato del precio mejorado y sin botón --}}
                    <div class="flex justify-between items-center mt-4">
                        <p class="text-lg font-normal text-gray-500">
                            Desde:
                        </p>
                        <div class="text-2xl font-bold text-teal-600 text-right">
                            {{ number_format($campervan->price_per_night, 2) }}€
                            <span class="text-sm font-normal text-gray-500">/ noche</span>
                        </div>
                    </div>

                    {{-- El botón "Ver Disponibilidad" se ha eliminado --}}
                </div>
            </a>
            @empty
            <p class="text-gray-500">No hay autocaravanas disponibles en este momento.</p>
            @endforelse
        </div>
    </div>
    
    @livewireScripts

</body>

</html>