<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Laravel\Sanctum\Sanctum;
use App\Models\GaraPersonalAccessToken;

class AppServiceProvider extends ServiceProvider
{
    


    public function register(): void
    {
        
    }

    


    public function boot(): void
    {
        
        if (env('APP_ENV') !== 'local' || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        
        Sanctum::usePersonalAccessTokenModel(GaraPersonalAccessToken::class);
    }
}
