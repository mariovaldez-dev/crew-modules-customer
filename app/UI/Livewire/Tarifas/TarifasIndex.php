<?php

namespace App\UI\Livewire\Tarifas;

use App\Domain\Tarifa\ListTarifasUseCase;
use App\UI\Livewire\Traits\WithZonaScope;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class TarifasIndex extends Component
{
    use WithZonaScope;

    public bool $readyToLoad = false;

    public function loadData()
    {
        $this->readyToLoad = true;
    }

    public function render(ListTarifasUseCase $useCase)
    {
        if ($this->readyToLoad) {
            session()->save(); // Libera el bloqueo de sesión para navegación concurrente
        }

        $esAdministrador = $this->rolUsuario === 'AM';
        
        $nombreZonaCO = '';
        $claveZonaCO = '';

        if ($esAdministrador) {
            $data = $this->readyToLoad ? $useCase->execute($this->zonaUsuario, true) : [];
            $cuadrillasArray = empty($data) ? [] : array_merge(...array_values($data));
        } else {
            $res = $this->readyToLoad ? $useCase->execute($this->zonaUsuario, false) : [];
            $data = $res['cuadrillas'] ?? [];
            $nombreZonaCO = $res['nombreZona'] ?? '';
            $claveZonaCO = $res['claveZona'] ?? $this->zonaUsuario;
            $cuadrillasArray = $data;
        }

        // Extraer todos los tipos de maniobra para crear las columnas dinámicamente
        $tiposManiobra = [];
        
        foreach ($cuadrillasArray as $cuadrilla) {
            if (isset($cuadrilla['listaTarifas'])) {
                foreach ($cuadrilla['listaTarifas'] as $tarifa) {
                    $tiposManiobra[$tarifa['idTipoManiobra']] = $tarifa['nombreTipoManiobra'];
                }
            }
        }

        // Ordenar los tipos por ID o nombre (opcional), aquí lo dejamos como venga
        return view('livewire.tarifas.tarifas-index', [
            'esAdministrador' => $esAdministrador,
            'data' => $data,
            'tiposManiobra' => $tiposManiobra,
            'nombreZonaCO' => $nombreZonaCO,
            'claveZonaCO' => $claveZonaCO
        ])->layout('layouts.app', ['title' => 'Tarifas']);
    }
}
