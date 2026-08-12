<?php

namespace App\UI\Livewire\Corte;

use App\Domain\Corte\ConsultarCorteUseCase;
use App\Domain\Corte\ConfirmarCorteCuadrillaUseCase;
use App\Domain\Corte\ConfirmarCorteGeneralUseCase;
use App\Domain\Corte\RegenerarCorteUseCase;
use App\UI\Livewire\Traits\WithZonaScope;
use Illuminate\Support\Facades\Auth;
use Exception;
use Livewire\Component;

class CorteDetalle extends Component
{
    use WithZonaScope;

    public int $corteId;
    public ?array $corte = null;
    public bool $cargando = true;
    public ?int $cuadrillaIndexSeleccionada = null;
    public ?string $modalConfirmarTipo = null;

    protected $listeners = [
        'corte-actualizado' => '$refresh',
        'corte-confirmar-cuadrilla' => 'confirmarCuadrilla'
    ];

    public function mount(int $id)
    {
        $context = session()->get('usuario_contexto');
        if (!$context || $context->tipo !== 'CO') {
            abort(403, 'No tienes permisos para acceder a esta sección.');
        }

        $this->corteId = $id;
    }

    public function loadData(ConsultarCorteUseCase $useCase)
    {
        \Illuminate\Support\Facades\Log::info("[CORTE-LIQUIDACION] [UI-Detalle] Cargando detalle del corte ID: {$this->corteId}");
        $this->cargando = true;
        try {
            $this->corte = $useCase->execute($this->corteId);
            \Illuminate\Support\Facades\Log::info("[CORTE-LIQUIDACION] [UI-Detalle] Detalle del corte ID: {$this->corteId} cargado exitosamente.");
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("[CORTE-LIQUIDACION] [UI-Detalle] Error cargando detalle del corte ID: {$this->corteId}: " . $e->getMessage(), ['exception' => $e]);
            $this->dispatch('notify', ['message' => 'Error al cargar el detalle del corte.', 'type' => 'error']);
            $this->corte = null;
        }
        $this->cargando = false;
    }

    public function getTodasConfirmadasProperty(): bool
    {
        if (!$this->corte || empty($this->corte['cuadrillas'])) {
            return false;
        }

        foreach ($this->corte['cuadrillas'] as $cuadrilla) {
            if (empty($cuadrilla['estaConfirmada'])) {
                return false;
            }
        }

        return true;
    }

    public function confirmarCuadrilla(int $cuadrillaId, ConfirmarCorteCuadrillaUseCase $useCase, ConsultarCorteUseCase $consultarUseCase)
    {
        \Illuminate\Support\Facades\Log::info("[CORTE-LIQUIDACION] [UI-Detalle] Confirmando cuadrilla ID: {$cuadrillaId} en corte ID: {$this->corteId}");
        try {
            if ($this->corte && isset($this->corte['corteId'])) {
                $useCase->execute($this->corte['corteId'], $cuadrillaId, $this->zonaUsuario);
                \Illuminate\Support\Facades\Log::info("[CORTE-LIQUIDACION] [UI-Detalle] Cuadrilla ID: {$cuadrillaId} confirmada con éxito.");
                $this->dispatch('notify', ['message' => 'Cuadrilla confirmada', 'type' => 'success']);
                $this->loadData($consultarUseCase);
            }
        } catch (Exception $e) {
            \Illuminate\Support\Facades\Log::error("[CORTE-LIQUIDACION] [UI-Detalle] Error al confirmar cuadrilla ID: {$cuadrillaId}: " . $e->getMessage());
            $this->dispatch('notify', ['message' => $e->getMessage(), 'type' => 'error']);
        }
    }

    public function abrirConfirmarGeneral(): void
    {
        \Illuminate\Support\Facades\Log::info("[CORTE-LIQUIDACION] [UI-Detalle] Abriendo modal confirm-corte-general");
        $this->dispatch('open-confirmar-general', corteId: $this->corteId);
    }

    public function abrirRegenerar(): void
    {
        \Illuminate\Support\Facades\Log::info("[CORTE-LIQUIDACION] [UI-Detalle] Abriendo modal confirm-regenerar");
        $this->dispatch('open-confirmar-regenerar', corteId: $this->corteId);
    }

