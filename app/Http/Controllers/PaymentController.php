<?php

namespace App\Http\Controllers;

use App\Models\Pago;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Stripe\PaymentIntent;
use Stripe\Stripe;

class PaymentController extends Controller
{
    public function __construct()
    {
        // Con esta funcion constructura le decimos a stripe que cada vez
        // ejecute este controlador usara la clave secreta que guardamos en .env de stripe
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    public function crearPaymentIntent(Request $request): JsonResponse
    {
        $request->validate([
            'pago_id' => 'required|exists:pagos,id',
        ]);

        $pago = Pago::findOrFail($request->pago_id);

        $paymentIntent = PaymentIntent::create([
            'amount' => (int) ($pago->monto * 100), // esto equivale a 100 centavos, ya que stripe no trtabaja con dolares
            'currency' => 'usd',
            'metadata' => [
                // Guardamos el pago_id para encontrarlo después en el webhook
                'pago_id' => $pago->id,
            ],
        ]);
        // Guardamos el id del PaymentIntent en nuestra database
        // Esto conecta nuestro pago local con el pago en Stripe
        $pago->update([
            'stripe_payment_intent_id' => $paymentIntent->id,
        ]);

        // El frontend lo necesita para mostrar el formulario de tarjeta
        return response()->json([
            'client_secret' => $paymentIntent->client_secret, // le mandamos el client secret al froted
        ]);

    }
}
