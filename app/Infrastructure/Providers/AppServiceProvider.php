<?php

namespace App\Infrastructure\Providers;

use App\Domain\Auth\Repositories\IAuthRepository;
use App\Domain\Corte\CorteRepositoryInterface;
use App\Domain\Cuadrilla\CuadrillaRepositoryInterface;
use App\Domain\Cuadrilla\TarifaAuditRepositoryInterface;
use App\Domain\Dashboard\DashboardRepositoryInterface;
use App\Domain\Maniobra\ManiobraRepositoryInterface;
use App\Domain\RegistroManiobra\RegistroManiobraRepositoryInterface;
use App\Domain\Shared\Repositories\ICommonRepository;
use App\Domain\Shared\Repositories\SucursalRepositoryInterface;
use App\Domain\Tarifa\TarifaRepositoryInterface;

use App\Infrastructure\Auth\AuthRepository;
use App\Infrastructure\Persistence\CommonRepository;
use App\Infrastructure\Repositories\CorteRepository;
use App\Infrastructure\Repositories\CuadrillaRepository;
use App\Infrastructure\Repositories\DashboardRepository;
use App\Infrastructure\Repositories\ManiobraRepository;
use App\Infrastructure\Repositories\RegistroManiobraRepository;
use App\Infrastructure\Repositories\SucursalRepository;
use App\Infrastructure\Repositories\TarifaAuditRepository;
use App\Infrastructure\Repositories\TarifaRepository;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(IAuthRepository::class, AuthRepository::class);
        $this->app->bind(ICommonRepository::class, CommonRepository::class);

        $this->app->singleton(ManiobraRepositoryInterface::class, ManiobraRepository::class);
        $this->app->singleton(CuadrillaRepositoryInterface::class, CuadrillaRepository::class);
        $this->app->singleton(TarifaAuditRepositoryInterface::class, TarifaAuditRepository::class);
        $this->app->singleton(RegistroManiobraRepositoryInterface::class, RegistroManiobraRepository::class);
        $this->app->singleton(CorteRepositoryInterface::class, CorteRepository::class);
        $this->app->singleton(SucursalRepositoryInterface::class, SucursalRepository::class);
        $this->app->singleton(TarifaRepositoryInterface::class, TarifaRepository::class);
        $this->app->singleton(DashboardRepositoryInterface::class, DashboardRepository::class);
    }

    public function boot()
    {
        error_reporting(E_ALL & ~E_DEPRECATED);
    }
}
