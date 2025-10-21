<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Confirmación de Reserva - {{ $campervan->name }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-50 font-sans">
    <div class="container mx-auto px-4 py-12">

        {{-- Botón de Volver --}}
        <div class="mb-6">
            {{--
              CAMBIO 1: 
              Cambiado de url()->previous() a la ruta de detalle de la autocaravana
            --}}
            <a href="{{ route('campervan.show', $campervan) }}" 
               class="inline-flex items-center text-pink-600 hover:text-pink-700 font-medium transition duration-300">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                </svg>
                Volver al detalle
            </a>
        </div>

        <h1 class="text-4xl font-extrabold text-gray-800 mb-8 border-b pb-4">Confirma tu Reserva</h1>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">
            {{-- Resumen de la Reserva (Columna Derecha) --}}
            <div class="lg:col-span-1 p-6 bg-white rounded-xl shadow-lg border-t-4 border-pink-600">
                <h2 class="text-xl font-bold text-gray-700 mb-4">Resumen</h2>
                <dl class="divide-y divide-gray-100 text-sm">
                    <div class="py-3 flex justify-between">
                        <dt class="text-gray-500">Autocaravana</dt>
                        <dd class="font-medium text-gray-900">{{ $campervan->name }}</dd>
                    </div>
                    <div class="py-3 flex justify-between">
                        <dt class="text-gray-500">Check-in</dt>
                        <dd class="font-medium text-gray-900">{{ \Carbon\Carbon::parse($start_date)->format('d/m/Y') }}</dd>
                    </div>
                    <div class="py-3 flex justify-between">
                        <dt class="text-gray-500">Check-out</dt>
                        <dd class="font-medium text-gray-900">{{ \Carbon\Carbon::parse($end_date)->format('d/m/Y') }}</dd>
                    </div>
                    <div class="py-3 flex justify-between">
                        <dt class="text-gray-500">Noches</dt>
                        <dd class="font-medium text-gray-900">{{ $nights }}</dd>
                    </div>
                    <div class="py-3 flex justify-between">
                        <dt class="text-gray-500">Precio por noche</dt>
                        <dd class="font-medium text-gray-900">{{ number_format($campervan->price_per_night, 2) }}€</dd>
                    </div>
                    <div class="py-3 flex justify-between">
                        <dt class="text-gray-500 font-bold">Total a pagar</dt>
                        <dd class="font-extrabold text-2xl text-pink-600">{{ number_format($total_price, 2) }}€</dd>
                    </div>
                </dl>
            </div>

            {{-- Formulario de Datos del Cliente (Columna Principal) --}}
            <div class="lg:col-span-2">
                <form method="POST" action="{{ route('booking.store') }}" class="space-y-6">
                    @csrf

                    @if ($errors->any())
                    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded">
                        <ul>
                            @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    <h2 class="text-2xl font-bold text-gray-700 mb-6">Tus Datos</h2>

                    <input type="hidden" name="campervan_id" value="{{ $campervan->id }}">
                    <input type="hidden" name="start_date" value="{{ $start_date }}">
                    <input type="hidden" name="end_date" value="{{ $end_date }}">
                    <input type="hidden" name="total_price" value="{{ $total_price }}">

                    <div>
                        <label for="customer_name" class="block text-sm font-medium text-gray-700">Nombre Completo *</label>
                        <input type="text" name="customer_name" id="customer_name" required
                            value="{{ old('customer_name') }}"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-pink-500 focus:ring-pink-500">
                        @error('customer_name')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="customer_email" class="block text-sm font-medium text-gray-700">Email *</label>
                        <input type="email" name="customer_email" id="customer_email" required
                            value="{{ old('customer_email') }}"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-pink-500 focus:ring-pink-500">
                        @error('customer_email')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="customer_phone" class="block text-sm font-medium text-gray-700">Teléfono *</label>
                        <input type="tel" name="customer_phone" id="customer_phone" required
                            value="{{ old('customer_phone') }}"
                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-pink-500 focus:ring-pink-500">
                        @error('customer_phone')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Botones --}}
                    <div class="pt-4 flex gap-4">
                        <button type="submit"
                            class="flex-1 bg-pink-600 hover:bg-pink-700 text-white font-bold py-3 px-4 rounded-xl shadow-lg transition duration-300">
                            Confirmar Reserva
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>

</html>
