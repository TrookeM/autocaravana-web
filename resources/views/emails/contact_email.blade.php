<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Solicitud de Contacto</title>
    {{-- Estilos mínimos para compatibilidad con clientes de correo --}}
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 20px; color: #333; }
        .container { max-width: 600px; margin: auto; border: 1px solid #ddd; border-radius: 8px; overflow: hidden; }
        .header { background-color: #f4f4f4; padding: 20px; text-align: center; }
        .header h1 { margin: 0; color: #CC8400; /* Color Naranja/Dorado para distinguir de la confirmación */ }
        .content { padding: 30px; }
        .content p { line-height: 1.6; }
        .details { background-color: #fafafa; padding: 20px; border-radius: 5px; }
        .details th { text-align: left; padding: 8px; border-bottom: 1px solid #eee; color: #666; }
        .details td { text-align: right; padding: 8px; border-bottom: 1px solid #eee; font-weight: bold; }
        .message-box { background-color: #ffffff; padding: 15px; border: 1px solid #ddd; border-radius: 5px; white-space: pre-wrap; word-wrap: break-word; }
        .footer { padding: 20px; text-align: center; font-size: 12px; color: #888; }
        .button {
            display: inline-block;
            padding: 10px 20px;
            margin-top: 20px;
            background-color: #059669; /* Verde Esmeralda */
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Nueva Solicitud de Contacto</h1>
        </div>
        <div class="content">
            <p>Estimado equipo,</p>
            <p>Has recibido una nueva consulta de un cliente potencial a través del formulario de contacto en la web. Aquí están los detalles:</p>
            
            <table class="details" width="100%" cellspacing="0" cellpadding="0">
                <thead>
                    <tr>
                        <th colspan="2" style="font-size: 1.1em; color: #333; padding-top: 0; border-bottom: 2px solid #059669;">
                            DATOS DEL SOLICITANTE
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <th>Nombre:</th>
                        <td><strong>{{ $data['name'] }}</strong></td>
                    </tr>
                    <tr>
                        <th>Email de Contacto:</th>
                        <td><a href="mailto:{{ $data['email'] }}" style="color: #059669; text-decoration: none;">{{ $data['email'] }}</a></td>
                    </tr>
                    <tr>
                        <th>Teléfono:</th>
                        <td>{{ $data['phone'] ?? 'No proporcionado' }}</td>
                    </tr>
                </tbody>
            </table>

            <p style="margin-top: 25px;"><strong>Mensaje del Cliente:</strong></p>
            <div class="message-box">
                {{ $data['message'] }}
            </div>
            
            <p style="margin-top: 25px;">Puedes responder directamente haciendo clic en el botón de abajo:</p>

            {{-- Botón de Respuesta con Protocolo mailto --}}
            <a href="mailto:{{ $data['email'] }}?subject={{ rawurlencode('RE: Consulta sobre Campervan') }}" 
               class="button" 
               style="color: #ffffff !important; background-color: #059669; text-decoration: none;">
                Responder a {{ $data['name'] }}
            </a>
            
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} Forester Campers. Este correo fue enviado desde la web.</p>
        </div>
    </div>
</body>
</html>
