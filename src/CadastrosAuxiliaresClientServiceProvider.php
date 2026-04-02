<?php

namespace Uspdev\CadastrosAuxiliaresClient;

use Illuminate\Support\ServiceProvider;
use Uspdev\CadastrosAuxiliaresClient\Contracts\CursosGraduacaoClientInterface;
use Uspdev\CadastrosAuxiliaresClient\Contracts\MensagensClientInterface;
use Uspdev\CadastrosAuxiliaresClient\Contracts\ProgramasClientInterface;
use Uspdev\CadastrosAuxiliaresClient\Services\CursosGraduacaoClient;
use Uspdev\CadastrosAuxiliaresClient\Services\MensagensClient;
use Uspdev\CadastrosAuxiliaresClient\Services\ProgramasClient;

class CadastrosAuxiliaresClientServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/cadastros-auxiliares-client.php',
            'cadastros-auxiliares-client'
        );

        $this->app->singleton(MensagensClientInterface::class, MensagensClient::class);
        $this->app->singleton(ProgramasClientInterface::class, ProgramasClient::class);
        $this->app->singleton(CursosGraduacaoClientInterface::class, CursosGraduacaoClient::class);
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/cadastros-auxiliares-client.php' => config_path('cadastros-auxiliares-client.php'),
        ], 'cadastros-auxiliares-client-config');
    }
}
