<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reserva Confirmada</title>
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
        
        /* Estilos para el desglose de precios */
        .price-breakdown th { padding-top: 15px; }
        .price-breakdown td { padding-top: 15px; }
        .price-breakdown .original-price { text-decoration: line-through; color: #888; font-weight: normal; }
        .price-breakdown .coupon-label { background-color: #fff7ed; color: #c2410c; font-weight: bold; }
        .price-breakdown .coupon-value { background-color: #fff7ed; color: #c2410c; }
        .price-breakdown .total-final-label { border-top: 2px solid #ddd; font-weight: bold; font-size: 1.1em; }
        .price-breakdown .total-final-value { border-top: 2px solid #ddd; font-size: 1.2em; }
        
        /* Estilos para el desglose de pago */
        .payment-breakdown .deposit-paid { color: #059669; }
        .payment-breakdown .amount-due-label { background-color: #fffbeb; color: #b45309; font-weight: bold; }
        .payment-breakdown .amount-due-value { background-color: #fffbeb; color: #b45309; }
        .payment-breakdown .full-paid-label { background-color: #f0fdf4; color: #15803d; font-size: 1.1em; font-weight: bold; }
        .payment-breakdown .full-paid-value { background-color: #f0fdf4; color: #15803d; font-size: 1.2em; }

        /* Estilo para el aviso inferior */
        .info-box {
            margin-top: 25px; padding: 15px; border-radius: 5px;
            background-color: #fffbeb; border: 1px solid #fef08a; color: #b45309;
        }

        /* Estilo para la lista de extras */
        .extras-list {
            margin: 0; 
            padding-left: 20px; 
            text-align: left; 
            font-size: 0.95em;
        }
        .extras-list li {
            margin-bottom: 5px;
        }

    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>¡Reserva Confirmada!</h1>
        </div>
        <div class="content">
            <p>Hola {{ $booking->customer_name }},</p>
            <p>Tu reserva para la autocaravana <strong>{{ $booking->campervan->name }}</strong> ha sido procesada exitosamente. Aquí tienes los detalles:</p>
            
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
                        @if($booking->campervan->check_in_time)
                            <span class="time-note">
                                a las {{ \Carbon\Carbon::parse($booking->campervan->check_in_time)->format('H:i') }}
                            </span>
                        @endif
                    </td>
                </tr>
                <tr>
                    <th>Check-out:</th>
                    <td>
                        {{ $booking->end_date->format('d/m/Y') }}
                        @if($booking->campervan->check_out_time)
                            <span class="time-note">
                                a las {{ \Carbon\Carbon::parse($booking->campervan->check_out_time)->format('H:i') }}
                            </span>
                        @endif
                    </td>
                </tr>

                @if ($booking->inventoryItems->isNotEmpty())
                    <tr>
                        <th style="vertical-align: top; padding-top: 10px;">Extras Contratados:</th>
                        <td style="padding-top: 10px;">
                            <ul class="extras-list">
                                @foreach ($booking->inventoryItems as $item)
                                    <li>
                                        {{ $item->name }}
                                        ({{ number_format($item->pivot->precio_cobrado, 2) }}€)
                                    </li>
                                @endforeach
                            </ul>
                        </td>
                    </tr>
                @endif
                @if ($booking->discount_amount > 0 && $booking->coupon_code)
                    <tr class="price-breakdown">
                        <th style="border-top: 2px dashed #eee; padding-top: 15px;">Subtotal (Base + Extras):</th>
                        <td style="border-top: 2px dashed #eee; padding-top: 15px;" class="original-price">{{ number_format($booking->original_price, 2) }}€</td>
                    </tr>
                    <tr class="price-breakdown">
                        <th class="coupon-label">Cupón ({{ $booking->coupon_code }}):</th>
                        <td class="coupon-value">- {{ number_format($booking->discount_amount, 2) }}€</td>
                    </tr>
                @else
                    @endif

                <tr class="price-breakdown">
                    <th class="total-final-label">PRECIO TOTAL FINAL:</th>
                    <td class="total-final-value">{{ number_format($booking->total_price, 2) }}€</td>
                </tr>

                @if ($booking->payment_status === 'deposit_paid')
                    {{-- PAGO PARCIAL (Señal) --}}
                    <tr class="payment-breakdown">
                        <th style="border-top: 2px solid #ddd; padding-top: 15px;">Pagado Hoy (Señal):</th>
                        <td style="border-top: 2px solid #ddd; padding-top: 15px;" class="deposit-paid">{{ number_format($booking->amount_paid, 2) }}€</td>
                    </tr>
                    <tr class="payment-breakdown">
                        <th class="amount-due-label">Pendiente de Pago:</th>
                        <td class="amount-due-value">{{ number_format($booking->amount_due, 2) }}€</td>
                    </tr>
                    <tr class="payment-breakdown">
                        <th>Fecha Límite Restante:</th>
                        <td>{{ $booking->payment_due_date ? $booking->payment_due_date->format('d/m/Y') : 'N/A' }}</td>
                    </tr>
                @else
                    {{-- PAGO TOTAL --}}
                    <tr class="payment-breakdown">
                        <th class="full-paid-label" style="border-top: 2px solid #ddd; padding-top: 15px;">Total Pagado Hoy (100%):</th>
                        <td class="full-paid-value" style="border-top: 2px solid #ddd; padding-top: 15px;">{{ number_format($booking->total_price, 2) }}€</td>
                    </tr>
                @endif
                </table>

            @if ($booking->payment_status === 'deposit_paid')
                <div class="info-box">
                    <p style="margin: 0;">
                        <strong>Importante:</strong> El pago restante de <strong>{{ number_format($booking->amount_due, 2) }}€</strong> vence el <strong>{{ $booking->payment_due_date ? $booking->payment_due_date->format('d/m/Y') : 'N/A' }}</strong>.
                    </p>
                </div>
            @endif

            <p style="margin-top: 25px;">Recibirás más instrucciones sobre la entrega y devolución pronto.</p>
            <p>¡Gracias por confiar en nosotros!</p>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} Autocaravanas. Todos los derechos reservados.</p>
        </div>
    </div>
</body>
</html>