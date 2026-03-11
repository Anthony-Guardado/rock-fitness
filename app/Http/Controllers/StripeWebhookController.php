<?php

namespace App\Http\Controllers;

use App\Models\Detalle_Membresia;
use App\Models\Pago;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;

class StripeWebhookController extends Controller
{
    public function handle(Request $request): Response // El handle es el que recibe el webhook de stripe
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');

        // Verificamos que el evento realmente viene de Stripe
       
        try {
            $event = Webhook::constructEvent(
                $payload,
                $sigHeader,
                config('services.stripe.webhook_secret')
            );
        } catch (SignatureVerificationException $e) {
            return response('Firma inválida', 400);
        }

//Estos son los eventos que revisara el stripe que procesara el webhook
        match ($event->type) {
            'payment_intent.succeeded' => $this->pagoExitoso($event->data->object),
            'payment_intent.payment_failed' => $this->pagoFallido($event->data->object),
            default => null, // cualquier otro evento lo ignoramos
        };

        // Le respondemos 200 a Stripe para confirmar que recibimos el evento

        return response('OK', 200);
    }

    private function pagoExitoso(object $paymentIntent): void
    {
        // Buscamos el pago en nuestra data base usando el stripe_payment_intent_id
        $pago = Pago::where('stripe_payment_intent_id', $paymentIntent->id)->first();
        if (! $pago) {
            return;
        }

        $pago->update(['estado' => 'pagado']);

        Detalle_Membresia::where('id', $pago->detalle_membresia_id)
            ->update(['estado' => 'activo']);
    }

    private function pagoFallido(object $paymentIntent): void
    {
        // Si el pago falló solo actualizamos el estado del pago
        Pago::where('stripe_payment_intent_id', $paymentIntent->id)
            ->update(['estado' => 'fallido']);
    }
}
