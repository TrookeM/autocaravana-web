<?php

namespace App\Mail;

use App\Models\Booking; // Importar el modelo Booking
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BookingConfirmed extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * La instancia de la reserva.
     *
     * @var \App\Models\Booking
     */
    public $booking;

    /**
     * Create a new message instance.
     */
    public function __construct(Booking $booking)
    {
        // Almacena la reserva para que esté disponible en la vista
        $this->booking = $booking->load('campervan', 'inventoryItems');
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '¡Tu reserva está confirmada!', // Asunto del email
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        // Apunta a la vista Blade que crearemos a continuación
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
        return [];
    }
}
