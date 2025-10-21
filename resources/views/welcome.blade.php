<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Alquiler de Autocaravanas</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    {{-- 1. NECESARIO PARA LIVEWIRE (aunque no lo uses aquí, es buena práctica) --}}
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
            <div class="bg-white rounded-lg shadow-lg overflow-hidden">

                <img src="https://via.placeholder.com/600x400.png?text=Autocaravana" alt="{{ $campervan->name }}" class="w-full h-48 object-cover">

                <div class="p-6">
                    <h3 class="text-xl font-bold mb-2">{{ $campervan->name }}</h3>

                    <div class="text-gray-600 mb-4">
                        {!! Str::limit($campervan->description, 100) !!}
                    </div>

                    <div class="text-2xl font-bold text-right text-teal-600">
                        {{ $campervan->price_per_night }}€
                        <span class="text-sm font-normal text-gray-500">/ noche</span>
                    </div>

                    {{-- 2. CAMBIO CLAVE: Usa la función route() para enlazar a la página de detalle --}}
                    <a href="{{ route('campervan.show', $campervan) }}" 
                       class="mt-4 block w-full text-center bg-blue-600 text-white font-bold py-2 px-4 rounded hover:bg-blue-700 transition duration-300">
                        Ver Disponibilidad
                    </a>
                </div>
            </div>
            @empty
            <p class="text-gray-500">No hay autocaravanas disponibles en este momento.</p>
            @endforelse
        </div>
    </div>
    
    {{-- NECESARIO PARA LIVEWIRE --}}
    @livewireScripts

</body>

</html>
