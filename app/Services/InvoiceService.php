<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Invoice;
use App\Mail\BookingConfirmed; // Importa tu email
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail; // Importa Mail
use Barryvdh\DomPDF\Facade\Pdf; // Importa la fachada de PDF

class InvoiceService
{
    /**
     * Genera una factura para una reserva, crea el PDF y lo envía por email.
     *
     * @param Booking $booking
     * @return Invoice|null
     */
    public function generate(Booking $booking)
    {
        Log::info("Iniciando generación de factura para Booking ID: {$booking->id}");

        try {
            $invoiceNumber = $this->generateNextInvoiceNumber();

            // Asumimos que total_price es el precio final CON IVA
            $totalInCents = $booking->total_price * 100;
            // Cálculo inverso para 21% de IVA (si el total es 121, el IVA es 21)
            $taxInCents = $totalInCents - ($totalInCents / 1.21); 

            // --- CAMBIO CLAVE 1: Asignar a variable $invoice ---
            // Creamos la factura en la base de datos
            $invoice = Invoice::create([
                'booking_id' => $booking->id,
                'invoice_number' => $invoiceNumber,
                'invoice_date' => now(),
                'total_amount' => $totalInCents,
                'tax_amount' => $taxInCents,
                'customer_details' => [
                    'name' => $booking->customer_name,
                    'email' => $booking->customer_email,
                    'nif' => $booking->customer_nif, // Asegúrate que $booking tiene esta info
                    'address' => $booking->customer_address, // Y esta...
                ],
            ]);

            Log::info("Factura Creada: {$invoice->invoice_number}");

            // --- CAMBIO CLAVE 2: Generar el PDF ---
            // Carga la vista 'pdf.invoice' con los datos necesarios
            $pdf = Pdf::loadView('pdf.invoice', [
                'invoice' => $invoice,
                'booking' => $booking->load('campervan') // Carga la relación
            ]);

            // --- CAMBIO CLAVE 3: Enviar el email con el PDF adjunto ---
            // Usamos el Mailable que modificamos (BookingConfirmed)
            Mail::to($booking->customer_email)->send(
                new BookingConfirmed($booking)
            );
            
            Log::info("Email de confirmación con factura enviado a {$booking->customer_email}");
            
            return $invoice;

        } catch (\Exception $e) {
            Log::error("Error generando factura para Booking ID {$booking->id}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Genera el siguiente número de factura secuencial para el año actual.
     * Ej: "FRA-2025-0001"
     *
     * @return string
     */
    private function generateNextInvoiceNumber(): string
    {
        $year = now()->year;
        $prefix = "FRA-$year-";

        // Busca la última factura de este año
        $lastInvoice = Invoice::where('invoice_number', 'like', "$prefix%")
                              ->orderBy('invoice_number', 'desc')
                              ->first();

        if (!$lastInvoice) {
            // Es la primera factura del año
            return $prefix . '0001';
        }

        // Extrae el número, lo incrementa y lo formatea
        $lastNumber = (int) str_replace($prefix, '', $lastInvoice->invoice_number);
        $nextNumber = $lastNumber + 1;

        return $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }
}
