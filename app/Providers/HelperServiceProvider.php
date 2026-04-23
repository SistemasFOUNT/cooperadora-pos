<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Helpers\AssetHelper;

class HelperServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Registrar el helper globalmente
        $this->app->singleton('asset_helper', function ($app) {
            return new AssetHelper();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Cargar el archivo de funciones helper
        require_once app_path('Helpers/functions.php');
    }
}
