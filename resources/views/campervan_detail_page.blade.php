{{--
    Página de detalle de la Autocaravana.
    Asume que la variable $campervan (instancia de App\Models\Campervan) está disponible.
--}}
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Detalle de Autocaravana: {{ $campervan->name ?? 'Ejemplo' }}</title>
    {{-- Carga tu CSS/JS con Tailwind y Alpine --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @livewireStyles
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

            @php
            // 1. Recopilar todas las rutas de imágenes (principal + secundarias)
            $images = array_merge(
                $campervan->main_image_path ? [$campervan->main_image_path] : [],
                $campervan->secondary_images ?? [] 
            );
            
            // 2. CORRECCIÓN: Reemplazar las barras invertidas ('\') por diagonales ('/') para Alpine/JS.
            $cleanedImages = array_map(function($path) {
                return str_replace('\\', '/', $path);
            }, $images);

            // 3. Si no hay imágenes reales, usa el placeholder
            if (empty($cleanedImages)) {
                $cleanedImages[] = 'placeholder';
            }
            
            $storageUrl = asset('storage') . '/';
            @endphp

            {{-- GALERÍA DE IMÁGENES CON ALPINE.JS --}}
            <div x-data="{ 
                    currentImage: 0, 
                    images: @js($cleanedImages),
                    imageCount: @js(count($cleanedImages)),

                    // Función para ir a la imagen anterior (cíclica)
                    prevImage() {
                        this.currentImage = (this.currentImage - 1 + this.imageCount) % this.imageCount;
                    },
                    
                    // Función para ir a la imagen siguiente (cíclica)
                    nextImage() {
                        this.currentImage = (this.currentImage + 1) % this.imageCount;
                    }
                }" 
                class="mb-8 relative"> 
                
                {{-- Imagen Principal (Visor) --}}
                <div class="relative w-full h-96 rounded-2xl shadow-xl overflow-hidden mb-4 bg-gray-200">
                    <template x-for="(image, index) in images" :key="index">
                        <img :src="image === 'placeholder' ? 'https://placehold.co/1200x600/10b981/ffffff?text=Tu+Autocaravana+Fantástica' : '{{ $storageUrl }}' + image"
                            :alt="'Imagen ' + (index + 1)"
                            x-show="currentImage === index"
                            x-transition:enter="transition ease-out duration-300"
                            x-transition:enter-start="opacity-0"
                            x-transition:enter-end="opacity-100"
                            x-transition:leave="transition ease-in duration-300 absolute top-0 left-0 w-full h-full"
                            x-transition:leave-end="opacity-0"
                            class="w-full h-full object-contain">
                            {{-- 👆 CAMBIO CLAVE: object-contain para no cortar --}}
                    </template>
                </div>

                {{-- BOTÓN DE NAVEGACIÓN IZQUIERDA --}}
                <button x-show="imageCount > 1" @click="prevImage()"
                        class="absolute left-4 top-[40%] transform -translate-y-1/2 p-3 bg-white/70 hover:bg-white rounded-full shadow-lg transition duration-200 z-10"
                        aria-label="Anterior">
                    <svg class="w-6 h-6 text-gray-800" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </button>

                {{-- BOTÓN DE NAVEGACIÓN DERECHA --}}
                <button x-show="imageCount > 1" @click="nextImage()"
                        class="absolute right-4 top-[40%] transform -translate-y-1/2 p-3 bg-white/70 hover:bg-white rounded-full shadow-lg transition duration-200 z-10"
                        aria-label="Siguiente">
                    <svg class="w-6 h-6 text-gray-800" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </button>


                {{-- Miniaturas de Navegación --}}
                <div class="flex space-x-2 overflow-x-auto p-1 mt-4">
                    <template x-for="(image, index) in images" :key="index">
                        <div @click="currentImage = index" 
                             :class="{ 'ring-2 ring-pink-500 ring-offset-2': currentImage === index, 'opacity-70': currentImage !== index }"
                             class="w-20 h-14 bg-gray-200 rounded-lg cursor-pointer transition duration-150 transform hover:opacity-100 hover:scale-105 overflow-hidden p-1">
                            {{-- Contenedor para que la miniatura muestre la imagen entera --}}
                            <img :src="image === 'placeholder' ? 'https://placehold.co/100x70.png?text=Img' : '{{ $storageUrl }}' + image"
                                class="w-full h-full object-contain">
                            {{-- 👆 CAMBIO CLAVE: object-contain en miniaturas --}}
                        </div>
                    </template>
                </div>
            </div>
            {{-- FIN GALERÍA --}}

            <h2 class="text-2xl font-bold text-gray-700 mb-3">Descripción</h2>
            <div class="text-gray-600 leading-relaxed mb-6">
                {{-- Renderiza la descripción (usando {!! !!} si viene de un RichEditor con HTML) --}}
                {!! $campervan->description ?? '<p>Esta autocaravana de lujo está equipada con todas las comodidades. Perfecta para una familia de 4, incluye cocina completa, baño con ducha y una cama king-size. ¡Aventuras inolvidables garantizadas!</p>' !!}
            </div>

            <h3 class="text-2xl font-bold text-gray-700 mb-3">Precio por noche: <span class="text-pink-600">{{ $campervan->price_per_night ?? 120 }}€</span></h3>
        </div>

        {{-- Columna Lateral: Calendario de Reserva (Sticky) --}}
        <div class="lg:col-span-1 sticky top-12">
            <h2 class="text-2xl font-bold text-gray-700 mb-6">Elige tus fechas</h2>

            {{-- INCLUSIÓN DEL COMPONENTE LIVEWIRE --}}
            @livewire('campervan-calendar', ['campervan' => $campervan])

        </div>
    </div>

    {{-- Scripts de Livewire deben estar al final del body --}}
    @livewireScripts

</body>

</html>