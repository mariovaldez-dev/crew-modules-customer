<?php

namespace App\UI\Livewire\RegistroManiobras;

use App\Domain\RegistroManiobra\ExportManiobrasUseCase;
use App\Domain\RegistroManiobra\ListRegistroManiobrasUseCase;
use App\UI\Livewire\Traits\WithTableFilters;
use App\UI\Livewire\Traits\WithZonaScope;
use Illuminate\Support\Facades\Auth;
use Exception;
use Livewire\Component;

class RegistroIndex extends Component
{
    use WithTableFilters, WithZonaScope;

    public function mount()
    {
        $this->filters = [
            'fechaInicio' => date('Y-m-d'),
            'fechaFin' => date('Y-m-d'),
            'almacenId' => '',
            'estado' => '',
        ];
    }

    public function export(ExportManiobrasUseCase $exportUseCase)
    {
        try {
            $filtros = array_merge(['search' => $this->search], $this->filters);
            $path = $exportUseCase->execute($filtros, $this->zonaUsuario, $this->rolUsuario);
            
            $this->dispatch('notify', ['message' => 'Exportación exitosa (Simulada)', 'type' => 'success']);
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

    public function render(ListRegistroManiobrasUseCase $useCase)
    {
        // Validar que fecha fin >= fecha inicio
        if ($this->filters['fechaFin'] < $this->filters['fechaInicio']) {
            $this->filters['fechaFin'] = $this->filters['fechaInicio'];
        }

        $filtros = array_merge(['search' => $this->search], $this->filters);
        
        $maniobras = $useCase->execute($filtros, $this->zonaUsuario, $this->rolUsuario);

        return view('livewire.registro-maniobras.registro-index', [
            'maniobras' => $maniobras,
            'almacenes' => [
                101 => 'Almacén Norte',
                102 => 'Almacén Sur'
            ]
        ])->layout('layouts.app', ['title' => 'Registro de Maniobras']);
    }
}
