<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recordatorio de Pago</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; color: #333; }
        .container { max-width: 600px; margin: auto; border: 1px solid #ddd; border-radius: 8px; overflow: hidden; }
        .header { background-color: #f4f4f4; padding: 20px; text-align: center; }
        .header h1 { margin: 0; color: #b45309; /* Naranja/Ámbar */ }
        .content { padding: 30px; }
        .content p { line-height: 1.6; }
        .details { background-color: #fafafa; padding: 20px; border-radius: 5px; }
        .details th { text-align: left; padding: 8px; border-bottom: 1px solid #eee; color: #666; font-weight: normal; }
        .details td { text-align: right; padding: 8px; border-bottom: 1px solid #eee; font-weight: bold; }
        .details .time-note { color: #666; font-weight: normal; font-size: 0.9em; display: block; }
        
        .price-breakdown th { padding-top: 15px; }
        .price-breakdown td { padding-top: 15px; }
        .price-breakdown .original-price { text-decoration: line-through; color: #888; font-weight: normal; }
        .price-breakdown .coupon-label { background-color: #fff7ed; color: #c2410c; font-weight: bold; }
        .price-breakdown .coupon-value { background-color: #fff7ed; color: #c2410c; }
        .price-breakdown .total-final-label { border-top: 2px solid #ddd; font-weight: bold; font-size: 1.1em; }
        .price-breakdown .total-final-value { border-top: 2px solid #ddd; font-size: 1.2em; }
        
        .payment-breakdown .deposit-paid { color: #059669; }
        .payment-breakdown .amount-due-label { background-color: #fffbeb; color: #b45309; font-weight: bold; }
        .payment-breakdown .amount-due-value { background-color: #fffbeb; color: #b45309; }
        .payment-breakdown .full-paid-label { background-color: #f0fdf4; color: #15803d; font-size: 1.1em; font-weight: bold; }
        .payment-breakdown .full-paid-value { background-color: #f0fdf4; color: #15803d; font-size: 1.2em; }

        .info-box {
            margin-top: 25px; padding: 15px; border-radius: 5px;
            background-color: #fffbeb; border: 1px solid #fef08a; color: #b45309;
        }

        /* Botón CTA (Portal del Cliente) */
        .cta-container {
            text-align: center;
            margin: 25px 0;
        }
        .cta-button {
            display: inline-block;
            background-color: #b45309; /* Color Naranja del header */
            color: #ffffff !important; 
            padding: 12px 25px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            font-size: 1.1em;
        }
    </style>
</head>
<body>
    {{-- 
        ¡ERROR LÓGICO CORREGIDO!
        Hemos eliminado el bloque @php que calculaba mal la fecha.
        Ahora usamos $booking->payment_due_date, que es la fecha real
        que usó el Comando para enviar este email.
    --}}
    <div class="container">
        <div class="header">
            <h1>Recordatorio de Pago Pendiente</h1>
        </div>
        <div class="content">
            <p>Hola {{ $booking->customer_name }},</p>
            <p>Este es un recordatorio amistoso sobre tu reserva <strong>#{{ $booking->id }}</strong> para la autocaravana <strong>{{ $booking->campervan->name }}</strong>. Notamos que aún tienes un pago pendiente.</p>
            
            <div class="cta-container">
                <a href="{{ route('public.booking.show', ['token' => $booking->public_token]) }}" class="cta-button">
                    Ver mi Reserva y Pagar
                </a>
            </div>

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
                    <td>
                        {{ $booking->start_date->format('d/m/Y') }}
                    </td>
                </tr>

                <tr class="price-breakdown">
                    <th class="total-final-label">PRECIO TOTAL FINAL:</th>
                    <td class="total-final-value">{{ number_format($booking->total_price, 2) }}€</td>
                </tr>

                <tr class="payment-breakdown">
                    <th style="border-top: 2px solid #ddd; padding-top: 15px;">Pagado (Señal):</th>
                    <td style="border-top: 2px solid #ddd; padding-top: 15px;" class="deposit-paid">{{ number_format($booking->amount_paid, 2) }}€</td>
                </tr>
                <tr class="payment-breakdown">
                    <th class="amount-due-label">Pendiente de Pago:</th>
                    <td class="amount-due-value">{{ number_format($booking->amount_due, 2) }}€</td>
                </tr>
                <tr class="payment-breakdown">
                    <th>Fecha Límite de Pago:</th>
                    {{-- USAMOS LA VARIABLE CORRECTA --}}
                    <td>{{ $booking->payment_due_date ? $booking->payment_due_date->format('d/m/Y') : 'N/A' }}</td>
                </tr>
            </table>

            <div class="info-box">
                <p style="margin: 0;">
                    <strong>Importante:</strong> El pago restante de <strong>{{ number_format($booking->amount_due, 2) }}€</strong> vence el <strong>{{ $booking->payment_due_date ? $booking->payment_due_date->format('d/m/Y') : 'N/A' }}</strong>.
                </p>
            </div>

            <p style="margin-top: 25px;">Si ya has realizado el pago, por favor ignora este mensaje.</p>
            <p>¡Gracias por confiar en nosotros!</p>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} Autocaravanas. Todos los derechos reservados.</p>
        </div>
    </div>
</body>
</html>