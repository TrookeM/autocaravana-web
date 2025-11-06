<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Booking;

class ReviewRequestMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * La instancia de la reserva.
     * ¡Debe ser pública para que la vista la herede!
     */
    public $booking;

    /**
     * Create a new message instance.
     */
    public function __construct(Booking $booking)
    {
        $this->booking = $booking->load('campervan');
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '¿Qué tal tu viaje? ¡Valora tu experiencia!',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        // ===================================
        // ¡CAMBIO CLAVE!
        // Dejamos de usar 'markdown:' y usamos 'view:'
        // para que cargue nuestra plantilla HTML personalizada.
        // ===================================
        return new Content(
            view: 'emails.review-request',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}