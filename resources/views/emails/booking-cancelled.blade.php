<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reserva Cancelada</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; color: #333; }
        .container { max-width: 600px; margin: auto; border: 1px solid #ddd; border-radius: 8px; overflow: hidden; }
        .header { background-color: #f4f4f4; padding: 20px; text-align: center; }
        .header h1 { margin: 0; color: #D9534F; /* Rojo de cancelación */ }
        .content { padding: 30px; }
        .content p { line-height: 1.6; }
        .reason-box {
            background-color: #fdf2f2;
            border: 1px solid #fecdca;
            color: #9b2c2c;
            padding: 15px 20px;
            border-radius: 5px;
            margin: 20px 0;
            font-size: 1.1em;
            font-weight: bold;
        }
        .details { background-color: #fafafa; padding: 20px; border-radius: 5px; }
        .details th { text-align: left; padding: 8px; border-bottom: 1px solid #eee; color: #666; font-weight: normal; }
        .details td { text-align: right; padding: 8px; border-bottom: 1px solid #eee; font-weight: bold; }
        .footer { text-align: center; padding: 20px; font-size: 0.9em; color: #888; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Reserva Cancelada</h1>
        </div>
        <div class="content">
            <p>Hola {{ $booking->customer_name }},</p>
            <p>Lamentamos informarte que tu reserva (<strong>#{{ $booking->id }}</strong>) para la autocaravana <strong>{{ $booking->campervan->name }}</strong> ha sido cancelada.</p>

            <div class="reason-box">
                {{ $reason }}
            </div>
            
            <p><strong>Detalles de la reserva cancelada:</strong></p>

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
                    <th>Total de la Reserva:</th>
                    <td>{{ number_format($booking->total_price, 2) }}€</td>
                </tr>
            </table>

            <p style="margin-top: 25px;">Si crees que esto es un error o deseas realizar una nueva reserva, por favor, ponte en contacto con nosotros.</p>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} Autocaravanas. Todos los derechos reservados.</p>
        </div>
    </div>
</body>
</html>