    public function confirmarCorteGeneral(ConfirmarCorteGeneralUseCase $useCase, ConsultarCorteUseCase $consultarUseCase)
    {
        \Illuminate\Support\Facades\Log::info("[CORTE-LIQUIDACION] [UI-Detalle] Confirmando corte general ID: {$this->corteId}");
        try {
            if ($this->corte && isset($this->corte['corteId'])) {
                $res = $useCase->execute($this->corte['corteId'], $this->zonaUsuario);
                \Illuminate\Support\Facades\Log::info("[CORTE-LIQUIDACION] [UI-Detalle] Corte general ID: {$this->corteId} confirmado exitosamente.", ['res' => $res]);
                $this->dispatch('notify', ['message' => 'Corte confirmado y folios asignados', 'type' => 'success']);
                return redirect()->route('corte-liquidacion.index');
            }
        } catch (Exception $e) {
            \Illuminate\Support\Facades\Log::error("[CORTE-LIQUIDACION] [UI-Detalle] Error al confirmar corte general ID: {$this->corteId}: " . $e->getMessage());
            $this->dispatch('notify', ['message' => $e->getMessage(), 'type' => 'error']);
        }
    }

    public function regenerar(RegenerarCorteUseCase $useCase, ConsultarCorteUseCase $consultarUseCase)
    {
        \Illuminate\Support\Facades\Log::info("[CORTE-LIQUIDACION] [UI-Detalle] Solicitando regenerar corte ID: {$this->corteId}");
        try {
            if ($this->corte && isset($this->corte['corteId'])) {
                $res = $useCase->execute($this->corte['corteId'], $this->zonaUsuario);

                if (!empty($res['resultado']['idu_corte'])) {
                    $this->corteId = (int) $res['resultado']['idu_corte'];
                }

                \Illuminate\Support\Facades\Log::info("[CORTE-LIQUIDACION] [UI-Detalle] Corte ID regenerado exitosamente. Nuevo ID: {$this->corteId}");
                $this->dispatch('notify', ['message' => 'Corte regenerado exitosamente', 'type' => 'success']);
                $this->loadData($consultarUseCase);
            }
        } catch (Exception $e) {
            \Illuminate\Support\Facades\Log::error("[CORTE-LIQUIDACION] [UI-Detalle] Error al regenerar corte ID: {$this->corteId}: " . $e->getMessage());
            $this->dispatch('notify', ['message' => $e->getMessage(), 'type' => 'error']);
        }
    }

    public function abrirResumenManiobras(int $index)
    {
        $this->cuadrillaIndexSeleccionada = $index;
        
        if (!isset($this->corte['cuadrillas'][$index])) {
            return;
        }

        $cuadrilla = $this->corte['cuadrillas'][$index];

        \Illuminate\Support\Facades\Log::info("[CORTE-LIQUIDACION] [UI-Detalle] Abriendo resumen de maniobras para cuadrilla ID: " . ($cuadrilla['cuadrillaId'] ?? 'N/A'));

        $this->dispatch('open-resumen-modal', 
            cuadrillaId: $cuadrilla['cuadrillaId'],
            nombre: $cuadrilla['nombreCuadrilla'],
            estaConfirmada: $cuadrilla['estaConfirmada'],
            maniobras: $cuadrilla['maniobrasDetalle'] ?? []
        );
    }

    public function confirmarManiobrasCuadrilla(ConfirmarCorteCuadrillaUseCase $useCase, ConsultarCorteUseCase $consultarUseCase)
    {
        if ($this->cuadrillaIndexSeleccionada === null || !isset($this->corte['cuadrillas'][$this->cuadrillaIndexSeleccionada])) {
            return;
        }

        $cuadrilla = $this->corte['cuadrillas'][$this->cuadrillaIndexSeleccionada];
        
        \Illuminate\Support\Facades\Log::info("[CORTE-LIQUIDACION] [UI-Detalle] Confirmando maniobras de cuadrilla ID: " . ($cuadrilla['cuadrillaId'] ?? 'N/A') . " en corte ID: {$this->corteId}");
        try {
            $useCase->execute($this->corte['corteId'], $cuadrilla['cuadrillaId'], $this->zonaUsuario);
            
            $this->corte = $consultarUseCase->execute($this->corteId);
            \Illuminate\Support\Facades\Log::info("[CORTE-LIQUIDACION] [UI-Detalle] Cuadrilla confirmada desde modal de resumen.");
            $this->dispatch('notify', ['message' => 'Cuadrilla confirmada', 'type' => 'success']);
            $this->dispatch('close-modal', 'resumen-maniobras-modal');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("[CORTE-LIQUIDACION] [UI-Detalle] Error confirmando maniobras de cuadrilla: " . $e->getMessage());
            $this->dispatch('notify', ['message' => $e->getMessage(), 'type' => 'error']);
        }
    }

    public function render()
    {
        session()->save(); 
        return view('livewire.corte.corte-detalle')
            ->layout('layouts.app', ['title' => 'Detalle de Corte']);
    }
}
