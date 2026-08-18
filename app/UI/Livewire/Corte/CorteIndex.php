<?php

namespace App\UI\Livewire\Corte;

use App\Domain\Corte\ConsultarCorteUseCase;
use App\Domain\Corte\ConfirmarCorteCuadrillaUseCase;
use App\Domain\Corte\ConfirmarCorteGeneralUseCase;
use App\Domain\Corte\GenerarCorteUseCase;
use App\Domain\Corte\RegenerarCorteUseCase;
use App\Domain\Corte\GenerarPdfCorteUseCase;
use App\UI\Livewire\Traits\WithZonaScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Exception;
use Livewire\Component;

use Livewire\WithPagination;

class CorteIndex extends Component
{
    use WithZonaScope, WithPagination;

    public string $fechaInicio = '';
    public string $fechaFin = '';
    public array $cortes = [];
    public bool $cargando = true;
    public bool $isPrimerCorte = true;
    public bool $mostrandoFormularioNuevo = false;

    public function mount()
    {
        $context = session()->get('usuario_contexto');
        if (!$context || $context->tipo !== 'CO') {
            abort(403, 'No tienes permisos para acceder a esta sección.');
        }

        $this->fechaInicio = date('Y-m-d');
        $this->fechaFin = date('Y-m-d');
    }

    public function loadData(\App\Domain\Corte\ListarCortesUseCase $useCase)
    {
        Log::info("[CORTE-LIQUIDACION] [UI-Index] Cargando lista de cortes para zona: {$this->zonaUsuario}");
        $this->cargando = true;
        try {
            $cortesRaw = $useCase->execute($this->zonaUsuario);
            $this->cortes = array_map(function($c) {
                if (is_array($c)) {
                    if (!isset($c['id']) && isset($c['corteId'])) {
                        $c['id'] = $c['corteId'];
                    }
                }
                return $c;
            }, $cortesRaw);

            // Extract ultima fecha fin from already loaded cortes in memory
            $ultimaFecha = null;
            foreach ($this->cortes as $c) {
                if (($c['estado'] ?? '') === 'Confirmado' || ($c['opc_estatus'] ?? 0) == 2) {
                    $ultimaFecha = $c['fechaFin'] ?? $c['fec_fin'] ?? null;
                    break;
                }
            }

            if ($ultimaFecha) {
                $this->isPrimerCorte = false;
                $this->fechaInicio = explode(' ', $ultimaFecha)[0];
            } else {
                $this->isPrimerCorte = true;
            }

            Log::info("[CORTE-LIQUIDACION] [UI-Index] Cortes cargados correctamente. Total: " . count($this->cortes));
        } catch (\Throwable $e) {
            Log::error("[CORTE-LIQUIDACION] [UI-Index] Error listando cortes: " . $e->getMessage(), ['exception' => $e]);
            $this->dispatch('notify', ['message' => 'Error al cargar cortes.', 'type' => 'error']);
        }
        $this->cargando = false;
    }

    public function generarCorte(GenerarCorteUseCase $useCase, \App\Domain\Corte\ListarCortesUseCase $listarUseCase, bool $reemplazar = false)
    {
        Log::info("[CORTE-LIQUIDACION] [UI-Index] Solicitando generar corte", [
            'fechaInicio' => $this->fechaInicio,
            'fechaFin' => $this->fechaFin,
            'zona' => $this->zonaUsuario,
            'reemplazar' => $reemplazar
        ]);

        $this->validate([
            'fechaInicio' => 'required|date|before_or_equal:today',
            'fechaFin' => 'required|date|after_or_equal:fechaInicio|before_or_equal:today'
        ], [
            'fechaInicio.required' => 'La fecha de inicio es requerida',
            'fechaInicio.before_or_equal' => 'La fecha de inicio no puede ser posterior a hoy',
            'fechaFin.required' => 'La fecha final es requerida',
            'fechaFin.after_or_equal' => 'La fecha fin no puede ser menor a la fecha inicio',
            'fechaFin.before_or_equal' => 'La fecha fin no puede ser posterior a hoy'
        ]);

        try {
            $res = $useCase->execute($this->fechaInicio, $this->fechaFin, $this->zonaUsuario, $reemplazar);

            if (isset($res['estatus']) && $res['estatus'] == 409) {
                Log::info("[CORTE-LIQUIDACION] [UI-Index] Borrador activo detectado. Abriendo modal de confirmación de reemplazo.");
                $this->dispatch('close-modal', 'nuevo-corte-modal');
                $this->dispatch('open-confirmar-reemplazo');
                return;
            }

            Log::info("[CORTE-LIQUIDACION] [UI-Index] Corte generado exitosamente.");
            
            $this->dispatch('notify', ['message' => 'Corte generado exitosamente', 'type' => 'success']);
            $this->mostrandoFormularioNuevo = false;
            $this->loadData($listarUseCase);
            $this->dispatch('close-modal', 'nuevo-corte-modal');
            $this->dispatch('close-confirmar-reemplazo');
        } catch (\Exception $e) {
            Log::error("[CORTE-LIQUIDACION] [UI-Index] Error al generar corte: " . $e->getMessage());
            $this->dispatch('notify', ['message' => $e->getMessage(), 'type' => 'error']);
        }
    }

    public function generarCorteConReemplazo(GenerarCorteUseCase $useCase, \App\Domain\Corte\ListarCortesUseCase $listarUseCase)
    {
        $this->generarCorte($useCase, $listarUseCase, reemplazar: true);
    }

    public function prepararNuevoCorte()
    {
        $this->mostrandoFormularioNuevo = true;
        $this->dispatch('open-modal', 'nuevo-corte-modal');
    }

    public function render()
    {
        session()->save();

        $currentPage = $this->paginators['page'] ?? 1;
        $perPage = 10;
        $items = $this->cargando ? [] : $this->cortes;

        $paginated = new \Illuminate\Pagination\LengthAwarePaginator(
            array_slice($items, ($currentPage - 1) * $perPage, $perPage),
            count($items),
            $perPage,
            $currentPage,
            ['path' => \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPath()]
        );

        return view('livewire.corte.corte-index', [
            'cortesList' => $paginated
        ])->layout('layouts.app', ['title' => 'Cortes de Liquidación']);
    }
}
