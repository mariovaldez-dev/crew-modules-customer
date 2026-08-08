<?php

namespace App\UI\Livewire\Dashboard;

use App\UI\Livewire\Traits\WithZonaScope;
use Livewire\Component;

class DashboardIndex extends Component
{
    use WithZonaScope;

    public bool $readyToLoad = false;

    public function loadData()
    {
        $this->readyToLoad = true;
    }

    public function render(
        \App\Domain\Dashboard\GetDashboardDataUseCase $getDashboardDataUseCase
    ) {
        if ($this->readyToLoad) {
            session()->save(); // Libera bloqueo de sesión
        }

        $zona = $this->zonaUsuario;
        $rol = $this->rolUsuario;
        $zonaNombre = $this->zonaNombreUsuario;

        $totalManiobras = 0;
        $totalCuadrillas = 0;
        $corteActual = null;
        $recientesRegistros = [];
        $toneladasTotalesHoy = 0.0;
        $maniobrasLiquidadasCount = 0;
        $maniobrasEnProcesoCount = 0;

        if ($this->readyToLoad) {
            $data = $getDashboardDataUseCase->execute($zona, $rol);

            if (!empty($data)) {
                $totalManiobras = $data['totalManiobras'] ?? 0;
                $totalCuadrillas = $data['totalCuadrillas'] ?? 0;
                $toneladasTotalesHoy = $data['toneladasTotales'] ?? 0.0;
                $maniobrasLiquidadasCount = $data['maniobrasLiquidadas'] ?? 0;
                $maniobrasEnProcesoCount = $data['maniobrasEnProceso'] ?? 0;
                $corteActual = $data['corteActual'] ?? null;
                $recientesRegistros = $data['recientesRegistros'] ?? [];
            }
        }

        return view('livewire.dashboard.dashboard-index', [
            'totalManiobras' => $totalManiobras,
            'totalCuadrillas' => $totalCuadrillas,
            'corteActual' => $corteActual,
            'recientesRegistros' => $recientesRegistros,
            'toneladasTotales' => $toneladasTotalesHoy,
            'maniobrasLiquidadas' => $maniobrasLiquidadasCount,
            'maniobrasEnProceso' => $maniobrasEnProcesoCount,
            'zona' => $zona,
            'zonaNombre' => $zonaNombre,
            'rol' => $rol,
            'readyToLoad' => $this->readyToLoad,
        ])->layout('layouts.app', ['title' => 'Inicio']);
    }
}
