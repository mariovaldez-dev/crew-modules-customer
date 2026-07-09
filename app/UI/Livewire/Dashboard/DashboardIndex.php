<?php

namespace App\UI\Livewire\Dashboard;

use App\Domain\Corte\CorteRepositoryInterface;
use App\Domain\Cuadrilla\CuadrillaRepositoryInterface;
use App\Domain\Maniobra\ManiobraRepositoryInterface;
use App\Domain\RegistroManiobra\RegistroManiobraRepositoryInterface;
use App\UI\Livewire\Traits\WithZonaScope;
use Livewire\Component;

class DashboardIndex extends Component
{
    use WithZonaScope;

    public function render(
        ManiobraRepositoryInterface $maniobraRepo,
        CuadrillaRepositoryInterface $cuadrillaRepo,
        RegistroManiobraRepositoryInterface $registroRepo,
        CorteRepositoryInterface $corteRepo
    ) {
        $zona = $this->zonaUsuario;
        $rol = $this->rolUsuario;

        // 1. Maniobras Totales (Catálogo)
        $maniobras = $maniobraRepo->list();
        $totalManiobras = count($maniobras);

        // 2. Cuadrillas Totales (Filtrado por Zona del usuario)
        $cuadrillas = $cuadrillaRepo->list([], $zona);
        $totalCuadrillas = count($cuadrillas);

        // 3. Corte de Liquidación actual en borrador para la zona
        $corteActual = $corteRepo->findByZona($zona);

        // 4. Últimos registros de maniobras (últimos 5)
        $todosRegistros = $registroRepo->list([], $zona, $rol);
        // Ordenar por fecha descendente
        usort($todosRegistros, function ($a, $b) {
            return $b->fecha <=> $a->fecha;
        });
        $recientesRegistros = array_slice($todosRegistros, 0, 5);

        // Calcular algunas estadísticas rápidas
        $toneladasTotalesHoy = 0.0;
        $maniobrasLiquidadasCount = 0;
        $maniobrasEnProcesoCount = 0;

        foreach ($todosRegistros as $reg) {
            $toneladasTotalesHoy += $reg->toneladas;
            if ($reg->estado === 'Liquidada') {
                $maniobrasLiquidadasCount++;
            } else {
                $maniobrasEnProcesoCount++;
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
            'rol' => $rol,
        ])->layout('layouts.app', ['title' => 'Inicio']);
    }
}
