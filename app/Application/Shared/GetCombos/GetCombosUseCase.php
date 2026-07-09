<?php

namespace App\Application\Shared\GetCombos;

use App\Domain\Shared\Repositories\ICommonRepository;
use Illuminate\Support\Facades\Log;

class GetCombosUseCase
{
    private ICommonRepository $repository;

    public function __construct(ICommonRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Obtiene las categorías de cultivo.
     */
    public function getCategorias(): array
    {
        Log::channel('daily')->info('Consultando categorías de cultivo via SP');
        return $this->repository->getCategoriasCultivo();
    }

    /**
     * Obtiene los tipos de ciclo de cultivo.
     */
    public function getTiposCiclo(): array
    {
        Log::channel('daily')->info('Consultando tipos de ciclo via SP');
        return $this->repository->getTiposCicloCultivo();
    }

    /**
     * Obtiene las zonas disponibles.
     */
    public function getZonas(): array
    {
        Log::channel('daily')->info('Consultando zonas via SP');
        return $this->repository->getZonas();
    }

    /**
     * Obtiene los territorios para una zona específica.
     */
    public function getTerritorios(string $zonaCodigo): array
    {
        Log::channel('daily')->info("Consultando territorios para la zona $zonaCodigo via SP");
        return $this->repository->getTerritorios($zonaCodigo);
    }

    /**
     * Obtiene el catálogo de productos.
     */
    public function getProductos(): array
    {
        Log::channel('daily')->info('Consultando catálogo de productos via SP');
        return $this->repository->getProductos();
    }
}
