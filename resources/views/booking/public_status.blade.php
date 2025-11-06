<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estado de tu Reserva (#{{ $booking->id }})</title>
    <style>
        /* =================================== */
        /* --- FONDO MODIFICADO --- */
        /* =================================== */
        body { 
            font-family: Arial, sans-serif; 
            margin: 0; 
            padding: 20px; 
            color: #333; 
            
            /* 1. Color de fondo verde pálido (de tu tema) */
            background-color: #f0fdf4; 
            
            /* 2. IMAGEN DE FONDO (¡DEBES CAMBIAR ESTO!) */
            /* - Cambia la URL por la ruta a tu imagen. */
            /* - La 'tonalidad verde' se la damos con el 'linear-gradient' */
            background-image: 
                linear-gradient(rgba(240, 253, 244, 0.85), rgba(240, 253, 244, 0.85)), /* Capa de tonalidad verde */
                url('/images/tu-imagen-de-fondo.jpg'); /* <-- ¡CAMBIA ESTA RUTA! */
            
            /* 3. Estilos para que la imagen cubra todo */
            background-size: cover;
            background-position: center center;
            background-attachment: fixed; /* Opcional: deja la imagen fija al hacer scroll */
        }
        
        .container { 
            max-width: 800px; 
            margin: 20px auto; 
            border: 1px solid #ddd; 
            border-radius: 8px; 
            overflow: hidden; 
            background-color: #fff; /* Contenedor blanco para que resalte */
            box-shadow: 0 4px 12px rgba(0,0,0,0.05); 
        }
        .header { background-color: #f4f4f4; padding: 20px; text-align: center; border-bottom: 1px solid #ddd; }
        .header h1 { margin: 0; color: #059669; } /* Tono verde principal (¡Perfecto!) */
        .content { padding: 30px; }
        .content p { line-height: 1.6; }
        .details { background-color: #fafafa; padding: 20px; border-radius: 5px; }
        .details th { text-align: left; padding: 8px; border-bottom: 1px solid #eee; color: #666; font-weight: normal; }
        .details td { text-align: right; padding: 8px; border-bottom: 1px solid #eee; font-weight: bold; }
        .details .time-note { color: #666; font-weight: normal; font-size: 0.9em; display: block; }
        
        /* ... (el resto de estilos de desglose de precios y pago se quedan igual) ... */
        .price-breakdown th, .price-breakdown td { padding-top: 15px; }
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
        .extras-list { margin: 0; padding-left: 20px; text-align: left; font-size: 0.95em; }
        .extras-list li { margin-bottom: 5px; }
        .guides-section { margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee; }
        .guides-section h2 { margin: 0 0 15px 0; color: #333; font-size: 1.2em; }
        .guide-item { background-color: #fafafa; padding: 15px; border-radius: 5px; margin-bottom: 15px; border: 1px solid #eee; }
        .guide-item h3 { margin: 0 0 10px 0; color: #059669; } /* Tono verde principal (¡Perfecto!) */
        .guide-content { font-size: 0.95em; line-height: 1.5; color: #555; }
        .guide-content p { margin: 0 0 10px 0; }
        .guide-content ul, .guide-content ol { margin-left: 20px; margin-bottom: 10px; padding-left: 0; }

        /* =================================== */
        /* --- BOTONES MODIFICADOS --- */
        /* =================================== */

        /* Botón de Descarga de PDF (Guías) */
        .pdf-download-link {
            display: inline-block;
            background-color: #047857; /* Tono verde más oscuro */
            color: #ffffff;
            padding: 8px 15px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            margin-top: 10px;
            font-size: 0.9em;
        }
        
        /* Botón de Contrato (Principal) */
        .contract-button {
            display: inline-block;
            background-color: #059669; /* Tono verde principal */
            color: #ffffff;
            padding: 12px 20px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            font-size: 1.1em;
            margin: 20px 0;
            text-align: center;
        }
        
        /* Efecto Hover para ambos botones */
        .pdf-download-link:hover, .contract-button:hover {
            opacity: 0.85;
        }

    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Estado de tu Reserva</h1>
        </div>
        <div class="content">
            <p>Hola <strong>{{ $booking->customer_name }}</strong>,</p>
            <p>Aquí puedes consultar los detalles de tu reserva, descargar tu contrato y ver las guías de uso de tu autocaravana.</p>
            
            <a href="{{ route('booking.contract.download', $booking) }}" class="contract-button" style="display: block;">
                Descargar Contrato (PDF)
            </a>
            
            <table class="details" width="100%">
                <tr>
                    <th>Número de Reserva:</th>
                    <td>#{{ $booking->id }}</td>
                </tr>
                 <tr>
                    <th>Estado de la Reserva:</th>
                    <td style="color: #059669;">{{ ucfirst($booking->status) }}</td>
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
                
                <tr class="price-breakdown">
                    <th class="total-final-label">PRECIO TOTAL:</th>
                    <td class="total-final-value">{{ number_format($booking->total_price, 2) }}€</td>
                </tr>

                @if ($booking->payment_status === 'deposit_paid')
                    <tr class="payment-breakdown">
                        <th style="border-top: 2px solid #ddd; padding-top: 15px;">Pagado (Señal):</th>
                        <td style="border-top: 2px solid #ddd; padding-top: 15px;" class="deposit-paid">{{ number_format($booking->amount_paid, 2) }}€</td>
                    </tr>
                    <tr class="payment-breakdown">
                        <th class="amount-due-label">Pendiente de Pago:</th>
                        <td class="amount-due-value">{{ number_format($booking->amount_due, 2) }}€</td>
                    </tr>
                @else
                    <tr class="payment-breakdown">
                        <th class="full-paid-label" style="border-top: 2px solid #ddd; padding-top: 15px;">Total Pagado (100%):</th>
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

            
            @if ($booking->campervan->guides->isNotEmpty())
                <div class="guides-section">
                    <h2>Guías y Manuales de tu Campervan</h2>
                    <p style="margin-top: 0; margin-bottom: 15px; font-size: 0.95em; color: #555;">
                        Aquí tienes las guías y manuales de uso para tu autocaravana.
                    </p>
                    
                    @foreach ($booking->campervan->guides as $guide)
                        <div class="guide-item">
                            <h3>{{ $guide->title }}</h3>

                            @if ($guide->content)
                                <div class="guide-content">
                                    {!! $guide->content !!}
                                </div>
                            @endif

                            @if ($guide->pdf_path)
                                <a href="{{ asset('storage/' . $guide->pdf_path) }}" 
                                   class="pdf-download-link" 
                                   target="_blank" 
                                   download>
                                    Descargar Manual (PDF)
                                </a>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif

        </div>
    </div>
</body>
</html>