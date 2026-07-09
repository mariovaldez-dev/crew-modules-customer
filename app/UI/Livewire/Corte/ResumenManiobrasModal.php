<?php

namespace App\UI\Livewire\Corte;

use App\Domain\Corte\ObtenerResumenManiobrasUseCase;
use Livewire\Component;

class ResumenManiobrasModal extends Component
{
    public bool $isOpen = false;
    public ?int $corteId = null;
    public ?int $cuadrillaId = null;
    public array $maniobras = [];
    public float $granTotal = 0;

    protected $listeners = [
        'open-resumen-modal' => 'openModal'
    ];

    public function openModal($corteId, $cuadrillaId, ObtenerResumenManiobrasUseCase $useCase)
    {
        $this->corteId = $corteId;
        $this->cuadrillaId = $cuadrillaId;
        
        $this->maniobras = $useCase->execute($corteId, $cuadrillaId);
        
        $this->granTotal = array_sum(array_column($this->maniobras, 'total'));

        $this->dispatch('open-modal', 'resumen-maniobras-modal');
    }

    public function confirmarCuadrilla()
    {
        $this->dispatch('close-modal', 'resumen-maniobras-modal');
        $this->dispatch('resumen-confirmado', corteId: $this->corteId, cuadrillaId: $this->cuadrillaId);
    }

    public function render()
    {
        return view('livewire.corte.resumen-maniobras-modal');
    }
}
