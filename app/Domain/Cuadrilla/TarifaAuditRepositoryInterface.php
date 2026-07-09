<?php

namespace App\Domain\Cuadrilla;

interface TarifaAuditRepositoryInterface
{
    public function record(TarifaCambioDTO $cambio): void;
}
