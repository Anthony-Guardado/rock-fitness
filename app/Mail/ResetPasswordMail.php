<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ResetPasswordMail extends Mailable
{
    use Queueable, SerializesModels;

    public $token;
    public $email;
    public $url;

    // Recibimos token y email desde el controlador
    public function __construct($token, $email)
    {
        $this->token = $token;
        $this->email = $email;
        // Lo mandamos al formulario de restablecer contraseña en el servicio de gmail
        $this->url = "http://localhost:5173/restablecer-contrasena?token={$token}&email={$email}";
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Recuperación de Contraseña - GymRook',
        );
    }

    public function content(): Content
    {
        return new Content(
            //Markdown es para mejorar el estilo del boton
            markdown: 'emails.reset_password',
        );
    }
}
