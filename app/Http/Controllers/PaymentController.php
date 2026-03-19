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

        Stripe::setApiKey(config('services.stripe.secret'));
    }

    public function crearPaymentIntent(Request $request): JsonResponse
    {
        $request->validate([
            'pago_id' => 'required|exists:pagos,id',
        ]);

        $pago = Pago::findOrFail($request->pago_id);

        $paymentIntent = PaymentIntent::create([
            'amount' => (int) ($pago->monto * 100),
            'currency' => 'usd',
            'metadata' => [

                'pago_id' => $pago->id,
            ],
        ]);
        
        $pago->update([
            'stripe_payment_intent_id' => $paymentIntent->id,
        ]);

        // El frontend lo necesita para mostrar el formulario de tarjeta
        return response()->json([
            'client_secret' => $paymentIntent->client_secret, // le mandamos el client secret al froted
        ]);

    }
}
