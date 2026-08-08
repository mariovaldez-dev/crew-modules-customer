<?php

namespace App\UI\Livewire\Cuadrillas;

use App\Domain\Cuadrilla\DeleteCuadrillaUseCase;
use App\Domain\Cuadrilla\ListCuadrillasUseCase;
use App\Domain\Shared\Repositories\SucursalRepositoryInterface;
use App\UI\Livewire\Traits\WithTableFilters;
use App\UI\Livewire\Traits\WithZonaScope;
use Exception;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithPagination;

class CuadrillaIndex extends Component
{
    use WithTableFilters, WithZonaScope, WithPagination;

    public ?int $cuadrillaToDelete = null;
    public bool $readyToLoad = false;

    /**
     * Todos los registros como arrays planos [id, nombre, lider, miembros, puntoVentaId, zona, tarifas].
     * Se usa array (no DTO) porque Livewire debe poder serializar el estado en el snapshot JS.
     */
    public array $cuadrillasAll = [];

    /** Catálogo de PV [codigo => nombre] */
    public array $puntosVenta = [];

    /** Catálogo de Lideres [codigo => nombre] */
    public array $lideres = [];

    /** Mensaje de error al eliminar cuadrilla */
    public string $deleteError = '';

    protected $listeners = [];

    public function mount(): void
    {
        $this->filters = [
            'puntoVentaId' => '',
            'lider'        => '',
        ];
    }

    /**
     * Se ejecuta una sola vez vía wire:init.
     * Carga TODOS los datos de cuadrillas en memoria.
     * Los filtros se aplican en render() sin volver a la BD.
     */
    public function loadData(ListCuadrillasUseCase $useCase, SucursalRepositoryInterface $sucursalRepo): void
    {
        session()->save(); // Libera el bloqueo de sesión para permitir navegación concurrente
        // Convertir DTOs a arrays planos — Livewire no puede serializar objetos custom en snapshot
        $this->cuadrillasAll = array_map(
            fn($dto) => $dto->toArray(),
            $useCase->execute([], $this->zonaUsuario)
        );
        $this->puntosVenta = $sucursalRepo->listaPuntosDeVentaPorZona($this->zonaUsuario);
        $this->lideres = $sucursalRepo->listaLideresPorZona($this->zonaUsuario);
        $this->readyToLoad = true;

        Log::debug('[CuadrillaIndex::loadData]', [
            'zona'            => $this->zonaUsuario,
            'total_cargadas'  => count($this->cuadrillasAll),
        ]);
    }

    public function refreshData(ListCuadrillasUseCase $useCase, SucursalRepositoryInterface $sucursalRepo): void
    {
        Log::debug('[CuadrillaIndex::refreshData] Iniciando recarga de datos tras evento');
        $this->cuadrillasAll = array_map(
            fn($dto) => $dto->toArray(),
            $useCase->execute([], $this->zonaUsuario)
        );
        $this->puntosVenta = $sucursalRepo->listaPuntosDeVentaPorZona($this->zonaUsuario);
        $this->lideres = $sucursalRepo->listaLideresPorZona($this->zonaUsuario);
    }

    public function confirmDelete(int $id): void
    {
        if ($this->rolUsuario !== 'CO') {
            abort(403, 'No tienes permisos para realizar esta acción.');
        }
        $this->deleteError = '';
        $this->cuadrillaToDelete = $id;
        $this->dispatch('open-modal', 'confirm-delete-cuadrilla');
    }

    public function delete(DeleteCuadrillaUseCase $deleteUseCase): void
    {
        if ($this->rolUsuario !== 'CO') {
            abort(403, 'No tienes permisos para realizar esta acción.');
        }
        if (!$this->cuadrillaToDelete) return;

        $this->deleteError = '';

        try {
            $deleteUseCase->execute($this->cuadrillaToDelete);

            // Recargar cuadrillas desde base de datos tras borrado exitoso
            $this->refreshData(
                app(ListCuadrillasUseCase::class),
                app(SucursalRepositoryInterface::class)
            );

            $this->dispatch('notify', ['message' => 'Cuadrilla eliminada correctamente', 'type' => 'success']);
            
            $this->cuadrillaToDelete = null;
            $this->dispatch('close-modal', 'confirm-delete-cuadrilla');
        } catch (Exception $e) {
            $this->deleteError = $e->getMessage();
            Log::error('[CuadrillaIndex::delete] Error al borrar cuadrilla', [
                'id'    => $this->cuadrillaToDelete,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Aplica filtros en memoria sobre $cuadrillasAll (arrays planos).
     * No toca la BD — es PHP puro, ~5ms.
     */
    private function applyFilters(): array
    {
        $search   = mb_strtolower(trim($this->search ?? ''));
        $pvFiltro = trim($this->filters['puntoVentaId'] ?? '');
        $liderFiltro = trim($this->filters['lider'] ?? '');

        return array_values(array_filter(
            $this->cuadrillasAll,
            function (array $c) use ($search, $pvFiltro, $liderFiltro): bool {
                if ($search !== '' &&
                    mb_strpos(mb_strtolower($c['nombre']), $search) === false &&
                    mb_strpos(mb_strtolower($c['lider']),  $search) === false
                ) {
                    return false;
                }

                if ($pvFiltro !== '' && ($c['puntoVentaId'] ?? '') !== $pvFiltro) {
                    return false;
                }

                if ($liderFiltro !== '' && ($c['lider'] ?? '') !== $liderFiltro) {
                    return false;
                }

                return true;
            }
        ));
    }

    public function render(): \Illuminate\View\View
    {
        $currentPage = $this->paginators['page'] ?? 1;
        $perPage = 10;
        $items = $this->applyFilters();

        $paginated = new LengthAwarePaginator(
            array_slice($items, ($currentPage - 1) * $perPage, $perPage),
            count($items),
            $perPage,
            $currentPage,
            ['path' => LengthAwarePaginator::resolveCurrentPath()]
        );

        return view('livewire.cuadrillas.cuadrilla-index', [
            'cuadrillas'  => $paginated,
            'puntosVenta' => $this->puntosVenta,
            'lideres'     => $this->lideres,
        ])->layout('layouts.app', ['title' => 'Cuadrillas']);
    }
}
