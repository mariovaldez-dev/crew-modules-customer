<?php

namespace App\UI\Livewire\Tarifas;

use App\Domain\Tarifa\ListTarifasUseCase;
use App\UI\Livewire\Traits\WithZonaScope;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class TarifasIndex extends Component
{
    use WithZonaScope;

    public function render(ListTarifasUseCase $useCase)
    {
        $esAdministrador = $this->rolUsuario === 'AM';
        
        $data = $useCase->execute($this->zonaUsuario, $esAdministrador);

        return view('livewire.tarifas.tarifas-index', [
            'esAdministrador' => $esAdministrador,
            'data' => $data, // Si es AM es array agrupado, si es CO es array plano
            'puntosVenta' => [
                101 => 'PV Norte Principal',
                102 => 'PV Sur Auxiliar'
            ]
        ])->layout('layouts.app', ['title' => 'Tarifas por Cuadrilla']);
    }
}
