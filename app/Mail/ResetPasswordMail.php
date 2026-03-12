<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ResetPasswordMail extends Mailable
{
    // permite que se pueda mandar en segundo plano y que los datos se guarden y se recuperen
    use Queueable, SerializesModels;

    // Esta variable s la que resive el token desde el controlador
    public $token;
    //
    public function __construct($token)
    {
        $this->token = $token;
    }
    //Muestra el servidor de corro que probiene(ej:El proceso que muestra de lo que esta realizando en gmail)
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Recuperación de Contraseña - GymRook',
        );
    }

    // Esta es la vista que crearemos en resources/views y es la que se envia al corrreo
    public function content(): Content
    {
        return new Content(
            view: 'emails.reset_password',
        );
    }
}
