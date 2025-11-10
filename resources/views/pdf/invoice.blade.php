<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Factura {{ $invoice->invoice_number }}</title>
    <style>
        /* Añade tu CSS aquí */
        body { font-family: sans-serif; }
        .header { text-align: right; }
        .details { margin-top: 30px; }
        .items { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .items th, .items td { border: 1px solid #ccc; padding: 8px; }
        .total { text-align: right; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="header">
        <h2>FACTURA</h2>
        <p><strong>Número:</strong> {{ $invoice->invoice_number }}</p>
        <p><strong>Fecha:</strong> {{ $invoice->invoice_date->format('d/m/Y') }}</p>
    </div>

    <div class="details">
        <h4>Datos del Cliente</h4>
        <p>{{ $invoice->customer_details['name'] }}</p>
        <p>{{ $invoice->customer_details['nif'] ?? 'NIF no especificado' }}</p>
        <p>{{ $invoice->customer_details['address'] ?? 'Dirección no especificada' }}</p>
    </div>

    <table class="items">
        <thead>
            <tr>
                <th>Concepto</th>
                <th>Precio</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Alquiler Autocaravana: {{ $booking->campervan->name }}
                    <br>
                    <small>Desde: {{ $booking->start_date->format('d/m/Y') }}</small>
                    <small>Hasta: {{ $booking->end_date->format('d/m/Y') }}</small>
                </td>
                <td>{{ number_format(($invoice->total_amount - $invoice->tax_amount) / 100, 2) }} €</td>
            </tr>
        </tbody>
    </table>

    <div class="total">
        <p><strong>Base Imponible:</strong> {{ number_format(($invoice->total_amount - $invoice->tax_amount) / 100, 2) }} €</p>
        <p><strong>IVA (21%):</strong> {{ number_format($invoice->tax_amount / 100, 2) }} €</p>
        <h3><strong>Total:</strong> {{ number_format($invoice->total_amount / 100, 2) }} €</h3>
    </div>
</body>
</html>