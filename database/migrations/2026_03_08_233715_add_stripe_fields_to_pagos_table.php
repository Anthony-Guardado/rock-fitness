<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pagos', function (Blueprint $table) {
            $table->string('stripe_payment_intent_id')->nullable()->after('referencia');
            $table->enum('estado', ['pendiente', 'pagado', 'fallido'])
                  ->default('pendiente')
                  ->after('stripe_payment_intent_id');
        });
    }

    public function down(): void
    {
        Schema::table('pagos', function (Blueprint $table) {
            $table->dropColumn(['stripe_payment_intent_id', 'estado']);
        });
    }
    
};
