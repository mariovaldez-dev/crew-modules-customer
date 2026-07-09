<?php

namespace App\Infrastructure\Providers;

use App\Domain\Auth\Repositories\IAuthRepository;
use App\Infrastructure\Auth\AuthRepository;

use App\Domain\Shared\Repositories\ICommonRepository;
use App\Infrastructure\Persistence\CommonRepository;
use App\Domain\Maniobra\ManiobraRepositoryInterface;
use App\Infrastructure\Repositories\MockManiobraRepository;
use App\Domain\Cuadrilla\CuadrillaRepositoryInterface;
use App\Infrastructure\Repositories\MockCuadrillaRepository;
use App\Domain\Cuadrilla\TarifaAuditRepositoryInterface;
use App\Infrastructure\Repositories\MockTarifaAuditRepository;
use App\Domain\RegistroManiobra\RegistroManiobraRepositoryInterface;
use App\Infrastructure\Repositories\MockRegistroManiobraRepository;
use App\Domain\Corte\CorteRepositoryInterface;
use App\Infrastructure\Repositories\MockCorteRepository;
use App\Domain\Shared\Repositories\SucursalRepositoryInterface;
use App\Infrastructure\Repositories\SqlServerSucursalRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(IAuthRepository::class, AuthRepository::class);

        $this->app->bind(ICommonRepository::class, CommonRepository::class);
        $this->app->singleton(ManiobraRepositoryInterface::class, \App\Infrastructure\Repositories\SqlServerManiobraRepository::class);
        $this->app->singleton(CuadrillaRepositoryInterface::class, \App\Infrastructure\Repositories\SqlServerCuadrillaRepository::class);
        $this->app->singleton(TarifaAuditRepositoryInterface::class, MockTarifaAuditRepository::class);
        $this->app->singleton(RegistroManiobraRepositoryInterface::class, MockRegistroManiobraRepository::class);
        $this->app->singleton(CorteRepositoryInterface::class, MockCorteRepository::class);
        $this->app->singleton(SucursalRepositoryInterface::class, SqlServerSucursalRepository::class);
    }

    public function boot()
    {
        error_reporting(E_ALL & ~E_DEPRECATED);
    }
}
