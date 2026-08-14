<?php

namespace App\UI\Livewire\RegistroManiobras;

use App\Domain\RegistroManiobra\ExportManiobrasUseCase;
use App\Domain\RegistroManiobra\ListRegistroManiobrasUseCase;
use App\UI\Livewire\Traits\WithTableFilters;
use App\UI\Livewire\Traits\WithZonaScope;
use Illuminate\Support\Facades\Auth;
use Exception;
use Livewire\Component;
use Livewire\WithPagination;

class RegistroIndex extends Component
{
    use WithTableFilters, WithZonaScope, WithPagination;

    public bool $readyToLoad = false;
    public array $maniobrasAll = [];

    protected $listeners = [
        'maniobra-registrada' => 'refreshData'
    ];

    public function mount()
    {
        $this->filters = [
            'fechaInicio' => date('Y-m-d'),
            'fechaFin' => date('Y-m-d'),
            'almacenId' => '',
            'estado' => '',
        ];
    }

    public function loadData(ListRegistroManiobrasUseCase $useCase)
    {
        session()->save(); // Libera el bloqueo de sesión para permitir navegación concurrente
        // Traer la data pasando las fechas al SP
        $filtrosFechas = [
            'fechaInicio' => $this->filters['fechaInicio'] ?? null,
            'fechaFin' => $this->filters['fechaFin'] ?? null,
        ];
        $dtos = $useCase->execute($filtrosFechas, $this->zonaUsuario, $this->rolUsuario);
        $this->maniobrasAll = array_map(fn($dto) => $dto->toArray(), $dtos);
        
        $this->readyToLoad = true;
    }

    public function updatedFilters($value, $key)
    {
        if ($key === 'fechaInicio' || $key === 'fechaFin') {
            if (!empty($this->filters['fechaFin']) && !empty($this->filters['fechaInicio'])) {
                if ($this->filters['fechaFin'] < $this->filters['fechaInicio']) {
                    $this->filters['fechaFin'] = $this->filters['fechaInicio'];
                }
            }
            $this->loadData(app(ListRegistroManiobrasUseCase::class));
        }
    }

    public function refreshData(ListRegistroManiobrasUseCase $useCase)
    {
        $filtrosFechas = [
            'fechaInicio' => $this->filters['fechaInicio'] ?? null,
            'fechaFin' => $this->filters['fechaFin'] ?? null,
        ];
        $dtos = $useCase->execute($filtrosFechas, $this->zonaUsuario, $this->rolUsuario);
        $this->maniobrasAll = array_map(fn($dto) => $dto->toArray(), $dtos);
    }

    public function export(ExportManiobrasUseCase $exportUseCase)
    {
        try {
            $filtros = array_merge(['search' => $this->search], $this->filters);
            
            // Reutilizamos el filtro local para que el Excel exporte exactamente lo de la tabla
            $itemsFiltrados = $this->applyFilters();
            $dtos = array_map(function($item) {
                $item['fecha'] = new \DateTimeImmutable($item['fecha']);
                return new \App\Domain\RegistroManiobra\RegistroManiobraDTO(...$item);
            }, $itemsFiltrados);

            return $exportUseCase->executeConDatos($dtos, $filtros);
        } catch (Exception $e) {
            $this->dispatch('notify', ['message' => $e->getMessage(), 'type' => 'error']);
        }
    }

    public function resetCustomFilters()
    {
        $this->resetFilters();
        $this->filters = [
            'fechaInicio' => date('Y-m-d'),
            'fechaFin' => date('Y-m-d'),
            'almacenId' => '',
            'estado' => '',
        ];
    }

    private function applyFilters(): array
    {
        $search = mb_strtolower(trim($this->search ?? ''));
        $almacenFiltro = trim($this->filters['almacenId'] ?? '');
        $estadoFiltro = trim($this->filters['estado'] ?? '');

        return array_values(array_filter(
            $this->maniobrasAll,
            function (array $m) use ($search, $almacenFiltro, $estadoFiltro): bool {
                // Filtro búsqueda
                if ($search !== '') {
                    $match = false;
                    if (mb_strpos(mb_strtolower($m['tipoManiobraNombre']), $search) !== false) $match = true;
                    if (mb_strpos(mb_strtolower($m['cuadrillaNombre']), $search) !== false) $match = true;
                    if (mb_strpos(mb_strtolower($m['folio']), $search) !== false) $match = true;
                    if (!$match) return false;
                }

                // (Las fechas ya vienen filtradas por el SP)

                // Filtro almacén
                if ($almacenFiltro !== '' && (string) $m['almacenId'] !== $almacenFiltro) {
                    return false;
                }

                // Filtro estado
                if ($estadoFiltro !== '' && mb_strtolower($m['estado']) !== mb_strtolower($estadoFiltro)) {
                    return false;
                }

                return true;
            }
        ));
    }

    public function render()
    {
        // Validar que fecha fin >= fecha inicio
        if ($this->filters['fechaFin'] < $this->filters['fechaInicio']) {
            $this->filters['fechaFin'] = $this->filters['fechaInicio'];
        }

        $currentPage = $this->paginators['page'] ?? 1;
        $perPage = 10;
        
        $items = $this->readyToLoad ? $this->applyFilters() : [];
        
        $paginated = new \Illuminate\Pagination\LengthAwarePaginator(
            array_slice($items, ($currentPage - 1) * $perPage, $perPage),
            count($items),
            $perPage,
            $currentPage,
            ['path' => \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPath()]
        );
        
        // Convertir de array plano a objetos genéricos y parsear fecha
        $maniobras = array_map(function($item) {
            $obj = (object) $item;
            $obj->fecha = new \DateTimeImmutable($obj->fecha);
            $obj->estatusCiclo = (int) ($item['estatusCiclo'] ?? 0);
            $obj->folioCorte = $item['folioCorte'] ?? null;
            return $obj;
        }, $paginated->items());
        
        $paginated->setCollection(collect($maniobras));
        
        $sucursalRepo = app(\App\Domain\Shared\Repositories\SucursalRepositoryInterface::class);
        $zonaFiltro = ($this->rolUsuario === 'AM') ? 'TODAS' : $this->zonaUsuario;

        $almacenes = $this->readyToLoad
            ? $sucursalRepo->listaPuntosDeVentaPorZona($zonaFiltro)
            : [];

        return view('livewire.registro-maniobras.registro-index', [
            'maniobras' => $paginated,
            'almacenes' => $almacenes
        ])->layout('layouts.app', ['title' => 'Registro de Maniobras']);
    }
}
