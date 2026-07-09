<?php

namespace App\UI\Livewire\Traits;

trait WithTableFilters
{
    public string $search = '';
    public array $filters = [];

    public function updatedSearch()
    {
        if (method_exists($this, 'resetPage')) {
            $this->resetPage();
        }
    }

    public function updatedFilters()
    {
        if (method_exists($this, 'resetPage')) {
            $this->resetPage();
        }
    }

    public function resetFilters()
    {
        $this->search = '';
        $this->filters = [];
        
        if (method_exists($this, 'resetPage')) {
            $this->resetPage();
        }
    }
}
