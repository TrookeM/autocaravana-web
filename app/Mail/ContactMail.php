<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Los datos del formulario de contacto (name, email, phone, message).
     *
     * @var array
     */
    public $data;

    /**
     * Crea una nueva instancia del mensaje.
     *
     * @param array $data
     * @return void
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Obtiene el envoltorio del mensaje.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            // El correo se enviará desde la dirección del cliente (para que puedas responder fácilmente)
            replyTo: $this->data['email'], 
            subject: 'Nueva Consulta Web de: ' . $this->data['name'],
        );
    }

    /**
     * Obtiene la definición del contenido del mensaje.
     *
     * @return \Illuminate\Mail\Mailables\Content
     */
    public function content(): Content
    {
        return new Content(
            // CAMBIO CLAVE: Usamos 'view' para renderizar el HTML completo
            view: 'emails.contact_email',
            
            // Se pasa el array $data a la vista del email
            with: ['data' => $this->data], 
        );
    }
    
    // Si usas adjuntos, la función attachments() iría aquí
    public function attachments(): array
    {
        return [];
    }
}
