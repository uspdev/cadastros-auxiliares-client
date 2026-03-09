<?php

namespace Uspdev\CadastrosAuxiliaresClient;

use Illuminate\Support\ServiceProvider;
use Uspdev\CadastrosAuxiliaresClient\Contracts\MensagensClientInterface;
use Uspdev\CadastrosAuxiliaresClient\Services\MensagensClient;

class CadastrosAuxiliaresClientServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/cadastros-auxiliares-client.php',
            'cadastros-auxiliares-client'
        );

        $this->app->singleton(MensagensClientInterface::class, MensagensClient::class);
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/cadastros-auxiliares-client.php' => config_path('cadastros-auxiliares-client.php'),
        ], 'cadastros-auxiliares-client-config');
    }
}
