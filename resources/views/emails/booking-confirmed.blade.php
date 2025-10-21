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
        .details th { text-align: left; padding: 8px; border-bottom: 1px solid #eee; color: #666; }
        .details td { text-align: right; padding: 8px; border-bottom: 1px solid #eee; font-weight: bold; }
        .footer { padding: 20px; text-align: center; font-size: 12px; color: #888; }
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
                    <td>{{ $booking->start_date->format('d/m/Y') }}</td>
                </tr>
                <tr>
                    <th>Check-out:</th>
                    <td>{{ $booking->end_date->format('d/m/Y') }}</td>
                </tr>
                <tr>
                    <th>Total Pagado:</th>
                    <td style="color: #059669; font-size: 1.2em;">{{ number_format($booking->total_price, 2) }}€</td>
                </tr>
            </table>

            <p style="margin-top: 25px;">Recibirás más instrucciones sobre la entrega y devolución pronto.</p>
            <p>¡Gracias por confiar en nosotros!</p>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} Autocaravanas. Todos los derechos reservados.</p>
        </div>
    </div>
</body>
</html>
