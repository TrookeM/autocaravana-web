<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reserva Confirmada</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-50 font-sans">
    <div class="container-main">
        <div class="max-w-2xl mx-auto text-center">

            {{-- Icono de éxito --}}
            <div class="icon-success">
                <svg class="w-10 h-10 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>

            <h1 class="text-4xl font-extrabold text-gray-800 mb-4">¡Reserva Confirmada!</h1>

            <p class="text-lg text-gray-600 mb-8">
                Tu reserva ha sido procesada exitosamente. Te hemos enviado un email de confirmación con todos los detalles.
            </p>

            {{-- Detalles de la reserva --}}
            <div class="card-booking">
                <h2 class="text-xl font-bold text-gray-700 mb-4">Detalles de tu Reserva</h2>
                <dl class="divide-y divide-gray-100">
                    <div class="summary-item">
                        <dt class="summary-label">Número de Reserva</dt>
                        <dd class="summary-value">#{{ $booking->id }}</dd>
                    </div>
                    <div class="summary-item">
                        <dt class="summary-label">Autocaravana</dt>
                        <dd class="summary-value">{{ $booking->campervan->name }}</dd>
                    </div>
                    <div class="summary-item">
                        <dt class="summary-label">Check-in</dt>
                        <dd class="summary-value">{{ $booking->start_date->format('d/m/Y') }}</dd>
                    </div>
                    <div class="summary-item">
                        <dt class="summary-label">Check-out</dt>
                        <dd class="summary-value">{{ $booking->end_date->format('d/m/Y') }}</dd>
                    </div>
                    <div class="summary-item">
                        <dt class="summary-label">Total Pagado</dt>
                        <dd class="total-price">{{ $booking->total_price }}€</dd>
                    </div>
                </dl>
            </div>

            {{-- Botones de acción --}}
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="/" class="btn-primary">
                    Ver Otras Autocaravanas
                </a>
            </div>

            {{-- Información adicional --}}
            <div class="info-section mt-8">
                <p>
                    <strong>Importante:</strong> Llegarás a recibir un email con las instrucciones de entrega y devolución de la autocaravana.
                    Si no recibes el email en 24 horas, revisa tu carpeta de spam o contáctanos.
                </p>
            </div>
        </div>
    </div>
</body>
</html>