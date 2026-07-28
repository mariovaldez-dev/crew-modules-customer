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
use Exception;
use Livewire\Component;

class CorteIndex extends Component
{
    use WithZonaScope;

    public string $fechaInicio = '';
    public string $fechaFin = '';
    public array $cortes = [];
    public bool $cargando = true;
    public bool $isPrimerCorte = true;
    public bool $mostrandoFormularioNuevo = false;

    public function mount(\App\Domain\Corte\ObtenerUltimaFechaFinCorteUseCase $ultimaFechaUseCase)
    {
        $context = session()->get('usuario_contexto');
        if (!$context || $context->tipo !== 'CO') {
            abort(403, 'No tienes permisos para acceder a esta sección.');
        }

        $ultimaFecha = $ultimaFechaUseCase->execute($this->zonaUsuario);
        
        if ($ultimaFecha) {
            $this->isPrimerCorte = false;
            $this->fechaInicio = explode(' ', $ultimaFecha)[0];
            $this->fechaFin = date('Y-m-d');
        } else {
            $this->isPrimerCorte = true;
            $this->fechaInicio = date('Y-m-d');
            $this->fechaFin = date('Y-m-d');
        }
    }

    public function loadData(\App\Domain\Corte\ListarCortesUseCase $useCase)
    {
        $this->cargando = true;
        try {
            $this->cortes = $useCase->execute($this->zonaUsuario);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error("Error listando cortes: " . $e->getMessage());
            $this->dispatch('notify', ['message' => 'Error al cargar cortes.', 'type' => 'error']);
        }
        $this->cargando = false;
    }

    public function generarCorte(GenerarCorteUseCase $useCase, \App\Domain\Corte\ListarCortesUseCase $listarUseCase)
    {
        $this->validate([
            'fechaInicio' => 'required|date',
            'fechaFin' => 'required|date|after_or_equal:fechaInicio'
        ], [
            'fechaInicio.required' => 'La fecha de inicio es requerida',
            'fechaFin.required' => 'La fecha final es requerida',
            'fechaFin.after_or_equal' => 'La fecha fin no puede ser menor a la fecha inicio'
        ]);

        try {
            $useCase->execute($this->fechaInicio, $this->fechaFin, $this->zonaUsuario);
            
            $this->dispatch('notify', ['message' => 'Corte generado exitosamente', 'type' => 'success']);
            $this->mostrandoFormularioNuevo = false;
            $this->loadData($listarUseCase);
            $this->dispatch('close-modal', 'nuevo-corte-modal');
        } catch (\Exception $e) {
            $this->dispatch('notify', ['message' => $e->getMessage(), 'type' => 'error']);
        }
    }

    public function prepararNuevoCorte()
    {
        $this->mostrandoFormularioNuevo = true;
        $this->dispatch('open-modal', 'nuevo-corte-modal');
    }

    public function render()
    {
        session()->save(); 
        return view('livewire.corte.corte-index')->layout('layouts.app', ['title' => 'Cortes de Liquidación']);
    }
}
