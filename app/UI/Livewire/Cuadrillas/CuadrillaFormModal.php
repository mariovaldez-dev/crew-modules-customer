<?php

namespace App\UI\Livewire\Cuadrillas;

use App\Domain\Cuadrilla\CreateCuadrillaUseCase;
use App\Domain\Cuadrilla\TarifasManiobra;
use App\Domain\Cuadrilla\UpdateCuadrillaUseCase;
use App\UI\Livewire\Traits\WithZonaScope;
use Exception;
use Livewire\Component;

class CuadrillaFormModal extends Component
{
    use WithZonaScope;

    public bool $isOpen = false;
    
    public ?int $cuadrillaId = null;
    public string $nombre = '';
    public string $lider = '';
    public int $miembros = 1;
    public ?int $puntoVentaId = null;
    
    // Tarifas
    public ?float $carga25 = null;
    public ?float $carga50 = null;
    public ?float $descarga25 = null;
    public ?float $descarga50 = null;
    public ?float $traslado = null;
    public ?float $apaleo = null;

    protected $listeners = [
        'open-cuadrilla-modal' => 'openModal'
    ];

    public function openModal($cuadrilla = null)
    {
        $this->resetValidation();
        $this->reset([
            'cuadrillaId', 'nombre', 'lider', 'miembros', 'puntoVentaId',
            'carga25', 'carga50', 'descarga25', 'descarga50', 'traslado', 'apaleo'
        ]);

        if ($cuadrilla) {
            $this->cuadrillaId = $cuadrilla['id'] ?? null;
            $this->nombre = $cuadrilla['nombre'] ?? '';
            $this->lider = $cuadrilla['lider'] ?? '';
            $this->miembros = $cuadrilla['miembros'] ?? 1;
            $this->puntoVentaId = $cuadrilla['puntoVentaId'] ?? null;
            
            $tarifas = $cuadrilla['tarifas'] ?? [];
            $this->carga25 = $tarifas['carga25'] ?? null;
            $this->carga50 = $tarifas['carga50'] ?? null;
            $this->descarga25 = $tarifas['descarga25'] ?? null;
            $this->descarga50 = $tarifas['descarga50'] ?? null;
            $this->traslado = $tarifas['traslado'] ?? null;
            $this->apaleo = $tarifas['apaleo'] ?? null;
        }

        $this->dispatch('open-modal', 'cuadrilla-modal');
    }

    private function parseTarifa($val): ?float
    {
        if ($val === '' || $val === null) return null;
        return (float) $val;
    }

    public function save(CreateCuadrillaUseCase $createUseCase, UpdateCuadrillaUseCase $updateUseCase)
    {
        $this->validate([
            'nombre' => 'required|max:100',
            'lider' => 'required|max:100',
            'miembros' => 'required|integer|min:1',
            'puntoVentaId' => 'required|integer',
            'carga25' => 'nullable|numeric|min:0',
            'carga50' => 'nullable|numeric|min:0',
            'descarga25' => 'nullable|numeric|min:0',
            'descarga50' => 'nullable|numeric|min:0',
            'traslado' => 'nullable|numeric|min:0',
            'apaleo' => 'nullable|numeric|min:0',
        ]);

        try {
            $tarifas = new TarifasManiobra(
                $this->parseTarifa($this->carga25),
                $this->parseTarifa($this->carga50),
                $this->parseTarifa($this->descarga25),
                $this->parseTarifa($this->descarga50),
                $this->parseTarifa($this->traslado),
                $this->parseTarifa($this->apaleo)
            );

            if ($this->cuadrillaId) {
                // Mock usuario ID
                $usuarioId = 'TEST_USER_01';
                
                $updateUseCase->execute(
                    id: $this->cuadrillaId,
                    nombre: $this->nombre,
                    lider: $this->lider,
                    miembros: $this->miembros,
                    puntoVentaId: $this->puntoVentaId,
                    zona: $this->zonaUsuario,
                    tarifas: $tarifas,
                    usuarioId: $usuarioId
                );
                $this->dispatch('notify', ['message' => 'Cuadrilla actualizada correctamente', 'type' => 'success']);
            } else {
                $createUseCase->execute(
                    nombre: $this->nombre,
                    lider: $this->lider,
                    miembros: $this->miembros,
                    puntoVentaId: $this->puntoVentaId,
                    zona: $this->zonaUsuario,
                    tarifas: $tarifas
                );
                $this->dispatch('notify', ['message' => 'Cuadrilla creada correctamente', 'type' => 'success']);
            }

            $this->dispatch('close-modal', 'cuadrilla-modal');
            $this->dispatch('cuadrilla-saved');

        } catch (Exception $e) {
            $this->addError('form', $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.cuadrillas.cuadrilla-form-modal', [
            'puntosVenta' => [
                101 => 'PV Norte Principal',
                102 => 'PV Sur Auxiliar'
            ]
        ]);
    }
}
