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

    public bool $readyToLoad = false;

    public function mount()
    {
        $this->fecha = date('Y-m-d');
    }

    public function loadData(RegistroManiobraRepositoryInterface $repository)
    {
        $sucursalRepo = app(\App\Domain\Shared\Repositories\SucursalRepositoryInterface::class);
        $zonaFiltro = ($this->rolUsuario === 'AM') ? 'TODAS' : $this->zonaUsuario;
        $this->almacenes = $sucursalRepo->listaPuntosDeVentaPorZona($zonaFiltro);
        $this->tiposManiobra = $repository->tiposManiobra();
        $this->readyToLoad = true;
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
            $this->cuadrillas = $repo->cuadrillasPorAlmacen((string) $value);
        } else {
            $this->cuadrillas = [];
        }
    }

    public function save(CreateManiobraManualUseCase $createUseCase)
    {
        $this->validate([
            'fecha' => 'required|date|before_or_equal:today',
            'almacenId' => 'required',
            'cuadrillaId' => 'required',
            'tipoManiobraId' => 'required',
            'toneladas' => 'required|numeric|min:0.001',
            'documentoSap' => 'nullable|string|max:50',
        ], [
            'fecha.required' => 'La fecha es obligatoria.',
            'fecha.date' => 'La fecha debe tener un formato válido.',
            'fecha.before_or_equal' => 'La fecha no puede ser posterior a hoy.',
            'almacenId.required' => 'El almacén es obligatorio.',
            'cuadrillaId.required' => 'La cuadrilla es obligatoria.',
            'tipoManiobraId.required' => 'El tipo de maniobra es obligatorio.',
            'toneladas.required' => 'Las toneladas son obligatorias.',
            'toneladas.numeric' => 'Las toneladas deben ser un valor numérico.',
            'toneladas.min' => 'Las toneladas deben ser mayores a 0.',
            'documentoSap.max' => 'El documento SAP no debe exceder 50 caracteres.',
        ]);

        try {
            // Mock de ID de usuario
            $usuarioId = Auth::user()?->id ?? 'TEST_USER_01';
            
            $mensaje = $createUseCase->execute(
                fecha: $this->fecha,
                almacenId: (string) $this->almacenId,
                cuadrillaId: (int) $this->cuadrillaId,
                tipoManiobraId: (int) $this->tipoManiobraId,
                toneladas: (float) $this->toneladas,
                usuarioId: $usuarioId,
                documentoSap: $this->documentoSap ?: null
            );

            $this->dispatch('notify', ['message' => $mensaje, 'type' => 'success']);
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
