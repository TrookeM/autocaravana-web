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

    /**
     * Create a new message instance.
     */
    public function __construct(Booking $booking, float $extraKmCharge = 0, int $extraKm = 0)
    {
        $this->booking = $booking;
        $this->extraKmCharge = $extraKmCharge;
        $this->extraKm = $extraKm;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Tu reserva #' . $this->booking->id . ' ha finalizado',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.booking-closed',
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        return [];
    }
}