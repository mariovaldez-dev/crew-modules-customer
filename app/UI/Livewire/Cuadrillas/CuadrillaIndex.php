<?php

namespace App\UI\Livewire\Cuadrillas;

use App\Domain\Cuadrilla\DeleteCuadrillaUseCase;
use App\Domain\Cuadrilla\ListCuadrillasUseCase;
use App\UI\Livewire\Traits\WithTableFilters;
use App\UI\Livewire\Traits\WithZonaScope;
use Exception;
use Livewire\Component;

class CuadrillaIndex extends Component
{
    use WithTableFilters, WithZonaScope;

    public ?int $cuadrillaToDelete = null;

    protected $listeners = [
        'cuadrilla-saved' => '$refresh',
    ];

    public function mount()
    {
        $this->filters = [
            'puntoVentaId' => '',
            'lider' => '',
        ];
    }

    public function confirmDelete(int $id)
    {
        if ($this->rolUsuario !== 'CO') {
            abort(403, 'No tienes permisos para realizar esta acción.');
        }
        $this->cuadrillaToDelete = $id;
        $this->dispatch('open-modal', 'confirm-delete-cuadrilla');
    }

    public function delete(DeleteCuadrillaUseCase $deleteUseCase)
    {
        if ($this->rolUsuario !== 'CO') {
            abort(403, 'No tienes permisos para realizar esta acción.');
        }
        if (!$this->cuadrillaToDelete) return;

        try {
            $deleteUseCase->execute($this->cuadrillaToDelete);
            $this->dispatch('notify', ['message' => 'Cuadrilla eliminada correctamente', 'type' => 'success']);
        } catch (Exception $e) {
            $this->dispatch('notify', ['message' => $e->getMessage(), 'type' => 'error']);
        }

        $this->cuadrillaToDelete = null;
        $this->dispatch('close-modal', 'confirm-delete-cuadrilla');
    }

    public function render(ListCuadrillasUseCase $useCase)
    {
        $filtros = array_merge(['search' => $this->search], $this->filters);
        
        $cuadrillas = $useCase->execute($filtros, $this->zonaUsuario);

        return view('livewire.cuadrillas.cuadrilla-index', [
            'cuadrillas' => $cuadrillas,
            'puntosVenta' => [
                101 => 'PV Norte Principal',
                102 => 'PV Sur Auxiliar'
            ] // Mock temporal de PVs
        ])->layout('layouts.app', ['title' => 'Gestión de Cuadrillas']);
    }
}
