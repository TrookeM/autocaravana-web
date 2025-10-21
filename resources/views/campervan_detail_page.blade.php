{{-- 
    Página de detalle de la Autocaravana. 
    Asegúrate de que tu layout principal tiene @livewireStyles y @livewireScripts.
    También asume que en el controlador (ej. CampervanController@show) se pasa la variable $campervan
--}}
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Detalle de Autocaravana: {{ $campervan->name ?? 'Ejemplo' }}</title>
    {{-- Carga tu CSS/JS con Tailwind --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    {{-- IMPORTANTE: Estilos de Livewire deben estar aquí --}}
    @livewireStyles 
    
    {{-- Carga Alpine.js si no está en app.js (Recomendado) --}}
</head>

<body class="bg-gray-50 font-sans">

    {{-- Simulación de una cabecera --}}
    <nav class="bg-white shadow-md">
        <div class="container mx-auto px-4 py-4">
            <a href="/" class="text-2xl font-bold text-gray-800">Autocaravanas</a>
        </div>
    </nav>
    
    <div class="container mx-auto px-4 py-12 grid grid-cols-1 lg:grid-cols-3 gap-12">
        
        {{-- Columna Principal: Imagen y Descripción --}}
        <div class="lg:col-span-2">
            <h1 class="text-4xl font-extrabold text-gray-800 mb-4">{{ $campervan->name ?? 'Modelo Aventura' }}</h1>
            
            <img src="https://placehold.co/1200x600/10b981/ffffff?text=Tu+Autocaravana+Fantástica" 
                 alt="Imagen de la autocaravana" 
                 class="w-full h-96 object-cover rounded-2xl shadow-xl mb-8">

            <h2 class="text-2xl font-bold text-gray-700 mb-3">Descripción</h2>
            <p class="text-gray-600 leading-relaxed mb-6">
                {{ $campervan->description ?? 'Esta autocaravana de lujo está equipada con todas las comodidades. Perfecta para una familia de 4, incluye cocina completa, baño con ducha y una cama king-size. ¡Aventuras inolvidables garantizadas!' }}
            </p>
            
            <h3 class="text-2xl font-bold text-gray-700 mb-3">Precio por noche: <span class="text-pink-600">{{ $campervan->price_per_night ?? 120 }}€</span></h3>
        </div>

        {{-- Columna Lateral: Calendario de Reserva (Sticky) --}}
        <div class="lg:col-span-1 sticky top-12">
            <h2 class="text-2xl font-bold text-gray-700 mb-6">Elige tus fechas</h2>
            
            {{-- !! INCLUSIÓN DEL COMPONENTE LIVEWIRE !! --}}
            {{-- Necesitas tener una instancia de Campervan llamada $campervan en esta vista --}}
            @livewire('campervan-calendar', ['campervan' => $campervan])
            
        </div>
    </div>
    
    {{-- Scripts de Livewire deben estar al final del body --}}
    @livewireScripts 

</body>

</html>