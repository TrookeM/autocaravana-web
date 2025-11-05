<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public $booking;

    public function __construct(Booking $booking)
    {
        // ¡CORRECCIÓN! Cargamos la nueva relación
        $this->booking = $booking->load('campervan', 'inventoryItems');
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Recordatorio de pago de tu reserva',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.payment-reminder',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}