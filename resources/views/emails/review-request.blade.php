<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>¡Valora tu experiencia!</title>
    <style>
        /* Estilos base copiados de tus otros emails */
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; color: #333; }
        .container { max-width: 600px; margin: auto; border: 1px solid #ddd; border-radius: 8px; overflow: hidden; }
        .header { background-color: #f4f4f4; padding: 20px; text-align: center; }
        
        /* Usamos el azul del CTA para este email */
        .header h1 { margin: 0; color: #0d6efd; } 
        
        .content { padding: 30px; }
        .content p { line-height: 1.6; }

        /* Estilo para el botón de reseña */
        .cta-container {
            text-align: center;
            margin: 25px 0;
        }
        .cta-button {
            display: inline-block;
            background-color: #0d6efd; /* Azul */
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
            <h1>¡Valora tu experiencia!</h1>
        </div>
        <div class="content">
            <p>Hola <strong>{{ $booking->customer_name }}</strong>,</p>
            
            <p>¡Esperamos que hayas disfrutado de tu viaje con nuestra autocaravana <strong>{{ $booking->campervan->name }}</strong>!</p>
            
            <p>Tu opinión es muy importante para nosotros y para futuros viajeros. ¿Te importaría dedicarnos un minuto a dejarnos una reseña?</p>

            <div class="cta-container">
                <a href="{{ route('campervan.show', $booking->campervan) }}" class="cta-button">
                    Dejar una reseña
                </a>
            </div>

            <p>¡Gracias por tu tiempo y por viajar con nosotros!</p>
        </div>
        <div class="footer" style="background-color: #f4f4f4; padding: 20px; text-align: center; font-size: 0.9em; color: #777;">
            <p>&copy; {{ date('Y') }} {{ config('app.name') }}. Todos los derechos reservados.</p>
        </div>
    </div>
</body>
</html>