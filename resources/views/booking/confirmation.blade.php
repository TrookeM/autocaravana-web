<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reserva Confirmada</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-50 font-sans">
    <div class="container mx-auto px-4 py-12">
        <div class="max-w-2xl mx-auto text-center">

            {{-- Icono de éxito --}}
            <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
                <svg class="w-10 h-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>

            <h1 class="text-4xl font-extrabold text-gray-800 mb-4">¡Reserva Confirmada!</h1>

            <p class="text-lg text-gray-600 mb-8">
                Tu reserva ha sido procesada exitosamente. Te hemos enviado un email de confirmación con todos los detalles.
            </p>

            {{-- Detalles de la reserva --}}
            <div class="bg-white p-6 rounded-xl shadow-lg border-t-4 border-green-500 text-left mb-8">
                <h2 class="text-xl font-bold text-gray-700 mb-4">Detalles de tu Reserva</h2>
                <dl class="divide-y divide-gray-100 text-sm">
                    <div class="py-3 flex justify-between">
                        <dt class="text-gray-500">Número de Reserva</dt>
                        <dd class="font-medium text-gray-900">#{{ $booking->id }}</dd>
                    </div>
                    <div class="py-3 flex justify-between">
                        <dt class="text-gray-500">Autocaravana</dt>
                        <dd class="font-medium text-gray-900">{{ $booking->campervan->name }}</dd>
                    </div>
                    <div class="py-3 flex justify-between">
                        <dt class="text-gray-500">Check-in</dt>
                        <dd class="font-medium text-gray-900">{{ $booking->start_date->format('d/m/Y') }}</dd>
                    </div>
                    <div class="py-3 flex justify-between">
                        <dt class="text-gray-500">Check-out</dt>
                        <dd class="font-medium text-gray-900">{{ $booking->end_date->format('d/m/Y') }}</dd>
                    </div>
                    <div class="py-3 flex justify-between">
                        <dt class="text-gray-500">Nombre</dt>
                        <dd class="font-medium text-gray-900">{{ $booking->customer_name }}</dd>
                    </div>
                    <div class="py-3 flex justify-between">
                        <dt class="text-gray-500">Email</dt>
                        <dd class="font-medium text-gray-900">{{ $booking->customer_email }}</dd>
                    </div>
                    <div class="py-3 flex justify-between">
                        <dt class="text-gray-500">Total Pagado</dt>
                        <dd class="font-extrabold text-2xl text-green-600">{{ $booking->total_price }}€</dd>
                    </div>
                </dl>
            </div>

            {{-- Botones de acción --}}
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="/" class="bg-pink-600 hover:bg-pink-700 text-white font-bold py-3 px-6 rounded-xl shadow-lg transition duration-300">
                    Ver Otras Autocaravanas
                </a>
            </div>

            {{-- Información adicional --}}
            <div class="mt-8 p-4 bg-blue-50 rounded-lg">
                <p class="text-sm text-blue-700">
                    <strong>Importante:</strong> Llegarás a recibir un email con las instrucciones de entrega y devolución de la autocaravana.
                    Si no recibes el email en 24 horas, revisa tu carpeta de spam o contáctanos.
                </p>
            </div>
        </div>
    </div>
</body>

</html>