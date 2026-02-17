<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL; // 1. Ajoute cet import crucial

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
        Schema::defaultStringLength(191);

        //Forcer HTTPS uniquement quand l'application est sur Render (production)
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }
    }
}