<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Services\PriceCalculatorService; // Tu import existente

// --- IMPORTACIONES AÑADIDAS ---
use Illuminate\Mail\Mailables\Attachment;
use Barryvdh\DomPDF\Facade\Pdf; // Generar PDF bajo demanda
// ------------------------------

class BookingConfirmed extends Mailable
{
    use Queueable, SerializesModels;

    public $booking;
    
    // --- Tus variables existentes (se conservan) ---
    public $base_seasonal_price;
    public $extras_price;
    public $duration_discount_amount;
    public $coupon_discount_amount;
    // ---------------------------------------------

    // --- NUEVA PROPIEDAD ---
    public $pdfContent;
    // -----------------------

    /**
     * Create a new message instance.
     *
     * @param Booking $booking
     * @param PDF $pdf  <-- AÑADIDO
     */
    public function __construct(Booking $booking, ?string $pdfContent = null)
    {
        // --- LÓGICA MODIFICADA ---
        $this->booking = $booking->load('campervan.guides', 'inventoryItems', 'invoice');
        $this->pdfContent = $pdfContent; // Contenido serializable del PDF
        // -------------------------

        // ==========================================================
        // (RF12.2) ESTA ES TU LÓGICA DE PRECIOS. SE QUEDA EXACTAMENTE IGUAL.
        // ==========================================================
        $priceCalculator = app(PriceCalculatorService::class);
        
        $priceBreakdown = $priceCalculator->getPriceBreakdown(
            $this->booking->campervan, 
            $this->booking->start_date, 
            $this->booking->end_date
        );
        
        $this->extras_price = $this->booking->inventoryItems->sum('pivot.precio_cobrado');
        $this->base_seasonal_price = $this->booking->original_price - $this->extras_price;
        $this->duration_discount_amount = $priceBreakdown['duration_discount_amount'];
        
        // Pequeña mejora: usamos ?? 0 por si discount_amount fuera null
        $this->coupon_discount_amount = ($this->booking->discount_amount ?? 0) - $this->duration_discount_amount;
        // ==========================================================
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '¡Tu reserva está confirmada! (Factura adjunta)', // <-- Asunto actualizado
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        // Esto no cambia, sigue usando tu vista de email
        return new Content(
            view: 'emails.booking-confirmed',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        // --- LÓGICA DE ADJUNTOS AÑADIDA ---
        // (Aseguramos que 'invoice' se cargó en el constructor)
        $invoiceNumber = $this->booking->invoice->invoice_number ?? 'Factura';

        if (!empty($this->pdfContent)) {
            return [
                Attachment::fromData(fn () => $this->pdfContent, "Factura-{$invoiceNumber}.pdf")
                    ->withMime('application/pdf'),
            ];
        }

        return [
            Attachment::fromData(function () use ($invoiceNumber) {
                if ($this->booking->invoice) {
                    return Pdf::loadView('pdf.invoice', [
                        'invoice' => $this->booking->invoice,
                        'booking' => $this->booking,
                    ])->output();
                }

                $priceCalculator = app(\App\Services\PriceCalculatorService::class);
                $priceBreakdown = $priceCalculator->getPriceBreakdown(
                    $this->booking->campervan,
                    $this->booking->start_date,
                    $this->booking->end_date
                );
                $extrasPrice = $this->booking->inventoryItems->sum('pivot.precio_cobrado');
                $baseSeasonalPrice = $this->booking->original_price - $extrasPrice;
                $durationDiscountAmount = $priceBreakdown['duration_discount_amount'];
                $couponDiscountAmount = ($this->booking->discount_amount ?? 0) - $durationDiscountAmount;

                return Pdf::loadView('pdf.contract', [
                    'booking' => $this->booking,
                    'base_seasonal_price' => $baseSeasonalPrice,
                    'extras_price' => $extrasPrice,
                    'duration_discount_amount' => $durationDiscountAmount,
                    'coupon_discount_amount' => $couponDiscountAmount,
                ])->output();
            }, "Factura-{$invoiceNumber}.pdf")->withMime('application/pdf'),
        ];
    }
}
