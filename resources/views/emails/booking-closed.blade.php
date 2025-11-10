<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reserva Finalizada</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; color: #333; }
        .container { max-width: 600px; margin: auto; border: 1px solid #ddd; border-radius: 8px; overflow: hidden; }
        .header { background-color: #f4f4f4; padding: 20px; text-align: center; }
        .header h1 { margin: 0; color: #059669; /* Verde */ }
        .content { padding: 30px; }
        .content p { line-height: 1.6; }
        .details { background-color: #fafafa; padding: 20px; border-radius: 5px; }
        .details th { text-align: left; padding: 8px; border-bottom: 1px solid #eee; color: #666; font-weight: normal; }
        .details td { text-align: right; padding: 8px; border-bottom: 1px solid #eee; font-weight: bold; }
        .details .time-note { color: #666; font-weight: normal; font-size: 0.9em; display: block; }
        
        .price-breakdown th { padding-top: 15px; }
        .price-breakdown td { padding-top: 15px; }
        .price-breakdown .total-final-label { border-top: 2px solid #ddd; font-weight: bold; font-size: 1.1em; }
        
        .payment-breakdown .amount-due-label { background-color: #fffbeb; color: #b45309; font-weight: bold; }
        .payment-breakdown .amount-due-value { background-color: #fffbeb; color: #b45309; }
        .payment-breakdown .full-paid-label { background-color: #f0fdf4; color: #15803d; font-size: 1.1em; font-weight: bold; }
        .payment-breakdown .full-paid-value { background-color: #f0fdf4; color: #15803d; }

        .info-box {
            margin-top: 25px; padding: 15px; border-radius: 5px;
            background-color: #fffbeb; border: 1px solid #fef08a; color: #b45309;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>¡Reserva Finalizada!</h1>
        </div>
        <div class="content">
            <p>Hola {{ $booking->customer_name }},</p>
            <p>Tu alquiler de la autocaravana <strong>{{ $booking->campervan->name }}</strong> (Reserva #{{ $booking->id }}) ha finalizado. Aquí tienes el resumen de cierre y el cálculo de kilometraje:</p>
            
            <table class="details" width="100%">
                <tr>
                    <th>Número de Reserva:</th>
                    <td>#{{ $booking->id }}</td>
                </tr>
                <tr>
                    <th>Autocaravana:</th>
                    <td>{{ $booking->campervan->name }}</td>
                </tr>
                <tr>
                    <th>Check-in:</th>
                    <td>{{ $booking->start_date->format('d/m/Y') }}</td>
                </tr>
                <tr>
                    <th>Check-out:</th>
                    <td>{{ $booking->end_date->format('d/m/Y') }}</td>
                </tr>

                <tr>
                    <th class="price-breakdown total-final-label" colspan="2" style="text-align: left; padding-top: 20px;">Resumen de Kilometraje</th>
                </tr>
                <tr>
                    <th>Kilometraje de Salida:</th>
                    <td>{{ $booking->km_salida ?? 0 }} km</td>
                </tr>
                <tr>
                    <th>Kilometraje de Llegada:</th>
                    <td>{{ $booking->km_llegada ?? 0 }} km</td>
                </tr>
                
                {{-- 
                  ¡BLOQUE @php ELIMINADO! 
                  Ahora usamos las variables $extraKmCharge y $extraKm 
                  directamente desde el Mailable.
                --}}

                <tr>
                    <th>Kilómetros Recorridos:</th>
                    <td>{{ ($booking->km_llegada ?? 0) - ($booking->km_salida ?? 0) }} km</td>
                </tr>
                
                @if ($extraKmCharge > 0)
                    <tr class="payment-breakdown">
                        <th class="amount-due-label">KM Extra Recorridos:</th>
                        <td class="amount-due-value">{{ $extraKm }} km</td>
                    </tr>
                    <tr class="payment-breakdown">
                        <th class="amount-due-label">Cargo por KM Extra:</th>
                        <td class="amount-due-value" style="font-size: 1.2em;">{{ number_format($extraKmCharge, 2) }}€</td>
                    </tr>
                @else
                    <tr class="payment-breakdown">
                        <th class="full-paid-label">Cargo por KM Extra:</th>
                        <td class="full-paid-value">0.00€</td>
                    </tr>
                @endif
                </table>

            @if ($extraKmCharge > 0)
                <div class="info-box">
                    <p style="margin: 0;">
                        <strong>Importante:</strong> Se ha generado un cargo por exceso de kilometraje de <strong>{{ number_format($extraKmCharge, 2) }}€</strong>. Nos pondremos en contacto contigo para gestionar el pago.
                    </p>
                </div>
            @endif

            <p style="margin-top: 25px;">¡Gracias por viajar con nosotros! Esperamos verte de nuevo pronto.</p>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} Autocaravanas. Todos los derechos reservados.</p>
        </div>
    </div>
</body>
</html>