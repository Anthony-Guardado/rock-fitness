<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Auth\Notifications\ResetPassword;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        ResetPassword::createUrlUsing(function (object $notifiable, string $token) {
        //  Redirecionamiento asi la ruta restablecer contraseña del frontend
        return "http://localhost:5173/restablecer-contrasena?token={$token}&email={$notifiable->getEmailForPasswordReset()}";
    });
    }
}
