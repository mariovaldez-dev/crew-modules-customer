<?php

namespace App\UI\Livewire\Maniobras;

use App\Domain\Maniobra\ListManiobrasUseCase;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Component;
use Livewire\WithPagination;

class ManiobraIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public bool $readyToLoad = false;

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

    public function loadData()
    {
        $this->readyToLoad = true;
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function render(ListManiobrasUseCase $useCase)
    {
        if ($this->readyToLoad) {
            session()->save(); // Libera la sesión durante la consulta lenta
        }
        
        $items = $this->readyToLoad ? $useCase->execute($this->search) : [];

        $currentPage = $this->paginators['page'] ?? 1;
        $perPage = 10;

        $paginated = new LengthAwarePaginator(
            array_slice($items, ($currentPage - 1) * $perPage, $perPage),
            count($items),
            $perPage,
            $currentPage,
            ['path' => LengthAwarePaginator::resolveCurrentPath()]
        );

        return view('livewire.maniobras.maniobra-index', [
            'maniobras' => $paginated
        ])->layout('layouts.app', ['title' => 'Maniobras']);
    }
}
