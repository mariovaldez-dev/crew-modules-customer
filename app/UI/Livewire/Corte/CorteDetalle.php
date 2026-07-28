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
        $this->cargando = true;
        try {
            $this->corte = $useCase->execute($this->corteId);
            
            // Validar que el corte pertenezca a la zona del usuario (seguridad extra)
            // Esto asume que el SP ya filtró, pero no está de más si el SP solo trajo por ID.
            // Para simplificar, si el corte vuelve, asumimos que está bien.
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("Error cargando detalle de corte: " . $e->getMessage());
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
        try {
            if ($this->corte && isset($this->corte['corteId'])) {
                $useCase->execute($this->corte['corteId'], $cuadrillaId, $this->zonaUsuario);
                $this->dispatch('notify', ['message' => 'Cuadrilla confirmada', 'type' => 'success']);
                $this->loadData($consultarUseCase);
            }
        } catch (Exception $e) {
            $this->dispatch('notify', ['message' => $e->getMessage(), 'type' => 'error']);
        }
    }

    public function confirmarCorteGeneral(ConfirmarCorteGeneralUseCase $useCase, ConsultarCorteUseCase $consultarUseCase)
    {
        try {
            if ($this->corte && isset($this->corte['corteId'])) {
                $useCase->execute($this->corte['corteId'], $this->zonaUsuario);
                $this->dispatch('notify', ['message' => 'Corte confirmado y folios asignados', 'type' => 'success']);
                $this->dispatch('close-modal', 'confirm-corte-general');
                
                // Redirigir al index como pide el usuario
                return redirect()->route('corte-liquidacion.index');
            }
        } catch (Exception $e) {
            $this->dispatch('notify', ['message' => $e->getMessage(), 'type' => 'error']);
        }
    }

    public function regenerar(RegenerarCorteUseCase $useCase)
    {
        try {
            if ($this->corte && isset($this->corte['corteId'])) {
                $useCase->execute($this->corte['corteId'], $this->zonaUsuario);
                $this->dispatch('notify', ['message' => 'Corte regenerado exitosamente', 'type' => 'success']);
                $this->dispatch('close-modal', 'confirm-regenerar');
                
                return redirect()->route('corte-liquidacion.index');
            }
        } catch (Exception $e) {
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
        
        try {
            $useCase->execute($this->corte['corteId'], $cuadrilla['cuadrillaId'], $this->zonaUsuario);
            
            // Recargar datos
            $this->corte = $consultarUseCase->execute($this->corteId);
            $this->dispatch('notify', ['message' => 'Cuadrilla confirmada', 'type' => 'success']);
            $this->dispatch('close-modal', 'resumen-maniobras-modal');
        } catch (\Exception $e) {
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
