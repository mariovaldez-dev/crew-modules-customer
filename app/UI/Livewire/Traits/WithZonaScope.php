<?php

namespace App\UI\Livewire\Traits;

use App\Domain\Shared\UsuarioContexto;

trait WithZonaScope
{
    public function getZonaUsuarioProperty(): string
    {
        $context = session()->get('usuario_contexto');
        if ($context instanceof UsuarioContexto) {
            return $context->zona;
        }
        
        return 'FA';
    }

    public function getRolUsuarioProperty(): string
    {
        $context = session()->get('usuario_contexto');
        if ($context instanceof UsuarioContexto) {
            return $context->tipo;
        }
        
        return 'CO';
    }
}
