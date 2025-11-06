<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reserva Confirmada</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            color: #333;
        }

        .container {
            max-width: 600px;
            margin: auto;
            border: 1px solid #ddd;
            border-radius: 8px;
            overflow: hidden;
        }

        .header {
            background-color: #f4f4f4;
            padding: 20px;
            text-align: center;
        }

        .header h1 {
            margin: 0;
            color: #059669;
        }

        .content {
            padding: 30px;
        }

        .content p {
            line-height: 1.6;
        }

        .details {
            background-color: #fafafa;
            padding: 20px;
            border-radius: 5px;
            width: 100%;
            border-collapse: collapse;
        }

        .details th {
            text-align: left;
            padding: 8px;
            border-bottom: 1px solid #eee;
            color: #666;
            font-weight: normal;
        }

        .details td {
            text-align: right;
            padding: 8px;
            border-bottom: 1px solid #eee;
            font-weight: bold;
        }

        .details .time-note {
            color: #666;
            font-weight: normal;
            font-size: 0.9em;
            display: block;
        }

        /* Estilos para el desglose de precios */
        .price-breakdown th {
            padding-top: 15px;
        }

        .price-breakdown td {
            padding-top: 15px;
        }

        .price-breakdown .original-price {
            text-decoration: line-through;
            color: #888;
            font-weight: normal;
        }

        .price-breakdown .coupon-label {
            background-color: #fff7ed;
            color: #c2410c;
            font-weight: bold;
        }

        .price-breakdown .coupon-value {
            background-color: #fff7ed;
            color: #c2410c;
        }

        .price-breakdown .total-final-label {
            border-top: 2px solid #ddd;
            font-weight: bold;
            font-size: 1.1em;
        }

        .price-breakdown .total-final-value {
            border-top: 2px solid #ddd;
            font-size: 1.2em;
        }

        /* Base Price Cell Dinámica */
        .base-price-cell {
            border-top: 2px dashed #eee;
            padding-top: 15px;
        }

        .base-price-strike {
            text-decoration: line-through;
            color: #888;
            font-weight: normal;
        }

        /* Estilos para el desglose de pago */
        .payment-breakdown .deposit-paid {
            color: #059669;
        }

        .payment-breakdown .amount-due-label {
            background-color: #fffbeb;
            color: #b45309;
            font-weight: bold;
        }

        .payment-breakdown .amount-due-value {
            background-color: #fffbeb;
            color: #b45309;
        }

        .payment-breakdown .full-paid-label {
            background-color: #f0fdf4;
            color: #15803d;
            font-size: 1.1em;
            font-weight: bold;
        }

        .payment-breakdown .full-paid-value {
            background-color: #f0fdf4;
            color: #15803d;
            font-size: 1.2em;
        }

        /* Estilo para el aviso inferior */
        .info-box {
            margin-top: 25px;
            padding: 15px;
            border-radius: 5px;
            background-color: #fffbeb;
            border: 1px solid #fef08a;
            color: #b45309;
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

        /* Estilos para guías */
        .guides-section {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }

        .guides-section h2 {
            margin: 0 0 15px 0;
            color: #333;
            font-size: 1.2em;
        }

        .guide-item {
            background-color: #fafafa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 15px;
            border: 1px solid #eee;
        }

        .guide-item h3 {
            margin: 0 0 10px 0;
            color: #059669;
        }

        .guide-content {
            font-size: 0.95em;
            line-height: 1.5;
            color: #555;
        }

        .guide-content p {
            margin: 0 0 10px 0;
        }

        .guide-content ul,
        .guide-content ol {
            margin-left: 20px;
            margin-bottom: 10px;
            padding-left: 0;
        }

        .pdf-download-link {
            display: inline-block;
            background-color: #d73d32;
            color: #ffffff;
            padding: 8px 15px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            margin-top: 10px;
            font-size: 0.9em;
        }

        /* CTA */
        .cta-container {
            text-align: center;
            margin: 25px 0;
        }

        .cta-button {
            display: inline-block;
            background-color: #0d6efd;
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
    <div class="container">
        <div class="header">
            <h1>¡Reserva Confirmada!</h1>
        </div>
        <div class="content">
            <p>Hola {{ $booking->customer_name }},</p>
            <p>Tu reserva para la autocaravana <strong>{{ $booking->campervan->name }}</strong> ha sido procesada exitosamente.</p>

            <div class="cta-container">
                <a href="{{ route('public.booking.show', ['token' => $booking->public_token]) }}" class="cta-button">
                    Ver Estado de mi Reserva
                </a>
            </div>
            <p>Aquí tienes los detalles:</p>

            <table class="details">
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

                {{-- PRECIO BASE --}}
                @php
                $applyStrike = $duration_discount_amount > 0;
                @endphp
                <tr class="price-breakdown">
                    <th>Precio Base (Temporada)</th>
                    <td class="base-price-cell {{ $applyStrike ? 'base-price-strike' : '' }}">
                        {{ number_format($base_seasonal_price, 2) }}€
                    </td>
                </tr>

                {{-- DESCUENTO POR DURACIÓN --}}
                @if ($duration_discount_amount > 0)
                <tr class="price-breakdown">
