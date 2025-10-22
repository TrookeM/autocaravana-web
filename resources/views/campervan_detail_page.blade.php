<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Detalle de Autocaravana: {{ $campervan->name ?? 'Ejemplo' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>

<body class="bg-gray-50 font-sans">

    {{-- Cabecera --}}
    <nav class="nav-primary">
        <div class="container mx-auto px-4 py-4">
            <a href="/" class="text-2xl font-bold text-gray-800">Autocaravanas</a>
        </div>
    </nav>

    <div class="container-main grid-main">

        {{-- Columna Principal: Imagen y Descripción --}}
        <div class="lg:col-span-2">
            <h1 class="text-4xl font-extrabold text-gray-800 mb-4">{{ $campervan->name ?? 'Modelo Aventura' }}</h1>

            @php
            $images = array_merge(
            $campervan->main_image_path ? [$campervan->main_image_path] : [],
            $campervan->secondary_images ?? []
            );

            $cleanedImages = array_map(function($path) {
            return str_replace('\\', '/', $path);
            }, $images);

            if (empty($cleanedImages)) {
            $cleanedImages[] = 'placeholder';
            }

            $storageUrl = asset('storage') . '/';
            @endphp

            {{-- GALERÍA DE IMÁGENES --}}
            <div x-data="{ 
                    currentImage: 0, 
                    images: @js($cleanedImages),
                    imageCount: @js(count($cleanedImages)),
                    prevImage() {
                        this.currentImage = (this.currentImage - 1 + this.imageCount) % this.imageCount;
                    },
                    nextImage() {
                        this.currentImage = (this.currentImage + 1) % this.imageCount;
                    }
                }"
                class="mb-8 relative">

                {{-- Imagen Principal --}}
                <div class="gallery-main">
                    <template x-for="(image, index) in images" :key="index">
                        <img :src="image === 'placeholder' ? 'https://placehold.co/1200x600/10b981/ffffff?text=Tu+Autocaravana+Fantástica' : '{{ $storageUrl }}' + image"
                            :alt="'Imagen ' + (index + 1)"
                            x-show="currentImage === index"
                            x-transition:enter="transition ease-out duration-300"
                            x-transition:enter-start="opacity-0"
                            x-transition:enter-end="opacity-100"
                            x-transition:leave="transition ease-in duration-300 absolute top-0 left-0 w-full h-full"
                            x-transition:leave-end="opacity-0"
                            class="w-full h-full object-cover">
                    </template>
                </div>

                {{-- Botones de Navegación --}}
                <button x-show="imageCount > 1" @click="prevImage()"
                    class="gallery-nav-btn left-4 cursor-pointer" aria-label="Anterior">
                    <svg class="w-6 h-6 text-gray-800" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </button>

                <button x-show="imageCount > 1" @click="nextImage()"
                    class="gallery-nav-btn right-4 cursor-pointer" aria-label="Siguiente">
                    <svg class="w-6 h-6 text-gray-800" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </button>

                {{-- Miniaturas --}}
                <div class="flex space-x-2 overflow-x-auto p-1 mt-4">
                    <template x-for="(image, index) in images" :key="index">
                        <img :src="image === 'placeholder' ? 'https://placehold.co/100x70.png?text=Img' : '{{ $storageUrl }}' + image"
                            @click="currentImage = index"
                            :class="{ 'ring-2 ring-emerald-500 ring-offset-2': currentImage === index, 'opacity-70': currentImage !== index }"
                            class="gallery-thumbnail hover:scale-105">
                    </template>
                </div>
            </div>

            <h2 class="text-2xl font-bold text-gray-700 mb-3">Descripción</h2>
            <div class="text-gray-600 leading-relaxed mb-6">
                {!! $campervan->description ?? '<p>Esta autocaravana de lujo está equipada con todas las comodidades. Perfecta para una familia de 4, incluye cocina completa, baño con ducha y una cama king-size. ¡Aventuras inolvidables garantizadas!</p>' !!}
            </div>

            <h3 class="text-2xl font-bold text-gray-700 mb-3">
                Precio por noche: <span class="text-emerald-600">{{ $campervan->price_per_night ?? 120 }}€</span>
            </h3>
        </div>

        {{-- Columna Lateral: Calendario --}}
        <div class="lg:col-span-1 sticky top-12">
            <h2 class="text-2xl font-bold text-gray-700 mb-6">Elige tus fechas</h2>
            @livewire('campervan-calendar', ['campervan' => $campervan])
        </div>
    </div>

    @livewireScripts
</body>

</html>