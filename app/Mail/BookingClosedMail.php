<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BookingClosedMail extends Mailable
{
    use Queueable, SerializesModels;

    public Booking $booking;
    public float $extraKmCharge;
    public int $extraKm;

    public function __construct(Booking $booking, float $extraKmCharge = 0, int $extraKm = 0)
    {
        // ¡CORRECCIÓN! Cargamos la new relación
        $this->booking = $booking->load('campervan', 'inventoryItems'); 
        $this->extraKmCharge = $extraKmCharge;
        $this->extraKm = $extraKm;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Tu reserva #' . $this->booking->id . ' ha finalizado',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.booking-closed',
        );
    }
    
    public function attachments(): array
    {
        return [];
    }
}