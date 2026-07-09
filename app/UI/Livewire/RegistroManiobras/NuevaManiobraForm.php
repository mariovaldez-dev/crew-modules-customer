<?php

namespace App\UI\Livewire\RegistroManiobras;

use App\Domain\RegistroManiobra\CreateManiobraManualUseCase;
use App\Domain\RegistroManiobra\RegistroManiobraRepositoryInterface;
use App\UI\Livewire\Traits\WithZonaScope;
use Illuminate\Support\Facades\Auth;
use Exception;
use Livewire\Component;

class NuevaManiobraForm extends Component
{
    use WithZonaScope;

    public string $fecha = '';
    public string $almacenId = '';
    public string $cuadrillaId = '';
    public string $tipoManiobraId = '';
    public string $toneladas = '';
    public string $documentoSap = '';

    public array $almacenes = [];
    public array $cuadrillas = [];
    public array $tiposManiobra = [];

    protected $listeners = [
        'open-nueva-maniobra-modal' => 'openModal'
    ];

    public function mount(RegistroManiobraRepositoryInterface $repository)
    {
        $this->fecha = date('Y-m-d');
        
        // Mock de almacenes por zona
        $this->almacenes = $this->zonaUsuario === 'ZONA-NORTE' 
            ? [101 => 'Almacén Norte'] 
            : [102 => 'Almacén Sur'];
            
        $this->tiposManiobra = $repository->tiposManiobra();
    }

    public function openModal()
    {
        $this->resetValidation();
        $this->fecha = date('Y-m-d');
        $this->almacenId = '';
        $this->cuadrillaId = '';
        $this->tipoManiobraId = '';
        $this->toneladas = '';
        $this->documentoSap = '';
        $this->cuadrillas = [];
        
        $this->dispatch('open-modal', 'nueva-maniobra-modal');
    }

    public function updatedAlmacenId($value)
    {
        $this->cuadrillaId = '';
        if ($value) {
            $repo = app(RegistroManiobraRepositoryInterface::class);
            $this->cuadrillas = $repo->cuadrillasPorAlmacen((int) $value);
        } else {
            $this->cuadrillas = [];
        }
    }

    public function save(CreateManiobraManualUseCase $createUseCase)
    {
        $this->validate([
            'fecha' => 'required|date',
            'almacenId' => 'required|integer',
            'cuadrillaId' => 'required|integer',
            'tipoManiobraId' => 'required|integer',
            'toneladas' => 'required|numeric|min:0.001',
            'documentoSap' => 'nullable|string|max:50',
        ]);

        try {
            // Mock de ID de usuario
            $usuarioId = Auth::user()?->id ?? 'TEST_USER_01';
            
            $createUseCase->execute(
                fecha: $this->fecha,
                almacenId: (int) $this->almacenId,
                cuadrillaId: (int) $this->cuadrillaId,
                tipoManiobraId: (int) $this->tipoManiobraId,
                toneladas: (float) $this->toneladas,
                usuarioId: $usuarioId,
                documentoSap: $this->documentoSap ?: null
            );

            $this->dispatch('notify', ['message' => 'Maniobra registrada correctamente', 'type' => 'success']);
            $this->dispatch('close-modal', 'nueva-maniobra-modal');
            $this->dispatch('maniobra-registrada');

        } catch (Exception $e) {
            $this->addError('form', $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.registro-maniobras.nueva-maniobra-form');
    }
}
