<?php

namespace App\UI\Livewire\Maniobras;

use App\Domain\Maniobra\ListManiobrasUseCase;
use Livewire\Component;
use Livewire\WithPagination;

class ManiobraIndex extends Component
{
    use WithPagination;

    public string $search = '';

    protected $listeners = [
        'maniobra-saved' => '$refresh',
    ];

    public function mount()
    {
        $context = session()->get('usuario_contexto');
        if (!$context || $context->tipo !== 'AM') {
            abort(403, 'No tienes permisos para acceder a esta sección.');
        }
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function render(ListManiobrasUseCase $useCase)
    {
        // Dado que el repositorio retorna un array, deberíamos paginar manualmente o retornar colección.
        // Por simplicidad en la UI, pasamos todo el arreglo. Si crece, deberíamos hacer paginación en SQL.
        $maniobras = $useCase->execute($this->search);

        return view('livewire.maniobras.maniobra-index', [
            'maniobras' => $maniobras
        ])->layout('layouts.app', ['title' => 'Catálogo de Maniobras']);
    }
}
