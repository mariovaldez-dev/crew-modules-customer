<?php

namespace App\UI\Livewire\Maniobras;

use App\Domain\Maniobra\CreateManiobraUseCase;
use App\Domain\Maniobra\UpdateManiobraUseCase;
use Exception;
use Livewire\Component;

class ManiobraFormModal extends Component
{
    // isOpen removido porque usamos Alpine JS para el estado del modal
    
    public ?int $maniobraId = null;
    public string $nombre = '';
    public string $descripcion = '';
    public string $estatus = 'Activo';

    protected $listeners = [
        'open-maniobra-modal' => 'openModal'
    ];

    public function openModal($maniobra = null)
    {
        $this->resetValidation();
        $this->reset(['maniobraId', 'nombre', 'descripcion', 'estatus']);

        if ($maniobra) {
            $this->maniobraId = $maniobra['id'] ?? null;
            $this->nombre = $maniobra['nombre'] ?? '';
            $this->descripcion = $maniobra['descripcion'] ?? '';
            $this->estatus = $maniobra['estatus'] ?? 'Activo';
        }

        $this->dispatch('open-modal', 'maniobra-modal');
    }

    public function save(CreateManiobraUseCase $createUseCase, UpdateManiobraUseCase $updateUseCase)
    {
        $this->validate([
            'nombre' => 'required|max:100',
            'descripcion' => 'nullable|max:100',
            'estatus' => 'required|in:Activo,Inactivo'
        ], [
            'nombre.required' => 'El nombre es obligatorio.',
            'nombre.max' => 'El nombre no debe exceder 100 caracteres.',
            'descripcion.max' => 'La descripción no debe exceder 100 caracteres.'
        ]);

        try {
            if ($this->maniobraId) {
                $updateUseCase->execute(
                    id: $this->maniobraId,
                    nombre: $this->nombre,
                    descripcion: $this->descripcion,
                    estatus: $this->estatus
                );
                $this->dispatch('notify', ['message' => 'Maniobra actualizada correctamente', 'type' => 'success']);
            } else {
                $createUseCase->execute(
                    nombre: $this->nombre,
                    descripcion: $this->descripcion
                );
                $this->dispatch('notify', ['message' => 'Maniobra creada correctamente', 'type' => 'success']);
            }

            $this->dispatch('close-modal', 'maniobra-modal');
            $this->dispatch('maniobra-saved');

        } catch (Exception $e) {
            $this->addError('form', $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.maniobras.maniobra-form-modal');
    }
}
