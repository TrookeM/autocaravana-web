<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Contrato de Alquiler #{{ $booking->id }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        .container { width: 95%; margin: auto; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { margin: 0; font-size: 20px; }
        .header h2 { margin: 0; font-size: 16px; font-weight: normal; }
        
        h3 { 
            font-size: 16px; 
            margin-top: 30px; 
            margin-bottom: 10px; 
            border-bottom: 1px solid #eee; 
            padding-bottom: 5px;
        }

        .details-table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 10px; 
        }
        .details-table th, .details-table td {
            border: 1px solid #ddd; 
            padding: 8px; 
            text-align: left;
            page-break-inside: avoid;
        }
        .details-table th { background-color: #f4f4f4; width: 30%; }
        
        /* Estilos para la tabla de costes */
        .costs-table th { background-color: #f9f9f9; }
        .costs-table .total-row th, .costs-table .total-row td {
            font-weight: bold;
            font-size: 14px;
            background-color: #f0f0f0;
        }
        /* Color verde para descuento por duración */
        .costs-table .duration-discount-row td {
            color: #059669; /* Verde */
            font-weight: bold;
        }
        /* Color rojo para descuento por cupón */
        .costs-table .coupon-discount-row td {
            color: #D90000; /* Rojo */
            font-weight: bold;
        }
        .costs-table .due-row td {
            font-weight: bold;
            font-size: 13px;
        }

        .footer { margin-top: 40px; }
        .signature { margin-top: 60px; width: 40%; float: left; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Contrato de Alquiler de Autocaravana</h1>
            <h2>Reserva #{{ $booking->id }}</h2>
        </div>

        <h3>Datos del Cliente</h3>
        <table class="details-table">
            <tr>
                <th>Cliente</th>
                <td>{{ $booking->customer_name }}</td>
            </tr>
            <tr>
                <th>Email</th>
                <td>{{ $booking->customer_email }}</td>
            </tr>
            <tr>
                <th>Teléfono</th>
                <td>{{ $booking->customer_phone }}</td>
            </tr>
        </table>

        <h3>Datos de la Reserva</h3>
        <table class="details-table">
            <tr>
                <th>Autocaravana</th>
                <td>{{ $booking->campervan->name }}</td>
            </tr>
            <tr>
                <th>Check-in</th>
                <td>{{ $booking->start_date->format('d/m/Y') }} a las {{ \Carbon\Carbon::parse($booking->campervan->check_in_time)->format('H:i') }}</td>
            </tr>
            <tr>
                <th>Check-out</th>
                <td>{{ $booking->end_date->format('d/m/Y') }} a las {{ \Carbon\Carbon::parse($booking->campervan->check_out_time)->format('H:i') }}</td>
            </tr>
        </table>
        
        @if($booking->inventoryItems->isNotEmpty())
            <h3>Extras Contratados</h3>
            <table class="details-table">
                <thead>
                    <tr>
                        <th style="width: 70%;">Extra</th>
                        <th style="width: 30%;">Coste Cobrado</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($booking->inventoryItems as $item)
                        <tr>
                            <td>{{ $item->name }}</td>
                            <td>{{ number_format($item->pivot->precio_cobrado, 2) }}€</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        <h3>Costes</h3>
        <table class="details-table costs-table">
            
            <tr>
                <th>Precio Base (Temporada)</th>
                <td>{{ number_format($base_seasonal_price, 2) }}€</td>
            </tr>
            
            @if ($duration_discount_amount > 0)
                <tr class="duration-discount-row">
                    <th>Descuento Larga Estancia</th>
                    <td>-{{ number_format($duration_discount_amount, 2) }}€</td>
                </tr>
            @endif
            
            @if ($extras_price > 0)
                <tr>
                    <th>Coste Extras</th>
                    <td>+{{ number_format($extras_price, 2) }}€</td>
                </tr>
            @endif

            @if ($coupon_discount_amount > 0 && $booking->coupon_code)
                <tr class="coupon-discount-row">
                    <th>Descuento Cupón ({{ $booking->coupon_code }})</th>
                    <td>-{{ number_format($coupon_discount_amount, 2) }}€</td>
                </tr>
            @endif
            <tr class="total-row">
                <th>Precio Total Final</th>
                <td>{{ number_format($booking->total_price, 2) }}€</td>
            </tr>
            <tr>
                <th>Cantidad Pagada</th>
                <td>{{ number_format($booking->amount_paid, 2) }}€</td>
            </tr>
            <tr class="due-row">
                <th>Cantidad Pendiente</th>
                <td>{{ number_format($booking->amount_due, 2) }}€</td>
            </tr>
        </table>
        <div class="footer">
            <p>Ambas partes, arrendador y arrendatario, aceptan los términos y condiciones...</p>
            
            <div class="signature">
                <p>_________________________</p>
                <p>Firma (Arrendador)</p>
            </div>
            
            <div class="signature" style="float: right;">
                <p>_________________________</p>
                <p>Firma (Cliente: {{ $booking->customer_name }})</p>
            </div>
        </div>
    </div>
</body>
</html>