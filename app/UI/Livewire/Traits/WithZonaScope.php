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

    public function getZonaNombreUsuarioProperty(): string
    {
        $context = session()->get('usuario_contexto');
        if ($context instanceof UsuarioContexto && isset($context->zonaNombre) && !empty($context->zonaNombre)) {
            return $context->zonaNombre;
        }
        
        return $this->zonaUsuario;
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
