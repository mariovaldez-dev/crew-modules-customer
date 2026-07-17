<?php

namespace App\UI\Livewire\Corte;

use App\Domain\Corte\CorteRepositoryInterface;
use App\Domain\Corte\ConfirmarCorteCuadrillaUseCase;
use App\Domain\Corte\ConfirmarCorteGeneralUseCase;
use App\Domain\Corte\GenerarCorteUseCase;
use App\Domain\Corte\RegenerarCorteUseCase;
use App\Domain\Corte\GenerarPdfCorteUseCase;
use App\UI\Livewire\Traits\WithZonaScope;
use Illuminate\Support\Facades\Auth;
use Exception;
use Livewire\Component;

class CorteIndex extends Component
{
    use WithZonaScope;

    public string $fechaInicio = '';
    public string $fechaFin = '';

    protected $listeners = ['corte-actualizado' => '$refresh', 'resumen-confirmado' => 'confirmarCuadrillaEvent'];

    public function mount(CorteRepositoryInterface $repository)
    {
        $context = session()->get('usuario_contexto');
        if (!$context || $context->tipo !== 'CO') {
            abort(403, 'No tienes permisos para acceder a esta sección.');
        }

        $this->fechaInicio = date('Y-m-d', strtotime('-15 days'));
        $this->fechaFin = date('Y-m-d');
    }

    public function getCorteProperty()
    {
        return app(CorteRepositoryInterface::class)->findByZona($this->zonaUsuario);
    }

    public function getTodasConfirmadasProperty(): bool
    {
        $corte = $this->corte;
        if (!$corte) return false;

        foreach ($corte->cuadrillas as $cuadrilla) {
            if (!$cuadrilla->confirmada) return false;
        }

        return count($corte->cuadrillas) > 0;
    }

    public function generar(GenerarCorteUseCase $useCase)
    {
        try {
            $useCase->execute($this->fechaInicio, $this->fechaFin, $this->zonaUsuario);
            $this->dispatch('notify', ['message' => 'Corte generado exitosamente', 'type' => 'success']);
        } catch (Exception $e) {
            $this->dispatch('notify', ['message' => $e->getMessage(), 'type' => 'error']);
        }
    }

    public function regenerar(RegenerarCorteUseCase $useCase)
    {
        try {
            if ($this->corte) {
                $useCase->execute($this->corte->id, $this->zonaUsuario);
                $this->dispatch('notify', ['message' => 'Corte regenerado', 'type' => 'success']);
            }
        } catch (Exception $e) {
            $this->dispatch('notify', ['message' => $e->getMessage(), 'type' => 'error']);
        }
    }

    public function confirmarCorteGeneral(ConfirmarCorteGeneralUseCase $useCase)
    {
        try {
            if ($this->corte) {
                $useCase->execute($this->corte->id, $this->zonaUsuario);
                $this->dispatch('notify', ['message' => 'Corte confirmado y maniobras liquidadas', 'type' => 'success']);
            }
        } catch (Exception $e) {
            $this->dispatch('notify', ['message' => $e->getMessage(), 'type' => 'error']);
        }
    }

    public function imprimirPdf(GenerarPdfCorteUseCase $useCase)
    {
        try {
            if ($this->corte) {
                // Return immediate download response from Livewire (since Livewire v3 handles binary downloads directly)
                return response()->streamDownload(
                    function () use ($useCase) {
                        echo $useCase->execute($this->corte->id, $this->zonaUsuario);
                    },
                    "Corte_{$this->corte->folio}.pdf"
                );
            }
        } catch (Exception $e) {
            $this->dispatch('notify', ['message' => $e->getMessage(), 'type' => 'error']);
        }
    }

    public function openResumen($cuadrillaId)
    {
        if ($this->corte) {
            $this->dispatch('open-resumen-modal', corteId: $this->corte->id, cuadrillaId: $cuadrillaId);
        }
    }

    public function confirmarCuadrillaEvent($corteId, $cuadrillaId)
    {
        try {
            app(ConfirmarCorteCuadrillaUseCase::class)->execute($corteId, $cuadrillaId, $this->zonaUsuario);
            $this->dispatch('notify', ['message' => 'Cuadrilla confirmada', 'type' => 'success']);
        } catch (Exception $e) {
            $this->dispatch('notify', ['message' => $e->getMessage(), 'type' => 'error']);
        }
    }

    public function render()
    {
        session()->save(); // Libera el bloqueo de sesión
        return view('livewire.corte.corte-index')->layout('layouts.app', ['title' => 'Corte de Liquidación']);
    }
}
