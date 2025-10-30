<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Contrato de Alquiler #{{ $booking->id }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; }
        .container { width: 90%; margin: auto; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { margin: 0; }
        .details-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .details-table th, .details-table td {
            border: 1px solid #ddd; padding: 8px; text-align: left;
        }
        .details-table th { background-color: #f4f4f4; }
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
        
        <h3>Costes</h3>
        <table class="details-table">
            <tr>
                <th>Precio Total</th>
                <td>{{ number_format($booking->total_price, 2) }}€</td>
            </tr>
            <tr>
                <th>Cantidad Pagada</th>
                <td>{{ number_format($booking->amount_paid, 2) }}€</td>
            </tr>
            <tr>
                <th>Cantidad Pendiente</th>
                <td style="font-weight: bold;">{{ number_format($booking->amount_due, 2) }}€</td>
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