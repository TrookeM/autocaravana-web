<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reserva Confirmada</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- El CSS personalizado se maneja en resources/css/app.css ahora -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
</head>

<body class="bg-gray-50 font-sans">
    <div class="container-main">
        <div class="max-w-2xl mx-auto text-center">

            {{-- Icono de éxito (Usa la clase componente .icon-success) --}}
            <div class="icon-success">
                <svg class="w-10 h-10 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>

            <h1 class="text-4xl font-extrabold text-gray-800 mb-4">¡Reserva Confirmada!</h1>

            <p class="text-lg text-gray-600 mb-8">
                Tu reserva ha sido procesada exitosamente. Te hemos enviado un email de confirmación con todos los detalles.
            </p>

            {{-- Detalles de la reserva (Usa la clase componente .card-booking) --}}
            <div class="card-booking">
                <h2 class="text-xl font-bold text-gray-700 mb-4">Detalles de tu Reserva</h2>
                <dl class="divide-y divide-gray-100">
                    {{-- 1. INFO BÁSICA DE LA RESERVA --}}
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
                        <dd class="summary-value">
                            {{ $booking->start_date->format('d/m/Y') }}

                            @if($booking->campervan->check_in_time)
                            <span class="text-gray-600">
                                a las {{ \Carbon\Carbon::parse($booking->campervan->check_in_time)->format('H:i') }}
                            </span>
                            @endif
                        </dd>
                    </div>
                    <div class="summary-item">
                        <dt class="summary-label">Check-out</dt>
                        <dd class="summary-value">
                            {{ $booking->end_date->format('d/m/Y') }}

                            @if($booking->campervan->check_out_time)
                            <span class="text-gray-600">
                                a las {{ \Carbon\Carbon::parse($booking->campervan->check_out_time)->format('H:i') }}
                            </span>
                            @endif
                        </dd>
                    </div>

                    {{-- 2. SECCIÓN DE DESCUENTO (RF5.1) --}}
                    @if ($booking->discount_amount > 0 && $booking->coupon_code)
                    <div class="summary-item pt-4 border-t-2 border-dashed border-gray-200">
                        <dt class="summary-label">Precio Original</dt>
                        <dd class="summary-value line-through text-gray-400">{{ number_format($booking->original_price, 2) }}€</dd>
                    </div>
                    <div class="summary-item bg-red-50/50">
                        <dt class="summary-label font-bold text-red-700">Cupón Aplicado ({{ $booking->coupon_code }})</dt>
                        <dd class="summary-value text-red-700 font-bold">- {{ number_format($booking->discount_amount, 2) }}€</dd>
                    </div>
                    @endif

                    {{-- 3. TOTAL FINAL (Precio de la Reserva) --}}
                    <div class="summary-item border-t pt-2 mt-2 {{ $booking->discount_amount > 0 ? 'bg-emerald-50 rounded-lg p-2' : '' }}">
                        <dt class="summary-label font-bold text-base">PRECIO TOTAL FINAL</dt>
                        <dd class="total-price font-extrabold text-lg text-gray-800">{{ number_format($booking->total_price, 2) }}€</dd>
                    </div>

                    {{-- 4. DESGLOSE DE PAGO (RF6.1) --}}
                    @if ($booking->payment_status === 'deposit_paid')
                    {{-- PAGO PARCIAL (Señal) --}}
                    <div class="summary-item pt-4 border-t">
                        <dt class="summary-label">Pagado Hoy (Señal)</dt>
                        <dd class="summary-value text-emerald-600 font-bold">{{ number_format($booking->amount_paid, 2) }}€</dd>
                    </div>
                    <div class="summary-item bg-amber-50/50">
                        <dt class="summary-label">Pendiente de Pago</dt>
                        <dd class="summary-value text-amber-700 font-bold">{{ number_format($booking->amount_due, 2) }}€</dd>
                    </div>
                    <div class="summary-item">
                        <dt class="summary-label">Fecha Límite Pago Restante</dt>
                        <dd class="summary-value">{{ $booking->payment_due_date ? $booking->payment_due_date->format('d/m/Y') : 'N/A' }}</dd>
                    </div>
                    @else
                    {{-- PAGO TOTAL --}}
                    <div class="summary-item bg-emerald-50/50 rounded-lg p-2 mt-4 border-t-2 border-emerald-100">
                        <dt class="summary-label font-bold text-lg text-emerald-700">Total Pagado Hoy (100%)</dt>
                        <dd class="total-price font-extrabold text-lg text-emerald-700">{{ number_format($booking->total_price, 2) }}€</dd>
                    </div>
                    @endif

                </dl>
            </div>

            {{-- Botones de acción (Usa la clase componente .btn-primary) --}}
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="/" class="btn-primary">
                    Ver Otras Autocaravanas
                </a>
            </div>

            {{-- Información adicional (Usa la clase componente .info-section) --}}
            <div class="info-section mt-8 bg-gray-100 p-4 rounded-xl text-sm text-gray-600 border border-gray-200">
                @if ($booking->payment_status === 'deposit_paid')
                <p class="text-red-600 font-semibold mb-4">
                    El pago restante de <strong>{{ number_format($booking->amount_due, 2) }}€</strong> se efectuará antes del <strong>{{ $booking->payment_due_date ? $booking->payment_due_date->format('d/m/Y') : 'N/A' }}</strong>.
                </p>
                @endif
                <p>
                    <strong>Importante:</strong> Llegarás a recibir un email con las instrucciones de entrega y devolución de la autocaravana.
                    Si no recibes el email en 24 horas, revisa tu carpeta de spam o contáctanos.
                </p>
            </div>
        </div>
    </div>
</body>

</html>