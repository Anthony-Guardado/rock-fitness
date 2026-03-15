@component('mail::message')
# Hola, GymRook

Has solicitado restablecer tu contraseña. Haz clic en el botón de abajo para continuar:

@component('mail::button', ['url' => $url])
Restablecer Contraseña
@endcomponent

Si no solicitaste este cambio, puedes ignorar este correo.

Gracias,<br>
{{ config('app.name') }}
@endcomponent
