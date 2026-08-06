<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{

    public function register(): void
    {
        //
    }


    public function boot(): void
    {
        if (env('APP_ENV') === 'production') {
            URL::forceScheme('https');
        }
    }

    protected function authenticated(Request $request, $user)
    {
        if ($user->isCajero()) {
            return redirect()->route('puntoventa.index'); // El cajero va directo a cobrar
        }
        return redirect()->route('dashboard'); // El gerente/admin va al dashboard
    }
}
