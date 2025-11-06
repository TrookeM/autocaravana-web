<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Services\PriceCalculatorService; // <-- AÑADIDO

class BookingConfirmed extends Mailable
{
    use Queueable, SerializesModels;

    public $booking;
    
    // --- VARIABLES NUEVAS ---
    public $base_seasonal_price;
    public $extras_price;
    public $duration_discount_amount;
    public $coupon_discount_amount;
    // ------------------------

    public function __construct(Booking $booking)
    {
        $this->booking = $booking->load('campervan.guides', 'inventoryItems');

        // ==========================================================
        // RECALCULAMOS EL DESGLOSE PARA EL EMAIL (RF12.2)
        // ==========================================================
        // Usamos app() para sacar el servicio del contenedor
        $priceCalculator = app(PriceCalculatorService::class);
        
        $priceBreakdown = $priceCalculator->getPriceBreakdown(
            $this->booking->campervan, 
            $this->booking->start_date, 
            $this->booking->end_date
        );
        
        $this->extras_price = $this->booking->inventoryItems->sum('pivot.precio_cobrado');
        $this->base_seasonal_price = $this->booking->original_price - $this->extras_price;
        $this->duration_discount_amount = $priceBreakdown['duration_discount_amount'];
        $this->coupon_discount_amount = $this->booking->discount_amount - $this->duration_discount_amount;
        // ==========================================================
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '¡Tu reserva está confirmada!',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.booking-confirmed',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}